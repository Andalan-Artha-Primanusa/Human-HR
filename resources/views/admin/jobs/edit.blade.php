@extends('layouts.karir', [ 'title' => 'Admin · Edit Job' ])


@section('content')
<form class="card" method="POST" action="{{ route('admin.jobs.update', $job) }}">@csrf @method('PUT')
    <div class="card-body grid gap-4 md:grid-cols-2">
        <div><label class="label">Code</label><input class="input" name="code" value="{{ $job->code }}" required></div>
        <div><label class="label">Title</label><input class="input" name="title" value="{{ $job->title }}" required></div>
        <div><label class="label">Division</label><input class="input" name="division" value="{{ $job->division }}"></div>
        <div><label class="label">Site</label><input class="input" name="site_code" value="{{ $job->site_code }}"></div>
        <div><label class="label">Level</label><input class="input" name="level" value="{{ $job->level }}"></div>
        <div><label class="label">Employment Type</label>
            <select class="input" name="employment_type" required>
                @foreach(['fulltime','contract','intern'] as $t)
                <option value="{{ $t }}" @selected($job->employment_type===$t)>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div><label class="label">Openings</label><input class="input" type="number" name="openings" min="1" value="{{ $job->openings }}"></div>
        <div><label class="label">Status</label>
            <select class="input" name="status">
                @foreach(['draft','open','closed'] as $s)
                <option value="{{ $s }}" @selected($job->status===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2"><label class="label">Description</label><textarea class="input min-h-[160px]" name="description">{{ $job->description }}</textarea></div>
    </div>
    <div class="flex items-center justify-end gap-3 px-5 pb-5">
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost">Cancel</a>
        <button class="btn btn-primary">Update</button>
    </div>
</form>
@endsection