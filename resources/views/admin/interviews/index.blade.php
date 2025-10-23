{{-- resources/views/admin/interviews/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Interviews' ])

@php
  $BLUE  = '#1d4ed8'; // blue-700
  $RED   = '#dc2626'; // red-600
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

  {{-- HEADER dua-tone (biru/merah) + search form terpisah dan ada spasi --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex items-center">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-white">Interviews</h1>
          <p class="text-sm text-white/90">Jadwal interview yang sudah dibuat & terkirim.</p>
        </div>
      </div>
    </div>

    {{-- SEARCH FORM (jarak mt-3, bukan -mt) --}}
    <form method="GET"
          class="mt-3 md:mt-4 grid grid-cols-1 gap-2 md:grid-cols-[1fr_auto_auto] px-3 py-3 md:px-4 md:py-4 shadow-sm"
          role="search" aria-label="Cari Interview" style="border-color: {{ $BORD }}">
      <input
        name="q"
        value="{{ e($q ?? request('q','')) }}"
        class="input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $BLUE }}"
        placeholder="Cari kandidat / job…"
        autocomplete="off"
      >

      {{-- Tombol Filter: gelap + ikon putih (inline, tanpa currentColor) --}}
      <button type="submit"
              class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white
                     hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 md:shrink-0"
              style="background-color: {{ $DARK }}; border:1px solid {{ $DARK }}; --tw-ring-color: {{ $BLUE }};"
              aria-label="Filter">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
          <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Filter</span>
      </button>

      @if(filled($q ?? request('q')))
        <a href="{{ route('admin.interviews.index') }}"
           class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50 text-slate-900">
          Reset
        </a>
      @endif
    </form>
  </section>

  {{-- TABEL --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="overflow-x-auto">
      @if(($interviews->count() ?? 0) > 0)
        <table class="min-w-[960px] w-full text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 text-left w-56">Tanggal</th>
              <th class="px-4 py-3 text-left w-60">Kandidat</th>
              <th class="px-4 py-3 text-left">Posisi</th>
              <th class="px-4 py-3 text-center w-32">Mode</th>
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
              <tr class="align-top hover:bg-slate-50/60">
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
        <div class="py-16 grid place-content-center text-center">
          <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V5m8 2V5M5 11h14M7 21h10a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H7A2 2 0 0 0 5 8v11a2 2 0 0 0 2 2Z"/>
            </svg>
          </div>
          <div class="text-slate-700 font-medium">Belum ada jadwal interview.</div>
          <div class="text-slate-500 text-sm mt-1">Buat dari tombol <span class="font-medium">Schedule</span> di Kanban.</div>
        </div>
      @endif
    </div>
  </section>

  {{-- PAGINATION (kapsul, custom) --}}
  @php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $interviews */
    $hasData = ($interviews->count() ?? 0) > 0;
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
  @endif
</div>
@endsection
