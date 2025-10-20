{{-- resources/views/admin/dashboard/manpower.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Manpower Dashboard' ])

@section('content')
  {{-- ===== Header ===== --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm/50 mb-6 overflow-hidden">
    <div class="h-2">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width:90%"></div>
        <div class="h-full bg-red-500"  style="width:10%"></div>
      </div>
    </div>
    <div class="p-6 md:p-8">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Manpower Dashboard</h1>
          <p class="mt-1 text-sm text-slate-600">Ringkasan lowongan, kandidat aktif, headcount, dan pipeline.</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}"
           class="hidden sm:inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 active:bg-slate-100">
          Kelola Jobs
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/>
          </svg>
        </a>
      </div>
    </div>
  </div>

  {{-- ===== KPI Cards ===== --}}
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
    @php
      $kpis = [
        ['label'=>'Open Jobs','value'=>number_format($openJobs),'icon'=>'M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z','bg'=>'bg-blue-50','fg'=>'text-blue-600'],
        ['label'=>'Active Applicants','value'=>number_format($activeApps),'icon'=>'M15 19a4 4 0 1 0-6 0m9-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z','bg'=>'bg-emerald-50','fg'=>'text-emerald-600'],
        ['label'=>'Headcount Budget','value'=>number_format($budget),'icon'=>'M3 19.5h18M6 17V9m6 8V5m6 12v-6','bg'=>'bg-amber-50','fg'=>'text-amber-600'],
        ['label'=>'Fulfillment','value'=>number_format($fulfillment,0)."%",'icon'=>'m9 12 2 2 4-4m5 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z','bg'=>'bg-purple-50','fg'=>'text-purple-600'],
      ];
    @endphp

    @foreach ($kpis as $k)
      <div class="card transition-shadow hover:shadow-md">
        <div class="card-body">
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
      </div>
    @endforeach
  </div>

  {{-- ===== Pipeline by Stage ===== --}}
  <div class="card">
    <div class="card-body">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-900">Pipeline by Stage</h2>
        <div class="hidden md:flex items-center gap-3 text-xs text-slate-500">
          <span class="inline-flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-sm bg-blue-500/80"></span> Applications
          </span>
        </div>
      </div>

      @php $hasData = is_array($byStage ?? null) && count($byStage ?? []) > 0; @endphp

      @if($hasData)
        <div class="relative h-[260px]">
          <canvas id="byStageChart" class="!h-[260px]"></canvas>
        </div>
      @else
        <div class="rounded-xl border border-dashed border-slate-300 p-10 text-center bg-white/50">
          <div class="text-slate-700 font-medium">Belum ada data pipeline.</div>
          <div class="text-slate-500 text-sm mt-1">Tambahkan aplikasi atau buka lowongan untuk melihat grafik.</div>
        </div>
      @endif
    </div>
  </div>

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
@endsection