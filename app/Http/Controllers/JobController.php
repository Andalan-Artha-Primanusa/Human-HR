<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobController extends Controller
{
    /**
     * PUBLIC & ADMIN LIST
     */
    public function index(Request $request)
    {
        $isAdminRoute = $request->routeIs('admin.*');

        $jobs = Job::query()
            ->select([
                'id','code','title','division','employment_type','openings',
                'site_id','status','description','created_at'
            ])
            // List cukup butuh code+name; biarkan ringan
            ->with(['site:id,code,name'])
            ->when(!$isAdminRoute, fn($q) => $q->where('status', 'open'))
            ->when($request->filled('division'), fn($q) => $q->where('division', $request->string('division')->toString()))
            ->when($request->filled('site'), function ($q) use ($request) {
                $siteCode = $request->string('site')->toString();
                $q->whereHas('site', fn($qq) => $qq->where('code', $siteCode));
            })
            ->when($request->filled('type'), fn($q) => $q->where('employment_type', $request->string('type')->toString()))
            ->when($request->filled('term'), function ($q) use ($request) {
                $term = '%'.$request->string('term')->toString().'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', $term)
                       ->orWhere('description', 'like', $term)
                       ->orWhere('code', 'like', $term);
                });
            })
            ->when(
                $request->string('sort')->toString(),
                function ($q, $sort) {
                    return match ($sort) {
                        'oldest' => $q->orderBy('created_at', 'asc'),
                        'title'  => $q->orderBy('title'),
                        default  => $q->orderBy('created_at', 'desc'),
                    };
                },
                fn($q) => $q->orderBy('created_at', 'desc')
            )
            ->paginate(12)
            ->withQueryString();

        $view = $isAdminRoute ? 'admin.jobs.index' : 'jobs.index';

        return view($view, ['jobs' => $jobs]);
    }

    /**
     * PUBLIC DETAIL
     */
    public function show(Job $job)
    {
        // >>> penting: muat kolom site yang dipakai di Blade (region, timezone, address)
        $job->loadMissing([
            'site:id,code,name,region,timezone,address',
        ])->loadCount('applications'); // opsional untuk footer “Jumlah Pelamar”

        return view('jobs.show', compact('job'));
    }

    /**
     * ADMIN CREATE FORM
     */
    public function create()
    {
        $sites = Site::orderBy('code')->get(['id','code','name']);
        return view('admin.jobs.create', compact('sites'));
    }

    /**
     * ADMIN STORE -> redirect ke index + JSON response (AJAX)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'            => ['required','string','max:50','unique:jobs,code'],
            'title'           => ['required','string','max:200'],
            'division'        => ['nullable','string','max:100'],
            'level'           => ['nullable','string','max:100'],
            // sinkron dengan Blade: tambah parttime & freelance
            'employment_type' => ['required', Rule::in(['intern','contract','fulltime','parttime','freelance'])],
            'openings'        => ['required','integer','min:1'],
            'status'          => ['required', Rule::in(['draft','open','closed'])],
            'description'     => ['nullable','string'],
            'site_id'         => ['nullable','exists:sites,id','required_without:site_code'],
            'site_code'       => ['nullable','string','exists:sites,code','required_without:site_id'],
        ]);

        // Normalisasi site
        $siteId   = $data['site_id'] ?? null;
        $siteCode = $data['site_code'] ?? null;
        unset($data['site_id'], $data['site_code']);
        if (!$siteId && $siteCode) {
            $siteId = Site::where('code', $siteCode)->value('id');
        }
        $data['site_id'] = $siteId;

        $job = Job::create($data);

        // optional sinkronisasi manpower
        if (method_exists($job, 'manpowerRequirement') && !$job->manpowerRequirement) {
            $job->manpowerRequirement()->create([
                'budget_headcount' => (int) $data['openings'],
                'filled_headcount' => 0,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Job created.',
                'job'      => $job->loadMissing('site:id,code,name'),
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
        $job->loadMissing('site:id,code,name');
        $sites = Site::orderBy('code')->get(['id','code','name']);
        return view('admin.jobs.edit', compact('job','sites'));
    }

    /**
     * ADMIN UPDATE -> redirect ke index + JSON response (AJAX)
     */
    public function update(Request $request, Job $job)
    {
        $data = $request->validate([
            'code'            => ['required','string','max:50', Rule::unique('jobs','code')->ignore($job->id)],
            'title'           => ['required','string','max:200'],
            'division'        => ['nullable','string','max:100'],
            'level'           => ['nullable','string','max:100'],
            'employment_type' => ['required', Rule::in(['intern','contract','fulltime','parttime','freelance'])],
            'openings'        => ['required','integer','min:1'],
            'status'          => ['required', Rule::in(['draft','open','closed'])],
            'description'     => ['nullable','string'],
            'site_id'         => ['nullable','exists:sites,id','required_without:site_code'],
            'site_code'       => ['nullable','string','exists:sites,code','required_without:site_id'],
        ]);

        $siteId   = $data['site_id'] ?? null;
        $siteCode = $data['site_code'] ?? null;
        unset($data['site_id'], $data['site_code']);

        if (!$siteId && $siteCode) {
            $siteId = Site::where('code', $siteCode)->value('id');
        }
        if ($siteId) {
            $data['site_id'] = $siteId;
        }

        $job->update($data);

        if (method_exists($job, 'manpowerRequirement') && $job->manpowerRequirement) {
            $job->manpowerRequirement->update([
                'budget_headcount' => (int) $data['openings'],
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message'  => 'Job updated.',
                'job'      => $job->fresh()->loadMissing('site:id,code,name'),
                'redirect' => route('admin.jobs.index'),
            ]);
        }

        return redirect()->route('admin.jobs.index')->with('success', 'Job updated.');
    }

    /**
     * ADMIN DELETE (tetap balik ke index, plus JSON opsional)
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
}
