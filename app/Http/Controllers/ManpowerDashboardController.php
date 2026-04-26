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
        if (!Schema::hasTable('jobs') || !Schema::hasTable('job_applications') || !Schema::hasTable('manpower_requirements')) {
            return view('admin.dashboard.manpower', [
                'openJobs' => 0,
                'activeApps' => 0,
                'byStage' => collect(),
                'budget' => 0,
                'filled' => 0,
                'fulfillment' => 0,
                'genderBreakdown' => ['male'=>0,'female'=>0,'other'=>0],
                'ageGroups' => ['<25'=>0,'25-34'=>0,'35-44'=>0,'45+'=>0],
                'acceptanceRate' => 0,
                'slaPerStage' => collect(),
            ]);
        }

        $metrics = Cache::remember('dashboard.manpower', 30, function () {

            // KPI
            $openJobs = Job::where('status', 'open')->count();
            $activeApps = JobApplication::where('overall_status', 'active')->count();

            // PIPELINE
            $byStage = JobApplication::select('current_stage', DB::raw('COUNT(*) as total'))
                ->groupBy('current_stage')
                ->get()
                ->mapWithKeys(fn($r) => [
                    $r->current_stage ?: 'unknown' => $r->total
                ]);

            // BUDGET
            $req = DB::table('manpower_requirements')
                ->selectRaw('SUM(budget_headcount) as budget, SUM(filled_headcount) as filled')
                ->first();

            $budget = (int) ($req->budget ?? 0);
            $filled = (int) ($req->filled ?? 0);
            $fulfillment = $budget > 0 ? round($filled / $budget * 100) : 0;

            // =========================
            // GENDER
            // =========================
            $genderRaw = DB::table('candidate_profiles')
                ->select('gender', DB::raw('COUNT(*) as total'))
                ->groupBy('gender')
                ->pluck('total', 'gender');

            $genderBreakdown = [
                'male' => $genderRaw['male'] ?? 0,
                'female' => $genderRaw['female'] ?? 0,
                'other' => $genderRaw->except(['male','female'])->sum(),
            ];

            // =========================
            // AGE GROUP
            // =========================
            $ageGroups = [
                '<25' => DB::table('candidate_profiles')->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) < 25')->count(),
                '25-34' => DB::table('candidate_profiles')->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 25 AND 34')->count(),
                '35-44' => DB::table('candidate_profiles')->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 35 AND 44')->count(),
                '45+' => DB::table('candidate_profiles')->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) >= 45')->count(),
            ];

            // =========================
            // ACCEPTANCE RATE
            // =========================
            $totalOffers = DB::table('offers')->count();
            $acceptedOffers = DB::table('offers')->where('status','accepted')->count();

            $acceptanceRate = $totalOffers > 0
                ? round(($acceptedOffers / $totalOffers) * 100)
                : 0;

            // =========================
            // SLA
            // =========================
            $slaPerStage = DB::table('application_stages')
                ->select('stage_key', DB::raw('AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)) as avg_sla_days'))
                ->groupBy('stage_key')
                ->get();

            return compact(
                'openJobs',
                'activeApps',
                'byStage',
                'budget',
                'filled',
                'fulfillment',
                'genderBreakdown',
                'ageGroups',
                'acceptanceRate',
                'slaPerStage'
            );
        });

        // =========================
        // APPLICATION TREND (monthly)
        // =========================
        $applicationTrend = DB::table('job_applications')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->whereYear('created_at', now()->year)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('total', 'month');

        $monthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
        $trend = collect();
        for ($i = 1; $i <= 12; $i++) {
            $trend[$monthNames[$i]] = $applicationTrend[$i] ?? 0;
        }

        return view('admin.dashboard.manpower', array_merge($metrics, [
            'applicationTrend' => $trend
        ]));
    }

    // =========================
    // OPTIONAL API (JSON)
    // =========================
    public function data()
    {
        return response()->json(Cache::get('dashboard.manpower'));
    }
}