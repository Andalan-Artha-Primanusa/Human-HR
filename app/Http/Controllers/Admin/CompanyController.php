<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Company\StoreCompanyRequest;
use App\Http\Requests\Admin\Company\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct()
    {
        // $this->middleware('can:manage-master-data');
        // $this->authorizeResource(Company::class, 'company');
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q',''));
        $status = $request->query('status');

        $items = Company::query()
            ->when($status, fn($qq) => $qq->where('status',$status))
            ->search($q)
            ->orderBy('name')
            ->paginate(12)
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
        Company::create($request->validated());

        // Langsung balik ke index
        return redirect()
            ->route('admin.companies.index')
            ->with('ok', 'Company created.');
    }

    public function show(Company $company): View
    {
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
        $company->update($request->validated());

        // Langsung balik ke index
        return redirect()
            ->route('admin.companies.index')
            ->with('ok', 'Company updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()
            ->route('admin.companies.index')
            ->with('ok', 'Company removed.');
    }
}
