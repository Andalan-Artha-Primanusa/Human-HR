<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Offer; // +++
use App\Models\JobApplication;
use App\Models\ApplicationStage;
use App\Models\PsychotestAttempt;
use App\Models\PsychotestTest;
use App\Models\CandidateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; // +++ untuk hapus file
use Illuminate\Validation\Rule;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    /**
     * === Canonical stage keys (SELARAS DENGAN BLADE/KANBAN) ===
     * HO:   Applied → Screening → Psikotest → HR Interview → User Interview → OL → Hired
     * Site: (Staff) sama, ditambah MCU → Mobilisasi sebelum Hired
     *       (Non-Staff Equipment/Non-Equipment) tanpa Psikotest, tambah User/Trainer Interview + Ground Test
     *
     * Kolom Kanban disatukan agar bisa menampung semua jalur: 
     * applied, screening, psychotest, hr_iv, user_iv, user_trainer_iv, offer(OL), mcu, mobilisasi, ground_test, hired, not_qualified
     */
    protected array $STAGES = [
        'applied',
        'screening',
        'psychotest',
        'hr_iv',
        'user_iv',
        'user_trainer_iv',
        'offer',        // tampil sebagai "OL"
        'mcu',
        'mobilisasi',
        'ground_test',
        'hired',
        'not_qualified',
    ];

    /** Alias masuk -> canonical (biar input bebas, key-nya konsisten) */
    protected array $ALIASES_IN = [
        // dasar
        'apply' => 'applied',
        'applied' => 'applied',

        // screening / seleksi berkas
        'screening' => 'screening',
        'screening_cv' => 'screening',
        'screening-berkas' => 'screening',
        'screening_berkas' => 'screening',
        'seleksi_berkas' => 'screening',

        // psikotes
        'psychotest' => 'psychotest',
        'psikotest' => 'psychotest',

        // interview
        'hr_iv' => 'hr_iv',
        'hriv' => 'hr_iv',
        'hr-interview' => 'hr_iv',
        'user_iv' => 'user_iv',
        'useriv' => 'user_iv',
        'user-interview' => 'user_iv',
        'user_trainer_iv' => 'user_trainer_iv',
        'user-trainer' => 'user_trainer_iv',
        'trainer_iv' => 'user_trainer_iv',
        'trainer-interview' => 'user_trainer_iv',

        // offering letter
        'offering' => 'offer',
        'offer' => 'offer',
        'ol' => 'offer',

        // medis & mobilisasi
        'mcu' => 'mcu',
        'medical_checkup' => 'mcu',
        'mobilisasi' => 'mobilisasi',
        'mobilization' => 'mobilisasi',

        // ground test
        'ground_test' => 'ground_test',
        'ground-test' => 'ground_test',

        // hasil akhir
        'diterima' => 'hired',
        'hired' => 'hired',
        'rejected' => 'not_qualified',
        'not_qualified' => 'not_qualified',
        'not-qualified' => 'not_qualified',
        'notqualified' => 'not_qualified',

        // kompat lama (tidak tampil sebagai kolom, tapi masih diterima)
        'final' => 'user_iv',
        'final_interview' => 'user_iv',
    ];

    /** Label cantik (untuk UI) */
    protected array $PRETTY = [
        'applied' => 'Applied',
        'screening' => 'Screening CV/Berkas Lamaran',
        'psychotest' => 'Psikotest',
        'hr_iv' => 'HR Interview',
        'user_iv' => 'User Interview',
        'user_trainer_iv' => 'User/Trainer Interview',
        'offer' => 'OL',
        'mcu' => 'MCU',
        'mobilisasi' => 'Mobilisasi',
        'ground_test' => 'Ground Test',
        'hired' => 'Hired',
        'not_qualified' => 'Tidak Lolos',
    ];

    /** Offer yang baru dibuat (untuk redirect ke PDF) */
    protected ?Offer $offerJustCreated = null; // +++

    /** Pelamar: daftar lamaran saya */
    public function index(Request $request)
    {
        $apps = JobApplication::with([
            'job:id,title,division,site_id',
            'job.site:id,code,name',
            'stages.actor:id,name',
            'stages.user:id,name',
        ])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12);

        return view('applications.mine', compact('apps'));
    }

    /** Pelamar: apply job -> redirect ke wizard profile */
    public function store(Request $request, Job $job)
    {
        abort_if($job->status !== 'open', 403, 'Job is not open');

        $already = JobApplication::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($already) {
            // Sudah pernah melamar, langsung redirect ke profil (tanpa isi form lagi)
            return redirect()
                ->route('candidate.profiles.edit', ['job' => $job->id])
                ->with('info', 'Kamu sudah melamar. Silakan lengkapi/cek data profil.');
        }

        $data = $request->validate([
            'poh_id' => ['nullable', 'uuid', 'exists:pohs,id'],
        ]);

        DB::transaction(function () use ($request, $job, $data) {
            /** @var JobApplication $app */
            $app = JobApplication::create([
                'job_id' => $job->id,
                'user_id' => $request->user()->id,
                'poh_id' => $data['poh_id'] ?? null,
                'current_stage' => 'applied',
                'overall_status' => 'active',
            ]);

            ApplicationStage::create([
                'application_id' => $app->id,
                'stage_key' => 'applied',
                'status' => 'pending',
                'score' => null,
                'payload' => ['note' => 'Initial application submitted'],
                'acted_by' => $request->user()->id,
                'user_id' => $request->user()->id,
                'notes' => null,
            ]);

            CandidateProfile::firstOrCreate(
                ['user_id' => $request->user()->id],
                ['full_name' => $request->user()->name]
            );
        });

        return redirect()
            ->route('candidate.profiles.edit', ['job' => $job->id])
            ->with('success', 'Lamaran dibuat. Lengkapi data profil & riwayat kamu ya.');
    }

    /** Admin: daftar semua aplikasi + filter */
    public function adminIndex(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'stage' => ['nullable', 'string', 'max:50'],
            'site' => ['nullable', 'string', 'max:50'],
        ]);

        $qRaw = (string) ($filters['q'] ?? '');
        $q = Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '', 120, '');
        $like = $q !== '' ? '%' . addcslashes($q, '\\%_') . '%' : null;
        $stage = (string) ($filters['stage'] ?? '');
        $site = (string) ($filters['site'] ?? '');

        $stage = $this->normalizeStage($stage);

        $apps = JobApplication::query()
            ->with([
                'job:id,title,division,site_id',
                'job.site:id,code,name',
                'user:id,name',
                'stages.actor:id,name',
                'stages.user:id,name',
            ])
            ->when($like !== null, function ($qq) use ($like) {
                $qq->where(function ($w) use ($like) {
                    $w->whereHas('user', fn($u) => $u->where('name', 'like', $like))
                        ->orWhereHas('job', function ($j) use ($like) {
                            $j->where('title', 'like', $like)
                                ->orWhere('division', 'like', $like)
                                ->orWhereHas('site', fn($s) => $s->where('code', 'like', $like));
                        });
                });
            })
            ->when($stage, fn($qq) => $qq->where('current_stage', $stage))
            ->when($site, fn($qq) => $qq->whereHas('job.site', fn($s) => $s->where('code', $site)))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.applications.index', compact('apps'));
    }

    /** Kanban board: admin, hr, karyawan, pelamar, trainer */
    public function board(Request $request)
    {
        $user = auth()->user();
        $stages = $this->STAGES;

        // Jika ingin filter data berdasarkan role, bisa tambahkan di sini
        $query = JobApplication::with([
            'job:id,title,division,site_id',
            'job.site:id,code,name',
            'user:id,name',
            'stages.actor:id,name',
            'stages.user:id,name',
        ]);

        // Jika bukan admin/hr/superadmin, hanya tampilkan lamaran milik user
        if (!in_array($user->role, ['admin','hr','superadmin'])) {
            $query->where('user_id', $user->id);
        }

        $apps = $query
            ->when($request->filled('job_id'), fn($q) => $q->where('job_id', $request->job_id))
            ->when($request->filled('only'), function ($q) use ($request) {
                $only = collect(explode(',', $request->only))
                    ->map(fn($s) => $this->normalizeStage($s))
                    ->filter()
                    ->values()
                    ->all();
                return $q->whereIn('current_stage', $only);
            })
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $grouped = collect($stages)->mapWithKeys(fn($s) => [$s => collect()]);
        foreach ($apps as $a) {
            $key = $this->normalizeStage($a->current_stage) ?: 'applied';
            if (!in_array($key, $stages, true))
                $key = 'applied';
            $grouped[$key]->push($a);
        }

        return view('admin.applications.board', compact('stages', 'grouped'));
    }

    /** Admin: pindahkan stage aplikasi (via tombol/POST) */
    public function moveStage(Request $request, JobApplication $application)
    {
        [$to, $status, $note, $score] = $this->validateMove($request);
        $attempt = $this->applyTransition($application, $to, $status, $note, $score);
        return $this->redirectAfterMove($request, $application, $to, $attempt);
    }

    /** Admin: AJAX pindah stage dari Kanban */
    public function moveStageAjax(Request $request)
    {
        $request->validate([
            'id' => ['required', 'uuid', 'exists:job_applications,id'],
        ]);

        [$to, $status, $note, $score] = $this->validateMove($request);

        /** @var JobApplication $application */
        $application = JobApplication::with(['job.manpowerRequirement'])->findOrFail($request->input('id'));

        $attempt = $this->applyTransition($application, $to, $status, $note, $score);

        return response()->json([
            'ok' => true,
            'moved_to' => $to,
            'id' => $application->id,
            'attempt' => $attempt?->id ?? null,
        ]);
    }

    /* ============================== Helpers ============================== */

    protected function normalizeStage(?string $s): ?string
    {
        if (!$s)
            return null;
        $key = strtolower(trim($s));
        return $this->ALIASES_IN[$key] ?? (in_array($key, $this->STAGES, true) ? $key : null);
    }

    /** Urutan stage sebagai indeks numerik */
    protected function stageIndex(string $s): int
    {
        $idx = array_search($s, $this->STAGES, true);
        return $idx === false ? -1 : $idx;
    }

    /** Apakah perpindahan ini mundur ke sebelum 'offer'? */
    protected function isBackwardBeforeOffer(string $from, string $to): bool
    {
        $offerIdx = $this->stageIndex('offer');
        $fromIdx = $this->stageIndex($from);
        $toIdx = $this->stageIndex($to);
        if ($offerIdx < 0)
            return false;
        if ($fromIdx < 0)
            return false;
        if ($toIdx < 0)
            return false;
        return $toIdx < $offerIdx && $fromIdx >= $offerIdx;
    }

    /** Hapus Offer + file PDF (jika ada tersimpan) */
    protected function purgeOffer(JobApplication $application): void
    {
        /** @var Offer|null $offer */
        $offer = $application->offer()->first();
        if (!$offer)
            return;

        $this->purgeOfferFiles($offer);
        $offer->delete();
    }

    /** Hapus file-file fisik Offer jika ada (file_path atau meta[pdf_path]) */
    protected function purgeOfferFiles(Offer $offer): void
    {
        $paths = [];

        // skema 1: kolom langsung
        if (!empty($offer->file_path)) {
            $paths[] = $offer->file_path;
        }
        // skema 2: di meta
        $meta = is_array($offer->meta ?? null) ? $offer->meta : [];
        if (!empty($meta['pdf_path'])) {
            $paths[] = $meta['pdf_path'];
        }

        foreach ($paths as $p) {
            // asumsikan disk 'public'; ganti jika perlu
            if (Storage::disk('public')->exists($p)) {
                Storage::disk('public')->delete($p);
            }
        }
    }

    /** @return array{string,string,?string,?float} */
    protected function validateMove(Request $request): array
    {
        $allowedStages = array_unique(array_merge($this->STAGES, array_values($this->ALIASES_IN)));
        $allowedStatus = ['pending', 'passed', 'failed', 'no-show', 'reschedule'];

        $toRaw = $request->input('to') ?? $request->input('to_stage');

        $validated = $request->validate([
            'to' => ['nullable', Rule::in($allowedStages)],
            'to_stage' => ['nullable', Rule::in($allowedStages)],
            'status' => ['nullable', Rule::in($allowedStatus)],
            'note' => ['nullable', 'string'],
            'score' => ['nullable', 'numeric'],
            // Tambahan untuk feedback dan persetujuan
            'feedback_hr' => ['nullable', 'string'],
            'approve_hr' => ['nullable', 'in:yes,no'],
            'feedback_trainer' => ['nullable', 'string'],
            'approve_trainer' => ['nullable', 'in:yes,no'],
            'feedback_user' => ['nullable', 'string'],
            'approve_user' => ['nullable', 'in:yes,no'],
        ]);

        $to = $this->normalizeStage($toRaw) ?: 'applied';
        $status = $validated['status'] ?? 'pending';
        $note = $validated['note'] ?? null;
        $score = isset($validated['score']) ? (float) $validated['score'] : null;

        // Validasi wajib feedback dan persetujuan HR hanya saat PINDAH DARI hr_iv ke stage berikutnya
        $from = $request->input('from_stage') ?? $request->input('from') ?? $application->current_stage ?? null;
        if ($from === 'hr_iv' && $to !== 'hr_iv' && in_array(auth()->user()->role, ['admin','hr','superadmin'])) {
            if (empty($validated['feedback_hr']) || ($validated['approve_hr'] ?? null) === null) {
                abort(422, 'HR wajib mengisi feedback dan setuju/tidak setuju sebelum lanjut dari HR Interview.');
            }
        }

        // Validasi wajib feedback dan persetujuan trainer HANYA jika user login adalah trainer
        if (in_array($to, ['user_trainer_iv'], true) && auth()->user() && auth()->user()->role === 'trainer') {
            if (empty($validated['feedback_trainer']) || ($validated['approve_trainer'] ?? null) === null) {
                abort(422, 'Trainer wajib mengisi feedback dan setuju/tidak setuju sebelum lanjut.');
            }
        }

        // Validasi wajib feedback dan persetujuan user/karyawan
        // Hanya wajib jika memindahkan DARI user_iv ke user_trainer_iv dan user login adalah karyawan
        $from = $request->input('from_stage') ?? $request->input('from') ?? null;
        if ($from === 'user_iv' && $to === 'user_trainer_iv' && auth()->user() && auth()->user()->role === 'karyawan') {
            if (empty($validated['feedback_user']) || ($validated['approve_user'] ?? null) === null) {
                abort(422, 'Karyawan wajib mengisi feedback dan setuju/tidak setuju sebelum lanjut.');
            }
        }

        return [$to, $status, $note, $score];
    }

    /**
     * Terapkan perpindahan stage + side-effects (atomic)
     * return: attempt psikotes (jika dibuat), selain itu null
     */
    protected function applyTransition(JobApplication $application, string $to, string $status = 'pending', ?string $note = null, ?float $score = null)
    {
        $attempt = null;
        $actorUser = Auth::user();
        $userId = Auth::id();
        $actor = $actorUser?->name ?? 'System';

        DB::transaction(function () use ($application, $to, $status, $note, $score, &$attempt, $userId, $actor) {
            $from = $application->current_stage;
            $prevOverall = $application->overall_status;

            // === Jika mundur ke sebelum 'offer', hapus Offer + file ===
            if ($this->isBackwardBeforeOffer($from, $to)) {
                $this->purgeOffer($application);
                $this->offerJustCreated = null; // reset
            }

            // timeline
            ApplicationStage::create([
                'application_id' => $application->id,
                'stage_key' => $to,
                'status' => $status ?: 'pending',
                'score' => $score,
                'payload' => ['note' => $note],
                'acted_by' => $userId,
                'user_id' => $userId,
                'notes' => $note,
            ]);

            // Simpan feedback HR jika PINDAH DARI hr_iv ke stage lain
            $actorUser = \Auth::user();
            if ($from === 'hr_iv' && $to !== 'hr_iv' && in_array($actorUser?->role, ['admin','hr','superadmin'])) {
                $application->feedback_hr = request('feedback_hr');
                $application->approve_hr = request('approve_hr');
                $application->save();
                // Simpan juga ke tabel riwayat feedbacks
                if (request('feedback_hr') !== null || request('approve_hr') !== null) {
                    \App\Models\ApplicationFeedback::create([
                        'application_id' => $application->id,
                        'stage_key' => 'hr_iv',
                        'role' => 'hr',
                        'feedback' => request('feedback_hr'),
                        'approve' => request('approve_hr'),
                        'user_id' => $userId,
                    ]);
                }
            }
            // current stage
            $application->update(['current_stage' => $to]);

            // === OFFER: buat 1x jika masuk ke 'offer' atau 'hired' (idempotent) ===
            if (in_array($to, ['offer', 'hired'], true)) {
                /** @var \App\Models\Offer|null $existing */
                $existing = $application->offer()->first();

                if (!$existing) {
                    // (opsional) prefill dari job
                    $job = $application->job()->with(['site:id,code'])->first();
                    $gross = (float) ($job->default_gross ?? 0);
                    $allow = (float) ($job->default_allowance ?? 0);

                    $existing = $application->offer()->create([
                        'status' => 'draft',
                        'salary' => ['gross' => $gross, 'allowance' => $allow],
                        'body_template' => null, // gunakan Blade offers.pdf
                        'meta' => [
                            'job_title' => $job?->title,
                            'site_code' => $job?->site?->code,
                        ],
                    ]);
                }

                // simpan untuk redirect setelah transaksi
                $this->offerJustCreated = $existing;
            }

            // overall status & headcount
            if ($to === 'hired') {
                $job = $application->job()->with('manpowerRequirement', 'site:id,code', 'company:id,code')->first();
                if ($job && $job->manpowerRequirement) {
                    $job->manpowerRequirement->increment('filled_headcount');
                    if ($job->manpowerRequirement->filled_headcount >= $job->manpowerRequirement->budget_headcount) {
                        $job->update(['status' => 'closed']);
                    }
                }
                $application->update(['overall_status' => 'hired']);

                // === AUTO-GENERATE users.id_employe (sekali saja) ===
                $user = User::find($application->user_id);
                if ($user && empty($user->id_employe)) {
                    // Format NIK: {COMPANY3}{SITE2}{YY}{MM}{SEQ5}
                    $nik = $this->makeNik($job, 1);

                    // retry sederhana jika kebetulan tabrakan
                    $tries = 0;
                    while (User::where('id_employe', $nik)->exists() && $tries < 5) {
                        $nik = $this->makeNik($job, 1);
                        $tries++;
                    }

                    $user->forceFill(['id_employe' => $nik])->save();
                }
            } elseif ($to === 'not_qualified') {
                $application->update(['overall_status' => 'not_qualified']);
            } else {
                if ($application->overall_status !== 'active') {
                    $application->update(['overall_status' => 'active']);
                }
            }

            // Psikotes (auto attempt)
            if ($to === 'psychotest') {
                $test = PsychotestTest::where('is_active', true)->latest('updated_at')->first();
                if (!$test) {
                    $test = PsychotestTest::create([
                        'name' => 'Tes Dasar (Auto)',
                        'duration_minutes' => 20,
                        'scoring' => ['pass_ratio' => 0.6],
                        'is_active' => true,
                    ]);
                }

                $attempt = PsychotestAttempt::where('application_id', $application->id)
                    ->where('test_id', $test->id)
                    ->first();

                if (!$attempt) {
                    $attempt = PsychotestAttempt::create([
                        'application_id' => $application->id,
                        'test_id' => $test->id,
                        'user_id' => $application->user_id,
                        'attempt_no' => 1,
                        'status' => 'pending',
                        'is_active' => true,
                        'started_at' => null,
                        'submitted_at' => null,
                        'expires_at' => now()->addDays(3),
                        'score' => null,
                        'meta' => ['note' => 'auto-created on stage move'],
                    ]);
                }
            }

            // Notifikasi ke pelamar
            $appReload = $application->fresh(['job:id,title', 'user:id,name']);
            $jobTitle = $appReload->job?->title ?? '—';
            $toPretty = $this->PRETTY[$to] ?? strtoupper($to);
            $fromPretty = $from ? ($this->PRETTY[$from] ?? strtoupper($from)) : null;

            $title = 'Perubahan Proses Lamaran';
            if ($appReload->overall_status === 'hired' && $prevOverall !== 'hired') {
                $body = "Selamat! Status lamaran kamu untuk posisi \"{$jobTitle}\" berubah menjadi DITERIMA.";
            } elseif ($appReload->overall_status === 'not_qualified' && $prevOverall !== 'not_qualified') {
                $body = "Maaf, lamaran kamu untuk posisi \"{$jobTitle}\" tidak melanjutkan proses.";
            } else {
                $stagePart = $fromPretty ? "{$fromPretty} → {$toPretty}" : $toPretty;
                $body = "Tahap lamaran kamu untuk posisi \"{$jobTitle}\" diperbarui menjadi: {$stagePart}.";
            }

            $url = route('applications.mine');

            DatabaseNotification::create([
                'id' => (string) Str::uuid(),
                'type' => 'app:application.stage_changed',
                'notifiable_type' => User::class,
                'notifiable_id' => $appReload->user_id,
                'data' => [
                    'title' => $title,
                    'body' => $body,
                    'job_title' => $jobTitle,
                    'application_id' => $appReload->id,
                    'job_id' => $appReload->job_id,
                    'stage_from' => $from,
                    'stage_to' => $to,
                    'stage_from_label' => $fromPretty,
                    'stage_to_label' => $toPretty,
                    'overall_status' => $appReload->overall_status,
                    'status_label' => strtoupper($appReload->overall_status ?? 'ACTIVE'),
                    'actor_id' => $userId,
                    'actor_name' => $actor,
                    'note' => $note,
                    'score' => $score,
                    'when_wib' => Carbon::now('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                    'url' => $url,
                ],
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return $attempt;
    }

    /** Redirect pintar setelah perpindahan stage */
    protected function redirectAfterMove(Request $request, JobApplication $application, string $to, $attempt = null)
    {
        // Jika request expects JSON (AJAX/drag & drop), return JSON saja
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'moved_to' => $to,
                'id' => $application->id,
            ]);
        }

        $isOwner = $request->user() && (string) $request->user()->id === (string) $application->user_id;

        switch ($to) {
            case 'psychotest':
                if ($isOwner && $attempt) {
                    return redirect()->route('psychotest.show', $attempt)
                        ->with('ok', 'Silakan mulai Psikotes.');
                }
                return redirect()->route('admin.psychotests.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke PSIKOTEST.');

            case 'hr_iv':
            case 'user_iv':
            case 'user_trainer_iv':
                // Jika karyawan atau trainer, redirect ke Kanban Kandidat
                if (auth()->user() && in_array(auth()->user()->role, ['karyawan','trainer'])) {
                    return redirect()->route('kanban.mine')->with('ok', 'Feedback berhasil, stage dipindah ke ' . strtoupper($this->PRETTY[$to] ?? $to) . '.');
                }
                return redirect()->route('admin.interviews.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke ' . strtoupper($this->PRETTY[$to] ?? $to) . '.');

            case 'offer': {
                $offer = $this->offerJustCreated ?: $application->offer()->first();
                if ($offer) {
                    return redirect()->route('admin.offers.pdf', $offer)
                        ->with('ok', 'Stage dipindah ke OL. Menampilkan Offering Letter.');
                }
                return redirect()->route('admin.offers.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke OL.');
            }

            case 'mcu':
            case 'mobilisasi':
            case 'ground_test':
                return redirect()->route('admin.applications.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke ' . strtoupper($this->PRETTY[$to] ?? $to) . '.');

            case 'hired': {
                $offer = $this->offerJustCreated ?: $application->offer()->first();
                if ($offer) {
                    return redirect()->route('admin.offers.pdf', $offer)
                        ->with('ok', 'DITERIMA. Menampilkan Offering Letter.');
                }
                return redirect()->route('admin.applications.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke DITERIMA.');
            }

            case 'not_qualified':
                return redirect()->route('admin.applications.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke ' . strtoupper($this->PRETTY[$to] ?? $to) . '.');

            case 'applied':
            case 'screening':
            default:
                return redirect()->back(303)->with('ok', 'Stage dipindah ke: ' . strtoupper($this->PRETTY[$to] ?? $to));
        }
    }

    /* ========================== UTIL: NIK ========================== */

    /**
     * Buat NIK sesuai skema foto:
     * {COMPANY3}{SITE2}{YY}{MM}{SEQ5}
     *
     * Contoh: AAP06260400200
     */
    protected function makeNik(Job $job, int $algo = 1): string
    {
        // 1) Prefix perusahaan (3 karakter, uppercase alnum)
        $companyRaw = strtoupper((string) ($job->company?->code ?? ''));
        $companyRaw = preg_replace('/[^A-Z0-9]/', '', $companyRaw) ?? '';
        $company3 = str_pad(substr($companyRaw, 0, 3), 3, 'X');

        // 2) Kode site 2 digit
        $siteCodeRaw = strtoupper((string) ($job->site?->code ?? ''));
        $siteCodeRaw = preg_replace('/[^A-Z0-9]/', '', $siteCodeRaw) ?? '';

        // Mapping default berdasarkan format yang dibagikan user.
        $siteMap = [
            'HO' => '01',
            'BGG' => '02',
            'SBS' => '03',
            'DBK' => '04',
            'POS' => '05',
            'IBP' => '06',
        ];

        if (preg_match('/^\d{2}$/', $siteCodeRaw)) {
            $site2 = $siteCodeRaw;
        } else {
            $site2 = $siteMap[$siteCodeRaw] ?? '00';
        }

        // 3) Tahun & bulan masuk = saat status HIRED
        $yy = now()->format('y');
        $mm = now()->format('m');

        // 4) Nomor urut 5 digit, reset per tahun join (YY)
        $maxSeq = 0;
        User::query()
            ->select(['id', 'id_employe'])
            ->whereNotNull('id_employe')
            ->orderBy('id')
            ->chunkById(1000, function ($users) use (&$maxSeq, $yy) {
                foreach ($users as $user) {
                    $nik = $user->id_employe;
                    if (!is_string($nik)) {
                        continue;
                    }

                    // Format target: COMPANY3 + SITE2 + YY + MM + SEQ5
                    if (preg_match('/^[A-Z0-9]{3}\d{2}(\d{2})\d{2}(\d{5})$/', $nik, $m) !== 1) {
                        continue;
                    }

                    if (($m[1] ?? '') !== $yy) {
                        continue;
                    }

                    $seq = (int) ($m[2] ?? 0);
                    if ($seq > $maxSeq) {
                        $maxSeq = $seq;
                    }
                }
            }, 'id');

        $nextSeq = str_pad((string) ($maxSeq + 1), 5, '0', STR_PAD_LEFT);

        return "{$company3}{$site2}{$yy}{$mm}{$nextSeq}";
    }

    /**
     * (Opsional) Generator lama — tidak dipakai lagi.
     * Dibiarkan bila masih ada referensi lama.
     */
    protected function makeEmployeeCode(string $prefix): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $prefix)) ?: 'EMP';
        $period = now()->format('ym'); // YYMM
        $rand = strtoupper(Str::random(5));
        return "{$prefix}-{$period}-{$rand}";
    }
}
