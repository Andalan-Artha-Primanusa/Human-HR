{{-- resources/views/admin/interviews/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Interviews' ])

@php
  $ACCENT = '#a77d52';
  $ACCENT_DARK = '#8b5e3c';
  $BORD  = '#e5e7eb'; // slate-200
  $DARK  = '#0f172a'; // slate-900-like, untuk tombol gelap
@endphp

@section('content')
@once
  {{-- Sprite ikon kecil yang dipakai di halaman ini --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
    <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
  </svg>
@endonce

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER dua-tone + search form terpisah --}}
  <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <div class="relative">
      <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

      <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Interviews</h1>
          <p class="text-xs sm:text-sm text-white/90">Jadwal interview yang sudah dibuat & terkirim.</p>
        </div>
      </div>
    </div>

    {{-- SEARCH FORM (matching Sites index style) --}}
    <div class="p-6 border-t md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]" style="border-color: {{ $BORD }}">
      <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-end" role="search" aria-label="Cari Interview">
        <label class="sr-only" for="q">Cari</label>
        <input id="q" type="text" name="q" value="{{ e($q ?? request('q','')) }}" placeholder="Cari kandidat / job…"
               class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $ACCENT }}" autocomplete="off">

        <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
          <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
                  style="--tw-ring-color: {{ $ACCENT }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
              <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Filter</span>
          </button>

          @if(filled($q ?? request('q')))
            <a href="{{ route('admin.interviews.index') }}"
               class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50">
              Reset
            </a>
          @endif
        </div>
      </form>
    </div>
  </div>

  {{-- TABEL --}}
  <div class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <div class="overflow-x-auto">
      @if(($interviews->count() ?? 0) > 0)
        <table class="min-w-[960px] w-full text-sm">
          <thead class="text-white" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});">
            <tr>
              <th class="w-56 px-4 py-3 text-left">Tanggal</th>
              <th class="px-4 py-3 text-left w-60">Kandidat</th>
              <th class="px-4 py-3 text-left">Posisi</th>
              <th class="w-32 px-4 py-3 text-center">Mode</th>
              <th class="px-4 py-3 text-left w-[22rem]">Lokasi / Link</th>
              <th class="px-4 py-3 text-left w-52">PIC / Email</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($interviews as $iv)
              @php
                $start     = \Illuminate\Support\Carbon::parse($iv->start_at);
                $end       = \Illuminate\Support\Carbon::parse($iv->end_at);
                $candidate = $iv->application?->user?->name ?? '—';
                $jobTitle  = $iv->application?->job?->title ?? '—';
                $siteCode  = $iv->application?->job?->site?->code ?? null;
                $picName   = auth()->user()->name  ?? '—';
                $picEmail  = auth()->user()->email ?? '—';

                $mode  = strtolower($iv->mode ?? 'online');
                $badge = $mode === 'onsite' ? 'badge-amber' : 'badge-blue';
              @endphp
              <tr class="align-top" style="background-color: #faf9f7;" onmouseover="this.style.backgroundColor='#f8f5f2'" onmouseout="this.style.backgroundColor='#faf9f7'">
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">
                    {{ $start->format('d M Y, H:i') }} — {{ $end->format('H:i') }}
                  </div>
                  <div class="text-xs text-slate-500">{{ $start->diffForHumans() }}</div>
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">{{ e($candidate) }}</div>
                  <div class="text-xs text-slate-500">#{{ e($iv->application_id) }}</div>
                </td>
                <td class="px-4 py-3">
                  <div class="text-slate-900">{{ e($jobTitle) }}</div>
                  @if($siteCode)
                    <div class="text-xs text-slate-500">Site: {{ e($siteCode) }}</div>
                  @endif
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="badge {{ $badge }}">{{ strtoupper($mode) }}</span>
                </td>
                <td class="px-4 py-3">
                  @if($mode === 'onsite')
                    {{ $iv->location ?? '—' }}
                  @else
                    @if($iv->meeting_link)
                      <a class="text-blue-600 hover:underline" href="{{ $iv->meeting_link }}" target="_blank" rel="noopener">
                        Join link
                      </a>
                    @else
                      —
                    @endif
                  @endif
                </td>
                <td class="px-4 py-3">
                  <div class="text-sm">{{ e($picName) }}</div>
                  <div class="text-xs text-slate-500">{{ e($picEmail) }}</div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        {{-- EMPTY STATE --}}
        <div class="py-12 text-center">
          <div class="inline-flex items-center justify-center w-12 h-12 mb-3 border border-dashed rounded-2xl border-slate-300 text-slate-400">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V5m8 2V5M5 11h14M7 21h10a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H7A2 2 0 0 0 5 8v11a2 2 0 0 0 2 2Z"/>
            </svg>
          </div>
          <div class="font-medium text-slate-700">Belum ada jadwal interview.</div>
          <div class="mt-1 text-sm text-slate-500">Buat dari tombol <span class="font-medium">Schedule</span> di Kanban.</div>
        </div>
      @endif
    </div>
  </section>

  {{-- PAGINATION (kapsul, custom) --}}
  @php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $interviews */
    $hasData = (int) $interviews->total() > 0;
  @endphp

  @if($hasData)
    @php
      $perPage = max(1, (int) $interviews->perPage());
      $current = (int) $interviews->currentPage();
      $last    = (int) $interviews->lastPage();
      $total   = (int) $interviews->total();
      $from    = ($current - 1) * $perPage + 1;
      $to      = min($current * $perPage, $total);

      // window halaman
      $pages = [];
      if ($last <= 7) {
        $pages = range(1, $last);
      } else {
        $pages = [1];
        $left  = max(2, $current - 1);
        $right = min($last - 1, $current + 1);
        if ($left > 2) $pages[] = '...';
        for ($i = $left; $i <= $right; $i++) $pages[] = $i;
        if ($right < $last - 1) $pages[] = '...';
        $pages[] = $last;
      }

      // url dengan query saat ini
      $pageUrl = function (int $p) use ($interviews) {
        return $interviews->appends(request()->except('page'))->url($p);
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
  @endif
</div>
@endsection
