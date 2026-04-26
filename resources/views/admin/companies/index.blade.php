{{-- resources/views/admin/companies/index.blade.php --}}
@extends('layouts.app', ['title' => 'Companies'])

@php
    // THEME
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
    @once
          {{-- Ikon kecil yang dipakai di halaman ini --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
            <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M5 12h14M13 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-building" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <rect x="3" y="4" width="18" height="16" rx="2" stroke-width="1.8"/>
              <path d="M8 8h2M8 12h2M8 16h2M14 8h2M14 12h2M14 16h2M3 14h18" stroke-width="1.8" stroke-linecap="round"/>
            </symbol>
          </svg>
    @endonce

    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- HEADER dua-tone biru/merah --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative">
          <div class="w-full h-20 sm:h-24 bg-[#a77d52]"></div>

          <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 md:px-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Companies</h1>
              <p class="text-xs sm:text-sm text-white/90">Kelola daftar perusahaan dengan cepat.</p>
            </div>
            <a href="{{ route('admin.companies.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2"
               style="--tw-ring-color: #a77d52">
              <svg class="w-4 h-4" style="color: #a77d52"><use href="#i-arrow"/></svg>
              New Company
            </a>
          </div>
        </div>

        {{-- FILTERS --}}
        <div class="p-5 md:p-6">
          <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-3" role="search" aria-label="Filter Companies">
            <input name="q" value="{{ e($q ?? '') }}" placeholder="Search name/code…"
                   class="w-full px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}" autocomplete="off" />
            <select name="status"
                    class="w-full px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
              style="--tw-ring-color: {{ $ACCENT }}">
              <option value="">All status</option>
              <option value="active" @selected(($status ?? '') === 'active')>Active</option>
              <option value="inactive" @selected(($status ?? '') === 'inactive')>Inactive</option>
            </select>
           <div class="flex gap-2">
      <button type="submit"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg hover:opacity-95 focus:outline-none focus:ring-2"
              style="background-color:#0f172a; border:1px solid #0f172a; --tw-ring-color:#0f172a;"
              aria-label="Filter">
        {{-- ICON inline: putih fix, tanpa currentColor --}}
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
          <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Filter</span>
      </button>
    </div>

          </form>
        </div>
      </section>

      {{-- GRID LIST --}}
      <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($items as $c)
              @php
                $isActive = strtolower((string) $c->status) === 'active';
              @endphp
              <a href="{{ route('admin.companies.show', $c) }}"
                 class="block p-4 transition bg-white border shadow-sm rounded-2xl border-slate-200 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <div class="text-xs text-slate-500">{{ e($c->code) }}</div>
                    <div class="mt-0.5 text-lg font-semibold text-slate-900 truncate">{{ e($c->name) }}</div>
                    @if($c->legal_name)
                          <div class="text-sm truncate text-slate-600">{{ e($c->legal_name) }}</div>
                    @endif
                  </div>
                  <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2 py-1 text-[11px] font-semibold ring-1 ring-inset
                               {{ $isActive ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                    <svg class="h-3.5 w-3.5 {{ $isActive ? 'text-emerald-600' : 'text-slate-500' }}"><use href="#i-building"/></svg>
                    {{ strtoupper(e($c->status ?? 'unknown')) }}
                  </span>
                </div>
              </a>
        @empty
              <div class="p-8 text-center bg-white border border-dashed col-span-full rounded-2xl border-slate-200 text-slate-500">
                No companies.
              </div>
        @endforelse
      </section>

    {{-- PAGINATION (mendukung Cursor & LengthAware) --}}
    @php
        $isCursor = $items instanceof \Illuminate\Pagination\CursorPaginator;
        $isLength = $items instanceof \Illuminate\Pagination\LengthAwarePaginator;

        // Range tampil
        $from = $isLength ? ($items->firstItem() ?? 0) : ($items->count() ? 1 : 0);
        $to = $isLength ? ($items->lastItem() ?? 0) : $items->count();
        $total = $isLength ? $items->total() : null; // cursor tidak punya total

        // URL prev/next (dua-duanya ada)
        $prevUrl = $items->previousPageUrl();
        $nextUrl = $items->nextPageUrl();

        // Nomor halaman hanya untuk LengthAware
        if ($isLength) {
            $paginator = $items;
            $current = $items->currentPage();
            $last = $items->lastPage();

            $pages = [];
            if ($last <= 7) {
                $pages = range(1, $last);
            } else {
                $pages = [1];
                $left = max(2, $current - 1);
                $right = min($last - 1, $current + 1);
                if ($left > 2)
                    $pages[] = '...';
                for ($i = $left; $i <= $right; $i++)
                    $pages[] = $i;
                if ($right < $last - 1)
                    $pages[] = '...';
                $pages[] = $last;
            }
            $pageUrl = fn(int $p) => $paginator->url($p);
        }
    @endphp

    <section class="p-3 bg-white border shadow-sm rounded-2xl border-slate-200 md:p-4">
      <div class="flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
        <div class="text-slate-700">
          Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
          @if($isLength)
            dari <span class="font-semibold text-slate-900">{{ $total }}</span>
          @endif
        </div>

        <nav class="ml-auto" aria-label="Pagination">
          <ul class="inline-flex items-stretch overflow-hidden bg-white border rounded-xl border-slate-200">
            {{-- Prev --}}
            <li>
              @if($prevUrl)
                <a href="{{ $prevUrl }}" class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}" aria-label="Previous">
                  <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </a>
              @else
                <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                  <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </span>
              @endif
            </li>

            {{-- Nomor halaman (hanya LengthAware) --}}
            @if($isLength)
                  @foreach($pages as $p)
                    @if($p === '...')
                          <li class="grid px-3 select-none place-items-center h-9 text-slate-500">…</li>
                    @else
                          @php $isCur = ((int) $p === $current); @endphp
                          <li class="grid place-items-center h-9">
                            @if($isCur)
                                  <span class="inline-flex items-center h-full px-3 font-semibold border-l select-none text-slate-900 bg-slate-100 border-slate-200">{{ $p }}</span>
                            @else
                                  <a href="{{ $pageUrl((int) $p) }}"
                                     class="inline-flex items-center h-full px-3 border-l text-slate-700 hover:bg-slate-50 border-slate-200 focus:outline-none focus:ring-2"
                                     style="--tw-ring-color: {{ $ACCENT }}" aria-label="Page {{ $p }}">{{ $p }}</a>
                            @endif
                          </li>
                    @endif
                  @endforeach
            @endif

            {{-- Next --}}
            <li class="border-l border-slate-200">
              @if($nextUrl)
                <a href="{{ $nextUrl }}" class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
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
