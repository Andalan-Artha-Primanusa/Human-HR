<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Support\Facades\DB;

class ManpowerDashboardController extends Controller
{
    public function __invoke()
    {
        $openJobs     = Job::where('status','open')->count();
        $activeApps   = JobApplication::where('overall_status','active')->count();
        $byStage      = JobApplication::select('current_stage', DB::raw('count(*) as total'))
                          ->groupBy('current_stage')->pluck('total','current_stage');

        // fulfillment
        $req = DB::table('manpower_requirements')->selectRaw('SUM(budget_headcount) as budget, SUM(filled_headcount) as filled')->first();
        $budget = (int)($req->budget ?? 0);
        $filled = (int)($req->filled ?? 0);
        $fulfillment = $budget > 0 ? round($filled / $budget * 100, 1) : 0;

        return view('admin.dashboard.manpower', compact('openJobs','activeApps','byStage','budget','filled','fulfillment'));
    }
}
