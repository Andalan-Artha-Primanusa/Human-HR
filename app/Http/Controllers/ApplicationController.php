<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\JobApplication;
use App\Models\ApplicationStage;
use App\Models\PsychotestAttempt;
use App\Models\PsychotestTest;
use App\Models\CandidateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Carbon;

class ApplicationController extends Controller
{
    /**
     * === Canonical stage keys (SELARAS DENGAN BLADE/KANBAN) ===
     * applied, psychotest, hr_iv, user_iv, final, offer, hired, not_qualified
     */
    protected array $STAGES = [
        'applied','psychotest','hr_iv','user_iv','final','offer','hired','not_qualified',
    ];

    /** Alias masuk (kompatibel dgn istilah lama/beragam) -> dipetakan ke canonical */
    protected array $ALIASES_IN = [
        // lama -> canonical
        'apply'           => 'applied',
        'applied'         => 'applied',
        'psychotest'      => 'psychotest',
        'hr_iv'           => 'hr_iv',
        'hriv'            => 'hr_iv',
        'user_iv'         => 'user_iv',
        'useriv'          => 'user_iv',
        'final'           => 'final',
        'offering'        => 'offer',
        'offer'           => 'offer',
        'diterima'        => 'hired',
        'hired'           => 'hired',
        'rejected'        => 'not_qualified',
        'not_qualified'   => 'not_qualified',
        'not-qualified'   => 'not_qualified',
        'notqualified'    => 'not_qualified',
        'final_interview' => 'final',
    ];

    /** Label cantik (opsional) */
    protected array $PRETTY = [
        'applied'       => 'Pengajuan Berkas',
        'psychotest'    => 'Psikotes',
        'hr_iv'         => 'HR Interview',
        'user_iv'       => 'User Interview',
        'final'         => 'Final',
        'offer'         => 'Offering',
        'hired'         => 'Diterima',
        'not_qualified' => 'Tidak Lolos',
    ];

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
                'current_stage'  => 'applied',     // canonical
                'overall_status' => 'active',      // SELARAS ENUM DB
            ]);

            ApplicationStage::create([
                'application_id' => $app->id,
                'stage_key'      => 'applied',     // canonical
                'status'         => 'pending',
                'score'          => null,
                'payload'        => ['note' => 'Initial application submitted'],
                'acted_by'       => $request->user()->id, // untuk “Diubah oleh”
                'user_id'        => $request->user()->id, // opsional: pembuat awal
                'notes'          => null,
            ]);

            // Pastikan profil kandidat ada
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

    /** @return array{string,string,?string,?float} */
    protected function validateMove(Request $request): array
    {
        $allowedStages = $this->STAGES;
        $allowedStatus = ['pending','passed','failed','no-show','reschedule'];

        $toRaw = $request->input('to') ?? $request->input('to_stage');

        $validated = $request->validate([
            'to'        => ['nullable', Rule::in(array_unique(array_merge($allowedStages, array_keys($this->ALIASES_IN))))],
            'to_stage'  => ['nullable', Rule::in(array_unique(array_merge($allowedStages, array_keys($this->ALIASES_IN))))],
            'status'    => ['nullable', Rule::in($allowedStatus)],
            'note'      => ['nullable', 'string'],
            'score'     => ['nullable', 'numeric'],
        ]);

        $to     = $this->normalizeStage($toRaw) ?: 'applied'; // canonical default
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
            $userId   = auth()->id(); // admin/owner yang mengubah
            $actor    = auth()->user()?->name ?? 'System';
            $from     = $application->current_stage; // stage sebelum diperbarui
            $prevOverall = $application->overall_status;

            // timeline: tulis canonical stage_key + actor info
            ApplicationStage::create([
                'application_id' => $application->id,
                'stage_key'      => $to,                 // canonical
                'status'         => $status ?: 'pending',
                'score'          => $score,
                'payload'        => ['note' => $note],
                'acted_by'       => $userId,            // penting utk “Diubah oleh”
                'user_id'        => $userId,            // opsional creator
                'notes'          => $note,
            ]);

            // current stage
            $application->update(['current_stage' => $to]);

            // overall status & headcount
            if ($to === 'hired') {
                $job = $application->job()->with('manpowerRequirement')->first();
                if ($job && $job->manpowerRequirement) {
                    $job->manpowerRequirement->increment('filled_headcount');
                    if ($job->manpowerRequirement->filled_headcount >= $job->manpowerRequirement->budget_headcount) {
                        $job->update(['status' => 'closed']);
                    }
                }
                $application->update(['overall_status' => 'hired']);
            } elseif ($to === 'not_qualified') {
                $application->update(['overall_status' => 'not_qualified']);
            } else {
                if ($application->overall_status !== 'active') {
                    $application->update(['overall_status' => 'active']);
                }
            }

            // === Khusus Psikotes: 1 lamaran x 1 tes = 1 attempt saja ===
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

            // =======================
            // NOTIFIKASI KE PELAMAR
            // =======================
            // Catatan: kita buat DatabaseNotification langsung (tanpa class Notification baru).
            // Ini aman untuk ditampilkan oleh UserNotificationController yang kamu punya.
            $appReload = $application->fresh(['job:id,title', 'user:id,name']);
            $jobTitle  = $appReload->job?->title ?? '—';
            $toPretty  = $this->PRETTY[$to] ?? strtoupper($to);
            $fromPretty= $from ? ($this->PRETTY[$from] ?? strtoupper($from)) : null;

            $title = 'Perubahan Proses Lamaran';
            // Pesan disesuaikan untuk kasus akhir
            if ($appReload->overall_status === 'hired' && $prevOverall !== 'hired') {
                $body = "Selamat! Status lamaran kamu untuk posisi \"{$jobTitle}\" berubah menjadi DITERIMA.";
            } elseif ($appReload->overall_status === 'not_qualified' && $prevOverall !== 'not_qualified') {
                $body = "Maaf, lamaran kamu untuk posisi \"{$jobTitle}\" tidak melanjutkan proses.";
            } else {
                $stagePart = $fromPretty ? "{$fromPretty} → {$toPretty}" : $toPretty;
                $body = "Tahap lamaran kamu untuk posisi \"{$jobTitle}\" diperbarui menjadi: {$stagePart}.";
            }

            // Link tujuan notif (kandidat melihat progres)
            $url = route('applications.mine');

            // Insert 1 baris ke tabel notifications
            DatabaseNotification::create([
                'id'              => (string) \Illuminate\Support\Str::uuid(),
                'type'            => 'app:application.stage_changed', // string bebas
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

    /** Redirect pintar setelah perpindahan stage (SEMUA stage) */
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
                    ->with('ok', 'Stage dipindah ke PSIKOTES.');

            case 'hr_iv':
            case 'user_iv':
            case 'final':
                return redirect()->route('admin.interviews.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke '.strtoupper($this->PRETTY[$to] ?? $to).'.');

            case 'offer':
                return redirect()->route('admin.offers.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke OFFERING.');

            case 'hired':
            case 'not_qualified':
                return redirect()->route('admin.applications.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke '.strtoupper($this->PRETTY[$to] ?? $to).'.');

            case 'applied':
            default:
                return redirect()->back(303)->with('ok', 'Stage dipindah ke: '.strtoupper($this->PRETTY[$to] ?? $to));
        }
    }
}
