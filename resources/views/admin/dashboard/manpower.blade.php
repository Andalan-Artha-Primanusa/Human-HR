@extends('layouts.app', [ 'title' => 'Admin · Manpower Dashboard' ])

@section('content')
  {{-- ===== Header ala bar biru–merah (konsisten) ===== --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm mb-6 overflow-hidden">
    <div class="h-2">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width:90%"></div>
        <div class="h-full bg-red-500"  style="width:10%"></div>
      </div>
    </div>
    <div class="p-6 md:p-7">
      <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Manpower Dashboard</h1>
      <p class="text-sm text-slate-600">Ringkasan lowongan, kandidat aktif, headcount, dan pipeline.</p>
    </div>
  </div>

  {{-- ===== KPI Cards ===== --}}
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
    <div class="card">
      <div class="card-body">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-600">Open Jobs</div>
            <div class="text-2xl font-semibold">{{ number_format($openJobs) }}</div>
          </div>
          <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 grid place-content-center">
            {{-- briefcase --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1m-9 4h12m-13 6h14a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2Z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-600">Active Applicants</div>
            <div class="text-2xl font-semibold">{{ number_format($activeApps) }}</div>
          </div>
          <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 grid place-content-center">
            {{-- users --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19a4 4 0 1 0-6 0m9-9a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-600">Headcount Budget</div>
            <div class="text-2xl font-semibold">{{ number_format($budget) }}</div>
          </div>
          <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 grid place-content-center">
            {{-- chart-bar --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 19.5h18M6 17V9m6 8V5m6 12v-6"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-slate-600">Fulfillment</div>
            <div class="text-2xl font-semibold">{{ number_format($fulfillment, 0) }}%</div>
          </div>
          <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 grid place-content-center">
            {{-- check-badge --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4m5 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ===== Pipeline by Stage ===== --}}
  <div class="card">
    <div class="card-body">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-slate-900">Pipeline by Stage</h2>
        {{-- optional legend kecil --}}
        <div class="hidden md:flex items-center gap-3 text-xs text-slate-500">
          <span class="inline-flex items-center gap-1">
            <span class="inline-block w-3 h-3 rounded-sm bg-blue-500/80"></span> Applications
          </span>
        </div>
      </div>

      @php
        $hasData = is_array($byStage ?? null) && count($byStage ?? []) > 0;
      @endphp

      @if($hasData)
        <canvas id="byStageChart" height="120"></canvas>
      @else
        <div class="rounded-xl border border-dashed border-slate-300 p-10 text-center bg-white">
          <div class="text-slate-700 font-medium">Belum ada data pipeline.</div>
          <div class="text-slate-500 text-sm mt-1">Tambahkan aplikasi atau buka lowongan untuk melihat grafik.</div>
        </div>
      @endif
    </div>
  </div>

  {{-- ===== Scripts: auto-load Chart.js jika belum ada, lalu render chart ===== --}}
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

        // warna-warna lembut agar enak di mata
        const palette = [
          'rgba(59,130,246,0.75)',  // blue-500
          'rgba(99,102,241,0.75)',  // indigo-500
          'rgba(245,158,11,0.75)',  // amber-500
          'rgba(16,185,129,0.75)',  // emerald-500
          'rgba(168,85,247,0.75)',  // purple-500
          'rgba(236,72,153,0.75)',  // pink-500
          'rgba(20,184,166,0.75)',  // teal-500
          'rgba(100,116,139,0.75)', // slate-500
        ];
        const bg = labels.map((_, i) => palette[i % palette.length]);

        new Chart(ctx, {
          type: 'bar',
          data: {
            labels,
            datasets: [{
              label: 'Applications',
              data: values,
              backgroundColor: bg,
              borderRadius: 6,
              borderSkipped: false,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: (item) => ` ${item.formattedValue} aplikasi`
                }
              }
            },
            scales: {
              x: {
                grid: { display: false },
                ticks: { color: '#475569' } // slate-600
              },
              y: {
                beginAtZero: true,
                grid: { color: 'rgba(148,163,184,0.15)' }, // slate-400/15
                ticks: {
                  precision: 0,
                  color: '#64748b' // slate-500
                }
              }
            }
          }
        });
      });
    </script>
  @endif
@endsection
