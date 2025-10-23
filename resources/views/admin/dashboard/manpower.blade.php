{{-- resources/views/admin/dashboard/manpower.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Manpower Dashboard' ])

@php
  // THEME
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
@once
  {{-- Sprite ikon kecil jika butuh (contoh panah) --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
    <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M5 12h14M13 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
  </svg>
@endonce

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER dua-tone (biru/merah), konsisten dengan halaman lain --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex items-center">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-white">Manpower Dashboard</h1>
          <p class="text-sm text-white/90">Ringkasan lowongan, kandidat aktif, headcount, dan pipeline.</p>
        </div>

        <a href="{{ route('admin.jobs.index') }}"
           class="ml-auto hidden sm:inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2"
           style="--tw-ring-color: {{ $BLUE }}">
          Kelola Jobs
          <svg class="h-4 w-4" style="color: {{ $BLUE }}"><use href="#i-arrow"/></svg>
        </a>
      </div>
    </div>
  </section>

  {{-- KPI Cards --}}
  <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @php
      $kpis = [
        ['label'=>'Open Jobs','value'=>number_format($openJobs),'icon'=>'M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z','bg'=>'bg-blue-50','fg'=>'text-blue-600'],
        ['label'=>'Active Applicants','value'=>number_format($activeApps),'icon'=>'M15 19a4 4 0 1 0-6 0m9-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z','bg'=>'bg-emerald-50','fg'=>'text-emerald-600'],
        ['label'=>'Headcount Budget','value'=>number_format($budget),'icon'=>'M3 19.5h18M6 17V9m6 8V5m6 12v-6','bg'=>'bg-amber-50','fg'=>'text-amber-600'],
        ['label'=>'Fulfillment','value'=>number_format($fulfillment,0)."%",'icon'=>'m9 12 2 2 4-4m5 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z','bg'=>'bg-purple-50','fg'=>'text-purple-600'],
      ];
    @endphp

    @foreach ($kpis as $k)
      <div class="rounded-2xl border bg-white p-4 shadow-sm transition-shadow hover:shadow-md" style="border-color: {{ $BORD }}">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-600 text-sm">{{ $k['label'] }}</div>
            <div class="text-2xl font-semibold tracking-tight text-slate-900 mt-1">{{ $k['value'] }}</div>
          </div>
          <div class="w-10 h-10 rounded-xl {{ $k['bg'] }} {{ $k['fg'] }} grid place-content-center ring-1 ring-inset ring-black/5">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/>
            </svg>
          </div>
        </div>
      </div>
    @endforeach
  </section>

  {{-- Pipeline by Stage --}}
  @php $hasData = is_array($byStage ?? null) && count($byStage ?? []) > 0; @endphp
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="p-4 md:p-5">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-900">Pipeline by Stage</h2>
        <div class="hidden md:flex items-center gap-3 text-xs text-slate-500">
          <span class="inline-flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-sm bg-blue-500/80"></span> Applications
          </span>
        </div>
      </div>

      @if($hasData)
        <div class="relative h-[260px]">
          <canvas id="byStageChart" class="!h-[260px]"></canvas>
        </div>
      @else
        <div class="rounded-xl border border-dashed p-10 text-center bg-white/50" style="border-color: {{ $BORD }}">
          <div class="text-slate-700 font-medium">Belum ada data pipeline.</div>
          <div class="text-slate-500 text-sm mt-1">Tambahkan aplikasi atau buka lowongan untuk melihat grafik.</div>
        </div>
      @endif
    </div>
  </section>

  @if($hasData)
    <script>
      (function loadChartJS(cb){
        if (window.Chart) return cb();
        var s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js';
        s.onload = cb; document.head.appendChild(s);
      })(function initChart(){
        const stageData = @json($byStage, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        const labels = Object.keys(stageData);
        const values = Object.values(stageData);
        const ctx = document.getElementById('byStageChart');
        if(!ctx) return;
        const palette = [
          'rgba(59,130,246,0.75)','rgba(99,102,241,0.75)','rgba(245,158,11,0.75)',
          'rgba(16,185,129,0.75)','rgba(168,85,247,0.75)','rgba(236,72,153,0.75)',
          'rgba(20,184,166,0.75)','rgba(100,116,139,0.75)',
        ];
        const bg = labels.map((_, i) => palette[i % palette.length]);
        new Chart(ctx, {
          type: 'bar',
          data: { labels, datasets: [{ label: 'Applications', data: values, backgroundColor: bg, borderRadius: 8, borderSkipped: false }] },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: (i) => ` ${i.formattedValue} aplikasi` } } },
            scales: {
              x: { grid: { display: false }, ticks: { color: '#475569' } },
              y: { beginAtZero: true, grid: { color: 'rgba(148,163,184,0.12)' }, ticks: { precision: 0, color: '#64748b' } }
            }
          }
        });
      });
    </script>
  @endif
</div>
@endsection
