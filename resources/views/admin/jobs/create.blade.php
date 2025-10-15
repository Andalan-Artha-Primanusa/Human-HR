@extends('layouts.karir', [ 'title' => 'Admin · Create Job' ])


@section('content')
<form class="card" method="POST" action="{{ route('admin.jobs.store') }}">@csrf
    <div class="card-body grid gap-4 md:grid-cols-2">
        <div><label class="label">Code</label><input class="input" name="code" required></div>
        <div><label class="label">Title</label><input class="input" name="title" required></div>
        <div><label class="label">Division</label><input class="input" name="division"></div>
        <div><label class="label">Site</label><input class="input" name="site_code"></div>
        <div><label class="label">Level</label><input class="input" name="level"></div>
        <div><label class="label">Employment Type</label>
            <select class="input" name="employment_type" required>
                <option value="fulltime">Fulltime</option>
                <option value="contract">Contract</option>
                <option value="intern">Intern</option>
            </select>
        </div>
        <div><label class="label">Openings</label><input class="input" type="number" name="openings" min="1" value="1"></div>
        <div><label class="label">Status</label>
            <select class="input" name="status">
                <option value="draft">Draft</option>
                <option value="open" selected>Open</option>
                <option value="closed">Closed</option>
            </select>
        </div>
        <div class="md:col-span-2"><label class="label">Description</label><textarea class="input min-h-[160px]" name="description"></textarea></div>
    </div>
    <div class="flex items-center justify-end gap-3 px-5 pb-5">
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost">Cancel</a>
        <button class="btn btn-primary">Save</button>
    </div>
</form>
@endsection