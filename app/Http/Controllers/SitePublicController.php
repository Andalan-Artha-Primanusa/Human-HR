<?php

namespace App\Http\Controllers;

use App\Models\Site;
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

        $qRaw = (string) ($data['q'] ?? '');
        $q = Str::limit(
            preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '',
            80,
            ''
        );
        $like = $q !== '' ? '%'.addcslashes($q, '\\%_').'%' : null;

        $sites = Site::query()
            ->select(['id', 'code', 'name', 'region', 'timezone', 'address'])
            ->where('is_active', true)
            ->when($like !== null, function ($qq) use ($like) {
                $qq->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                      ->orWhere('name', 'like', $like)
                      ->orWhere('region', 'like', $like)
                      ->orWhere('timezone', 'like', $like)
                      ->orWhere('address', 'like', $like);
                });
            })
            ->orderBy('code')
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

        // Hitung total jobs open dan siapkan 5 job open terbaru (untuk panel di view).
        $site->loadCount([
            'jobs as open_jobs_count' => fn($q) => $q->where('status', 'open'),
        ]);
        $site->load([
            'jobs' => fn($q) => $q->select(['id','title','status','site_id','created_at'])
                                  ->where('status', 'open')
                                  ->latest('created_at')
                                  ->limit(5),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'site' => $site->only(['id', 'code', 'name', 'region', 'timezone', 'address']),
                'open_jobs_count' => (int) $site->open_jobs_count,
                'jobs' => $site->jobs->map(fn ($job) => [
                    'id' => $job->id,
                    'title' => $job->title,
                    'status' => $job->status,
                    'site_id' => $job->site_id,
                    'created_at' => optional($job->created_at)?->toISOString(),
                ])->values(),
            ]);
        }

        return view('sites.show', compact('site'));
    }
}
