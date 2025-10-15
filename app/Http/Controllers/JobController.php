<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    // PUBLIC & ADMIN LIST
    public function index(Request $request)
    {
        $q = Job::query()
            ->when(!$request->routeIs('admin.*'), fn($q) => $q->where('status','open'))
            ->when($request->filled('division'), fn($q) => $q->where('division', $request->division))
            ->when($request->filled('site'), fn($q) => $q->where('site_code', $request->site))
            ->when($request->filled('term'), function($q) use ($request) {
                $term = '%'.$request->term.'%';
                $q->where(function($qq) use ($term){
                    $qq->where('title','like',$term)->orWhere('description','like',$term)->orWhere('code','like',$term);
                });
            })
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $view = $request->routeIs('admin.*') ? 'admin.jobs.index' : 'jobs.index';
        return view($view, ['jobs' => $q]);
    }

    // PUBLIC DETAIL
    public function show(Job $job)
    {
        return view('jobs.show', compact('job'));
    }

    // ADMIN CREATE FORM
    public function create()
    {
        return view('admin.jobs.create');
    }

    // ADMIN STORE
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'            => 'required|string|max:50|unique:jobs,code',
            'title'           => 'required|string|max:200',
            'site_code'       => 'nullable|string|max:50',
            'division'        => 'nullable|string|max:100',
            'level'           => 'nullable|string|max:100',
            'employment_type' => 'required|in:intern,contract,fulltime',
            'openings'        => 'required|integer|min:1',
            'status'          => 'required|in:draft,open,closed',
            'description'     => 'nullable|string',
        ]);

        $job = Job::create($data);

        // optionally: create manpower requirement
        $job->manpowerRequirement()->create([
            'budget_headcount' => $data['openings'],
            'filled_headcount' => 0,
        ]);

        return redirect()->route('admin.jobs.edit', $job)->with('ok','Job created');
    }

    // ADMIN EDIT FORM
    public function edit(Job $job)
    {
        return view('admin.jobs.edit', compact('job'));
    }

    // ADMIN UPDATE
    public function update(Request $request, Job $job)
    {
        $data = $request->validate([
            'code'            => 'required|string|max:50|unique:jobs,code,'.$job->id.',id',
            'title'           => 'required|string|max:200',
            'site_code'       => 'nullable|string|max:50',
            'division'        => 'nullable|string|max:100',
            'level'           => 'nullable|string|max:100',
            'employment_type' => 'required|in:intern,contract,fulltime',
            'openings'        => 'required|integer|min:1',
            'status'          => 'required|in:draft,open,closed',
            'description'     => 'nullable|string',
        ]);

        $job->update($data);

        // sync manpower budget to openings (optional)
        if ($job->manpowerRequirement) {
            $job->manpowerRequirement->update(['budget_headcount' => $data['openings']]);
        }

        return back()->with('ok','Job updated');
    }

    // ADMIN DELETE
    public function destroy(Job $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.index')->with('ok','Job deleted');
    }
}
