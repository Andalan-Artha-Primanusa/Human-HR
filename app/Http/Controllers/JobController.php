<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * PUBLIC & ADMIN LIST
     */
    public function index(Request $request)
    {
        $isAdminRoute = $request->routeIs('admin.*');

        $jobs = Job::query()
            // pilih kolom inti dari jobs
            ->select(['id','code','title','division','employment_type','openings','site_id','status','description','created_at'])
            // eager load site (kode/nama)
            ->with(['site:id,code,name'])

            // public hanya tampilkan yang open
            ->when(!$isAdminRoute, fn($q) => $q->where('status', 'open'))

            // filter division (exact; kalau mau LIKE tinggal ubah)
            ->when($request->filled('division'), fn($q) => $q->where('division', $request->string('division')->toString()))

            // filter site via relasi site.code (bukan kolom site_code)
            ->when($request->filled('site'), function ($q) use ($request) {
                $siteCode = $request->string('site')->toString();
                $q->whereHas('site', fn($qq) => $qq->where('code', $siteCode));
            })

            // filter tipe kerja
            ->when($request->filled('type'), fn($q) => $q->where('employment_type', $request->string('type')->toString()))

            // search sederhana
            ->when($request->filled('term'), function ($q) use ($request) {
                $term = '%'.$request->string('term')->toString().'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('title', 'like', $term)
                       ->orWhere('description', 'like', $term)
                       ->orWhere('code', 'like', $term);
                });
            })

            // sorting
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
        // pastikan site ikut diload untuk tampilan
        $job->loadMissing('site:id,code,name');
        return view('jobs.show', compact('job'));
    }

    /**
     * ADMIN CREATE FORM
     */
    public function create()
    {
        return view('admin.jobs.create');
    }

    /**
     * ADMIN STORE
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code'            => 'required|string|max:50|unique:jobs,code',
            'title'           => 'required|string|max:200',
            // form lama mungkin kirim site_code; kita validasi & pakai mutator untuk set site_id
            'site_code'       => 'nullable|string|max:50',
            'division'        => 'nullable|string|max:100',
            'level'           => 'nullable|string|max:100',
            'employment_type' => 'required|in:intern,contract,fulltime',
            'openings'        => 'required|integer|min:1',
            'status'          => 'required|in:draft,open,closed',
            'description'     => 'nullable|string',
        ]);

        // Ambil & lepas site_code dari mass assignment (karena tidak ada kolomnya)
        $siteCode = $data['site_code'] ?? null;
        unset($data['site_code']);

        // Buat job tanpa site_code
        $job = Job::create($data);

        // Set via mutator (akan mengisi site_id berdasarkan sites.code)
        if ($siteCode !== null) {
            $job->site_code = $siteCode; // memakai setSiteCodeAttribute di model
            $job->save();
        }

        // optionally: create manpower requirement
        $job->manpowerRequirement()->create([
            'budget_headcount' => (int) $data['openings'],
            'filled_headcount' => 0,
        ]);

        return redirect()->route('admin.jobs.edit', $job)->with('ok', 'Job created');
    }

    /**
     * ADMIN EDIT FORM
     */
    public function edit(Job $job)
    {
        $job->loadMissing('site:id,code,name');
        return view('admin.jobs.edit', compact('job'));
    }

    /**
     * ADMIN UPDATE
     */
    public function update(Request $request, Job $job)
    {
        $data = $request->validate([
            'code'            => 'required|string|max:50|unique:jobs,code,' . $job->id . ',id',
            'title'           => 'required|string|max:200',
            'site_code'       => 'nullable|string|max:50', // tetap support form lama
            'division'        => 'nullable|string|max:100',
            'level'           => 'nullable|string|max:100',
            'employment_type' => 'required|in:intern,contract,fulltime',
            'openings'        => 'required|integer|min:1',
            'status'          => 'required|in:draft,open,closed',
            'description'     => 'nullable|string',
        ]);

        $siteCode = $data['site_code'] ?? null;
        unset($data['site_code']);

        // Update field utama
        $job->update($data);

        // Sinkronkan site via mutator kalau ada perubahan
        if ($siteCode !== null) {
            $job->site_code = $siteCode; // mutator akan set site_id
            $job->save();
        }

        // sync manpower budget ke openings (opsional)
        if ($job->manpowerRequirement) {
            $job->manpowerRequirement->update([
                'budget_headcount' => (int) $data['openings'],
            ]);
        }

        return back()->with('ok', 'Job updated');
    }

    /**
     * ADMIN DELETE
     */
    public function destroy(Job $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.index')->with('ok', 'Job deleted');
    }
}
