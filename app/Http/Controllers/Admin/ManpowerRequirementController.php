<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\ManpowerRequirement;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Models\JobApplication;

class ManpowerRequirementController extends Controller
{
    /**
     * Daftar job untuk entry point pengaturan manpower.
     */
    public function index(Request $request): View|JsonResponse
    {
        $qRaw = (string) $request->query('q', '');
        $q = Str::limit(
            preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '',
            120,
            ''
        );
        $like = $q !== '' ? '%' . addcslashes($q, '\\%_') . '%' : null;

        $jobsForManpower = Job::query()
            ->select(['id', 'code', 'title', 'created_at'])
            ->when($like !== null, function ($qq) use ($like) {
                $qq->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                        ->orWhere('title', 'like', $like);
                });
            })
            ->latest('created_at')
            ->limit(100)
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'jobs' => $jobsForManpower,
            ]);
        }

        return view('admin.manpower.index', compact('jobsForManpower', 'q'));
    }

    /**
     * Tampilkan form untuk mengelola manpower per Job (tanpa site):
     * - List baris per asset_name (opsional)
     * - Form tambah/ubah baris
     */
    public function edit(Job $job): View|JsonResponse
    {
        // Ambil semua baris manpower untuk job (tanpa site)
        $rows = $job->manpowerRequirements()
            ->select(['id', 'job_id', 'asset_name', 'assets_count', 'ratio_per_asset', 'budget_headcount', 'filled_headcount'])
            ->orderByRaw('COALESCE(asset_name,"") asc')
            ->get();

        // JSON (opsional)
        if (request()->wantsJson()) {
            return response()->json([
                'job' => [
                    'id' => $job->id,
                    'code' => $job->code,
                    'title' => $job->title,
                    'openings' => (int) $job->openings,
                ],
                'rows' => $rows->map(function (ManpowerRequirement $m) {
                    return [
                        'id' => $m->id,
                        'asset_name' => $m->asset_name,
                        'assets_count' => (int) $m->assets_count,
                        'ratio_per_asset' => (float) $m->ratio_per_asset,
                        'budget_headcount' => (int) $m->budget_headcount,
                        'filled_headcount' => (int) $m->filled_headcount,
                    ];
                }),
            ]);
        }

        // View: resources/views/admin/manpower/edit.blade.php (form + tabel)
        // (Pastikan view-nya juga diubah: hapus dropdown Site)
        return view('admin.manpower.edit', compact('job', 'rows'));
    }

    /**
     * Upsert satu baris manpower per (job, asset_name).
     * Recalc budget via model hook; openings job ikut tersinkron otomatis.
     */
    public function update(Request $request, Job $job): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'asset_name' => ['nullable', 'string', 'max:120'],
            'assets_count' => ['required', 'integer', 'min:0'],
            'ratio_per_asset' => ['required', 'numeric', 'between:0,9.99'],
            // jika update baris existing by id:
            'row_id' => ['nullable', 'uuid'],
        ]);

        /** @var ManpowerRequirement $row */
        $row = null;

        DB::transaction(function () use ($job, $data, &$row) {
            // Jika ada row_id -> update by id; jika tidak, upsert by unique key (job_id, asset_name)
            if (!empty($data['row_id'])) {
                $row = ManpowerRequirement::where('job_id', $job->id)
                    ->where('id', $data['row_id'])
                    ->firstOrFail();

                $row->fill([
                    'asset_name' => $data['asset_name'] ?? null,
                    'assets_count' => (int) $data['assets_count'],
                    'ratio_per_asset' => (float) $data['ratio_per_asset'],
                ])->save();
            } else {
                $row = ManpowerRequirement::updateOrCreate(
                    [
                        'job_id' => $job->id,
                        'asset_name' => $data['asset_name'] ?? null,
                    ],
                    [
                        'assets_count' => (int) $data['assets_count'],
                        'ratio_per_asset' => (float) $data['ratio_per_asset'],
                    ]
                );
            }
            // Model hook akan set budget_headcount & sync jobs.openings
        });

        $payload = [
            'message' => 'Manpower tersimpan & openings tersinkron.',
            'job' => [
                'id' => $job->id,
                'openings' => (int) $job->fresh()->openings,
            ],
            'row' => [
                'id' => $row->id,
                'asset_name' => $row->asset_name,
                'assets_count' => (int) $row->assets_count,
                'ratio_per_asset' => (float) $row->ratio_per_asset,
                'budget_headcount' => (int) $row->budget_headcount,
            ],
        ];

        if ($request->wantsJson()) {
            return response()->json($payload);
        }

        return redirect()
            ->route('admin.manpower.edit', $job)
            ->with('success', $payload['message']);
    }

    /**
     * Hapus satu baris manpower (tanpa site).
     * Recalc openings job otomatis via model hook.
     */
    public function destroy(Request $request, Job $job, ManpowerRequirement $manpower): RedirectResponse|JsonResponse
    {
        abort_if($manpower->job_id !== $job->id, 404);
        $manpower->delete();

        $message = 'Baris manpower dihapus & openings tersinkron.';
        if ($request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('admin.manpower.edit', $job)->with('success', $message);
    }

    /**
     * Preview perhitungan tanpa menyimpan ke DB.
     * Body: { assets_count:int, ratio_per_asset:float }
     */
    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'assets_count' => ['required', 'integer', 'min:0'],
            'ratio_per_asset' => ['required', 'numeric', 'between:0,9.99'],
        ]);

        $assets = (int) $data['assets_count'];
        $ratio = (float) $data['ratio_per_asset'];
        $budget = (int) ceil($assets * $ratio);

        return response()->json([
            'input' => [
                'assets_count' => $assets,
                'ratio_per_asset' => $ratio,
            ],
            'result' => [
                'budget_headcount' => $budget,
            ],
            'note' => 'Ini hanya preview. Untuk menyimpan & menyinkronkan openings, gunakan endpoint update.',
        ]);
    }

    /**
     * (Opsional) Dashboard manpower — tidak terkait site.
     * Boleh kamu pindahkan ke controller khusus Dashboard jika mau.
     */
    public function __invoke()
    {
        $openJobs = Job::query()->where('status', 'open')->count('id');
        $activeApps = JobApplication::query()->where('overall_status', 'active')->count('id');

        $byStage = JobApplication::query()
            ->selectRaw("COALESCE(NULLIF(current_stage, ''), 'unknown') AS stage_key, COUNT(*) AS total")
            ->groupByRaw("COALESCE(NULLIF(current_stage, ''), 'unknown')")
            ->pluck('total', 'stage_key')
            ->toArray();

        $req = DB::table('manpower_requirements')
            ->selectRaw('COALESCE(SUM(budget_headcount), 0) as budget, COALESCE(SUM(filled_headcount), 0) as filled')
            ->first();

        $budget = (int) ($req->budget ?? 0);
        $filled = (int) ($req->filled ?? 0);
        $fulfillment = $budget > 0 ? round($filled / $budget * 100, 1) : 0;

        $jobsForManpower = Job::query()
            ->select('id', 'code', 'title')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('admin.dashboard.manpower', compact(
            'openJobs',
            'activeApps',
            'byStage',
            'budget',
            'filled',
            'fulfillment',
            'jobsForManpower'
        ));
    }
}
