@extends('layouts.app', ['title' => 'Admin · Manpower Dashboard'])

@php
    $primary = '#a77d52';
    $secondary = '#8b5e3c';
@endphp

@section('content')

<div class="mx-auto w-full max-w-[1440px] px-4 py-6 space-y-6">

  {{-- HEADER --}}
  <section class="relative overflow-hidden text-white shadow rounded-2xl">
    <div class="absolute inset-0 bg-gradient-to-r from-[#a77d52] via-[#b88a5c] to-[#8b5e3c]"></div>

    <div class="relative flex items-center justify-between px-6 py-6">
      <div>
        <h1 class="text-3xl font-bold tracking-wide">Manpower Dashboard</h1>
        <p class="text-sm opacity-90">Recruitment analytics & insights</p>
      </div>

      <div class="text-right">
        <p class="text-xs opacity-80">Last Updated</p>
        <p class="text-sm font-semibold">{{ now()->format('d M Y H:i') }}</p>
      </div>
    </div>
  </section>

  {{-- KPI --}}
  <section class="grid grid-cols-2 gap-4 md:grid-cols-4">
    @php
      $kpis = [
        ['title'=>'Open Jobs','value'=>$openJobs,'icon'=>'💼'],
        ['title'=>'Applicants','value'=>$activeApps,'icon'=>'👤'],
        ['title'=>'Budget','value'=>$budget,'icon'=>'💰'],
        ['title'=>'Fulfillment','value'=>$fulfillment.'%','icon'=>'⚡'],
      ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="p-5 transition bg-white border shadow-sm rounded-xl hover:shadow-md">
      <div class="flex items-center justify-between">
        <p class="text-xs uppercase text-slate-500">{{ $kpi['title'] }}</p>
        <span class="text-xl">{{ $kpi['icon'] }}</span>
      </div>

      <p class="mt-2 text-2xl font-bold text-[#a77d52]">
        {{ $kpi['value'] ?? '-' }}
      </p>

      <div class="mt-2 text-xs text-green-600">
        ▲ +12% vs last month
      </div>
    </div>
    @endforeach
  </section>

  {{-- CHARTS --}}
  <section class="grid grid-cols-1 gap-6 md:grid-cols-2">

    <div class="p-6 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold">Pipeline Stage</h2>
        <span class="text-xs text-slate-400">Overview</span>
      </div>
      <canvas id="pipelineChart" class="h-[300px]"></canvas>
    </div>

    <div class="p-6 bg-white border shadow-sm rounded-2xl">
      <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold">Candidate Demographics</h2>
        <span class="text-xs text-slate-400">Distribution</span>
      </div>
      <canvas id="genderChart" class="h-[300px]"></canvas>
    </div>

  </section>

  {{-- TREND --}}
  <section class="p-6 bg-white border shadow-sm rounded-2xl">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold">Application Trend</h2>
      <span class="text-xs text-slate-400">Monthly</span>
    </div>
    <canvas id="trendChart" class="h-[300px]"></canvas>
  </section>

  {{-- INSIGHT --}}
  <section class="grid gap-4 md:grid-cols-2">
    <div class="p-5 bg-white border shadow-sm rounded-xl">
      <p class="text-xs text-slate-500">Top Stage</p>
      <p class="text-lg font-semibold text-[#a77d52]">
        {{ array_key_first(($byStage ?? collect())->toArray()) }}
      </p>
    </div>

    <div class="p-5 bg-white border shadow-sm rounded-xl">
      <p class="text-xs text-slate-500">Fulfillment Rate</p>
      <p class="text-lg font-semibold text-green-600">
        {{ $fulfillment }}%
      </p>
    </div>
  </section>

  {{-- GRID EXTRA --}}
  <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">

    <div class="p-6 bg-white border shadow-sm rounded-2xl">
      <h2 class="mb-4 font-semibold">SLA per Stage</h2>
      <canvas id="slaChart"></canvas>
    </div>

    <div class="p-6 bg-white border shadow-sm rounded-2xl">
      <h2 class="mb-4 font-semibold">Age Groups</h2>
      <canvas id="ageChart"></canvas>
    </div>

    <div class="p-6 bg-white border shadow-sm rounded-2xl">
      <h2 class="mb-4 font-semibold">Acceptance Rate</h2>
      <canvas id="acceptanceChart"></canvas>
    </div>

  </section>

</div>

{{-- SCRIPT --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

const primary = '#a77d52';
const secondary = '#8b5e3c';

// PIPELINE
new Chart(document.getElementById('pipelineChart'), {
  type: 'bar',
  data: {
    labels: Object.keys(@json(($byStage ?? collect())->toArray())),
    datasets: [{
      data: Object.values(@json(($byStage ?? collect())->toArray())),
      backgroundColor: primary,
      borderRadius: 8
    }]
  },
  options: { plugins: { legend: { display: false } } }
});

// GENDER
new Chart(document.getElementById('genderChart'), {
  type: 'doughnut',
  data: {
    labels: ['Male','Female','Other'],
    datasets: [{
      data: [
        {{ $genderBreakdown['male'] ?? 0 }},
        {{ $genderBreakdown['female'] ?? 0 }},
        {{ $genderBreakdown['other'] ?? 0 }}
      ],
      backgroundColor: ['#60a5fa','#f472b6','#a3a3a3']
    }]
  },
  options: { cutout: '70%' }
});

// TREND - Real data from controller
new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: @json(($applicationTrend ?? collect())->keys()->toArray()),
    datasets: [{
      label: 'Applications',
      data: @json(($applicationTrend ?? collect())->values()->toArray()),
      borderColor: primary,
      backgroundColor: primary + '20',
      fill: true,
      tension: 0.4
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});

// SLA (FIXED)
new Chart(document.getElementById('slaChart'), {
  type: 'bar',
  data: {
    labels: @json(($slaPerStage ?? collect())->pluck('stage_key')),
    datasets: [{
      data: @json(($slaPerStage ?? collect())->pluck('avg_sla_days')),
      backgroundColor: secondary
    }]
  }
});

// AGE
new Chart(document.getElementById('ageChart'), {
  type: 'bar',
  data: {
    labels: ['<25','25-34','35-44','45+'],
    datasets: [{
      data: [
        {{ $ageGroups['<25'] ?? 0 }},
        {{ $ageGroups['25-34'] ?? 0 }},
        {{ $ageGroups['35-44'] ?? 0 }},
        {{ $ageGroups['45+'] ?? 0 }}
      ],
      backgroundColor: primary
    }]
  }
});

// ACCEPTANCE
new Chart(document.getElementById('acceptanceChart'), {
  type: 'doughnut',
  data: {
    labels: ['Accepted','Other'],
    datasets: [{
      data: [
        {{ $acceptanceRate ?? 0 }},
        {{ 100 - ($acceptanceRate ?? 0) }}
      ],
      backgroundColor: ['#22c55e','#e5e7eb']
    }]
  },
  options: { cutout: '70%' }
});

</script>

@endsection