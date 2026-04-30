<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\ManpowerRequirement;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ManpowerRequirementController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $like = $q ? '%' . addcslashes($q, '\\%_') . '%' : null;

        $jobsForManpower = Job::query()
            ->select(['id', 'code', 'title', 'created_at'])
            ->when($like, fn($qq) =>
                $qq->where('code', 'like', $like)
                   ->orWhere('title', 'like', $like)
            )
            ->latest()
            ->limit(100)
            ->get();

        return $request->wantsJson()
            ? response()->json(['jobs' => $jobsForManpower])
            : view('admin.manpower.index', compact('jobsForManpower', 'q'));
    }

    public function edit(Job $job): View|JsonResponse
    {
        $rows = $job->manpowerRequirements()->get();

        return request()->wantsJson()
            ? response()->json(['job' => $job, 'rows' => $rows])
            : view('admin.manpower.edit', compact('job', 'rows'));
    }

    public function update(Request $request, Job $job): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'asset_name' => ['nullable', 'string'],
            'assets_count' => ['required', 'integer', 'min:0'],
            'ratio_per_asset' => ['required', 'numeric'],
            'row_id' => ['nullable', 'uuid'],
        ]);

        DB::transaction(function () use ($job, $data) {
            ManpowerRequirement::updateOrCreate(
                [
                    'job_id' => $job->id,
                    'asset_name' => $data['asset_name'] ?? null,
                ],
                [
                    'assets_count' => $data['assets_count'],
                    'ratio_per_asset' => $data['ratio_per_asset'],
                ]
            );
        });

        return $request->wantsJson()
            ? response()->json(['message' => 'Saved'])
            : back()->with('success', 'Saved');
    }

    public function destroy(Request $request, Job $job, ManpowerRequirement $manpower)
    {
        abort_if($manpower->job_id !== $job->id, 404);
        $manpower->delete();

        return $request->wantsJson()
            ? response()->json(['message' => 'Deleted'])
            : back()->with('success', 'Deleted');
    }

    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'assets_count' => ['required', 'integer'],
            'ratio_per_asset' => ['required', 'numeric'],
        ]);

        return response()->json([
            'budget_headcount' => ceil($data['assets_count'] * $data['ratio_per_asset'])
        ]);
    }

    // =========================
    // 🔥 DASHBOARD FIX
    // =========================
    public function __invoke()
    {
        // fallback kalau tabel belum ada
        if (!Schema::hasTable('job_applications')) {
            return view('admin.dashboard.manpower', [
                'openJobs' => 0,
                'activeApps' => 0,
                'byStage' => collect(),
                'budget' => 0,
                'filled' => 0,
                'fulfillment' => 0,
                'genderBreakdown' => ['male'=>0,'female'=>0,'other'=>0],
                'slaPerStage' => collect(),
                'ageGroups' => ['<25'=>0,'25-34'=>0,'35-44'=>0,'45+'=>0],
                'acceptanceRate' => 0,
            ]);
        }

        if (app()->runningUnitTests()) {
            Cache::forget('dashboard.manpower');
        }

        $data = Cache::remember('dashboard.manpower', 30, function () {

            $openJobs = Job::where('status','open')->count();
            $activeApps = JobApplication::where('overall_status','active')->count();

            $byStage = JobApplication::select('current_stage', DB::raw('count(*) as total'))
                ->groupBy('current_stage')
                ->pluck('total','current_stage');

            $req = DB::table('manpower_requirements')->selectRaw('SUM(budget_headcount) as budget, SUM(filled_headcount) as filled')->first();

            $budget = (int) ($req->budget ?? 0);
            $filled = (int) ($req->filled ?? 0);
            $fulfillment = $budget ? round($filled/$budget*100) : 0;

            // gender (AMAN)
            $genderBreakdown = ['male'=>0,'female'=>0,'other'=>0];

            if (Schema::hasColumn('candidate_profiles','gender')) {
                $g = DB::table('candidate_profiles')->select('gender', DB::raw('count(*) as total'))->groupBy('gender')->pluck('total','gender');
                $genderBreakdown = [
                    'male'=>$g['male']??0,
                    'female'=>$g['female']??0,
                    'other'=>$g->except(['male','female'])->sum()
                ];
            }

            // SLA
            if (app()->runningUnitTests()) {
                $slaPerStage = JobApplication::selectRaw("current_stage as stage_key, AVG(julianday(updated_at) - julianday(created_at)) as avg_sla_days")
                    ->groupBy('current_stage')
                    ->get();
            } else {
                $slaPerStage = JobApplication::selectRaw('current_stage as stage_key, AVG(DATEDIFF(updated_at,created_at)) as avg_sla_days')
                    ->groupBy('current_stage')
                    ->get();
            }

            // AGE
            $ageGroups = ['<25'=>0,'25-34'=>0,'35-44'=>0,'45+'=>0];

            if (Schema::hasColumn('candidate_profiles','birthdate')) {
                if (app()->runningUnitTests()) {
                    // SQLite simple age calculation: strftime('%Y', 'now') - strftime('%Y', birthdate)
                    $ageGroups = [
                        '<25'=>DB::table('candidate_profiles')->whereRaw("(strftime('%Y', 'now') - strftime('%Y', birthdate)) < 25")->count(),
                        '25-34'=>DB::table('candidate_profiles')->whereRaw("(strftime('%Y', 'now') - strftime('%Y', birthdate)) BETWEEN 25 AND 34")->count(),
                        '35-44'=>DB::table('candidate_profiles')->whereRaw("(strftime('%Y', 'now') - strftime('%Y', birthdate)) BETWEEN 35 AND 44")->count(),
                        '45+'=>DB::table('candidate_profiles')->whereRaw("(strftime('%Y', 'now') - strftime('%Y', birthdate)) >= 45")->count(),
                    ];
                } else {
                    $ageGroups = [
                        '<25'=>DB::table('candidate_profiles')->whereRaw('TIMESTAMPDIFF(YEAR,birthdate,CURDATE())<25')->count(),
                        '25-34'=>DB::table('candidate_profiles')->whereRaw('TIMESTAMPDIFF(YEAR,birthdate,CURDATE()) BETWEEN 25 AND 34')->count(),
                        '35-44'=>DB::table('candidate_profiles')->whereRaw('TIMESTAMPDIFF(YEAR,birthdate,CURDATE()) BETWEEN 35 AND 44')->count(),
                        '45+'=>DB::table('candidate_profiles')->whereRaw('TIMESTAMPDIFF(YEAR,birthdate,CURDATE())>=45')->count(),
                    ];
                }
            }

            $total = JobApplication::count();
            $accepted = JobApplication::where('current_stage','hired')->count();
            $acceptanceRate = $total ? round($accepted/$total*100) : 0;

            return compact(
                'openJobs','activeApps','byStage','budget','filled','fulfillment',
                'genderBreakdown','slaPerStage','ageGroups','acceptanceRate'
            );
        });

        return view('admin.dashboard.manpower', $data);
    }
}