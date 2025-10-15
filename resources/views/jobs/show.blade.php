@extends('layouts.karir', [ 'title' => $job->title ])


@section('content')
<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2 card">
        <div class="card-body">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $job->title }}</h1>
            <div class="mt-1 text-sm text-slate-600">{{ $job->division ?? '—' }} · {{ $job->site_code ?? '—' }}</div>
            <div class="mt-4 prose max-w-none">
                {!! nl2br(e($job->description)) !!}
            </div>
        </div>
    </div>
    <aside class="card">
        <div class="card-body space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-slate-600">Tipe</span>
                <span class="badge badge-blue">{{ strtoupper($job->employment_type) }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-600">Openings</span>
                <span class="font-semibold">{{ $job->openings }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-slate-600">Status</span>
                <span class="badge {{ $job->status === 'open' ? 'badge-green' : 'badge-amber' }}">{{ strtoupper($job->status) }}</span>
            </div>
            @auth
            @if($job->status === 'open')
            <form method="POST" action="{{ route('applications.store', $job) }}">@csrf
                <button class="btn btn-primary w-full">Lamar Sekarang</button>
            </form>
            @else
            <button class="btn btn-outline w-full" disabled>Tutup</button>
            @endif
            @else
            <a class="btn btn-primary w-full" href="{{ route('login') }}">Login untuk Melamar</a>
            @endauth
        </div>
    </aside>
</div>
@endsection