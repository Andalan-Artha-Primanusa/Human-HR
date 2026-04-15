{{-- resources/views/admin/jobs/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Jobs' ])

@php
  // THEME (solid)
  $ACCENT = '#a77d52'; // brown
  $ACCENT_DARK  = '#8b5e3c'; // dark brown
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
  <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    {{-- Masthead dua-tone --}}
    <div class="relative">
      <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

      <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 md:px-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Jobs</h1>
          <p class="mt-0.5 text-xs sm:text-sm text-white/90">Kelola lowongan, filter cepat, dan tindakan edit/hapus.</p>
        </div>

        <a href="{{ route('admin.jobs.create') }}"
           class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
           style="--tw-ring-color: {{ $ACCENT }}">
          <svg class="w-4 h-4" style="color: {{ $ACCENT }}"><use href="#i-plus"/></svg>
          Create Job
        </a>
      </div>
    </div>

    {{-- FILTER (di dalam kartu header) --}}
    <div class="p-6 border-t md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]" style="border-color: {{ $BORD }}">
      <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-5 md:items-end" role="search" aria-label="Filter Jobs">
        {{-- term --}}
        <label class="sr-only" for="term">Term</label>
        <input id="term" name="term" value="{{ e(request('term','')) }}"
               placeholder="Cari code / title / division…"
               class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl input md:col-span-2 border-slate-200 focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $ACCENT }}" autocomplete="off">

        {{-- site by code --}}
        <label class="sr-only" for="site">Site</label>
        <select id="site" name="site"
                class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl input border-slate-200 focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $ACCENT }}">
          <option value="">All Sites</option>
          @foreach(($sites ?? []) as $code => $name)
            <option value="{{ e($code) }}" @selected(request('site')===$code)>{{ e($code) }} — {{ e($name) }}</option>
          @endforeach
        </select>

        {{-- company by code --}}
        <label class="sr-only" for="company">Company</label>
        <select id="company" name="company"
                class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl input border-slate-200 focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $ACCENT }}">
          <option value="">All Companies</option>
          @foreach(($companies ?? []) as $code => $name)
            <option value="{{ e($code) }}" @selected(request('company')===$code)>{{ e($code) }} — {{ e($name) }}</option>
          @endforeach
        </select>

        {{-- status --}}
        @php $statuses = ['' => 'All Status', 'open'=>'Open', 'draft'=>'Draft', 'closed'=>'Closed']; @endphp
        <label class="sr-only" for="status">Status</label>
        <select id="status" name="status"
                class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl input border-slate-200 focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $ACCENT }}">
          @foreach($statuses as $val => $label)
            <option value="{{ $val }}" @selected(request('status')===$val)>{{ $label }}</option>
          @endforeach
        </select>

        <div class="flex flex-col gap-2 md:col-span-5 sm:flex-row sm:justify-end">
          <button class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
                  style="--tw-ring-color: {{ $ACCENT }}">
            <svg class="w-4 h-4"><use href="#i-search"/></svg>
            Filter
          </button>
          @if(request()->filled('term') || request()->filled('site') || request()->filled('company') || request()->filled('status'))
            <a href="{{ route('admin.jobs.index') }}"
               class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50">Reset</a>
          @endif
        </div>
      </form>
    </div>
  </section>

  {{-- TABEL --}}
  <section class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
    <div class="p-0 overflow-x-auto">
      @if($jobs->count())
        <table class="min-w-[980px] w-full text-sm">
          <thead class="text-white bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)]">
            <tr>
              <th class="px-4 py-3 text-left w-28">Code</th>
              <th class="px-4 py-3 text-left">Title</th>
              <th class="px-4 py-3 text-left w-44">Division</th>
              <th class="w-32 px-4 py-3 text-left">Company</th>
              <th class="px-4 py-3 text-left w-28">Site</th>
              <th class="px-4 py-3 text-center w-28">Openings</th>
              <th class="px-4 py-3 text-center w-28">Status</th>
              <th class="px-4 py-3 text-right w-44">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($jobs as $job)
              <tr class="transition align-top hover:bg-[#f8f5f2]">
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
                    <div class="text-xs truncate text-slate-500">{{ e($job->company->name) }}</div>
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
        <section class="p-10 text-center bg-white border border-dashed rounded-2xl border-slate-300">
          <div class="grid w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 place-content-center text-slate-400">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z"/>
            </svg>
          </div>
          <div class="font-medium text-slate-700">Belum ada data yang cocok.</div>
          <div class="mt-1 text-sm text-slate-500">Coba ubah filter atau buat lowongan baru.</div>
          <a class="inline-flex items-center gap-2 px-4 py-2 mt-4 text-sm font-semibold text-white rounded-lg bg-slate-900 focus:outline-none focus:ring-2"
             style="--tw-ring-color: {{ $ACCENT }}"
             href="{{ route('admin.jobs.create') }}">
            <svg class="w-4 h-4"><use href="#i-plus"/></svg>
            Create Job
          </a>
        </section>
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

  <section class="p-3 mt-4 bg-white border shadow-sm rounded-2xl border-slate-200 md:p-4">
    <div class="flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
      <div class="text-slate-700">
        Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
        dari <span class="font-semibold text-slate-900">{{ $total }}</span>
      </div>
      <nav class="ml-auto" aria-label="Pagination">
        <ul class="inline-flex items-stretch overflow-hidden bg-white border rounded-xl border-slate-200">
          {{-- Prev --}}
          <li>
            @if($current > 1)
              <a href="{{ $pageUrl($current - 1) }}"
                 class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $ACCENT }}" aria-label="Previous">
                <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
              </a>
            @else
              <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
              </span>
            @endif
          </li>

          {{-- Pages --}}
          @foreach($pages as $p)
            @if($p === '...')
              <li class="grid px-3 select-none place-items-center h-9 text-slate-500">…</li>
            @else
              @php $isCur = ((int)$p === $current); @endphp
              <li class="grid place-items-center h-9">
                @if($isCur)
                  <span class="inline-flex items-center h-full px-3 font-semibold border-l select-none text-slate-900 bg-slate-100 border-slate-200">{{ $p }}</span>
                @else
                  <a href="{{ $pageUrl((int)$p) }}"
                     class="inline-flex items-center h-full px-3 border-l text-slate-700 hover:bg-slate-50 border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}" aria-label="Page {{ $p }}">{{ $p }}</a>
                @endif
              </li>
            @endif
          @endforeach

          {{-- Next --}}
          <li class="border-l border-slate-200">
            @if($current < $last)
              <a href="{{ $pageUrl($current + 1) }}"
                 class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $ACCENT }}" aria-label="Next">
                <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
              </a>
            @else
              <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
              </span>
            @endif
          </li>
        </ul>
      </nav>
    </div>
  </section>
</div>
@endsection
