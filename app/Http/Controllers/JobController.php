<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Site;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    public function __construct()
    {
        // Policy untuk admin resource; index/show/create/store dikelola via middleware/route group
        $this->authorizeResource(Job::class, 'job', [
            'except' => ['index', 'show', 'create', 'store'],
        ]);
    }

    /**
     * PUBLIC & ADMIN LIST (aman + cepat)
     */
    public function index(Request $request)
    {
        $isAdminRoute = $request->routeIs('admin.*');

        // 1) Validasi & normalisasi query params (whitelist)
        $data = $request->validate([
            'division'   => ['nullable', 'string', 'max:100'],
            'site'       => ['nullable', 'string', 'max:50'], // site code
            'company'    => ['nullable', 'string', 'max:50'], // company code
            'company_id' => ['nullable', 'uuid', 'exists:companies,id'],
            'type'       => ['nullable', Rule::in(['intern', 'contract', 'fulltime'])], // enum DB
            'term'       => ['nullable', 'string', 'max:200'],
            'sort'       => ['nullable', Rule::in(['latest', 'oldest', 'title'])],
            'page'       => ['nullable', 'integer', 'min:1'],
            'per_page'   => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $data['per_page'] ?? 12;
        $sort    = $data['sort']     ?? 'latest';

        // 2) Query dasar (kolom minimal + eager load ketat)
        $baseQuery = Job::query()
            ->select([
                'id',
                'code',
                'title',
                'division',
                'level',
                'employment_type',
                'openings',
                'site_id',
                'company_id',
                'status',
                'description',
                'keywords',
                'skills', // <-- tambahkan ini
                'created_at',
                'updated_at'
            ])
            ->with([
                'site:id,code,name',
                'company:id,code,name',
            ]);

        if (!$isAdminRoute) {
            $baseQuery->where('status', 'open');
        }

        if (!empty($data['division'])) {
            // Model kamu sudah punya normalizer, tapi di sini tetap pakai raw string yang divalidasi
            $baseQuery->where('division', $data['division']);
        }

        if (!empty($data['site'])) {
            $siteCode = $data['site'];
            $baseQuery->whereHas('site', fn($q) => $q->where('code', $siteCode));
        }

        // Filter by company_id atau company code
        if (!empty($data['company_id'])) {
            $baseQuery->where('company_id', $data['company_id']);
        } elseif (!empty($data['company'])) {
            $companyCode = $data['company'];
            $baseQuery->whereHas('company', fn($q) => $q->where('code', $companyCode));
        }

        if (!empty($data['type'])) {
            $baseQuery->where('employment_type', $data['type']);
        }

        if (!empty($data['term'])) {
            $term = trim($data['term']);
            $like = '%' . $term . '%';
            $baseQuery->where(function ($q) use ($like, $term) {
                $q->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('keywords', 'like', $like);

                // Opsional: cari dalam JSON "skills" (MySQL 5.7+/8 & MariaDB modern)
                try {
                    $q->orWhereJsonContains('skills', $term);
                } catch (\Throwable $e) {
                    // ignore jika engine tidak mendukung JSON contains
                }
            });
        }

        // 3) Sorting
        match ($sort) {
            'oldest' => $baseQuery->orderBy('created_at', 'asc'),
            'title'  => $baseQuery->orderBy('title')->orderByDesc('created_at'),
            default  => $baseQuery->orderBy('created_at', 'desc'), // latest
        };

        // 4) Micro-cache untuk publik
        if (!$isAdminRoute) {
            $cacheKey = 'jobs.public.' . md5(json_encode([
                'division'   => $data['division'] ?? null,
                'site'       => $data['site'] ?? null,
                'company'    => $data['company'] ?? null,
                'company_id' => $data['company_id'] ?? null,
                'type'       => $data['type'] ?? null,
                'term'       => $data['term'] ?? null,
                'sort'       => $sort,
                'page'       => $data['page'] ?? 1,
                'per_page'   => $perPage,
            ]));

            $jobs = Cache::remember($cacheKey, now()->addSeconds(30), function () use ($baseQuery, $perPage) {
                return $baseQuery->paginate($perPage)->withQueryString();
            });
        } else {
            $jobs = $baseQuery->paginate($perPage)->withQueryString();
        }

        $view = $isAdminRoute ? 'admin.jobs.index' : 'jobs.index';
        return view($view, compact('jobs'));
    }

    /**
     * PUBLIC DETAIL
     */
    public function show(Job $job)
    {
        $job->loadMissing([
            'site:id,code,name,region,timezone,address',
            'company:id,code,name',
        ])->loadCount('applications');

        return view('jobs.show', compact('job'));
    }

    /**
     * ADMIN CREATE FORM
     */
    public function create()
    {
        $sitesQuery = Site::query()->select(['id', 'code', 'name'])->orderBy('code');
        if (method_exists($this, 'restrictSitesForUser')) {
            $sitesQuery = $this->restrictSitesForUser($sitesQuery, Auth::user());
        }
        $sites = $sitesQuery->get();

        // (opsional) dropdown company
        $companies = Company::query()->select(['id', 'code', 'name'])->orderBy('code')->get();

        return view('admin.jobs.create', compact('sites', 'companies'));
    }

    /**
     * ADMIN STORE -> openings disinkron dari manpower_requirements
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            'code'            => ['required', 'string', 'max:50'],
            'title'           => ['required', 'string', 'max:200'],
            'division'        => ['nullable', 'string', 'max:100'],
            'level'           => ['nullable', 'string', 'max:100'],
            'employment_type' => ['required', Rule::in(['intern', 'contract', 'fulltime'])], // enum DB
            'status'          => ['required', Rule::in(['draft', 'open', 'closed'])],
            'description'     => ['nullable', 'string'],

            // NEW (opsional)
            'skills'          => ['nullable'], // boleh array/string; dinormalisasi di mutator model
            'keywords'        => ['nullable', 'string', 'max:500'],

            'site_id'         => ['nullable', 'uuid', 'exists:sites,id', 'required_without:site_code'],
            'site_code'       => ['nullable', 'string', 'exists:sites,code', 'required_without:site_id'],

            'company_id'      => ['nullable', 'uuid', 'exists:companies,id', 'prohibits:company_code'],
            'company_code'    => ['nullable', 'string', 'exists:companies,code', 'prohibits:company_id'],
        ]);

        $siteId    = $this->resolveSiteId($payload['site_id'] ?? null, $payload['site_code'] ?? null);
        $companyId = $this->resolveCompanyId($payload['company_id'] ?? null, $payload['company_code'] ?? null);

        // Validasi: code unik per company
        $this->validateUniqueJobCodePerCompany($payload['code'], $companyId);

        $this->checkUserCanUseSite($siteId);

        $job = DB::transaction(function () use ($payload, $siteId, $companyId) {
            unset($payload['site_id'], $payload['site_code'], $payload['company_id'], $payload['company_code']);

            $payload['site_id']     = $siteId;
            $payload['company_id']  = $companyId; // boleh null
            $payload['openings']    = 0;          // disinkron dari manpower
            $payload['created_by']  = Auth::id();
            $payload['updated_by']  = Auth::id();

            /** @var Job $job */
            $job = Job::create($payload);

            // Sync openings dari manpower_requirements
            $sum = (int) $job->manpowerRequirements()->sum('budget_headcount');
            if ($sum !== (int) $job->openings) {
                $job->update(['openings' => $sum, 'updated_by' => Auth::id()]);
            }

            return $job->fresh()->loadMissing('site:id,code,name', 'company:id,code,name');
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Job created (openings disinkron dari manpower).',
                'job'      => $job,
                'redirect' => route('admin.jobs.index'),
            ], 201);
        }

        return redirect()->route('admin.jobs.index')->with('success', 'Job created.');
    }

    /**
     * ADMIN EDIT FORM
     */
    public function edit(Job $job)
    {
        $job->loadMissing('site:id,code,name', 'company:id,code,name');

        $sitesQuery = Site::query()->select(['id', 'code', 'name'])->orderBy('code');
        if (method_exists($this, 'restrictSitesForUser')) {
            $sitesQuery = $this->restrictSitesForUser($sitesQuery, Auth::user());
        }
        $sites = $sitesQuery->get();

        $companies = Company::query()->select(['id', 'code', 'name'])->orderBy('code')->get();

        return view('admin.jobs.edit', compact('job', 'sites', 'companies'));
    }

    /**
     * ADMIN UPDATE -> openings disinkron dari manpower_requirements
     */
    public function update(Request $request, Job $job)
    {
        $payload = $request->validate([
            'code'            => ['required', 'string', 'max:50'],
            'title'           => ['required', 'string', 'max:200'],
            'division'        => ['nullable', 'string', 'max:100'],
            'level'           => ['nullable', 'string', 'max:100'],
            'employment_type' => ['required', Rule::in(['intern', 'contract', 'fulltime'])],
            'status'          => ['required', Rule::in(['draft', 'open', 'closed'])],
            'description'     => ['nullable', 'string'],

            // NEW (opsional)
            'skills'          => ['nullable'], // array/string/JSON → normalisasi di mutator
            'keywords'        => ['nullable', 'string', 'max:500'],

            'site_id'         => ['nullable', 'uuid', 'exists:sites,id', 'required_without:site_code'],
            'site_code'       => ['nullable', 'string', 'exists:sites,code', 'required_without:site_id'],

            'company_id'      => ['nullable', 'uuid', 'exists:companies,id', 'prohibits:company_code'],
            'company_code'    => ['nullable', 'string', 'exists:companies,code', 'prohibits:company_id'],
        ]);

        $siteId    = $this->resolveSiteId($payload['site_id'] ?? null, $payload['site_code'] ?? null);
        $companyId = $this->resolveCompanyId($payload['company_id'] ?? null, $payload['company_code'] ?? null);

        // Validasi: code unik per company (abaikan baris saat ini)
        $this->validateUniqueJobCodePerCompany($payload['code'], $companyId, $job->id);

        if ($siteId) {
            $this->checkUserCanUseSite($siteId);
        }

        DB::transaction(function () use ($payload, $job, $siteId, $companyId) {
            unset($payload['site_id'], $payload['site_code'], $payload['openings'], $payload['company_id'], $payload['company_code']);

            if ($siteId) {
                $payload['site_id'] = $siteId;
            }
            // company_id bisa diubah/di-null-kan
            $payload['company_id'] = $companyId;

            $payload['updated_by'] = Auth::id();

            $job->update($payload);

            $sum = (int) $job->manpowerRequirements()->sum('budget_headcount');
            if ($sum !== (int) $job->openings) {
                $job->update(['openings' => $sum, 'updated_by' => Auth::id()]);
            }
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Job updated (openings disinkron dari manpower).',
                'job'      => $job->fresh()->loadMissing('site:id,code,name', 'company:id,code,name'),
                'redirect' => route('admin.jobs.index'),
            ]);
        }

        return redirect()->route('admin.jobs.index')->with('success', 'Job updated.');
    }

    /**
     * ADMIN DELETE
     */
    public function destroy(Request $request, Job $job)
    {
        $job->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Job deleted.',
                'redirect' => route('admin.jobs.index'),
            ]);
        }

        return redirect()->route('admin.jobs.index')->with('success', 'Job deleted.');
    }

    // =====================
    // Helpers (aman & rapi)
    // =====================

    private function resolveSiteId(?string $siteId, ?string $siteCode): string
    {
        if ($siteId) return $siteId;

        if ($siteCode) {
            $found = Site::where('code', $siteCode)->value('id');
            abort_unless($found, 422, 'Site code tidak valid.');
            return (string) $found;
        }

        abort(422, 'Site harus diisi via site_id atau site_code.');
    }

    /** company_id opsional (boleh null). Jika ada company_code, di-resolve; jika keduanya null → return null. */
    private function resolveCompanyId(?string $companyId, ?string $companyCode): ?string
    {
        if ($companyId) return $companyId;

        if ($companyCode) {
            $found = Company::where('code', $companyCode)->value('id');
            abort_unless($found, 422, 'Company code tidak valid.');
            return (string) $found;
        }

        return null; // jobs boleh tanpa company
    }

    /** Enforce unik (company_id, code). Jika $companyId null → unik untuk company_id NULL saja (ikut perilaku DB). */
    private function validateUniqueJobCodePerCompany(string $code, ?string $companyId, ?string $ignoreJobId = null): void
    {
        $exists = Job::query()
            ->when($ignoreJobId, fn($q) => $q->where('id', '!=', $ignoreJobId))
            ->where('code', $code)
            ->where(function ($q) use ($companyId) {
                if (is_null($companyId)) {
                    $q->whereNull('company_id');
                } else {
                    $q->where('company_id', $companyId);
                }
            })
            ->exists();

        abort_if($exists, 422, 'Kode lowongan sudah dipakai pada company tersebut.');
    }

    private function checkUserCanUseSite(string $siteId): void
    {
        // Jika punya relasi user->sites:
        // abort_if(!Auth::user()->sites()->whereKey($siteId)->exists(), 403, 'Tidak berwenang memilih site ini.');
    }

    private function restrictSitesForUser($sitesQuery, $user)
    {
        // return $sitesQuery->whereIn('id', $user->sites()->pluck('sites.id'));
        return $sitesQuery;
    }
}
