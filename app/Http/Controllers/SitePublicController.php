<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class SitePublicController extends Controller
{
    /**
     * List site aktif (search ringan).
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        $sites = Site::query()
            ->where('is_active', true)
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%")
                      ->orWhere('region', 'like', "%{$q}%")
                      ->orWhere('timezone', 'like', "%{$q}%")
                      ->orWhere('address', 'like', "%{$q}%");
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
        if (!$site->is_active) {
            abort(404);
        }

        // Hitung total jobs dan siapkan 5 job "open" terbaru (untuk panel di view).
        $site->loadCount(['jobs']);
        $site->load([
            'jobs' => fn($q) => $q->select(['id','title','status','site_id','created_at'])
                                  ->where('status', 'open')
                                  ->latest('created_at')
                                  ->limit(5),
        ]);

        if ($request->wantsJson()) {
            return response()->json($site);
        }

        return view('sites.show', compact('site'));
    }
}
