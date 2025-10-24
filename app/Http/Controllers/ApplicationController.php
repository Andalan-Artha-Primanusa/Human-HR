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
        'apply' => 'applied','applied' => 'applied',

        // screening / seleksi berkas
        'screening' => 'screening',
        'screening_cv' => 'screening','screening-berkas' => 'screening',
        'screening_berkas' => 'screening','seleksi_berkas' => 'screening',

        // psikotes
        'psychotest' => 'psychotest','psikotest' => 'psychotest',

        // interview
        'hr_iv' => 'hr_iv','hriv'=>'hr_iv','hr-interview' => 'hr_iv',
        'user_iv'=>'user_iv','useriv'=>'user_iv','user-interview' => 'user_iv',
        'user_trainer_iv' => 'user_trainer_iv','user-trainer' => 'user_trainer_iv',
        'trainer_iv' => 'user_trainer_iv','trainer-interview' => 'user_trainer_iv',

        // offering letter
        'offering'=>'offer','offer'=>'offer','ol' => 'offer',

        // medis & mobilisasi
        'mcu' => 'mcu','medical_checkup' => 'mcu',
        'mobilisasi' => 'mobilisasi','mobilization' => 'mobilisasi',

        // ground test
        'ground_test' => 'ground_test','ground-test' => 'ground_test',

        // hasil akhir
        'diterima'=>'hired','hired'=>'hired',
        'rejected'=>'not_qualified','not_qualified'=>'not_qualified','not-qualified'=>'not_qualified','notqualified'=>'not_qualified',

        // kompat lama (tidak tampil sebagai kolom, tapi masih diterima)
        'final'=>'user_iv','final_interview'=>'user_iv',
    ];

    /** Label cantik (untuk UI) */
    protected array $PRETTY = [
        'applied'         => 'Applied',
        'screening'       => 'Screening CV/Berkas Lamaran',
        'psychotest'      => 'Psikotest',
        'hr_iv'           => 'HR Interview',
        'user_iv'         => 'User Interview',
        'user_trainer_iv' => 'User/Trainer Interview',
        'offer'           => 'OL',
        'mcu'             => 'MCU',
        'mobilisasi'      => 'Mobilisasi',
        'ground_test'     => 'Ground Test',
        'hired'           => 'Hired',
        'not_qualified'   => 'Tidak Lolos',
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
            ->paginate(12);

        return view('applications.mine', compact('apps'));
    }

    /** Pelamar: apply job -> redirect ke wizard profile */
    public function store(Request $request, Job $job)
    {
        abort_if($job->status !== 'open', 403, 'Job is not open');

        $already = JobApplication::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($already) {
            return redirect()
                ->route('candidate.profiles.edit', ['job' => $job->id])
                ->with('info', 'Kamu sudah melamar. Silakan lengkapi/cek data profil.');
        }

        DB::transaction(function () use ($request, $job) {
            /** @var JobApplication $app */
            $app = JobApplication::create([
                'job_id'         => $job->id,
                'user_id'        => $request->user()->id,
                'current_stage'  => 'applied',
                'overall_status' => 'active',
            ]);

            ApplicationStage::create([
                'application_id' => $app->id,
                'stage_key'      => 'applied',
                'status'         => 'pending',
                'score'          => null,
                'payload'        => ['note' => 'Initial application submitted'],
                'acted_by'       => $request->user()->id,
                'user_id'        => $request->user()->id,
                'notes'          => null,
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
        $q     = (string) $request->query('q', '');
        $stage = (string) $request->query('stage', '');
        $site  = (string) $request->query('site', '');

        $stage = $this->normalizeStage($stage);

        $apps = JobApplication::query()
            ->with([
                'job:id,title,division,site_id',
                'job.site:id,code,name',
                'user:id,name',
                'stages.actor:id,name',
                'stages.user:id,name',
            ])
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"))
                      ->orWhereHas('job', function ($j) use ($q) {
                          $j->where('title', 'like', "%{$q}%")
                            ->orWhere('division', 'like', "%{$q}%")
                            ->orWhereHas('site', fn ($s) => $s->where('code', 'like', "%{$q}%"));
                      });
                });
            })
            ->when($stage, fn ($qq) => $qq->where('current_stage', $stage))
            ->when($site,  fn ($qq) => $qq->whereHas('job.site', fn ($s) => $s->where('code', $site)))
            ->latest()
            ->paginate(15);

        return view('admin.applications.index', compact('apps'));
    }

    /** Admin: Kanban board */
    public function board(Request $request)
    {
        $stages = $this->STAGES;

        $apps = JobApplication::with([
                'job:id,title,division,site_id',
                'job.site:id,code,name',
                'user:id,name',
                'stages.actor:id,name',
                'stages.user:id,name',
            ])
            ->when($request->filled('job_id'), fn ($q) => $q->where('job_id', $request->job_id))
            ->when($request->filled('only'),  function ($q) use ($request) {
                $only = collect(explode(',', $request->only))
                    ->map(fn($s) => $this->normalizeStage($s))
                    ->filter()
                    ->values()
                    ->all();
                return $q->whereIn('current_stage', $only);
            })
            ->orderBy('created_at')
            ->get();

        $grouped = collect($stages)->mapWithKeys(fn($s) => [$s => collect()]);
        foreach ($apps as $a) {
            $key = $this->normalizeStage($a->current_stage) ?: 'applied';
            if (!in_array($key, $stages, true)) $key = 'applied';
            $grouped[$key]->push($a);
        }

        return view('admin.applications.board', compact('stages','grouped'));
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
            'ok'       => true,
            'moved_to' => $to,
            'id'       => $application->id,
            'attempt'  => $attempt?->id ?? null,
        ]);
    }

    /* ============================== Helpers ============================== */

    protected function normalizeStage(?string $s): ?string
    {
        if (!$s) return null;
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
        $fromIdx  = $this->stageIndex($from);
        $toIdx    = $this->stageIndex($to);
        if ($offerIdx < 0) return false;
        if ($fromIdx < 0)  return false;
        if ($toIdx   < 0)  return false;
        return $toIdx < $offerIdx && $fromIdx >= $offerIdx;
    }

    /** Hapus Offer + file PDF (jika ada tersimpan) */
    protected function purgeOffer(JobApplication $application): void
    {
        /** @var Offer|null $offer */
        $offer = $application->offer()->first();
        if (!$offer) return;

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
        $allowedStatus = ['pending','passed','failed','no-show','reschedule'];

        $toRaw = $request->input('to') ?? $request->input('to_stage');

        $validated = $request->validate([
            'to'        => ['nullable', Rule::in($allowedStages)],
            'to_stage'  => ['nullable', Rule::in($allowedStages)],
            'status'    => ['nullable', Rule::in($allowedStatus)],
            'note'      => ['nullable', 'string'],
            'score'     => ['nullable', 'numeric'],
        ]);

        $to     = $this->normalizeStage($toRaw) ?: 'applied';
        $status = $validated['status'] ?? 'pending';
        $note   = $validated['note']   ?? null;
        $score  = isset($validated['score']) ? (float) $validated['score'] : null;

        return [$to, $status, $note, $score];
    }

    /**
     * Terapkan perpindahan stage + side-effects (atomic)
     * return: attempt psikotes (jika dibuat), selain itu null
     */
    protected function applyTransition(JobApplication $application, string $to, string $status = 'pending', ?string $note = null, ?float $score = null)
    {
        $attempt = null;

        DB::transaction(function () use ($application, $to, $status, $note, $score, &$attempt) {
            $userId   = auth()->id();
            $actor    = auth()->user()?->name ?? 'System';
            $from     = $application->current_stage;
            $prevOverall = $application->overall_status;

            // === Jika mundur ke sebelum 'offer', hapus Offer + file ===
            if ($this->isBackwardBeforeOffer($from, $to)) {
                $this->purgeOffer($application);
                $this->offerJustCreated = null; // reset
            }

            // timeline
            ApplicationStage::create([
                'application_id' => $application->id,
                'stage_key'      => $to,
                'status'         => $status ?: 'pending',
                'score'          => $score,
                'payload'        => ['note' => $note],
                'acted_by'       => $userId,
                'user_id'        => $userId,
                'notes'          => $note,
            ]);

            // current stage
            $application->update(['current_stage' => $to]);

            // === OFFER: buat 1x jika masuk ke 'offer' atau 'hired' (idempotent) ===
            if (in_array($to, ['offer', 'hired'], true)) {
                /** @var \App\Models\Offer|null $existing */
                $existing = $application->offer()->first();

                if (!$existing) {
                    // (opsional) prefill dari job
                    $job   = $application->job()->with(['site:id,code'])->first();
                    $gross = (float) ($job->default_gross     ?? 0);
                    $allow = (float) ($job->default_allowance ?? 0);

                    $existing = $application->offer()->create([
                        'status'        => 'draft',
                        'salary'        => ['gross' => $gross, 'allowance' => $allow],
                        'body_template' => null, // gunakan Blade offers.pdf
                        'meta'          => [
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
                $job = $application->job()->with('manpowerRequirement','site:id,code','company:id,code')->first();
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
                    // GUNAKAN Algoritma 1; ganti ke 2 jika ingin kebalikannya
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
                        'name'             => 'Tes Dasar (Auto)',
                        'duration_minutes' => 20,
                        'scoring'          => ['pass_ratio' => 0.6],
                        'is_active'        => true,
                    ]);
                }

                $attempt = PsychotestAttempt::where('application_id', $application->id)
                    ->where('test_id', $test->id)
                    ->first();

                if (!$attempt) {
                    $attempt = PsychotestAttempt::create([
                        'application_id' => $application->id,
                        'test_id'        => $test->id,
                        'user_id'        => $application->user_id,
                        'attempt_no'     => 1,
                        'status'         => 'pending',
                        'is_active'      => true,
                        'started_at'     => null,
                        'submitted_at'   => null,
                        'expires_at'     => now()->addDays(3),
                        'score'          => null,
                        'meta'           => ['note' => 'auto-created on stage move'],
                    ]);
                }
            }

            // Notifikasi ke pelamar
            $appReload = $application->fresh(['job:id,title', 'user:id,name']);
            $jobTitle  = $appReload->job?->title ?? '—';
            $toPretty  = $this->PRETTY[$to] ?? strtoupper($to);
            $fromPretty= $from ? ($this->PRETTY[$from] ?? strtoupper($from)) : null;

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
                'id'              => (string) Str::uuid(),
                'type'            => 'app:application.stage_changed',
                'notifiable_type' => User::class,
                'notifiable_id'   => $appReload->user_id,
                'data'            => [
                    'title'           => $title,
                    'body'            => $body,
                    'job_title'       => $jobTitle,
                    'application_id'  => $appReload->id,
                    'job_id'          => $appReload->job_id,
                    'stage_from'      => $from,
                    'stage_to'        => $to,
                    'stage_from_label'=> $fromPretty,
                    'stage_to_label'  => $toPretty,
                    'overall_status'  => $appReload->overall_status,
                    'status_label'    => strtoupper($appReload->overall_status ?? 'ACTIVE'),
                    'actor_id'        => $userId,
                    'actor_name'      => $actor,
                    'note'            => $note,
                    'score'           => $score,
                    'when_wib'        => Carbon::now('Asia/Jakarta')->format('d M Y, H:i').' WIB',
                    'url'             => $url,
                ],
                'read_at'         => null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        });

        return $attempt;
    }

    /** Redirect pintar setelah perpindahan stage */
    protected function redirectAfterMove(Request $request, JobApplication $application, string $to, $attempt = null)
    {
        $isAdmin = $request->user()
            && (method_exists($request->user(), 'hasAnyRole')
                ? $request->user()->hasAnyRole(['hr','superadmin'])
                : in_array($request->user()->role ?? null, ['hr','superadmin'], true));

        $isOwner = $request->user() && (string)$request->user()->id === (string)$application->user_id;

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
                return redirect()->route('admin.interviews.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke '.strtoupper($this->PRETTY[$to] ?? $to).'.');

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
                    ->with('ok', 'Stage dipindah ke '.strtoupper($this->PRETTY[$to] ?? $to).'.');

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
                    ->with('ok', 'Stage dipindah ke '.strtoupper($this->PRETTY[$to] ?? $to).'.');

            case 'applied':
            case 'screening':
            default:
                return redirect()->back(303)->with('ok', 'Stage dipindah ke: '.strtoupper($this->PRETTY[$to] ?? $to));
        }
    }

    /* ========================== UTIL: NIK ========================== */

    /**
     * Buat NIK sesuai skema:
     *  - Algoritma 1: {CompanyLetter}{SiteNumber}{YY}{MM}{SEQ4}
     *  - Algoritma 2: {SiteNumber}{CompanyLetter}{YY}{MM}{SEQ4}
     * - SEQ diambil dari database, direset per tahun berjalan (YY).
     */
    protected function makeNik(Job $job, int $algo = 1): string
    {
        // 1) Kode perusahaan (1 huruf)
        $companyLetter = strtoupper(substr((string)($job->company?->code ?? ''), 0, 1)) ?: 'X';

        // 2) Kode site (angka) — ambil dari kolom yang tersedia di tabel sites
        $site = $job->site;
        $siteNumber =
            (string)($site->nik_code
                ?? $site->code_site
                ?? $site->nik_number
                ?? $site->code_number
                ?? '0');

        // 3) Tahun & bulan masuk = saat di-HIRED
        $yy = now()->format('y');
        $mm = now()->format('m');

        // 4) Dapatkan nomor urut (reset per tahun); cari max seq dari semua NIK yang YY-nya sama.
        //    Posisi YY selalu di karakter ke-3..4 untuk kedua algoritma.
        $candidates = User::query()
            ->whereRaw('LENGTH(id_employe) >= 9')                 // minimal panjang wajar
            ->whereRaw('SUBSTRING(id_employe,3,2) = ?', [$yy])    // filter by YY
            ->pluck('id_employe');

        $maxSeq = 0;
        foreach ($candidates as $nik) {
            // match kedua algoritma (huruf/angka di posisi 1-2), lalu YYMM + 4 digit di akhir
            if (preg_match('/^[A-Z]\d\d{2}\d{2}\d{4}$/', $nik) || preg_match('/^\d[A-Z]\d{2}\d{2}\d{4}$/', $nik)) {
                $seq = (int) substr($nik, -4);
                if ($seq > $maxSeq) $maxSeq = $seq;
            }
        }
        $nextSeq = str_pad((string)($maxSeq + 1), 4, '0', STR_PAD_LEFT);

        // 5) Susun NIK
        if ($algo === 2) {
            return "{$siteNumber}{$companyLetter}{$yy}{$mm}{$nextSeq}";
        }
        // default Algoritma 1
        return "{$companyLetter}{$siteNumber}{$yy}{$mm}{$nextSeq}";
    }

    /**
     * (Opsional) Generator lama — tidak dipakai lagi.
     * Dibiarkan bila masih ada referensi lama.
     */
    protected function makeEmployeeCode(string $prefix): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $prefix)) ?: 'EMP';
        $period = now()->format('ym'); // YYMM
        $rand   = strtoupper(Str::random(5));
        return "{$prefix}-{$period}-{$rand}";
    }
}
