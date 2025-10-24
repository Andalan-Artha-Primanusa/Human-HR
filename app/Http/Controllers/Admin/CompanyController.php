<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Company\StoreCompanyRequest;
use App\Http\Requests\Admin\Company\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct()
    {
        // $this->middleware('can:manage-master-data');
        // $this->authorizeResource(Company::class, 'company');
    }

    /**
     * List: ORM-only, hemat kolom, adaptif FULLTEXT (fallback LIKE), cursor paginate.
     */
    public function index(Request $request): View
    {
        // Sanitize pencarian
        $qRaw   = (string) $request->query('q', '');
        $qClean = trim(preg_replace('/[^\pL\pN\s\-\_\.]/u', '', $qRaw) ?? '');
        $q      = Str::limit($qClean, 80, '');
        $status = $request->query('status');

        // Deteksi dukungan FULLTEXT (cache 1 hari)
        $hasFulltext = Cache::remember('companies:has_fulltext', 86400, function () {
            try {
                $rows = DB::select("SHOW INDEX FROM companies");
                foreach ($rows as $r) {
                    $type = $r->Index_type ?? ($r->Index_type ?? null);
                    if ($type === 'FULLTEXT') return true;
                }
            } catch (\Throwable $e) { /* fallback */ }
            return false;
        });

        $items = Company::query()
            ->select(['id','name','code','status','created_at'])
            ->when($status, fn($q2) => $q2->where('status', $status))
            ->when($q !== '', function ($q2) use ($q, $hasFulltext) {
                if ($hasFulltext) {
                    return $q2->whereRaw("MATCH(name, code, alias) AGAINST (? IN NATURAL LANGUAGE MODE)", [$q]);
                }
                $like = '%'.$q.'%';
                return $q2->where(function ($w) use ($like) {
                    $w->where('name','like',$like)
                      ->orWhere('code','like',$like)
                      ->orWhere('alias','like',$like);
                });
            })
            ->orderBy('name')
            ->cursorPaginate(20)
            ->withQueryString();

        return view('admin.companies.index', compact('items','q','status'));
    }

    public function create(): View
    {
        $record = new Company(['status' => 'active']);
        return view('admin.companies.create', compact('record'));
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            Company::create($request->validated());
        });

        return redirect()
            ->route('admin.companies.index')
            ->with('ok', 'Company created.');
    }

    public function show(Company $company): View
    {
        // Eager secukupnya untuk metrik/relasi yang ditampilkan
        $record = $company->loadCount('jobs');
        return view('admin.companies.show', compact('record'));
    }

    public function edit(Company $company): View
    {
        $record = $company;
        return view('admin.companies.edit', compact('record'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        DB::transaction(function () use ($request, $company) {
            $company->update($request->validated());
        });

        return redirect()
            ->route('admin.companies.index')
            ->with('ok', 'Company updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        DB::transaction(function () use ($company) {
            $company->delete();
        });

        return redirect()
            ->route('admin.companies.index')
            ->with('ok', 'Company removed.');
    }
}
