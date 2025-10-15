@extends('layouts.app', [ 'title' => 'Admin · Jobs' ])

@section('content')
  {{-- Header panel ala bar biru–merah (tanpa component) --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
    {{-- Top color bar --}}
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>

    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Jobs</h1>
          <p class="mt-1 text-sm text-slate-600">Kelola lowongan, filter cepat, dan tindakan edit/hapus.</p>
        </div>
        <a class="btn btn-primary self-start md:self-auto" href="{{ route('admin.jobs.create') }}">
          + Create Job
        </a>
      </div>
    </div>
  </div>

  {{-- Toolbar Filter --}}
  <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-4 gap-2 md:gap-3">
    <input
      name="q"
      value="{{ request('q') }}"
      placeholder="Cari code / title / division…"
      class="input md:col-span-2"
      autocomplete="off"
    >
    <select name="site" class="input">
      <option value="">All Sites</option>
      @foreach(($sites ?? []) as $code => $name)
        <option value="{{ $code }}" @selected(request('site')===$code)>{{ $code }} — {{ $name }}</option>
      @endforeach
    </select>
    <select name="status" class="input">
      @php $statuses = ['' => 'All Status', 'open'=>'Open', 'draft'=>'Draft', 'closed'=>'Closed']; @endphp
      @foreach($statuses as $val => $label)
        <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
      @endforeach
    </select>
    <div class="md:col-span-4 flex gap-2">
      <button class="btn btn-primary">Filter</button>
      <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost">Reset</a>
    </div>
  </form>

  {{-- Tabel --}}
  <div class="card">
    <div class="card-body overflow-x-auto">
      @if($jobs->count())
        <table class="table min-w-[720px]">
          <thead>
            <tr>
              <th class="th w-24">Code</th>
              <th class="th">Title</th>
              <th class="th w-40">Division</th>
              <th class="th w-28">Site</th>
              <th class="th w-24 text-center">Openings</th>
              <th class="th w-28 text-center">Status</th>
              <th class="th w-40 text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($jobs as $job)
              <tr class="align-top">
                <td class="td font-mono text-slate-700">{{ $job->code }}</td>
                <td class="td">
                  <div class="font-medium text-slate-900">{{ $job->title }}</div>
                  @if($job->manpowerRequirement?->budget_headcount)
                    <div class="text-xs text-slate-500">
                      HC: {{ $job->manpowerRequirement->filled_headcount ?? 0 }}/{{ $job->manpowerRequirement->budget_headcount }}
                    </div>
                  @endif
                </td>
                <td class="td">{{ $job->division }}</td>
                <td class="td">{{ $job->site_code }}</td>
                <td class="td text-center">{{ $job->openings }}</td>
                <td class="td text-center">
                  @php
                    $badge = $job->status==='open' ? 'badge-green' : ($job->status==='draft' ? 'badge-amber' : 'badge-maroon');
                  @endphp
                  <span class="badge {{ $badge }}">{{ strtoupper($job->status) }}</span>
                </td>
                <td class="td">
                  <div class="flex justify-end gap-2">
                    <a class="btn btn-outline" href="{{ route('admin.jobs.edit', $job) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-ghost">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        {{-- Empty State --}}
        <div class="py-16 grid place-content-center text-center">
          <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z"/>
            </svg>
          </div>
          <div class="text-slate-600">Belum ada data yang cocok.</div>
          <div class="mt-2">
            <a class="btn btn-primary" href="{{ route('admin.jobs.create') }}">+ Create Job</a>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div class="mt-6">{{ $jobs->withQueryString()->links() }}</div>
@endsection
