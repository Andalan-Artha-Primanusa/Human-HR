<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class WelcomeController extends Controller
{
    /**
     * Landing page: jobs + ringkasan lamaran + daftar site sederhana + jumlah divisi (open).
     * - ORM hemat kolom & eager load ketat
     * - Micro-cache untuk data publik (sites & agregasi)
     * - Aman untuk user guest maupun login
     */
    public function __invoke(Request $request)
    {
        // ===== Jobs terbaru (public only, kolom minimal) =====
        $jobsQuery = Job::query()
            ->select(['id','title','site_id','created_at','status'])
            ->with(['site:id,code,name'])
            ->orderByDesc('created_at');

        // Tampilkan hanya yang "open" bila kolom status ada
        if (\Schema::hasColumn('jobs', 'status')) {
            $jobsQuery->where('status', 'open');
        }

        $jobs = $jobsQuery->paginate(9, ['*'], 'jobs_page')->withQueryString();

        // ===== Lokasi site (ikon + nama) – micro-cache 5 menit =====
        $sitesSimple = Cache::remember('welcome.sites_simple', 300, function () {
            return $this->loadSitesSimple()
                ->map(function (Site $s) {
                    $name = $s->name ?: ($s->code ?: 'Tanpa Nama');
                    $seed = $s->code ?: $name ?: (string) $s->id;
                    return [
                        'id'   => $s->id,
                        'name' => $name,
                        'dot'  => $this->colorFromString($seed),
                    ];
                });
        });

        // ===== Jumlah divisi (open only) – micro-cache 1 menit =====
        $byDivision = Cache::remember('welcome.open_by_division', 60, fn () => $this->countOpenByDivision());

        // ===== Default untuk guest =====
        $myApps         = collect();
        $myAppsSummary  = [
            'total'    => 0,
            'byStatus' => collect(['SUBMITTED'=>0,'SCREENING'=>0,'INTERVIEW'=>0,'OFFERED'=>0,'HIRED'=>0,'not_qualified'=>0]),
        ];
        $myAppsProgress = collect();

        // ===== Untuk user login: ambil lamaran terakhir + ringkasan =====
        if ($userId = auth()->id()) {
            $myApps = JobApplication::query()
                ->select(['id','job_id','user_id','current_stage','overall_status','created_at'])
                ->with([
                    'job:id,title,site_id,division,level',
                    'job.site:id,code,name',
                    'stages' => fn ($q) => $q->select(['id','application_id','stage_key','created_at'])->orderBy('created_at', 'asc'),
                ])
                ->where('user_id', $userId)
                ->orderByDesc('created_at')
                ->limit(6)
                ->get();

            $myAppsSummary  = $this->buildSummary($userId);
            $myAppsProgress = $myApps->map(fn ($app) => $this->decorateProgress($app))
                                     ->keyBy('application_id');
        }

        return view('welcome', compact(
            'jobs',
            'myApps',
            'myAppsSummary',
            'myAppsProgress',
            'sitesSimple',
            'byDivision'
        ));
    }

    /** Ambil daftar site minimal (id, code, name). */
    protected function loadSitesSimple(): Collection
    {
        return Site::query()
            ->select(['id','code','name'])
            ->orderBy('name')
            ->get();
    }

    /** Hitung jumlah lowongan open per divisi (global). */
    protected function countOpenByDivision(): Collection
    {
        $q = Job::query();

        if (\Schema::hasColumn('jobs', 'status')) {
            $q->where('status', 'open');
        }

        // Gunakan COALESCE + NULLIF agar NULL/'' -> "Tanpa Divisi"
        return $q->selectRaw("COALESCE(NULLIF(division, ''), 'Tanpa Divisi') AS div_name, COUNT(*) AS total")
                 ->groupBy('div_name')
                 ->orderByDesc('total')
                 ->get()
                 ->pluck('total', 'div_name');
    }

    /** Warna HSL konsisten dari string (untuk ikon dot). */
    protected function colorFromString(string $s): string
    {
        $hash = crc32($s);
        $h = $hash % 360; $sat = 70; $lum = 45;
        return "hsl($h, {$sat}%, {$lum}%)";
    }

    /* ===================== Progress Lamaran ===================== */

    protected function stagePipeline(): array
    {
        return [
            'SUBMITTED'      => ['step_no'=>1,'label'=>'Lamaran Masuk','hint'=>'Menunggu screening HR.'],
            'SCREENING'      => ['step_no'=>2,'label'=>'Screening HR','hint'=>'HR meninjau CV & profil.'],
            'INTERVIEW'      => ['step_no'=>3,'label'=>'Interview','hint'=>'Siapkan sesi interview.'],
            'OFFERED'        => ['step_no'=>4,'label'=>'Offering Letter','hint'=>'Cek & konfirmasi penawaran.'],
            'HIRED'          => ['step_no'=>5,'label'=>'Diterima','hint'=>'Selamat! Lanjut onboarding.'],
            'not_qualified'  => ['step_no'=>0,'label'=>'Tidak Lolos','hint'=>'Tetap semangat—coba lowongan lain.'],
        ];
    }

    /**
     * Ringkasan status lamaran user.
     * - Normalisasi key dari DB (lowercase seperti 'hired','not_qualified') ke pipeline uppercase.
     */
    protected function buildSummary(string|int $userId): array
    {
        $counts = JobApplication::query()
            ->selectRaw('LOWER(COALESCE(overall_status, "submitted")) as k, COUNT(*) as total')
            ->where('user_id', $userId)
            ->groupBy('k')
            ->pluck('total', 'k');

        // Mapping DB -> pipeline
        $map = [
            'submitted'      => 'SUBMITTED',
            'active'         => 'SCREENING',      // asumsi: "active" = sedang proses → tampilkan sebagai SCREENING
            'screening'      => 'SCREENING',
            'interview'      => 'INTERVIEW',
            'offer'          => 'OFFERED',
            'offered'        => 'OFFERED',
            'hired'          => 'HIRED',
            'not_qualified'  => 'not_qualified',
        ];

        $bucket = ['SUBMITTED'=>0,'SCREENING'=>0,'INTERVIEW'=>0,'OFFERED'=>0,'HIRED'=>0,'not_qualified'=>0];
        foreach ($counts as $k => $v) {
            $norm = $map[$k] ?? 'SUBMITTED';
            $bucket[$norm] += (int) $v;
        }

        return [
            'total'    => array_sum($bucket),
            'byStatus' => collect($bucket),
        ];
    }

    protected function decorateProgress(JobApplication $app): array
    {
        $pipeline   = $this->stagePipeline();
        $totalSteps = 5;

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

        $isNQ = (strtolower((string) $app->overall_status) === 'not_qualified' || $currentStage === 'not_qualified');
        $isH  = (strtolower((string) $app->overall_status) === 'hired' || $currentStage === 'HIRED');

        if ($isNQ) {
            $progressPct = 0;   $nextLabel = null; $isFinal = true;
        } elseif ($isH) {
            $progressPct = 100; $nextLabel = null; $isFinal = true;
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
            'is_final'         => $isNQ || $isH,
            'hint'             => $hint,
            'applied_at'       => optional($app->created_at)?->toDateString(),
        ];
    }

    protected function nextStageLabel(string $currentStage): ?string
    {
        $order    = ['SUBMITTED','SCREENING','INTERVIEW','OFFERED','HIRED'];
        $pipeline = $this->stagePipeline();

        $idx = array_search($currentStage, $order, true);
        if ($idx === false) return $pipeline['SUBMITTED']['label'];

        $next = $order[$idx + 1] ?? null;
        return $next ? $pipeline[$next]['label'] : null;
    }
}
    