<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManpowerDashboardController extends Controller
{
    public function __invoke()
    {
        $requiredTables = [
            'job_listings',
            'job_applications',
            'candidate_profiles',
            'application_stages',
            'offers',
            'pohs',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                return view('admin.dashboard.manpower', $this->emptyMetrics());
            }
        }

        if (app()->runningUnitTests()) {
            Cache::forget('dashboard.manpower');
        }

        $metrics = Cache::remember('dashboard.manpower', 30, function () {
            $levels = Job::LEVEL_LABELS;
            $hasSourceChannel = Schema::hasColumn('candidate_profiles', 'source_channel');

            $openJobsQuery = Job::query()
                ->where('status', 'open')
                ->withCount([
                    'applications as applicants_count',
                    'applications as hired_count' => fn ($q) => $q->where('overall_status', 'hired'),
                    'applications as accepted_ol_count' => fn ($q) => $q->whereHas('offer', fn ($offerQuery) => $offerQuery->where('status', 'accepted')),
                ])
                ->orderByDesc('created_at')
                ->get(['id', 'title', 'level', 'openings', 'created_at']);

            $openJobs = $openJobsQuery->count();
            $activeApps = JobApplication::count();
            $openJobApplicants = (int) $openJobsQuery->sum('applicants_count');
            $hiredCount = JobApplication::where('overall_status', 'hired')->count();
            $acceptedOlCount = DB::table('offers')->where('status', 'accepted')->count();

            $sourceRaw = $hasSourceChannel
                ? DB::table('candidate_profiles')
                    ->pluck('source_channel')
                    ->map(function ($value) {
                        $value = is_string($value) ? trim($value) : '';
                        return $value !== '' ? $value : 'unknown';
                    })
                    ->countBy()
                : collect();

            $candidateRows = DB::table('candidate_profiles')
                ->select(['gender', 'last_education', 'poh_id', 'source_channel', 'birthdate', 'age'])
                ->get();

            $genderRaw = $candidateRows
                ->pluck('gender')
                ->map(function ($value) {
                    $value = is_string($value) ? trim($value) : '';
                    return $value !== '' ? strtolower($value) : 'unknown';
                })
                ->countBy();

            $educationRaw = $candidateRows
                ->pluck('last_education')
                ->map(function ($value) {
                    $value = is_string($value) ? trim($value) : '';
                    return $value !== '' ? strtoupper($value) : 'unknown';
                })
                ->countBy();

            $pohLabels = DB::table('pohs')->pluck('name', 'id');
            $pohRaw = $candidateRows
                ->pluck('poh_id')
                ->map(function ($value) use ($pohLabels) {
                    return $pohLabels[$value] ?? 'Unknown';
                })
                ->countBy();

            $ageExpr = app()->runningUnitTests()
                ? "COALESCE(age, CAST(strftime('%Y', 'now') AS INTEGER) - CAST(strftime('%Y', birthdate) AS INTEGER))"
                : "COALESCE(age, TIMESTAMPDIFF(YEAR, birthdate, CURDATE()))";

            $ageStats = DB::table('candidate_profiles')
                ->selectRaw("ROUND(AVG($ageExpr), 1) as avg_age, MIN($ageExpr) as min_age, MAX($ageExpr) as max_age")
                ->first();

            $sourcingOnsiteTotal = $hasSourceChannel
                ? DB::table('candidate_profiles')
                    ->whereIn('source_channel', ['sourcing', 'onsite'])
                    ->count()
                : 0;

            $sourcingOnsiteHired = $hasSourceChannel
                ? DB::table('candidate_profiles as cp')
                    ->join('users as u', 'u.id', '=', 'cp.user_id')
                    ->join('job_applications as ja', 'ja.user_id', '=', 'u.id')
                    ->whereIn('cp.source_channel', ['sourcing', 'onsite'])
                    ->where('ja.overall_status', 'hired')
                    ->count()
                : 0;

            $fulfillment = $sourcingOnsiteTotal > 0
                ? round(($sourcingOnsiteHired / $sourcingOnsiteTotal) * 100)
                : 0;

            $acceptanceRate = $activeApps > 0
                ? round(($acceptedOlCount / $activeApps) * 100)
                : 0;

            $jobApplicationsByLevel = DB::table('job_applications as ja')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->select(['j.level'])
                ->get()
                ->groupBy(fn ($row) => $row->level ?: 'unknown')
                ->map(fn ($rows) => $rows->count());

            $jobHiredByLevel = DB::table('job_applications as ja')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->where('ja.overall_status', 'hired')
                ->select(['j.level'])
                ->get()
                ->groupBy(fn ($row) => $row->level ?: 'unknown')
                ->map(fn ($rows) => $rows->count());

            $hiredRows = DB::table('job_applications as ja')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->where('ja.overall_status', 'hired')
                ->select(['j.level', 'j.created_at as job_created_at', 'ja.updated_at as hired_updated_at'])
                ->get();

            $slaByLevel = $hiredRows
                ->groupBy(fn ($row) => $row->level ?: 'unknown')
                ->map(function ($rows) {
                    $durations = $rows->map(function ($row) {
                        $start = $row->job_created_at ? \Carbon\Carbon::parse($row->job_created_at) : null;
                        $end = $row->hired_updated_at ? \Carbon\Carbon::parse($row->hired_updated_at) : null;
                        return ($start && $end) ? $start->diffInDays($end) : 0;
                    });

                    return round((float) $durations->avg(), 1);
                });

            $failureRows = DB::table('application_stages')
                ->whereIn('status', ['failed', 'no-show'])
                ->select(['stage_key'])
                ->get()
                ->groupBy(fn ($row) => $row->stage_key ?: 'unknown')
                ->map(fn ($rows) => $rows->count())
                ->sortDesc()
                ->map(fn ($count, $key) => (object) ['stage_key' => $key, 'total' => $count])
                ->values();

            $failedStageTop = $failureRows->first();
            $failedStageName = $failedStageTop?->stage_key ?? '-';
            $failedStageCount = (int) ($failedStageTop?->total ?? 0);

            $openJobCards = $openJobsQuery->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'level_key' => $job->level ?: 'unknown',
                    'level_label' => Job::LEVEL_LABELS[$job->level ?: ''] ?? strtoupper((string) ($job->level ?: 'unknown')),
                    'openings' => (int) ($job->openings ?? 0),
                    'applicants' => (int) ($job->applicants_count ?? 0),
                    'hired' => (int) ($job->hired_count ?? 0),
                    'accepted_ol' => (int) ($job->accepted_ol_count ?? 0),
                ];
            });

            $levelStats = collect($levels)->map(function ($label, $levelKey) use ($openJobsQuery, $jobApplicationsByLevel, $jobHiredByLevel, $slaByLevel) {
                $jobCount = $openJobsQuery->where('level', $levelKey)->count();
                $applicants = (int) ($jobApplicationsByLevel[$levelKey] ?? 0);
                $hired = (int) ($jobHiredByLevel[$levelKey] ?? 0);
                $successRate = $applicants > 0 ? round(($hired / $applicants) * 100) : 0;

                return [
                    'level_key' => $levelKey,
                    'level_label' => $label,
                    'open_jobs' => $jobCount,
                    'applicants' => $applicants,
                    'hired' => $hired,
                    'avg_sla_days' => round((float) ($slaByLevel[$levelKey] ?? 0), 1),
                    'success_rate' => $successRate,
                ];
            })->values();

            $sourceLabels = [
                'sourcing' => 'Sourcing',
                'onsite' => 'Onsite',
                'referral' => 'Referral',
                'linkedin' => 'LinkedIn',
                'instagram' => 'Instagram',
                'job_portal' => 'Job Portal',
                'other' => 'Lainnya',
                'unknown' => 'Unknown',
            ];

            $educationLabels = [
                'SD' => 'SD',
                'SMP' => 'SMP',
                'SMA_SMK' => 'SMA/SMK',
                'D1' => 'D1',
                'D2' => 'D2',
                'D3' => 'D3',
                'D4' => 'D4',
                'S1' => 'S1',
                'S2' => 'S2',
                'S3' => 'S3',
                'LAINNYA' => 'Lainnya',
                'unknown' => 'Unknown',
            ];

            $sourceBreakdown = collect($sourceLabels)->mapWithKeys(function ($label, $key) use ($sourceRaw) {
                return [$key => (int) ($sourceRaw[$key] ?? 0)];
            });

            $pohBreakdown = $pohRaw->map(fn ($value) => (int) $value);

            $educationBreakdown = collect($educationLabels)->mapWithKeys(function ($label, $key) use ($educationRaw) {
                return [$key => (int) ($educationRaw[$key] ?? 0)];
            });

            $genderBreakdown = [
                'male' => (int) ($genderRaw['male'] ?? 0),
                'female' => (int) ($genderRaw['female'] ?? 0),
                'other' => (int) ($genderRaw->except(['male', 'female'])->sum()),
            ];

            $trendSource = DB::table('job_applications')
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                ->whereYear('created_at', now()->year)
                ->groupByRaw('MONTH(created_at)')
                ->pluck('total', 'month');

            $monthNames = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
            $applicationTrend = collect();
            for ($i = 1; $i <= 12; $i++) {
                $applicationTrend[$monthNames[$i]] = (int) ($trendSource[$i] ?? 0);
            }

            return [
                'openJobs' => $openJobs,
                'activeApps' => $activeApps,
                'totalApplicants' => $activeApps,
                'openJobApplicants' => $openJobApplicants,
                'pohCandidates' => (int) DB::table('candidate_profiles')->whereNotNull('poh_id')->count(),
                'budget' => (int) Job::query()->where('status', 'open')->sum('openings'),
                'filled' => $hiredCount,
                'fulfillment' => $fulfillment,
                'acceptanceRate' => $acceptanceRate,
                'avgAge' => (float) ($ageStats->avg_age ?? 0),
                'minAge' => (int) ($ageStats->min_age ?? 0),
                'maxAge' => (int) ($ageStats->max_age ?? 0),
                'sourceLabels' => $sourceLabels,
                'sourceBreakdown' => $sourceBreakdown,
                'pohBreakdown' => $pohBreakdown,
                'educationLabels' => $educationLabels,
                'educationBreakdown' => $educationBreakdown,
                'genderBreakdown' => $genderBreakdown,
                'levelStats' => $levelStats,
                'openJobCards' => $openJobCards,
                'failureRows' => $failureRows,
                'failedStageName' => $failedStageName,
                'failedStageCount' => $failedStageCount,
                'slaByLevel' => $slaByLevel,
                'applicationTrend' => $applicationTrend,
            ];
        });

        return view('admin.dashboard.manpower', $metrics + [
            'generatedAt' => now(),
        ]);
    }

    public function data()
    {
        return response()->json(Cache::get('dashboard.manpower') ?? $this->emptyMetrics());
    }

    protected function emptyMetrics(): array
    {
        return [
            'openJobs' => 0,
            'activeApps' => 0,
            'totalApplicants' => 0,
            'openJobApplicants' => 0,
            'pohCandidates' => 0,
            'budget' => 0,
            'filled' => 0,
            'fulfillment' => 0,
            'acceptanceRate' => 0,
            'avgAge' => 0,
            'minAge' => 0,
            'maxAge' => 0,
            'sourceLabels' => [],
            'sourceBreakdown' => collect(),
            'pohBreakdown' => collect(),
            'educationLabels' => [],
            'educationBreakdown' => collect(),
            'genderBreakdown' => ['male' => 0, 'female' => 0, 'other' => 0],
            'levelStats' => collect(),
            'openJobCards' => collect(),
            'failureRows' => collect(),
            'failedStageName' => '-',
            'failedStageCount' => 0,
            'slaByLevel' => collect(),
            'applicationTrend' => collect(),
        ];
    }
}
