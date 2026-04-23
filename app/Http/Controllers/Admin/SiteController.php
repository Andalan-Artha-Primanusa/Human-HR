<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    /**
     * List + pencarian + filter status + sort (ORM-only, cepat & aman).
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'sort' => ['nullable', Rule::in(['code', 'name', 'region', 'created_at'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        // === Ambil & sanitasi input ===
        $qRaw = (string) ($filters['q'] ?? '');
        $q = Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '', 120, '');
        $like = $q !== '' ? '%' . addcslashes($q, '\\%_') . '%' : null;

        $status = (string) ($filters['status'] ?? '');
        $sort = (string) ($filters['sort'] ?? 'code');
        $order = (string) ($filters['order'] ?? 'asc');

        // === Query: pilih kolom minimal + filter aman + cursor paginate ===
        $sites = Site::query()
            ->select(['id', 'code', 'name', 'region', 'timezone', 'address', 'is_active', 'created_at'])
            ->when($like !== null, function ($qq) use ($like) {
                $qq->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('region', 'like', $like)
                        ->orWhere('timezone', 'like', $like)
                        ->orWhere('address', 'like', $like);
                });
            })
            ->when($status === 'active', fn($qq) => $qq->where('is_active', true))
            ->when($status === 'inactive', fn($qq) => $qq->where('is_active', false))
            ->orderBy($sort, $order)
            ->orderBy('id', $order)
            // cursorPaginate lebih hemat di dataset besar
            ->cursorPaginate(20)
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($sites);
        }

        return view('admin.sites.index', compact('sites', 'q', 'sort', 'order', 'status'));
    }

    /**
     * Form create (TANPA toggle is_active).
     */
    public function create()
    {
        return view('admin.sites.create');
    }

    /**
     * Simpan site baru (is_active dipaksa true). Redirect -> index.
     */
    public function store(Request $request)
    {
        $data = $this->validatedStore($request);

        // Paksa aktif saat dibuat
        $data['is_active'] = true;

        // meta_json (opsional) -> meta (array)
        if ($request->filled('meta_json')) {
            $decoded = json_decode((string) $request->input('meta_json'), true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['meta'] = $decoded;
            } else {
                return back()->withErrors(['meta' => 'Meta JSON tidak valid.'])->withInput();
            }
        }

        Site::create($data);

        if ($request->wantsJson()) {
            return response()->json(['created' => true], 201);
        }

        return redirect()->route('admin.sites.index')->with('success', 'Site created.');
    }

    /**
     * Detail site (muat count secukupnya).
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
     * Form edit (TANPA toggle is_active).
     */
    public function edit(Site $site)
    {
        return view('admin.sites.edit', compact('site'));
    }

    /**
     * Update (tidak menerima is_active). Redirect -> index.
     */
    public function update(Request $request, Site $site)
    {
        $data = $this->validatedUpdate($request, $site);

        // meta_json (opsional) -> meta (array/null)
        if ($request->has('meta_json')) {
            $raw = (string) $request->input('meta_json');
            if ($raw === '') {
                $data['meta'] = null;
            } else {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data['meta'] = $decoded;
                } else {
                    return back()->withErrors(['meta' => 'Meta JSON tidak valid.'])->withInput();
                }
            }
        }

        // Jangan sentuh is_active pada update admin standar
        unset($data['is_active']);

        $site->update($data);

        if ($request->wantsJson()) {
            return response()->json(['updated' => true]);
        }

        return redirect()->route('admin.sites.index')->with('success', 'Site updated.');
    }

    /**
     * Hapus (dicegah bila masih ada jobs).
     */
    public function destroy(Request $request, Site $site)
    {
        $site->loadCount('jobs');

        if ($site->jobs_count > 0) {
            $msg = 'Tidak dapat menghapus: masih terkait ke ' . $site->jobs_count . ' job(s).';
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
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:sites,code'],
            'name' => ['required', 'string', 'max:150'],
            'region' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'], // bila ada submit langsung (bukan meta_json)
            // is_active tidak diterima di create (dipaksa true)
        ], [
            'code.regex' => 'Kode hanya boleh huruf, angka, titik, strip, dan underscore.',
        ]);
    }

   protected function validatedUpdate(Request $request, Site $site): array
{
    return $request->validate([
        'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('sites', 'code')->ignore($site->id)],
        'name' => ['required', 'string', 'max:150'],
        'region' => ['nullable', 'string', 'max:100'],
        'timezone' => ['nullable', 'string', 'max:64'],
        'address' => ['nullable', 'string', 'max:255'],
        'notes' => ['nullable', 'string'],
        'meta' => ['nullable', 'array'],

        // ✅ TAMBAH INI
        'latitude' => ['nullable', 'numeric'],
        'longitude' => ['nullable', 'numeric'],
    ]);
}
}
