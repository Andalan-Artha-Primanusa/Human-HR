<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\Job;
use App\Models\Site;
use App\Models\Company;
use App\Models\ManpowerRequirement;
use App\Services\MineproRfrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'division' => ['nullable', 'string', 'max:100'],
            'site' => ['nullable', 'string', 'max:50'], // site code
            'company' => ['nullable', 'string', 'max:50', 'prohibits:company_id'], // company code
            'company_id' => ['nullable', 'uuid', 'exists:companies,id', 'prohibits:company'],
            'type' => ['nullable', Rule::in(['intern', 'contract', 'fulltime'])], // enum DB
            'term' => ['nullable', 'string', 'max:200'],
            'sort' => ['nullable', Rule::in(['latest', 'oldest', 'title'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $perPage = $data['per_page'] ?? 12;
        $sort = $data['sort'] ?? 'latest';
        $division = isset($data['division'])
            ? Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) $data['division'])) ?? '', 100, '')
            : null;
        $siteCode = isset($data['site'])
            ? Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) $data['site'])) ?? '', 50, '')
            : null;
        $companyCode = isset($data['company'])
            ? Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) $data['company'])) ?? '', 50, '')
            : null;
        $term = isset($data['term'])
            ? Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim((string) $data['term'])) ?? '', 200, '')
            : null;

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
                'site:id,code,name,address,region',
                'company:id,code,name',
            ]);

        if (!$isAdminRoute) {
            $baseQuery->where('status', 'open');
            if (Auth::check()) {
                $baseQuery->with(['applications' => function ($q) {
                    $q->where('user_id', Auth::id())->select('id', 'job_id', 'user_id', 'current_stage');
                }]);
            }
        }

        if (!empty($division)) {
            $baseQuery->inDivision($division);
        }

        if (!empty($siteCode)) {
            $baseQuery->whereHas('site', fn($q) => $q->where('code', $siteCode));
        }

        // Filter by company_id atau company code
        if (!empty($data['company_id'])) {
            $baseQuery->where('company_id', $data['company_id']);
        } elseif (!empty($companyCode)) {
            $baseQuery->whereHas('company', fn($q) => $q->where('code', $companyCode));
        }

        if (!empty($data['type'])) {
            $baseQuery->where('employment_type', $data['type']);
        }

        if (!empty($term)) {
            $baseQuery->search($term);
        }

        // 3) Sorting
        match ($sort) {
            'oldest' => $baseQuery->orderBy('created_at', 'asc')->orderBy('id', 'asc'),
            'title' => $baseQuery->orderBy('title')->orderByDesc('created_at'),
            default => $baseQuery->orderBy('created_at', 'desc')->orderBy('id', 'desc'), // latest
        };


        // 4) Micro-cache untuk publik (hanya untuk guest)
        if (!$isAdminRoute && !Auth::check()) {
            $cacheKey = 'jobs.public.' . md5(json_encode([
                'division' => $division,
                'site' => $siteCode,
                'company' => $companyCode,
                'company_id' => $data['company_id'] ?? null,
                'type' => $data['type'] ?? null,
                'term' => $term,
                'sort' => $sort,
                'page' => $data['page'] ?? 1,
                'per_page' => $perPage,
            ]));

            $jobs = Cache::remember($cacheKey, 30, function () use ($baseQuery, $perPage) {
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
    public function show(Request $request, Job $job, MineproRfrService $mineproRfrService)
    {
        $this->authorize('view', $job);

        $job->loadMissing([
            'site:id,code,name,region,timezone,address',
            'company:id,code,name',
        ])->loadCount('applications');

        $myApp = null;
        $mineproProgress = null;
        $meProfile = null;
        if (Auth::check()) {
            $meProfile = \App\Models\CandidateProfile::query()
                ->where('user_id', Auth::id())
                ->first(['id', 'user_id', 'nik']);

            $myApp = $job->applications()
                ->where('user_id', Auth::id())
                ->with(['stages', 'stages.actor', 'stages.user', 'offer', 'job:id,code', 'user.candidateProfile:id,user_id,nik'])
                ->latest()
                ->first();

            if ($myApp) {
                $mineproProgress = $mineproRfrService->progressForApplication(
                    $myApp,
                    $request->query('minepro_start_date'),
                    $request->query('minepro_end_date')
                );
            } elseif ($meProfile && filled($meProfile->nik)) {
                $mineproProgress = $mineproRfrService->progressForRfrAndNik(
                    (string) $job->code,
                    (string) $meProfile->nik,
                    $request->query('minepro_start_date'),
                    $request->query('minepro_end_date')
                );
            }
        }

        // Ambil POH hanya jika user superadmin/hr/admin
        $user = auth()->user();
        $pohs = null;
        if ($user && in_array($user->role, ['superadmin', 'hr', 'admin'])) {
            $pohs = \App\Models\Poh::query()->orderBy('name')->get(['id', 'name']);
        }

        return view('jobs.show', compact('job', 'pohs', 'myApp', 'mineproProgress'));
    }

    /**
     * ADMIN CREATE FORM
     */
    public function create(Request $request, MineproRfrService $rfrService)
    {
        $sitesQuery = Site::query()->select(['id', 'code', 'name'])->orderBy('code');
        if (method_exists($this, 'restrictSitesForUser')) {
            $sitesQuery = $this->restrictSitesForUser($sitesQuery, Auth::user());
        }
        $sites = $sitesQuery->get();

        // (opsional) dropdown company
        $companies = Company::query()->select(['id', 'code', 'name'])->orderBy('code')->get();

        $rfrStartDate = $request->query('rfr_start_date', now()->startOfMonth()->format('Y-m-d'));
        $rfrStartDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $rfrStartDate)
            ? (string) $rfrStartDate
            : now()->startOfMonth()->format('Y-m-d');
        $rfrVacancies = $rfrService->approvedVacancies($rfrStartDate);
        $rfrMeta = $rfrService->lastVacancyMeta();

        return view('admin.jobs.create', compact('sites', 'companies', 'rfrVacancies', 'rfrStartDate', 'rfrMeta'));
    }

    /**
     * ADMIN STORE -> openings disinkron dari manpower_requirements
     */
    public function store(StoreJobRequest $request)
    {
        $payload = $request->validated();

        $siteId = $this->resolveSiteId($payload['site_id'] ?? null, $payload['site_code'] ?? null);
        $companyId = $this->resolveCompanyId($payload['company_id'] ?? null, $payload['company_code'] ?? null);

        // Validasi: code unik per company → auto-generate suffix bila RFR/code sudah dipakai
        $requestedCode = $payload['code'];
        $payload['code'] = $this->resolveUniqueJobCode($payload['code'], $companyId);

        $this->checkUserCanUseSite($siteId);

        $job = DB::transaction(function () use ($payload, $siteId, $companyId) {
            $initialOpenings = (int) ($payload['initial_openings'] ?? $payload['openings'] ?? 0);
            unset($payload['site_id'], $payload['site_code'], $payload['company_id'], $payload['company_code'], $payload['initial_openings']);

            $payload['site_id'] = $siteId;
            $payload['company_id'] = $companyId; // boleh null
            $payload['openings'] = 0;          // disinkron dari manpower
            $payload['created_by'] = Auth::id();
            $payload['updated_by'] = Auth::id();

            /** @var Job $job */
            $job = Job::create($payload);

            if ($initialOpenings > 0) {
                ManpowerRequirement::create([
                    'job_id' => $job->id,
                    'asset_name' => 'RFR MinePro',
                    'assets_count' => $initialOpenings,
                    'ratio_per_asset' => 1,
                    'filled_headcount' => 0,
                ]);
            }

            // Sync openings dari manpower_requirements
            $sum = (int) $job->manpowerRequirements()->sum('budget_headcount');
            if ($sum !== (int) $job->openings) {
                $job->update(['openings' => $sum, 'updated_by' => Auth::id()]);
            }

            return $job->fresh()->loadMissing('site:id,code,name', 'company:id,code,name');
        });

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Job created (openings disinkron dari manpower).',
                'job' => $job,
                'code_renamed' => $requestedCode !== $payload['code'],
                'redirect' => route('admin.jobs.index'),
            ], 201);
        }

        $note = $requestedCode !== $payload['code'] ? ' (code diganti menjadi ' . $payload['code'] . ' karena sudah dipakai)' : '';
        return redirect()->route('admin.jobs.index')->with('success', 'Job created.' . $note);
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
    public function update(UpdateJobRequest $request, Job $job)
    {
        $payload = $request->validated();

        $siteId = $this->resolveSiteId($payload['site_id'] ?? null, $payload['site_code'] ?? null);
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
                'message' => 'Job updated (openings disinkron dari manpower).',
                'job' => $job->fresh()->loadMissing('site:id,code,name', 'company:id,code,name'),
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
                'message' => 'Job deleted.',
                'redirect' => route('admin.jobs.index'),
            ]);
        }

        return redirect()->route('admin.jobs.index')->with('success', 'Job deleted.');
    }

    public function toggle(Request $request, Job $job)
    {
        $this->authorize('update', $job);

        $nextStatus = $job->status === 'open' ? 'closed' : 'open';
        $job->update([
            'status' => $nextStatus,
            'updated_by' => Auth::id(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $nextStatus,
                'message' => $nextStatus === 'open' ? 'Lowongan dibuka.' : 'Lowongan ditutup.',
            ]);
        }

        return back()->with('success', $nextStatus === 'open' ? 'Lowongan dibuka.' : 'Lowongan ditutup.');
    }

    // =====================
    // Helpers (aman & rapi)
    // =====================

    private function resolveSiteId(?string $siteId, ?string $siteCode): string
    {
        if ($siteId && $siteCode) {
            $matched = Site::whereKey($siteId)->where('code', $siteCode)->exists();
            abort_unless($matched, 422, 'site_id dan site_code tidak cocok.');

            return $siteId;
        }

        if ($siteId)
            return $siteId;

        if ($siteCode) {
            $siteCode = trim($siteCode);
            $site = Site::firstOrCreate(
                ['code' => $siteCode],
                [
                    'name' => $siteCode,
                    'is_active' => true,
                    'meta' => ['source' => 'minepro_rfr'],
                ]
            );

            return (string) $site->id;
        }

        abort(422, 'Site harus diisi via site_id atau site_code.');
    }

    /** company_id opsional (boleh null). Jika ada company_code, di-resolve; jika keduanya null → return null. */
    private function resolveCompanyId(?string $companyId, ?string $companyCode): ?string
    {
        if ($companyId && $companyCode) {
            $matched = Company::whereKey($companyId)->where('code', $companyCode)->exists();
            abort_unless($matched, 422, 'company_id dan company_code tidak cocok.');

            return $companyId;
        }

        if ($companyId)
            return $companyId;

        if ($companyCode) {
            $companyCode = trim($companyCode);
            $company = Company::firstOrCreate(
                ['code' => $companyCode],
                [
                    'name' => $companyCode,
                    'status' => 'active',
                    'meta' => ['source' => 'minepro_rfr'],
                ]
            );

            return (string) $company->id;
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

    /** Kembalikan code unik: jika code sudah dipakai (per company), tambahkan suffix -2, -3, dst. */
    private function resolveUniqueJobCode(string $code, ?string $companyId, ?string $ignoreJobId = null): string
    {
        $candidate = trim($code);
        $suffix = 2;

        while ($this->jobCodeExists($candidate, $companyId, $ignoreJobId)) {
            $base = (string) preg_replace('/-\d+$/', '', $candidate);
            $suffixText = '-' . $suffix;
            // jaga panjang maks 50 karakter kolom `code`
            $candidate = mb_substr($base, 0, 50 - mb_strlen($suffixText)) . $suffixText;
            $suffix++;
        }

        return $candidate;
    }

    private function jobCodeExists(string $code, ?string $companyId, ?string $ignoreJobId = null): bool
    {
        return Job::query()
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
    }

    private function checkUserCanUseSite(string $siteId): void
    {
        $user = Auth::user();
        $relation = 'sites';

        // Default: jika relasi sites tidak tersedia, jangan memblokir flow.
        if (!$user || !method_exists($user, $relation)) {
            return;
        }

        $sites = $user->{$relation}();
        abort_if(!$sites->whereKey($siteId)->exists(), 403, 'Tidak berwenang memilih site ini.');
    }

    private function restrictSitesForUser($sitesQuery, $user)
    {
        $relation = 'sites';

        if ($user && method_exists($user, $relation)) {
            $sites = $user->{$relation}();
            return $sitesQuery->whereIn('id', $sites->select('sites.id'));
        }

        return $sitesQuery;
    }
}
