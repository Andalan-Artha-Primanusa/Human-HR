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
            $openJobs = 0;
            $activeApps = 0;
            $byStage = collect();
            $budget = 0;
            $filled = 0;
            $fulfillment = 0;

            return view('admin.dashboard.manpower', compact('openJobs', 'activeApps', 'byStage', 'budget', 'filled', 'fulfillment'));
        }

        $metrics = Cache::remember(
            'admin.manpower.dashboard.metrics.v2',
            30,
            function () {
                $openJobs = Job::query()->where('status', 'open')->count('id');
                $activeApps = JobApplication::query()->where('overall_status', 'active')->count('id');
                $byStage = JobApplication::query()
                    ->select('current_stage', DB::raw('COUNT(*) as total'))
                    ->groupBy('current_stage')
                    ->get()
                    ->mapWithKeys(fn($row) => [
                        (empty($row->current_stage) ? 'unknown' : $row->current_stage) => $row->total
                    ]);

                $req = DB::table('manpower_requirements')
                    ->selectRaw('COALESCE(SUM(budget_headcount), 0) as budget, COALESCE(SUM(filled_headcount), 0) as filled')
                    ->first();
                $budget = (int) ($req->budget ?? 0);
                $filled = (int) ($req->filled ?? 0);
                $fulfillment = $budget > 0 ? round($filled / $budget * 100, 1) : 0;

                // --- METRIK LANJUTAN ---
                // Total kandidat, breakdown JK, distribusi usia, count per POH
                $candidates = \App\Models\CandidateProfile::query();
                $totalCandidates = $candidates->count('id');
                $genderBreakdown = [
                    'male' => \App\Models\CandidateProfile::where('gender', 'male')->count(),
                    'female' => \App\Models\CandidateProfile::where('gender', 'female')->count(),
                    'other' => \App\Models\CandidateProfile::whereNotIn('gender', ['male', 'female'])->count(),
                ];
                // Distribusi usia (kelompok: <25, 25-34, 35-44, 45+)
                $ageNow = now();
                $ageGroups = [
                    '<25' => \App\Models\CandidateProfile::whereNotNull('birthdate')->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, ?) < 25', [$ageNow])->count(),
                    '25-34' => \App\Models\CandidateProfile::whereNotNull('birthdate')->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, ?) BETWEEN 25 AND 34', [$ageNow])->count(),
                    '35-44' => \App\Models\CandidateProfile::whereNotNull('birthdate')->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, ?) BETWEEN 35 AND 44', [$ageNow])->count(),
                    '45+' => \App\Models\CandidateProfile::whereNotNull('birthdate')->whereRaw('TIMESTAMPDIFF(YEAR, birthdate, ?) >= 45', [$ageNow])->count(),
                ];
                // Count per POH
                $byPoh = \App\Models\CandidateProfile::query()
                    ->select('poh_id', DB::raw('COUNT(*) as total'))
                    ->groupBy('poh_id')
                    ->get();

                // Acceptance rate (offer accepted / total offer)
                $totalOffers = \App\Models\Offer::count('id');
                $acceptedOffers = \App\Models\Offer::where('status', 'accepted')->count('id');
                $acceptanceRate = $totalOffers > 0 ? round($acceptedOffers / $totalOffers * 100, 1) : 0;

                // Offer rate staff (jumlah offer untuk job level staff)
                $staffJobIds = \App\Models\Job::where('level', 'staff')->pluck('id');
                $staffOffers = \App\Models\Offer::whereHas('application.job', function($q) use ($staffJobIds) {
                    $q->whereIn('id', $staffJobIds);
                })->count('id');

                // SLA rata-rata per stage (dalam hari)
                $slaPerStage = \App\Models\ApplicationStage::query()
                    ->select('stage_key', DB::raw('AVG(TIMESTAMPDIFF(DAY, created_at, updated_at)) as avg_sla_days'), DB::raw('COUNT(*) as total'))
                    ->groupBy('stage_key')
                    ->get();

                return compact(
                    'openJobs', 'activeApps', 'byStage', 'budget', 'filled', 'fulfillment',
                    'totalCandidates', 'genderBreakdown', 'ageGroups', 'byPoh',
                    'acceptanceRate', 'staffOffers', 'slaPerStage'
                );
            }
        );

        return view('admin.dashboard.manpower', $metrics);
    }
}
