<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    /**
     * Landing page: jobs + ringkasan & progres lamaran user.
     */
    public function __invoke(Request $request)
    {
        // === Jobs (public) ===
        $jobs = Job::query()
            ->with(['site:id,code,name'])
            ->latest()
            ->paginate(9, ['*'], 'jobs_page');

        // Default untuk guest
        $myApps         = collect();
        $myAppsSummary  = [
            'total'    => 0,
            'byStatus' => collect([
                'SUBMITTED'=>0,'SCREENING'=>0,'INTERVIEW'=>0,'OFFERED'=>0,'HIRED'=>0,'not_qualified'=>0,
            ]),
        ];
        $myAppsProgress = collect();

        // === Only if authenticated ===
        $userId = auth()->id(); // <— AMAN: null kalau belum login (tanpa akses ->id)
        if ($userId) {
            $myApps = JobApplication::with([
                    'job:id,title,site_id,division,level',
                    'job.site:id,code,name',
                    'stages' => fn($q) => $q->orderBy('created_at', 'asc'),
                ])
                ->where('user_id', $userId)   // <— langsung pakai $userId
                ->latest()
                ->take(6)
                ->get();

            $myAppsSummary = $this->buildSummary($userId);

            // Hitung progres tiap aplikasi
            $myAppsProgress = $myApps->map(function (JobApplication $app) {
                return $this->decorateProgress($app);
            })->keyBy('application_id');
        }

        return view('welcome', compact('jobs', 'myApps', 'myAppsSummary', 'myAppsProgress'));
    }

    /** Urutan & label tahapan. */
    protected function stagePipeline(): array
    {
        return [
            'SUBMITTED' => ['step_no'=>1,'label'=>'Lamaran Masuk','hint'=>'Menunggu screening HR.'],
            'SCREENING' => ['step_no'=>2,'label'=>'Screening HR','hint'=>'HR meninjau CV & profil.'],
            'INTERVIEW' => ['step_no'=>3,'label'=>'Interview','hint'=>'Siapkan sesi interview.'],
            'OFFERED'   => ['step_no'=>4,'label'=>'Offering Letter','hint'=>'Cek & konfirmasi penawaran.'],
            'HIRED'     => ['step_no'=>5,'label'=>'Diterima','hint'=>'Selamat! Lanjut onboarding.'],
            'not_qualified'  => ['step_no'=>0,'label'=>'Tidak Lolos','hint'=>'Tetap semangat—coba lowongan lain.'],
        ];
    }

    /** Ringkasan total & per-status. */
    protected function buildSummary(string|int $userId): array
    {
        $counts = JobApplication::query()
            ->selectRaw('overall_status, COUNT(*) as total')
            ->where('user_id', $userId)
            ->groupBy('overall_status')
            ->pluck('total', 'overall_status');

        $total = (int) $counts->sum();

        $keys = ['SUBMITTED','SCREENING','INTERVIEW','OFFERED','HIRED','not_qualified'];
        $byStatus = collect($keys)->mapWithKeys(fn($k) => [$k => (int) ($counts[$k] ?? 0)]);

        return ['total' => $total, 'byStatus' => $byStatus];
    }

    /** Hitung progres sebuah lamaran. */
    protected function decorateProgress(JobApplication $app): array
    {
        $pipeline   = $this->stagePipeline();
        $totalSteps = 5;

        // Tentukan stage aktif
        $currentStage = null;

        if ($app->relationLoaded('stages') && $app->stages->count() > 0) {
            $currentStage = strtoupper((string) $app->stages->last()->stage_key);
        }

        if (!$currentStage && $app->current_stage) {
            $currentStage = strtoupper((string) $app->current_stage);
        }

        if (!$currentStage && $app->overall_status) {
            $currentStage = strtoupper((string) $app->overall_status);
        }

        if (!$currentStage || !array_key_exists($currentStage, $pipeline)) {
            $currentStage = 'SUBMITTED';
        }

        $stepNo = $pipeline[$currentStage]['step_no'];
        $label  = $pipeline[$currentStage]['label'];
        $hint   = $pipeline[$currentStage]['hint'];

        $isnot_qualified = ($app->overall_status === 'not_qualified' || $currentStage === 'not_qualified');
        $isHired    = ($app->overall_status === 'HIRED'     || $currentStage === 'HIRED');

        if ($isnot_qualified) {
            $progressPct = 0;
            $nextLabel   = null;
            $isFinal     = true;
        } elseif ($isHired) {
            $progressPct = 100;
            $nextLabel   = null;
            $isFinal     = true;
        } else {
            $progressPct = (int) round(($stepNo / $totalSteps) * 100);
            $nextLabel   = $this->nextStageLabel($currentStage);
            $isFinal     = false;
        }

        return [
            'application_id'   => $app->id,
            'job_title'        => optional($app->job)->title,
            'site_code'        => optional($app->job?->site)->code,
            'current_stage'    => $currentStage,
            'current_label'    => $label,
            'progress_percent' => $progressPct,
            'next_stage_label' => $nextLabel,
            'is_final'         => $isnot_qualified || $isHired,
            'hint'             => $hint,
            'applied_at'       => optional($app->created_at)?->toDateString(),
        ];
    }

    /** Label tahap berikutnya. */
    protected function nextStageLabel(string $currentStage): ?string
    {
        $order = ['SUBMITTED','SCREENING','INTERVIEW','OFFERED','HIRED'];
        $pipeline = $this->stagePipeline();

        $idx = array_search($currentStage, $order, true);
        if ($idx === false) return $pipeline['SUBMITTED']['label'];
        $next = $order[$idx + 1] ?? null;
        return $next ? $pipeline[$next]['label'] : null;
    }
}
