@extends('layouts.app', [ 'title' => 'Admin · Jobs' ])


@section('content')
<div class="mb-4 flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-slate-900">Jobs</h1>
    <a class="btn btn-primary" href="{{ route('admin.jobs.create') }}">+ New Job</a>
</div>


<div class="card">
    <div class="card-body overflow-x-auto">
        <table class="table">
            <thead>
                <tr>
                    <th class="th">Code</th>
                    <th class="th">Title</th>
                    <th class="th">Division</th>
                    <th class="th">Site</th>
                    <th class="th">Openings</th>
                    <th class="th">Status</th>
                    <th class="th"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($jobs as $job)
                <tr>
                    <td class="td">{{ $job->code }}</td>
                    <td class="td font-medium">{{ $job->title }}</td>
                    <td class="td">{{ $job->division }}</td>
                    <td class="td">{{ $job->site_code }}</td>
                    <td class="td">{{ $job->openings }}</td>
                    <td class="td"><span class="badge {{ $job->status==='open'?'badge-green':($job->status==='draft'?'badge-amber':'badge-maroon') }}">{{ strtoupper($job->status) }}</span></td>
                    <td class="td text-right">
                        <a class="btn btn-outline" href="{{ route('admin.jobs.edit', $job) }}">Edit</a>
                        <form class="inline" method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-ghost">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="td" colspan="7">No data</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $jobs->links() }}</div>
@endsection