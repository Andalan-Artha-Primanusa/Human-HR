{{-- resources/views/admin/jobs/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Jobs' ])

@php
  // THEME (solid)
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
@once
  {{-- Ikon sprite yang dipakai di halaman ini --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
    <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
    </symbol>
    <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <circle cx="11" cy="11" r="7" stroke-width="2"/>
      <path d="M21 21l-3.5-3.5" stroke-width="2" stroke-linecap="round"/>
    </symbol>
    <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-width="2" stroke-linecap="round" d="M12 20h9"/>
      <path stroke-width="2" stroke-linecap="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
    </symbol>
    <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-width="2" stroke-linecap="round" d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
    </symbol>
    <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
  </svg>
@endonce

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER + FILTER (menyatu dalam satu kartu dua-tone) --}}
  <section class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    {{-- Masthead dua-tone --}}
    <div class="relative">
      <div class="h-20 sm:h-24 w-full" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="absolute inset-0 flex flex-col gap-3 px-5 md:px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white">Jobs</h1>
          <p class="mt-0.5 text-xs sm:text-sm text-white/90">Kelola lowongan, filter cepat, dan tindakan edit/hapus.</p>
        </div>

        <a href="{{ route('admin.jobs.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2"
           style="--tw-ring-color: {{ $BLUE }}">
          <svg class="w-4 h-4" style="color: {{ $BLUE }}"><use href="#i-plus"/></svg>
          Create Job
        </a>
      </div>
    </div>

    {{-- FILTER (di dalam kartu header) --}}
    <div class="p-5 md:p-6 border-t" style="border-color: {{ $BORD }}">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-2 md:gap-3" role="search" aria-label="Filter Jobs">
        {{-- term --}}
        <label class="sr-only" for="term">Term</label>
        <input id="term" name="term" value="{{ e(request('term','')) }}"
               placeholder="Cari code / title / division…"
               class="input w-full md:col-span-2 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}" autocomplete="off">

        {{-- site by code --}}
        <label class="sr-only" for="site">Site</label>
        <select id="site" name="site"
                class="input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $BLUE }}">
          <option value="">All Sites</option>
          @foreach(($sites ?? []) as $code => $name)
            <option value="{{ e($code) }}" @selected(request('site')===$code)>{{ e($code) }} — {{ e($name) }}</option>
          @endforeach
        </select>

        {{-- company by code --}}
        <label class="sr-only" for="company">Company</label>
        <select id="company" name="company"
                class="input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $BLUE }}">
          <option value="">All Companies</option>
          @foreach(($companies ?? []) as $code => $name)
            <option value="{{ e($code) }}" @selected(request('company')===$code)>{{ e($code) }} — {{ e($name) }}</option>
          @endforeach
        </select>

        {{-- status --}}
        @php $statuses = ['' => 'All Status', 'open'=>'Open', 'draft'=>'Draft', 'closed'=>'Closed']; @endphp
        <label class="sr-only" for="status">Status</label>
        <select id="status" name="status"
                class="input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $BLUE }}">
          @foreach($statuses as $val => $label)
            <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
          @endforeach
        </select>

        <div class="md:col-span-5 flex gap-2">
          <button class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2"
                  style="--tw-ring-color: {{ $BLUE }}">
            <svg class="w-4 h-4"><use href="#i-search"/></svg>
            Filter
          </button>
          @if(request()->filled('term') || request()->filled('site') || request()->filled('company') || request()->filled('status'))
            <a href="{{ route('admin.jobs.index') }}"
               class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50">Reset</a>
          @endif
        </div>
      </form>
    </div>
  </section>

  {{-- TABEL --}}
  <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="p-0 overflow-x-auto">
      @if($jobs->count())
        <table class="min-w-[980px] w-full text-sm">
          <thead class="bg-slate-800 text-white">
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
                {{-- Code --}}
                <td class="px-4 py-3 font-mono text-slate-700">{{ e($job->code) }}</td>

                {{-- Title + meta --}}
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">
                    <a href="{{ route('admin.jobs.edit', $job) }}" class="hover:underline">{{ e($job->title) }}</a>
                  </div>
                  <div class="mt-0.5 flex flex-wrap gap-1 text-xs text-slate-500">
                    @if($job->employment_type)
                      <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                        {{ e(ucfirst($job->employment_type)) }}
                      </span>
                    @endif
                    @if($job->manpowerRequirement?->budget_headcount)
                      <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                        HC: {{ (int)($job->manpowerRequirement->filled_headcount ?? 0) }}/{{ (int)$job->manpowerRequirement->budget_headcount }}
                      </span>
                    @endif
                    <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                      Created: {{ e(optional($job->created_at)->format('d M Y')) }}
                    </span>
                  </div>
                </td>

                {{-- Division --}}
                <td class="px-4 py-3">
                  <span class="text-slate-800">{{ e($job->division ?: '—') }}</span>
                </td>

                {{-- Company --}}
                <td class="px-4 py-3">
                  @if($job->company)
                    <span class="font-mono text-slate-700">{{ e($job->company->code) }}</span>
                    <div class="text-xs text-slate-500 truncate">{{ e($job->company->name) }}</div>
                  @else
                    <span class="text-slate-400">—</span>
                  @endif
                </td>

                {{-- Site --}}
                <td class="px-4 py-3">
                  <span class="font-mono text-slate-700">{{ e($job->site->code ?? '—') }}</span>
                </td>

                {{-- Openings --}}
                <td class="px-4 py-3 text-center">{{ (int) $job->openings }}</td>

                {{-- Status --}}
                <td class="px-4 py-3 text-center">
                  @php
                    $s = strtolower((string)$job->status);
                    $badgeClass = $s === 'open'
                      ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                      : ($s === 'draft'
                          ? 'bg-amber-50 text-amber-700 ring-amber-200'
                          : 'bg-red-50 text-red-700 ring-red-200');
                  @endphp
                  <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $badgeClass }}">
                    {{ strtoupper(e($job->status)) }}
                  </span>
                </td>

                {{-- Actions --}}
                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <a class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs hover:bg-slate-50"
                       href="{{ route('admin.jobs.edit', $job) }}">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-edit"/></svg>
                      Edit
                    </a>
                    <form method="POST" action="{{ route('admin.jobs.destroy', $job) }}"
                          onsubmit="return confirm('Delete this job?')">
                      @csrf @method('DELETE')
                      <button class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs hover:bg-red-50">
                        <svg class="w-4 h-4 text-slate-700"><use href="#i-trash"/></svg>
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
          <a class="mt-3 inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 border border-slate-200 hover:bg-slate-50"
             href="{{ route('admin.jobs.create') }}">
            <svg class="w-4 h-4" style="color: {{ $BLUE }}"><use href="#i-plus"/></svg>
            Create Job
          </a>
        </div>
      @endif
    </div>
  </section>

  {{-- PAGINATION: kapsul putih konsisten --}}
  @php
    $perPage = max(1, (int) $jobs->perPage());
    $current = (int) $jobs->currentPage();
    $last    = (int) $jobs->lastPage();
    $total   = (int) $jobs->total();
    $from    = ($current - 1) * $perPage + 1;
    $to      = min($current * $perPage, $total);

    $pages = [];
    if ($last <= 7) {
      $pages = range(1, $last);
    } else {
      $pages = [1];
      $left = max(2, $current - 1);
      $right = min($last - 1, $current + 1);
      if ($left > 2) $pages[] = '...';
      for ($i = $left; $i <= $right; $i++) $pages[] = $i;
      if ($right < $last - 1) $pages[] = '...';
      $pages[] = $last;
    }

    $pageUrl = function (int $p) use ($jobs) {
      return $jobs->appends(request()->except('page'))->url($p);
    };
  @endphp

  <section class="rounded-2xl border border-slate-200 bg-white p-3 md:p-4 shadow-sm">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-sm">
      <div class="text-slate-700">
        Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
        dari <span class="font-semibold text-slate-900">{{ $total }}</span>
      </div>
      <div class="hidden md:block text-slate-700">
        Showing <span class="font-semibold text-slate-900">{{ $from }}</span>
        to <span class="font-semibold text-slate-900">{{ $to }}</span>
        of <span class="font-semibold text-slate-900">{{ $total }}</span> results
      </div>

      <nav class="ml-auto" aria-label="Pagination">
        <ul class="inline-flex items-stretch overflow-hidden rounded-xl border border-slate-200 bg-white">
          {{-- Prev --}}
          <li>
            @if($current > 1)
              <a href="{{ $pageUrl($current - 1) }}"
                 class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}" aria-label="Previous">
                <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
              </a>
            @else
              <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
              </span>
            @endif
          </li>

          {{-- Pages --}}
          @foreach($pages as $p)
            @if($p === '...')
              <li class="grid place-items-center px-3 h-9 text-slate-500 select-none">…</li>
            @else
              @php $isCur = ((int)$p === $current); @endphp
              <li class="grid place-items-center h-9">
                @if($isCur)
                  <span class="px-3 h-full inline-flex items-center font-semibold text-slate-900 bg-slate-100 border-l border-slate-200 select-none">{{ $p }}</span>
                @else
                  <a href="{{ $pageUrl((int)$p) }}"
                     class="px-3 h-full inline-flex items-center text-slate-700 hover:bg-slate-50 border-l border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $BLUE }}" aria-label="Page {{ $p }}">{{ $p }}</a>
                @endif
              </li>
            @endif
          @endforeach

          {{-- Next --}}
          <li class="border-l border-slate-200">
            @if($current < $last)
              <a href="{{ $pageUrl($current + 1) }}"
                 class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}" aria-label="Next">
                <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-right"/></svg>
              </a>
            @else
              <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-right"/></svg>
              </span>
            @endif
          </li>
        </ul>
      </nav>
    </div>
  </section>
</div>
@endsection
