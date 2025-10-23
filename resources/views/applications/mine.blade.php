{{-- resources/views/applications/mine.blade.php --}}
@extends('layouts.app', ['title' => 'Lamaran Saya'])

@php
  // === THEME (solid) ===
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200

  // === STAGES ===
  $stageOrder = ['applied','psychotest','hr_iv','user_iv','final','offer','hired'];
  $pretty = [
    'applied'=>'Pengajuan Berkas','psychotest'=>'Psikotes','hr_iv'=>'HR Interview',
    'user_iv'=>'User Interview','final'=>'Final','offer'=>'Offering',
    'hired'=>'Diterima','rejected'=>'Ditolak'
  ];

  // === SUMMARY (gunakan koleksi halaman aktif agar ringan) ===
  $col = $apps->getCollection();
  $summary = [
    'total'    => $apps->total(),
    'active'   => $col->where('overall_status','active')->count(),
    'hired'    => $col->where('overall_status','hired')->count(),
    'rejected' => $col->where('overall_status','rejected')->count(),
  ];

  // === HELPERS ===
  $progressOf = function($app) use ($stageOrder){
    $key = strtolower($app->current_stage ?? 'applied');
    $idx = array_search($key,$stageOrder,true); if($idx===false) $idx=0;
    $max = max(count($stageOrder)-1,1);
    if(($app->overall_status ?? '')==='rejected'){
      return min(100, max(40, (int)round($idx/$max*100)));
    }
    return (int)round($idx/$max*100);
  };

  $badge = function($overall) {
    return match(strtolower((string)$overall)) {
      'hired'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
      'rejected' => 'bg-red-50 text-red-700 ring-red-200',
      'active'   => 'bg-blue-50 text-blue-700 ring-blue-200',
      default    => 'bg-zinc-50 text-zinc-700 ring-zinc-200',
    };
  };
@endphp

@section('content')
{{-- === ICON SPRITE (solid colors: blue / red) === --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
  {{-- briefcase (base slate, emphasis blue) --}}
  <symbol id="i-brief" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <rect x="3" y="7" width="18" height="12" rx="2" stroke-width="1.6" />
    <path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" stroke-width="1.6" />
  </symbol>

  {{-- clock (active -> blue) --}}
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <circle cx="12" cy="12" r="9" stroke-width="1.8" />
    <path d="M12 7v5l3 2" stroke-width="1.8" stroke-linecap="round" />
  </symbol>

  {{-- check (done -> blue) --}}
  <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M4 12l5 5 11-11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </symbol>

  {{-- x (rejected -> red) --}}
  <symbol id="i-x" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M6 6l12 12M18 6l-12 12" stroke-width="2" stroke-linecap="round" />
  </symbol>

  {{-- arrow (link) --}}
  <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M5 12h14M13 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </symbol>

  {{-- chevrons untuk pagination --}}
  <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
    <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
    <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
</svg>

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6">

  {{-- FLASH --}}
  @if(session('ok') || session('warn'))
    <div class="mb-4 space-y-2" role="status" aria-live="polite">
      @if(session('ok'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ e(session('ok')) }}</div>
      @endif
      @if(session('warn'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">{{ e(session('warn')) }}</div>
      @endif
    </div>
  @endif

  {{-- HEADER CARD (dua-tone: biru + merah) --}}
  <section class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    {{-- Masthead dua-tone --}}
    <div class="relative">
      {{-- Latar biru penuh --}}
      <div class="h-24 sm:h-28 w-full" style="background: {{ $BLUE }}"></div>
      {{-- Blok merah di kanan --}}
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: {{ $RED }}"></div>

      {{-- Konten judul + CTA di atas latar --}}
      <div class="absolute inset-0 flex flex-col gap-3 px-5 md:px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white">Lamaran Saya</h1>
          <p class="text-xs sm:text-sm text-white/90">Pantau progres seleksi kamu secara ringkas & jelas.</p>
        </div>

        <a href="{{ route('jobs.index') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2"
           style="--tw-ring-color: {{ $BLUE }}">
          <svg class="h-4 w-4" style="color: {{ $BLUE }}"><use href="#i-arrow" /></svg>
          Cari Lowongan
        </a>
      </div>
    </div>

    {{-- Stats row (tetap di body putih) --}}
    <div class="p-5 md:p-6">
      <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
          <div class="text-xs text-slate-500">Total</div>
          <div class="mt-1 flex items-center gap-2">
            <svg class="h-4 w-4 text-slate-500"><use href="#i-brief" /></svg>
            <div class="text-2xl font-semibold text-slate-900">{{ $summary['total'] }}</div>
          </div>
        </div>
        <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
          <div class="text-xs text-slate-500">Aktif</div>
          <div class="mt-1 flex items-center gap-2">
            <svg class="h-4 w-4" style="color: {{ $BLUE }}"><use href="#i-clock" /></svg>
            <div class="text-2xl font-semibold" style="color: {{ $BLUE }}">{{ $summary['active'] }}</div>
          </div>
        </div>
        <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
          <div class="text-xs text-slate-500">Hired</div>
          <div class="mt-1 flex items-center gap-2">
            <svg class="h-4 w-4 text-emerald-600"><use href="#i-check" /></svg>
            <div class="text-2xl font-semibold text-emerald-700">{{ $summary['hired'] }}</div>
          </div>
        </div>
        <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
          <div class="text-xs text-slate-500">Rejected</div>
          <div class="mt-1 flex items-center gap-2">
            <svg class="h-4 w-4" style="color: {{ $RED }}"><use href="#i-x" /></svg>
            <div class="text-2xl font-semibold" style="color: {{ $RED }}">{{ $summary['rejected'] }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  @if($apps->count())
    {{-- GRID CARDS --}}
    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
      @foreach($apps as $app)
        @php
          $job = $app->job;
          $title = (string)($job->title ?? '—');
          $site  = (string)($job?->site?->code ?? '—');
          $div   = (string)($job->division ?? '—');
          $overall = strtolower((string)($app->overall_status ?? 'active'));
          $currKey = strtolower((string)($app->current_stage ?? 'applied'));
          $pct = $progressOf($app);
          $badgeClass = $badge($overall);
          $isRejected = $overall === 'rejected';
        @endphp

        <article class="relative rounded-2xl border bg-white shadow-sm transition will-change-transform hover:-translate-y-[2px] hover:shadow-md focus-within:ring-2"
                 style="border-color: {{ $BORD }}; --tw-ring-color: {{ $BLUE }}">
          {{-- strip warna solid atas --}}
          <div class="absolute inset-x-0 top-0 h-1.5 rounded-t-2xl"
               style="background: {{ $isRejected ? $RED : $BLUE }}"></div>

          <div class="p-5">
            <header class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <a href="{{ route('jobs.show', $app->job_id) }}"
                   class="block truncate text-[18px] font-semibold text-slate-900 hover:underline focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $BLUE }}"
                   aria-label="Buka detail lowongan {{ e($title) }}">
                   {{ e($title) }}
                </a>
                <div class="mt-1 text-sm text-slate-600">{{ e($div) }} · {{ e($site) }}</div>
              </div>
              <span class="rounded-full px-2 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $badgeClass }}">
                {{ strtoupper(e($overall)) }}
              </span>
            </header>

            {{-- progress --}}
            <div class="mt-3" aria-label="Progres tahapan">
              <div class="flex items-center justify-between text-xs text-slate-600">
                <span>Saat ini:
                  <span class="font-medium text-slate-900">{{ e($pretty[$currKey] ?? ucfirst($currKey)) }}</span>
                </span>
                <span aria-label="Persentase progres">{{ $pct }}%</span>
              </div>
              <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $pct }}">
                <div class="h-full rounded-full"
                     style="width: {{ $pct }}%; background: {{ $isRejected ? $RED : $BLUE }}"></div>
              </div>
            </div>

            {{-- pills tahapan --}}
            <ul class="mt-3 flex flex-wrap gap-1">
              @php
                $visited = collect($app->stages ?? [])->pluck('stage_key')->map(fn($v)=>strtolower((string)$v))->all();
              @endphp
              @foreach($stageOrder as $key)
                @php
                  $done = in_array($key,$visited,true) && $key!=='hired' && !$isRejected;
                  $isNow = $key === $currKey && !$isRejected;
                @endphp
                <li class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] ring-1 ring-inset
                  {{ $done ? 'bg-blue-50 text-blue-700 ring-blue-200' :
                     ($isNow ? 'bg-white text-slate-900 ring-blue-300' : 'bg-slate-50 text-slate-700 ring-slate-200') }}">
                  @if($done)
                    <svg class="mr-1 h-3.5 w-3.5" style="color: {{ $BLUE }}"><use href="#i-check"/></svg>
                  @elseif($isNow)
                    <svg class="mr-1 h-3.5 w-3.5" style="color: {{ $BLUE }}"><use href="#i-clock"/></svg>
                  @endif
                  {{ e($pretty[$key]) }}
                </li>
              @endforeach

              @if($isRejected)
                <li class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[11px] text-red-700 ring-1 ring-inset ring-red-200">
                  <svg class="mr-1 h-3.5 w-3.5" style="color: {{ $RED }}"><use href="#i-x"/></svg>
                  Ditolak
                </li>
              @endif
            </ul>

            {{-- meta + action --}}
            <div class="mt-4 flex items-center justify-between text-xs text-slate-600">
              <time datetime="{{ optional($app->created_at)->toDateString() }}">
                Diajukan: {{ optional($app->created_at)->format('d M Y') }}
              </time>
              <a href="{{ route('jobs.show', $app->job_id) }}"
                 class="inline-flex items-center gap-1 rounded-lg border px-3 py-1.5 text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2"
                 style="border-color: {{ $BORD }}; --tw-ring-color: {{ $BLUE }}">
                 Detail
              </a>
            </div>
          </div>
        </article>
      @endforeach
    </section>

    {{-- PAGINATION (gaya kapsul seperti contoh) --}}
    @php
      $perPage = max(1, (int) $apps->perPage());
      $current = (int) $apps->currentPage();
      $last    = (int) $apps->lastPage();
      $total   = (int) $apps->total();
      $from    = ($current - 1) * $perPage + 1;
      $to      = min($current * $perPage, $total);

      // window halaman
      $pages = [];
      if ($last <= 7) {
        $pages = range(1, $last);
      } else {
        $pages = [1];
        $left = max(2, $current - 1);
        $right = min($last - 1, $current + 1);

        if ($left > 2)   $pages[] = '...';
        for ($i = $left; $i <= $right; $i++) $pages[] = $i;
        if ($right < $last - 1) $pages[] = '...';
        $pages[] = $last;
      }

      $pageUrl = function(int $p) use ($apps) {
        return $apps->url($p);
      };
    @endphp

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-3 md:p-4 shadow-sm">
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
                   style="--tw-ring-color: {{ $BLUE }}" aria-label="Sebelumnya">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </a>
              @else
                <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </span>
              @endif
            </li>

            {{-- Page numbers --}}
            @foreach($pages as $p)
              @if($p === '...')
                <li class="grid place-items-center px-3 h-9 text-slate-500 select-none">…</li>
              @else
                @php $isCur = ((int)$p === $current); @endphp
                <li class="grid place-items-center h-9">
                  @if($isCur)
                    <span class="px-3 h-full inline-flex items-center font-semibold text-slate-900 bg-slate-100 border-l border-slate-200 select-none">
                      {{ $p }}
                    </span>
                  @else
                    <a href="{{ $pageUrl((int)$p) }}"
                       class="px-3 h-full inline-flex items-center text-slate-700 hover:bg-slate-50 border-l border-slate-200 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $BLUE }}" aria-label="Halaman {{ $p }}">
                      {{ $p }}
                    </a>
                  @endif
                </li>
              @endif
            @endforeach

            {{-- Next --}}
            <li class="border-l border-slate-200">
              @if($current < $last)
                <a href="{{ $pageUrl($current + 1) }}"
                   class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $BLUE }}" aria-label="Berikutnya">
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
  @else
    {{-- EMPTY STATE --}}
    <section class="mt-6 grid place-items-center rounded-2xl border border-dashed bg-white" style="border-color: {{ $BORD }}">
      <div class="w-full max-w-3xl text-center px-8 py-12">
        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full border" style="border-color: {{ $BORD }}">
          <svg class="h-5 w-5 text-slate-500"><use href="#i-brief" /></svg>
        </div>
        <h3 class="mt-3 text-xl font-semibold text-slate-900">Belum ada lamaran</h3>
        <p class="mt-1 text-sm text-slate-600">Mulai dengan memilih lowongan yang tersedia.</p>
        <a href="{{ route('jobs.index') }}"
           class="mt-4 inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-offset-2"
           style="background: {{ $RED }}; --tw-ring-color: {{ $RED }}">
          <svg class="h-4 w-4" style="color:#fff"><use href="#i-arrow" /></svg>
          Lihat Lowongan
        </a>
      </div>
    </section>
  @endif
</div>
@endsection
