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

            return view('admin.dashboard.manpower', compact('openJobs','activeApps','byStage','budget','filled','fulfillment'));
        }

        [$openJobs, $activeApps, $byStage, $budget, $filled, $fulfillment] = Cache::remember(
            'admin.manpower.dashboard.metrics',
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

                return [$openJobs, $activeApps, $byStage, $budget, $filled, $fulfillment];
            }
        );

        return view('admin.dashboard.manpower', compact('openJobs','activeApps','byStage','budget','filled','fulfillment'));
    }
}
