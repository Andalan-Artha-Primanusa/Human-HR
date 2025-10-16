<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\ApplicationStage;
use App\Models\PsychotestAttempt;
use App\Models\PsychotestTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    /** Format stage standar (internal) */
    protected array $STAGES = [
        'apply','psychotest','hr_iv','user_iv','final','offering','diterima','not_qualified'
    ];

    /** Alias/kompatibilitas penamaan stage dari UI lama / Blade lain */
    protected array $ALIASES_IN = [
        'applied'        => 'apply',
        'offer'          => 'offering',
        'hired'          => 'diterima',
        'accepted'       => 'diterima',
        'rejected'       => 'not_qualified',
        'not-qualified'  => 'not_qualified',
        'notqualified'   => 'not_qualified',
        'final_interview'=> 'final',
        'useriv'         => 'user_iv',
        'hriv'           => 'hr_iv',
        'apply'          => 'apply',
        'psychotest'     => 'psychotest',
        'hr_iv'          => 'hr_iv',
        'user_iv'        => 'user_iv',
        'final'          => 'final',
        'offering'       => 'offering',
        'diterima'       => 'diterima',
        'not_qualified'  => 'not_qualified',
    ];

    /** Label cantik (opsional) */
    protected array $PRETTY = [
        'apply'          => 'Pengajuan Berkas',
        'psychotest'     => 'Psikotes',
        'hr_iv'          => 'HR Interview',
        'user_iv'        => 'User Interview',
        'final'          => 'Final',
        'offering'       => 'Offering',
        'diterima'       => 'Diterima',
        'not_qualified'  => 'Tidak Lolos',
    ];

    /** Pelamar: daftar lamaran saya */
    public function index(Request $request)
    {
        $apps = JobApplication::with([
                'job:id,title,division,site_id',
                'job.site:id,code,name',
                'stages',
            ])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('applications.mine', compact('apps'));
    }

    /** Pelamar: apply job */
    public function store(Request $request, Job $job)
    {
        abort_if($job->status !== 'open', 403, 'Job is not open');

        $exists = JobApplication::where('job_id', $job->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($exists) {
            return back()->with('warn', 'Kamu sudah melamar posisi ini.');
        }

        DB::transaction(function () use ($request, $job) {
            /** @var JobApplication $app */
            $app = JobApplication::create([
                'job_id'         => $job->id,
                'user_id'        => $request->user()->id,
                'current_stage'  => 'apply',
                'overall_status' => 'active',
            ]);

            ApplicationStage::create([
                'application_id' => $app->id,
                'stage_key'      => 'apply',
                'status'         => 'pending',
                'payload'        => ['note' => 'Initial application submitted'],
            ]);
        });

        return redirect()->route('applications.mine')->with('ok', 'Lamaran terkirim.');
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
                'stages',
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
            $key = $this->normalizeStage($a->current_stage) ?: 'apply';
            if (!in_array($key, $stages, true)) $key = 'apply';
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
            'id' => ['required', 'uuid', 'exists:job_applications,id'], // UUID, bukan integer
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

        $to     = $this->normalizeStage($toRaw) ?: 'apply';
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
            // timeline
            ApplicationStage::create([
                'application_id' => $application->id,
                'stage_key'      => $to,
                'status'         => $status ?: 'pending',
                'score'          => $score,
                'payload'        => ['note' => $note],
            ]);

            // current stage
            $application->update(['current_stage' => $to]);

            // overall status & headcount
            if ($to === 'diterima') {
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
                // Ambil test aktif atau auto-create
                $test = PsychotestTest::where('is_active', true)->latest('updated_at')->first();
                if (!$test) {
                    $test = PsychotestTest::create([
                        'name'             => 'Tes Dasar (Auto)',
                        'duration_minutes' => 20,
                        'scoring'          => ['pass_ratio' => 0.6],
                        'is_active'        => true,
                    ]);
                }

                // Reuse attempt jika sudah ada
                $attempt = PsychotestAttempt::where('application_id', $application->id)
                    ->where('test_id', $test->id)
                    ->first();

                // Kalau belum ada, buat satu attempt
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

            case 'offering':
                return redirect()->route('admin.offers.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke OFFERING.');

            case 'diterima':
            case 'not_qualified':
                return redirect()->route('admin.applications.index', ['focus' => $application->id])
                    ->with('ok', 'Stage dipindah ke '.strtoupper($this->PRETTY[$to] ?? $to).'.');

            case 'apply':
            default:
                return redirect()->back(303)->with('ok', 'Stage dipindah ke: '.strtoupper($this->PRETTY[$to] ?? $to));
        }
    }
}
