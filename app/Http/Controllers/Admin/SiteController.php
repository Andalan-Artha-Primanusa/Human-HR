<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    /**
     * List + pencarian + sort sederhana.
     */
    public function index(Request $request)
    {
        $q     = trim((string) $request->get('q'));
        $sort  = $request->get('sort', 'code');   // code|name|region|created_at
        $order = $request->get('order', 'asc');   // asc|desc

        $sites = Site::query()
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%")
                      ->orWhere('region', 'like', "%{$q}%");
                });
            })
            ->when(in_array($sort, ['code','name','region','created_at'], true), fn($qq) => $qq->orderBy($sort, $order))
            ->paginate(20)
            ->appends($request->query());

        if ($request->wantsJson()) {
            return response()->json($sites);
        }

        return view('admin.sites.index', compact('sites', 'q', 'sort', 'order'));
    }

    /**
     * Form create (TANPA toggle is_active).
     */
    public function create()
    {
        return view('admin.sites.create');
    }

    /**
     * Simpan site baru (is_active dipaksa true).
     */
    public function store(Request $request)
    {
        $data = $this->validatedStore($request);

        // Paksa aktif saat dibuat
        $data['is_active'] = true;

        // (opsional) jika form kirim meta_json
        if ($request->filled('meta_json')) {
            $decoded = json_decode((string) $request->input('meta_json'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['meta'] = $decoded;
            }
        }

        $site = Site::create($data);

        if ($request->wantsJson()) {
            return response()->json($site, 201);
        }

        return redirect()->route('admin.sites.show', $site)->with('success', 'Site created.');
    }

    /**
     * Detail site.
     */
    public function show(Site $site)
    {
        $site->loadCount('jobs');

        if (request()->wantsJson()) {
            return response()->json($site);
        }

        return view('admin.sites.show', compact('site'));
    }

    /**
     * Form edit (tetap TANPA toggle is_active).
     */
    public function edit(Site $site)
    {
        return view('admin.sites.edit', compact('site'));
    }

    /**
     * Update (tidak ada input is_active).
     */
    public function update(Request $request, Site $site)
    {
        $data = $this->validatedUpdate($request, $site);

        // (opsional) meta_json
        if ($request->filled('meta_json')) {
            $decoded = json_decode((string) $request->input('meta_json'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['meta'] = $decoded;
            }
        }

        // Jangan sentuh is_active pada update admin standar
        unset($data['is_active']);

        $site->update($data);

        if ($request->wantsJson()) {
            return response()->json($site);
        }

        return redirect()->route('admin.sites.show', $site)->with('success', 'Site updated.');
    }

    /**
     * Hapus (dicegah bila masih ada jobs).
     */
    public function destroy(Request $request, Site $site)
    {
        $site->loadCount('jobs');

        if ($site->jobs_count > 0) {
            $msg = 'Tidak dapat menghapus: masih terkait ke '.$site->jobs_count.' job(s).';
            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $site->delete();

        if ($request->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return redirect()->route('admin.sites.index')->with('success', 'Site deleted.');
    }

    /* =====================
     * Validation
     * ===================== */

    protected function validatedStore(Request $request): array
    {
        return $request->validate([
            'code'   => ['required','string','max:20','regex:/^[A-Z0-9\-_.]+$/','unique:sites,code'],
            'name'   => ['required','string','max:150'],
            'region' => ['nullable','string','max:100'],
            'meta'   => ['nullable','array'],
            // is_active tidak diterima di create (dipaksa true)
        ]);
    }

    protected function validatedUpdate(Request $request, Site $site): array
    {
        return $request->validate([
            'code'   => ['required','string','max:20','regex:/^[A-Z0-9\-_.]+$/', Rule::unique('sites','code')->ignore($site->id)],
            'name'   => ['required','string','max:150'],
            'region' => ['nullable','string','max:100'],
            'meta'   => ['nullable','array'],
        ]);
    }
}
