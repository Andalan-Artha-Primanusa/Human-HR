{{-- resources/views/applications/mine.blade.php --}}
@extends('layouts.app', ['title' => 'Lamaran Saya'])

@php
    // === THEME (GANTI TOTAL KE COKLAT) ===
    $PRIMARY = '#a77d52';
    $SOFT = '#f5efe8';
    $CARD = '#ede5dc';
    $TEXT = '#6b4f3a';
    $BORD = '#e7ded6';

    // === STAGES ===
    $stageOrder = ['applied', 'psychotest', 'hr_iv', 'user_iv', 'final', 'offer', 'hired'];
    $pretty = [
        'applied' => 'Pengajuan Berkas',
        'psychotest' => 'Psikotes',
        'hr_iv' => 'HR Interview',
        'user_iv' => 'User Interview',
        'final' => 'Final',
        'offer' => 'Offering',
        'hired' => 'Diterima',
        'rejected' => 'Ditolak'
    ];

    $col = $apps->getCollection();
    $summary = [
        'total' => $apps->total(),
        'active' => $col->where('overall_status', 'active')->count(),
        'hired' => $col->where('overall_status', 'hired')->count(),
        'rejected' => $col->where('overall_status', 'rejected')->count(),
    ];

    $progressOf = function ($app) use ($stageOrder) {
        $key = strtolower($app->current_stage ?? 'applied');
        $idx = array_search($key, $stageOrder, true);
        if ($idx === false)
            $idx = 0;
        $max = max(count($stageOrder) - 1, 1);
        return (int) round($idx / $max * 100);
    };

    $badge = function ($overall) {
        return match (strtolower((string) $overall)) {
            'hired' => 'bg-[#ede5dc] text-[#6b4f3a]',
            'rejected' => 'bg-[#f5efe8] text-[#6b4f3a]',
            'active' => 'bg-[#ede5dc] text-[#a77d52]',
            default => 'bg-[#f5efe8] text-[#6b4f3a]',
        };
    };
@endphp

@section('content')

    {{-- ICON (TETAP ADA) --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="hidden">
      <symbol id="i-brief" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="12" rx="2"/></symbol>
      <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></symbol>
      <symbol id="i-check" viewBox="0 0 24 24"><path d="M4 12l5 5 11-11"/></symbol>
      <symbol id="i-x" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6l-12 12"/></symbol>
      <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></symbol>
    </svg>

    <div class="mx-auto max-w-7xl px-6 py-8">

      {{-- HEADER --}}
      <section class="rounded-2xl overflow-hidden border shadow-sm"
               style="border-color: {{ $BORD }}">

        <div class="p-6 flex justify-between items-center"
             style="background: {{ $PRIMARY }}">
          <div>
            <h1 class="text-2xl font-semibold text-white">Lamaran Saya</h1>
            <p class="text-sm text-white/80">Pantau progres seleksi kamu secara ringkas</p>
          </div>

          <a href="{{ route('jobs.index') }}"
             class="px-4 py-2 rounded-lg bg-white text-sm font-semibold"
             style="color: {{ $PRIMARY }}">
             Cari Lowongan
          </a>
        </div>

    {{-- STATS --}}
    <div class="p-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
         style="background: #ffffff">

      @php
        $stats = [
            ['Total', $summary['total'], $TEXT, 'users'],
            ['Aktif', $summary['active'], $PRIMARY, 'clock'],
            ['Hired', $summary['hired'], '#16a34a', 'check'],
            ['Rejected', $summary['rejected'], '#dc2626', 'x'],
        ];
      @endphp

      @foreach($stats as [$label, $val, $color, $icon])
          <div class="rounded-xl border px-4 py-4 flex items-center gap-4 hover:shadow-md transition"
               style="border-color: {{ $BORD }}">

            {{-- ICON --}}
            <div class="p-2 rounded-lg"
                 style="background: {{ $color }}20; color: {{ $color }}">

              @if($icon === 'users')
                <!-- Users -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-1a4 4 0 00-5-3.87M9 20H4v-1a4 4 0 015-3.87m0 0a4 4 0 110-8 4 4 0 010 8zm8 0a4 4 0 10-8 0"/>
                </svg>
              @elseif($icon === 'clock')
                <!-- Clock -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              @elseif($icon === 'check')
                <!-- Check -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7"/>
                </svg>
              @elseif($icon === 'x')
                <!-- X -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
              @endif

            </div>

            {{-- TEXT --}}
            <div>
              <div class="text-xs text-slate-500">{{ $label }}</div>
              <div class="text-2xl font-semibold" style="color: {{ $color }}">
                {{ $val }}
              </div>
            </div>

          </div>
      @endforeach
    </div>
      </section>

      {{-- GRID --}}
      <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($apps as $app)
                @php
                    $job = $app->job;
                    $pct = $progressOf($app);
                @endphp

                <article class="rounded-2xl border shadow-sm hover:shadow-md transition"
                         style="border-color: {{ $BORD }}; background: {{ $CARD }}">

                  {{-- STRIP --}}
                  <div class="h-1.5 rounded-t-2xl"
                       style="background: {{ $PRIMARY }}"></div>

            <div class="p-5 bg-white rounded-xl border hover:shadow-md transition"
                 style="border-color: {{ $BORD }}">

              {{-- HEADER --}}
              <div class="flex justify-between items-start gap-3">
                <div class="flex items-center gap-2">

                  {{-- ICON JOB --}}
                  <div class="p-2 rounded-lg"
                       style="background: {{ $PRIMARY }}20; color: {{ $PRIMARY }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7V6a2 2 0 012-2h8a2 2 0 012 2v1M6 7h12M6 7v11a2 2 0 002 2h8a2 2 0 002-2V7"/>
                    </svg>
                  </div>

                  <h3 class="font-semibold leading-tight">
                    {{ $job->title ?? '-' }}
                  </h3>
                </div>

                {{-- STATUS --}}
                <span class="text-[11px] px-2.5 py-1 rounded-full font-medium {{ $badge($app->overall_status) }}">
                  {{ strtoupper($app->overall_status) }}
                </span>
              </div>

              {{-- PROGRESS --}}
              <div class="mt-4">
                <div class="flex justify-between text-xs mb-1"
                     style="color: {{ $TEXT }}">
                  <span class="flex items-center gap-1">
                    {{-- ICON STEP --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"/>
                    </svg>
                    {{ $pretty[$app->current_stage] ?? '-' }}
                  </span>

                  <span class="font-medium">{{ $pct }}%</span>
                </div>

                {{-- BAR --}}
                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div class="h-full rounded-full transition-all duration-500"
                       style="width: {{ $pct }}%; background: linear-gradient(to right, {{ $PRIMARY }}, #6366f1)">
                  </div>
                </div>
              </div>

              {{-- STAGES --}}
              <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach($stageOrder as $key)
                    <span class="text-[11px] px-2 py-1 rounded-full flex items-center gap-1"
                          style="background: {{ $SOFT }}; color: {{ $TEXT }}">

                      {{-- DOT --}}
                      <span class="w-1.5 h-1.5 rounded-full"
                            style="background: {{ $PRIMARY }}"></span>

                      {{ $pretty[$key] }}
                    </span>
                @endforeach
              </div>

              {{-- FOOTER --}}
              <div class="mt-5 flex flex-col gap-1 text-sm">
                <div class="flex justify-between items-center">
                  <span class="flex items-center gap-1.5" style="color: {{ $TEXT }}">
                    {{-- ICON DATE --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-70"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10m-11 8h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ optional($app->created_at)->format('d M Y') }}
                  </span>
                  <a href="{{ route('jobs.show', $app->job_id) }}"
                     class="px-3 py-1.5 rounded-lg text-white text-xs font-medium flex items-center gap-1 hover:opacity-90 transition"
                     style="background: {{ $PRIMARY }}">
                    Detail
                    {{-- ICON ARROW --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"/>
                    </svg>
                  </a>
                </div>
                @if($app->poh)
                  <div class="mt-1 text-xs text-slate-600 flex items-center gap-1">
                    <svg class="w-4 h-4 text-slate-400" aria-hidden="true"><use href="#i-pin"/></svg>
                    <span>POH: {{ $app->poh->name }}</span>
                  </div>
                @endif
              </div>

            </div>
                </article>
        @endforeach
      </section>

      {{-- EMPTY --}}
      @if(!$apps->count())
          <div class="mt-6 text-center p-6 rounded-xl border"
               style="border-color: {{ $BORD }}; background: {{ $CARD }}">
            <p style="color: {{ $TEXT }}">Belum ada lamaran</p>
          </div>
      @endif

    </div>
@endsection