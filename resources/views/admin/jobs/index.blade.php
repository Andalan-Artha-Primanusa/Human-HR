@extends('layouts.app', [ 'title' => 'Admin · Jobs' ])

@section('content')
  {{-- HEADER: panel biru–merah --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
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
        <a class="btn btn-primary inline-flex items-center gap-2 self-start md:self-auto" href="{{ route('admin.jobs.create') }}">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
          </svg>
          Create Job
        </a>
      </div>
    </div>
  </div>

  {{-- TOOLBAR FILTER --}}
  <form method="GET" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-2 md:gap-3">
    {{-- term (cocok dengan controller) --}}
    <input
      name="term"
      value="{{ request('term') }}"
      placeholder="Cari code / title / division…"
      class="input md:col-span-2"
      autocomplete="off"
    >

    {{-- site by code --}}
    <select name="site" class="input">
      <option value="">All Sites</option>
      @foreach(($sites ?? []) as $code => $name)
        <option value="{{ $code }}" @selected(request('site')===$code)>{{ $code }} — {{ $name }}</option>
      @endforeach
    </select>

    {{-- company by code --}}
    <select name="company" class="input">
      <option value="">All Companies</option>
      @foreach(($companies ?? []) as $code => $name)
        <option value="{{ $code }}" @selected(request('company')===$code)>{{ $code }} — {{ $name }}</option>
      @endforeach
    </select>

    {{-- status (opsional: hanya berfungsi jika controllernya memfilter status) --}}
    @php $statuses = ['' => 'All Status', 'open'=>'Open', 'draft'=>'Draft', 'closed'=>'Closed']; @endphp
    <select name="status" class="input">
      @foreach($statuses as $val => $label)
        <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
      @endforeach
    </select>

    <div class="md:col-span-5 flex gap-2">
      <button class="btn btn-primary inline-flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" d="M21 21l-3.5-3.5"/>
        </svg>
        Filter
      </button>
      <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost">Reset</a>
    </div>
  </form>

  {{-- TABEL --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="p-0 overflow-x-auto">
      @if($jobs->count())
        <table class="min-w-[980px] w-full text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 text-left w-28">Code</th>
              <th class="px-4 py-3 text-left">Title</th>
              <th class="px-4 py-3 text-left w-44">Division</th>
              <th class="px-4 py-3 text-left w-32">Company</th>
              <th class="px-4 py-3 text-left w-28">Site</th>
              <th class="px-4 py-3 text-center w-28">Openings</th>
              <th class="px-4 py-3 text-center w-28">Status</th>
              <th class="px-4 py-3 text-right w-44">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($jobs as $job)
              <tr class="align-top hover:bg-slate-50/60">
                <td class="px-4 py-3 font-mono text-slate-700">{{ $job->code }}</td>

                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">
                    <a href="{{ route('admin.jobs.edit', $job) }}" class="hover:underline">{{ $job->title }}</a>
                  </div>
                  <div class="mt-0.5 flex flex-wrap gap-1 text-xs text-slate-500">
                    @if($job->employment_type)
                      <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                        {{ ucfirst($job->employment_type) }}
                      </span>
                    @endif
                    @if($job->manpowerRequirement?->budget_headcount)
                      <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                        HC: {{ $job->manpowerRequirement->filled_headcount ?? 0 }}/{{ $job->manpowerRequirement->budget_headcount }}
                      </span>
                    @endif
                    <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                      Created: {{ optional($job->created_at)->format('d M Y') }}
                    </span>
                  </div>
                </td>

                <td class="px-4 py-3">
                  <span class="text-slate-800">{{ $job->division ?: '—' }}</span>
                </td>

                <td class="px-4 py-3">
                  @if($job->company)
                    <span class="font-mono text-slate-700">{{ $job->company->code }}</span>
                    <div class="text-xs text-slate-500 truncate">{{ $job->company->name }}</div>
                  @else
                    <span class="text-slate-400">—</span>
                  @endif
                </td>

                <td class="px-4 py-3">
                  <span class="font-mono text-slate-700">{{ $job->site->code ?? '—' }}</span>
                </td>

                <td class="px-4 py-3 text-center">{{ $job->openings }}</td>

                <td class="px-4 py-3 text-center">
                  @php
                    $badge = $job->status==='open' ? 'badge-green' : ($job->status==='draft' ? 'badge-amber' : 'badge-maroon');
                  @endphp
                  <span class="badge {{ $badge }}">{{ strtoupper($job->status) }}</span>
                </td>

                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <a class="btn btn-outline btn-sm inline-flex items-center gap-1.5" href="{{ route('admin.jobs.edit', $job) }}">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" d="M12 20h9"/>
                        <path stroke-width="2" stroke-linecap="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                      </svg>
                      Edit
                    </a>
                    <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job?')">
                      @csrf @method('DELETE')
                      <button class="btn btn-ghost btn-sm inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path stroke-width="2" stroke-linecap="round" d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
                        </svg>
                        Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        {{-- EMPTY STATE --}}
        <div class="py-16 grid place-content-center text-center">
          <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z"/>
            </svg>
          </div>
          <div class="text-slate-700 font-medium">Belum ada data yang cocok.</div>
          <div class="text-slate-500 text-sm mt-1">Coba ubah filter atau buat lowongan baru.</div>
          <a class="btn btn-primary mt-3 inline-flex items-center gap-2" href="{{ route('admin.jobs.create') }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
            </svg>
            Create Job
          </a>
        </div>
      @endif
    </div>
  </div>

  {{-- PAGINATION --}}
  <div class="mt-6">{{ $jobs->withQueryString()->links() }}</div>
@endsection
