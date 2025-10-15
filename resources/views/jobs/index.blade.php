@extends('layouts.app', [ 'title' => 'Lowongan' ])


@section('content')
<div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Lowongan Tersedia</h1>
        <p class="text-slate-600">Cari dan lamar posisi yang cocok untukmu.</p>
    </div>
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="label">Divisi</label>
            <input class="input" name="division" value="{{ request('division') }}" placeholder="Plant/SCM/HRGA" />
        </div>
        <div>
            <label class="label">Site</label>
            <input class="input" name="site" value="{{ request('site') }}" placeholder="DBK/POS/SBS" />
        </div>
        <div>
            <label class="label">Kata kunci</label>
            <input class="input" name="term" value="{{ request('term') }}" placeholder="Judul/Deskripsi" />
        </div>
        <button class="btn btn-primary">Filter</button>
    </form>
</div>


@if($jobs->count())
<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($jobs as $job)
    <div class="card">
        <div class="card-body">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-lg font-semibold text-slate-900">{{ $job->title }}</h3>
                <span class="badge badge-blue">{{ strtoupper($job->employment_type) }}</span>
            </div>
            <div class="mt-1 text-sm text-slate-600">{{ $job->division ?? '—' }} · {{ $job->site_code ?? '—' }}</div>
            <p class="mt-3 line-clamp-3 text-sm text-slate-700">{{ Str::limit(strip_tags($job->description), 160) }}</p>
            <div class="mt-4 flex items-center justify-between">
                <span class="text-xs text-slate-500">Openings: {{ $job->openings }}</span>
                <a class="btn btn-outline" href="{{ route('jobs.show', $job) }}">Detail</a>
            </div>
        </div>
    </div>
    @endforeach
</div>
<div class="mt-6">{{ $jobs->links() }}</div>
@else
<div class="card">
    <div class="card-body">Belum ada lowongan.</div>
</div>
@endif
@endsection