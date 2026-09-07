<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SitePublicController extends Controller
{
    /**
     * List site aktif (search ringan).
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $qRaw = (string) $request->query('q', '');
        $q = Str::limit(
            preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '',
            80,
            ''
        );
        $like = $q !== '' ? '%' . $q . '%' : null;

        $query = Site::query()
            ->select(['id', 'code', 'name', 'region', 'timezone', 'address'])
            ->active();

        if ($like !== null) {
            $query->where(function ($w) use ($like) {
                $w->where('code', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('region', 'like', $like)
                    ->orWhere('timezone', 'like', $like)
                    ->orWhere('address', 'like', $like);
            });
        }

        $sites = $query->orderBy('code')
            ->paginate(20)
            ->withQueryString();


        if ($request->wantsJson()) {
            return response()->json($sites);
        }

        return view('sites.index', compact('sites', 'q'));
    }

    /**
     * Detail site – hanya untuk yang aktif.
     */
    public function show(Request $request, Site $site)
    {
        abort_unless((bool) $site->is_active, 404);

        $jobColumns = [
            'id',
            'title',
            'code',
            'division',
            'level',
            'employment_type',
            'openings',
            'status',
            'site_id',
            'created_at',
            'closing_at',
        ];
        $siteCode = Str::lower(trim((string) $site->code));
        $siteName = Str::lower(trim((string) $site->name));
        $isAllSitesPage = in_array($siteCode, ['all', 'ast'], true)
            || in_array($siteName, ['all site', 'all sites', 'semua site'], true);

        if ($isAllSitesPage) {
            $allOpenJobs = Job::query()
                ->select($jobColumns)
                ->with('site:id,code,name')
                ->where('status', 'open')
                ->whereHas('site', fn($q) => $q->active())
                ->latest('created_at');

            $site->open_jobs_count = (clone $allOpenJobs)->count();
            $site->setRelation('jobs', $allOpenJobs->limit(12)->get());
        } else {
            // Hitung total jobs open dan siapkan job open terbaru untuk panel publik.
            $site->loadCount([
                'jobs as open_jobs_count' => fn($q) => $q->where('status', 'open'),
            ]);
            $site->load([
                'jobs' => fn($q) => $q->select($jobColumns)
                    ->where('status', 'open')
                    ->latest('created_at')
                    ->limit(8),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'site' => $site->only(['id', 'code', 'name', 'region', 'timezone', 'address']),
                'is_all_sites_page' => $isAllSitesPage,
                'open_jobs_count' => (int) $site->open_jobs_count,
                'jobs' => $site->jobs->map(fn($job) => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'status' => $job->status,
                    'site_id' => $job->site_id,
                    'site_name' => $job->site?->name,
                    'created_at' => optional($job->created_at)?->toISOString(),
                ])->values(),
            ]);
        }

        return view('sites.show', compact('site', 'isAllSitesPage'));
    }
}
