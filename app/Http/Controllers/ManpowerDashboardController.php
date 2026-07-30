<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Interview;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManpowerDashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $requiredTables = [
            'job_listings',
            'job_applications',
            'candidate_profiles',
            'application_stages',
            'offers',
            'pohs',
            'sites',
            'interviews',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                return view('admin.dashboard.manpower', $this->emptyMetrics());
            }
        }

        if (app()->runningUnitTests()) {
            Cache::forget('dashboard.manpower');
        }

        $sites = Site::query()
            ->orderBy('code')
            ->get(['id', 'code', 'name']);
        $siteId = (string) $request->query('site_id', '');
        if ($siteId !== '' && ! $sites->contains('id', $siteId)) {
            $siteId = '';
        }

        $periodStart = $this->parseDate($request->query('start_date'), now()->startOfMonth())->toDateString();
        $periodEnd = $this->parseDate($request->query('end_date'), now())->toDateString();
        if (Carbon::parse($periodEnd)->lt(Carbon::parse($periodStart))) {
            [$periodStart, $periodEnd] = [$periodEnd, $periodStart];
        }
        $periodStartAt = Carbon::parse($periodStart)->startOfDay();
        $periodEndAt = Carbon::parse($periodEnd)->endOfDay();

        $cacheKey = 'dashboard.manpower.' . ($siteId !== '' ? $siteId : 'all') . '.' . $periodStart . '.' . $periodEnd;
        if (app()->runningUnitTests()) {
            Cache::forget($cacheKey);
        }

        $metrics = Cache::remember($cacheKey, 30, function () use ($siteId, $periodStartAt, $periodEndAt) {
            $levels = Job::LEVEL_LABELS;
            $hasSourceChannel = Schema::hasColumn('candidate_profiles', 'source_channel');

            $openJobsQuery = Job::query()
                ->where('status', 'open')
                ->when($siteId !== '', fn ($q) => $q->where('site_id', $siteId))
                ->withCount([
                    'applications as applicants_count',
                    'applications as hired_count' => fn ($q) => $q->where('overall_status', 'hired'),
                    'applications as accepted_ol_count' => fn ($q) => $q->whereHas('offer', fn ($offerQuery) => $offerQuery->where('status', 'accepted')),
                ])
                ->orderByDesc('created_at')
                ->get(['id', 'title', 'division', 'level', 'openings', 'created_at', 'site_id']);

            $openJobs = $openJobsQuery->count();
            $activeApps = JobApplication::query()
                ->when($siteId !== '', fn ($q) => $q->whereHas('job', fn ($job) => $job->where('site_id', $siteId)))
                ->whereBetween('created_at', [$periodStartAt, $periodEndAt])
                ->count();
            $openJobApplicants = (int) $openJobsQuery->sum('applicants_count');
            $hiredCount = JobApplication::query()
                ->where('overall_status', 'hired')
                ->when($siteId !== '', fn ($q) => $q->whereHas('job', fn ($job) => $job->where('site_id', $siteId)))
                ->whereBetween('updated_at', [$periodStartAt, $periodEndAt])
                ->count();
            $acceptedOlCount = DB::table('offers as o')
                ->join('job_applications as ja', 'ja.id', '=', 'o.application_id')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->whereBetween('o.created_at', [$periodStartAt, $periodEndAt])
                ->where('o.status', 'accepted')
                ->count();
            $declinedOlCount = DB::table('offers as o')
                ->join('job_applications as ja', 'ja.id', '=', 'o.application_id')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->whereBetween('o.created_at', [$periodStartAt, $periodEndAt])
                ->where('o.status', 'declined')
                ->count();

            $olRejectionReasons = DB::table('offers as o')
                ->join('job_applications as ja', 'ja.id', '=', 'o.application_id')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->whereBetween('o.created_at', [$periodStartAt, $periodEndAt])
                ->where('o.status', 'declined')
                ->whereNotNull('o.rejection_reason')
                ->where('o.rejection_reason', '<>', '')
                ->selectRaw('o.rejection_reason, COUNT(*) as total')
                ->groupBy('o.rejection_reason')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'reason' => (string) $row->rejection_reason,
                    'total' => (int) $row->total,
                ]);

            $timeToFillRows = DB::table('job_listings as j')
                ->join('job_applications as ja', 'ja.job_id', '=', 'j.id')
                ->join('offers as o', function ($join) {
                    $join->on('o.application_id', '=', 'ja.id')
                        ->where('o.status', '=', 'sent');
                })
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->whereBetween('o.created_at', [$periodStartAt, $periodEndAt])
                ->whereNotNull('j.created_at')
                ->selectRaw('j.id, j.created_at as job_created_at, MIN(o.created_at) as first_ol_at')
                ->groupBy('j.id', 'j.created_at')
                ->get();

            $timeToFillDays = $timeToFillRows->isNotEmpty()
                ? round((float) $timeToFillRows->avg(function ($row) {
                    $start = $row->job_created_at ? \Carbon\Carbon::parse($row->job_created_at) : null;
                    $end = $row->first_ol_at ? \Carbon\Carbon::parse($row->first_ol_at) : null;

                    return ($start && $end) ? $start->diffInDays($end) : 0;
                }), 1)
                : 0;

            $candidateQuery = DB::table('candidate_profiles as cp');
            if ($siteId !== '') {
                $candidateQuery
                    ->join('users as u', 'u.id', '=', 'cp.user_id')
                    ->join('job_applications as ja', 'ja.user_id', '=', 'u.id')
                    ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                    ->whereBetween('ja.created_at', [$periodStartAt, $periodEndAt])
                    ->where('j.site_id', $siteId);
            } else {
                $candidateQuery
                    ->join('users as u', 'u.id', '=', 'cp.user_id')
                    ->join('job_applications as ja', 'ja.user_id', '=', 'u.id')
                    ->whereBetween('ja.created_at', [$periodStartAt, $periodEndAt]);
            }

            $sourceRaw = $hasSourceChannel
                ? (clone $candidateQuery)
                    ->distinct()
                    ->pluck('cp.source_channel')
                    ->map(function ($value) {
                        $value = is_string($value) ? trim($value) : '';
                        return $value !== '' ? $value : 'unknown';
                    })
                    ->countBy()
                : collect();

            $candidateRows = (clone $candidateQuery)
                ->distinct()
                ->select(['cp.id', 'cp.gender', 'cp.last_education', 'cp.poh_id', 'cp.source_channel', 'cp.birthdate', 'cp.age'])
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
                ? "COALESCE(cp.age, CAST(strftime('%Y', 'now') AS INTEGER) - CAST(strftime('%Y', cp.birthdate) AS INTEGER))"
                : "COALESCE(cp.age, TIMESTAMPDIFF(YEAR, cp.birthdate, CURDATE()))";

            $ageStats = (clone $candidateQuery)
                ->selectRaw("ROUND(AVG($ageExpr), 1) as avg_age, MIN($ageExpr) as min_age, MAX($ageExpr) as max_age")
                ->first();

            $sourcingOnsiteTotal = $hasSourceChannel
                ? (clone $candidateQuery)
                    ->whereIn('cp.source_channel', ['sourcing', 'onsite'])
                    ->distinct('cp.id')
                    ->count()
                : 0;

            $sourcingOnsiteHired = $hasSourceChannel
                ? DB::table('candidate_profiles as cp')
                    ->join('users as u', 'u.id', '=', 'cp.user_id')
                    ->join('job_applications as ja', 'ja.user_id', '=', 'u.id')
                    ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                    ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                    ->whereBetween('ja.updated_at', [$periodStartAt, $periodEndAt])
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
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->whereBetween('ja.created_at', [$periodStartAt, $periodEndAt])
                ->select(['j.level'])
                ->get()
                ->groupBy(fn ($row) => $row->level ?: 'unknown')
                ->map(fn ($rows) => $rows->count());

            $jobHiredByLevel = DB::table('job_applications as ja')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->where('ja.overall_status', 'hired')
                ->whereBetween('ja.updated_at', [$periodStartAt, $periodEndAt])
                ->select(['j.level'])
                ->get()
                ->groupBy(fn ($row) => $row->level ?: 'unknown')
                ->map(fn ($rows) => $rows->count());

            $hiredRows = DB::table('job_applications as ja')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->where('ja.overall_status', 'hired')
                ->whereBetween('ja.updated_at', [$periodStartAt, $periodEndAt])
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

            $failureRows = DB::table('application_stages as ast')
                ->join('job_applications as ja', 'ja.id', '=', 'ast.application_id')
                ->join('job_listings as j', 'j.id', '=', 'ja.job_id')
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->whereBetween('ast.updated_at', [$periodStartAt, $periodEndAt])
                ->whereIn('ast.status', ['failed', 'no-show'])
                ->select(['ast.stage_key'])
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
                    'division' => Job::DIVISIONS[$job->division ?: ''] ?? strtoupper((string) ($job->division ?: '-')),
                    'level_key' => $job->level ?: 'unknown',
                    'level_label' => Job::LEVEL_LABELS[$job->level ?: ''] ?? strtoupper((string) ($job->level ?: 'unknown')),
                    'openings' => (int) ($job->openings ?? 0),
                    'applicants' => (int) ($job->applicants_count ?? 0),
                    'hired' => (int) ($job->hired_count ?? 0),
                    'accepted_ol' => (int) ($job->accepted_ol_count ?? 0),
                ];
            });

            $mppRows = Job::query()
                ->where('status', 'open')
                ->when($siteId !== '', fn ($q) => $q->where('site_id', $siteId))
                ->with(['site:id,code,name'])
                ->withCount([
                    'applications as screening_count' => fn ($q) => $q->where('current_stage', 'screening')->whereBetween('created_at', [$periodStartAt, $periodEndAt]),
                    'applications as hr_count' => fn ($q) => $q->where('current_stage', 'hr_iv')->whereBetween('created_at', [$periodStartAt, $periodEndAt]),
                    'applications as user_count' => fn ($q) => $q->whereIn('current_stage', ['user_iv', 'user_trainer_iv'])->whereBetween('created_at', [$periodStartAt, $periodEndAt]),
                    'applications as practical_ground_count' => fn ($q) => $q->whereIn('current_stage', ['psychotest', 'ground_test'])->whereBetween('created_at', [$periodStartAt, $periodEndAt]),
                    'applications as ol_count' => fn ($q) => $q->where('current_stage', 'offer')->whereBetween('created_at', [$periodStartAt, $periodEndAt]),
                    'applications as mcu_count' => fn ($q) => $q->where('current_stage', 'mcu')->whereBetween('created_at', [$periodStartAt, $periodEndAt]),
                    'applications as waiting_inbound_count' => fn ($q) => $q->where('current_stage', 'onsite')->whereBetween('created_at', [$periodStartAt, $periodEndAt]),
                    'applications as travel_count' => fn ($q) => $q->where('current_stage', 'mobilisasi')->whereBetween('created_at', [$periodStartAt, $periodEndAt]),
                    'applications as hired_count' => fn ($q) => $q->whereBetween('updated_at', [$periodStartAt, $periodEndAt])->where(function ($w) {
                        $w->where('overall_status', 'hired')->orWhere('current_stage', 'hired');
                    }),
                ])
                ->orderBy('division')
                ->orderBy('level')
                ->orderBy('title')
                ->get(['id', 'title', 'division', 'level', 'openings', 'site_id'])
                ->map(function (Job $job, int $index) {
                    $mpp = (int) ($job->openings ?? 0);
                    $qty = (int) ($job->hired_count ?? 0);
                    $dev = $qty - $mpp;
                    $totalProgress = collect([
                        $job->screening_count,
                        $job->hr_count,
                        $job->user_count,
                        $job->practical_ground_count,
                        $job->ol_count,
                        $job->mcu_count,
                        0,
                        $job->waiting_inbound_count,
                        $job->travel_count,
                    ])->sum();

                    return [
                        'no' => $index + 1,
                        'site' => trim(($job->site?->code ? $job->site->code . ' - ' : '') . ($job->site?->name ?? '-')),
                        'department' => Job::DIVISIONS[$job->division ?: ''] ?? strtoupper((string) ($job->division ?: '-')),
                        'level' => Job::LEVEL_LABELS[$job->level ?: ''] ?? strtoupper((string) ($job->level ?: '-')),
                        'position' => $job->title,
                        'mpp' => $mpp,
                        'qty' => $qty,
                        'fulfillment' => $mpp > 0 ? round(($qty / $mpp) * 100) : 0,
                        'dev' => $dev,
                        'screening' => (int) $job->screening_count,
                        'hr' => (int) $job->hr_count,
                        'user' => (int) $job->user_count,
                        'practical_ground' => (int) $job->practical_ground_count,
                        'ol' => (int) $job->ol_count,
                        'mcu' => (int) $job->mcu_count,
                        'fu_mcu' => 0,
                        'waiting_inbound' => (int) $job->waiting_inbound_count,
                        'travel' => (int) $job->travel_count,
                        'total_progress' => (int) $totalProgress,
                        'update_dev' => (int) ($totalProgress + $dev),
                    ];
                });

            $mppTotals = [
                'mpp' => (int) $mppRows->sum('mpp'),
                'qty' => (int) $mppRows->sum('qty'),
                'dev' => (int) $mppRows->sum('dev'),
                'screening' => (int) $mppRows->sum('screening'),
                'hr' => (int) $mppRows->sum('hr'),
                'user' => (int) $mppRows->sum('user'),
                'practical_ground' => (int) $mppRows->sum('practical_ground'),
                'ol' => (int) $mppRows->sum('ol'),
                'mcu' => (int) $mppRows->sum('mcu'),
                'fu_mcu' => (int) $mppRows->sum('fu_mcu'),
                'waiting_inbound' => (int) $mppRows->sum('waiting_inbound'),
                'travel' => (int) $mppRows->sum('travel'),
                'total_progress' => (int) $mppRows->sum('total_progress'),
                'update_dev' => (int) $mppRows->sum('update_dev'),
            ];
            $mppTotals['fulfillment'] = $mppTotals['mpp'] > 0
                ? round(($mppTotals['qty'] / $mppTotals['mpp']) * 100)
                : 0;

            $levelStats = collect($levels)->map(function ($label, $levelKey) use ($openJobsQuery, $jobApplicationsByLevel, $jobHiredByLevel, $slaByLevel) {
                $jobCount = $openJobsQuery->where('level', $levelKey)->count();
                $applicants = (int) ($jobApplicationsByLevel[$levelKey] ?? 0);
                $hired = (int) ($jobHiredByLevel[$levelKey] ?? 0);
                $successRate = $jobCount > 0 ? round(($hired / $jobCount) * 100) : 0;

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
                ->join('job_listings as j', 'j.id', '=', 'job_applications.job_id')
                ->when($siteId !== '', fn ($q) => $q->where('j.site_id', $siteId))
                ->selectRaw('MONTH(job_applications.created_at) as month, COUNT(*) as total')
                ->whereBetween('job_applications.created_at', [$periodStartAt->copy()->startOfYear(), $periodEndAt])
                ->groupByRaw('MONTH(job_applications.created_at)')
                ->pluck('total', 'month');

            $interviewRows = Interview::query()
                ->with([
                    'application:id,job_id,user_id,current_stage,overall_status',
                    'application.user:id,name,email',
                    'application.job:id,title,division,site_id',
                    'application.job.site:id,code,name',
                ])
                ->whereBetween('start_at', [$periodStartAt, $periodEndAt])
                ->where('start_at', '<=', now())
                ->when($siteId !== '', fn ($q) => $q->whereHas('application.job', fn ($job) => $job->where('site_id', $siteId)))
                ->orderByDesc('start_at')
                ->limit(50)
                ->get()
                ->map(function (Interview $interview) {
                    $panel = collect($interview->panel ?? [])
                        ->map(function ($person) {
                            if (is_array($person)) {
                                return trim((string) ($person['name'] ?? $person['email'] ?? ''));
                            }

                            return trim((string) $person);
                        })
                        ->filter()
                        ->values();

                    $job = $interview->application?->job;
                    $site = $job?->site;

                    return [
                        'date' => optional($interview->start_at)->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                        'end' => optional($interview->end_at)->timezone(config('app.timezone'))->format('H:i'),
                        'candidate' => $interview->application?->user?->name ?: '-',
                        'email' => $interview->application?->user?->email ?: '-',
                        'job' => $job?->title ?: '-',
                        'site' => trim(($site?->code ? $site->code . ' - ' : '') . ($site?->name ?? '-')),
                        'title' => $interview->title ?: 'Interview',
                        'mode' => $interview->mode ?: '-',
                        'interviewers' => $panel->isNotEmpty() ? $panel->implode(', ') : '-',
                    ];
                });

            $interviewTotals = [
                'total' => $interviewRows->count(),
                'online' => $interviewRows->filter(fn ($row) => str_contains(strtolower((string) $row['mode']), 'online'))->count(),
                'onsite' => $interviewRows->filter(fn ($row) => str_contains(strtolower((string) $row['mode']), 'offline') || str_contains(strtolower((string) $row['mode']), 'onsite'))->count(),
            ];

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
                'pohCandidates' => (int) $candidateRows->whereNotNull('poh_id')->count(),
                'budget' => (int) Job::query()
                    ->where('status', 'open')
                    ->when($siteId !== '', fn ($q) => $q->where('site_id', $siteId))
                    ->sum('openings'),
                'filled' => $hiredCount,
                'fulfillment' => $fulfillment,
                'acceptanceRate' => $acceptanceRate,
                'acceptedOlCount' => $acceptedOlCount,
                'declinedOlCount' => $declinedOlCount,
                'olRejectionReasons' => $olRejectionReasons,
                'timeToFillDays' => $timeToFillDays,
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
                'mppRows' => $mppRows,
                'mppTotals' => $mppTotals,
                'interviewRows' => $interviewRows,
                'interviewTotals' => $interviewTotals,
                'failedStageName' => $failedStageName,
                'failedStageCount' => $failedStageCount,
                'slaByLevel' => $slaByLevel,
                'applicationTrend' => $applicationTrend,
            ];
        });

        return view('admin.dashboard.manpower', $metrics + [
            'generatedAt' => now(),
            'sites' => $sites,
            'selectedSiteId' => $siteId,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
        ]);
    }

    public function data()
    {
        return response()->json(Cache::get('dashboard.manpower') ?? $this->emptyMetrics());
    }

    protected function parseDate(mixed $value, Carbon $fallback): Carbon
    {
        try {
            return $value ? Carbon::parse($value) : $fallback->copy();
        } catch (\Throwable) {
            return $fallback->copy();
        }
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
            'acceptedOlCount' => 0,
            'declinedOlCount' => 0,
            'olRejectionReasons' => collect(),
            'timeToFillDays' => 0,
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
            'mppRows' => collect(),
            'mppTotals' => [
                'mpp' => 0,
                'qty' => 0,
                'fulfillment' => 0,
                'dev' => 0,
                'screening' => 0,
                'hr' => 0,
                'user' => 0,
                'practical_ground' => 0,
                'ol' => 0,
                'mcu' => 0,
                'fu_mcu' => 0,
                'waiting_inbound' => 0,
                'travel' => 0,
                'total_progress' => 0,
                'update_dev' => 0,
            ],
            'interviewRows' => collect(),
            'interviewTotals' => ['total' => 0, 'online' => 0, 'onsite' => 0],
            'failedStageName' => '-',
            'failedStageCount' => 0,
            'slaByLevel' => collect(),
            'applicationTrend' => collect(),
        ];
    }
}
