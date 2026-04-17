{{-- resources/views/admin/dashboard/manpower.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Manpower Dashboard'])

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
@endphp

@section('content')

<div class="mx-auto w-full max-w-[1440px] px-4 py-6 space-y-6">

  {{-- HEADER --}}
  <section class="overflow-hidden bg-white border shadow-sm rounded-2xl">
    <div class="relative">
      <div class="w-full h-24 bg-gradient-to-r from-[#a77d52] to-[#8b5e3c]"></div>

      <div class="absolute inset-0 flex items-center justify-between px-6 text-white">
        <div>
          <h1 class="text-3xl font-bold">Manpower Dashboard</h1>
          <p class="text-sm opacity-90">Recruitment analytics & insights</p>
        </div>
      </div>
    </div>
  </section>

  {{-- KPI --}}
  <section class="grid grid-cols-2 gap-4 md:grid-cols-4">
    @php
      $kpis = [
        ['title'=>'Open Jobs','value'=>$openJobs],
        ['title'=>'Applicants','value'=>$activeApps],
        ['title'=>'Budget','value'=>$budget],
        ['title'=>'Fulfillment','value'=>$fulfillment.'%'],
      ];
    @endphp

    @foreach($kpis as $kpi)
    <div class="p-5 bg-gradient-to-br from-[#f7e7d0] to-white rounded-xl shadow hover:scale-[1.02] transition">
      <p class="text-xs uppercase text-slate-500">{{ $kpi['title'] }}</p>
      <p class="text-2xl font-bold text-[#a77d52] mt-1">{{ $kpi['value'] ?? '-' }}</p>
      <p class="mt-1 text-xs text-green-600">+12% vs last month</p>
    </div>
    @endforeach
  </section>

  {{-- CHARTS --}}
  <section class="grid grid-cols-1 gap-6 md:grid-cols-2">

    {{-- BAR --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">Pipeline Stage</h2>
      <div class="h-[300px]">
        <canvas id="barChart"></canvas>
      </div>
    </div>

    {{-- DONUT --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">Candidate Demographics</h2>
      <div class="h-[300px]">
        <canvas id="donutChart"></canvas>
      </div>
    </div>

  </section>

  {{-- TREND --}}
  <section class="p-6 bg-white shadow rounded-2xl">
    <h2 class="mb-4 font-semibold">Application Trend</h2>
    <div class="h-[300px]">
      <canvas id="lineChart"></canvas>
    </div>
  </section>

  {{-- INSIGHT --}}
  <section class="grid gap-4 md:grid-cols-2">
    <div class="p-4 bg-[#fff8f0] rounded-xl">
      🔍 Stage terbanyak: <b>{{ array_key_first(($byStage ?? collect())->toArray()) }}</b>
    </div>
    <div class="p-4 bg-[#fff8f0] rounded-xl">
      ⚡ Fulfillment: <b>{{ $fulfillment }}%</b>
    </div>
  </section>

  {{-- GRAFIK DASHBOARD --}}
  <section class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
    {{-- 1. Pipeline (Total Candidate vs Process) --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">Pipeline (Total Candidate vs Process)</h2>
      <canvas id="pipelineChart" class="h-[220px]"></canvas>
    </div>
    {{-- 2. SLA Average per Stage --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">SLA Average per Stage (days)</h2>
      <canvas id="slaChart" class="h-[220px]"></canvas>
    </div>
    {{-- 3. Gender --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">Gender</h2>
      <canvas id="genderChart" class="h-[220px]"></canvas>
    </div>
    {{-- 4. Usia --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">Age Groups</h2>
      <canvas id="ageChart" class="h-[220px]"></canvas>
    </div>
    {{-- 5. POH --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">By POH</h2>
      <canvas id="pohChart" class="h-[220px]"></canvas>
    </div>
    {{-- 6. Acceptance Rate --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">Acceptance Rate</h2>
      <canvas id="acceptanceChart" class="h-[220px]"></canvas>
    </div>
    {{-- 7. Staff Offer Rate --}}
    <div class="p-6 bg-white shadow rounded-2xl">
      <h2 class="mb-4 font-semibold">Staff Offer Rate</h2>
      <canvas id="staffOfferChart" class="h-[220px]"></canvas>
    </div>
  </section>

  {{-- SCRIPTS --}}
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    @php
      $byStageArr = ($byStage ?? collect())->toArray();
      $slaArr = ($slaPerStage ?? collect())->toArray();
      $byPohArr = ($byPoh ?? collect())->toArray();
    @endphp

    // 1. Pipeline
    new Chart(document.getElementById('pipelineChart'), {
      type: 'bar',
      data: {
        labels: Object.keys(@json($byStageArr)),
        datasets: [{
          label: 'Total',
          data: Object.values(@json($byStageArr)),
          backgroundColor: '#a77d52',
          borderRadius: 8,
          barThickness: 32
        }]
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    // 2. SLA per Stage
    new Chart(document.getElementById('slaChart'), {
      type: 'bar',
      data: {
        labels: [@foreach($slaArr as $row)'{{ $row['stage_key'] ?? '' }}',@endforeach],
        datasets: [{
          label: 'Avg Days',
          data: [@foreach($slaArr as $row){{ number_format($row['avg_sla_days'] ?? 0, 1) }},@endforeach],
          backgroundColor: '#8b5e3c',
          borderRadius: 8,
          barThickness: 32
        }]
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    // 3. Gender
    new Chart(document.getElementById('genderChart'), {
      type: 'doughnut',
      data: {
        labels: ['Male', 'Female', 'Other'],
        datasets: [{
          data: [{{ $genderBreakdown['male'] ?? 0 }}, {{ $genderBreakdown['female'] ?? 0 }}, {{ $genderBreakdown['other'] ?? 0 }}],
          backgroundColor: ['#60a5fa', '#f472b6', '#a3a3a3'],
        }]
      },
      options: { cutout: '70%', plugins: { legend: { position: 'bottom' } } }
    });
    // 4. Usia
    new Chart(document.getElementById('ageChart'), {
      type: 'bar',
      data: {
        labels: ['<25', '25-34', '35-44', '45+'],
        datasets: [{
          data: [{{ $ageGroups['<25'] ?? 0 }}, {{ $ageGroups['25-34'] ?? 0 }}, {{ $ageGroups['35-44'] ?? 0 }}, {{ $ageGroups['45+'] ?? 0 }}],
          backgroundColor: '#a77d52',
          borderRadius: 8,
          barThickness: 32
        }]
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    // 5. POH
    new Chart(document.getElementById('pohChart'), {
      type: 'bar',
      data: {
        labels: [@foreach($byPohArr as $row)'{{ optional(\App\Models\Poh::find($row['poh_id'] ?? null))->name ?? 'Unknown' }}',@endforeach],
        datasets: [{
          data: [@foreach($byPohArr as $row){{ $row['total'] ?? 0 }},@endforeach],
          backgroundColor: '#a77d52',
          borderRadius: 8,
          barThickness: 32
        }]
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    // 6. Acceptance Rate
    new Chart(document.getElementById('acceptanceChart'), {
      type: 'doughnut',
      data: {
        labels: ['Accepted', 'Other'],
        datasets: [{
          data: [{{ $acceptanceRate ?? 0 }}, {{ 100 - ($acceptanceRate ?? 0) }}],
          backgroundColor: ['#22c55e', '#e5e7eb'],
        }]
      },
      options: { cutout: '70%', plugins: { legend: { position: 'bottom' } } }
    });
    // 7. Staff Offer Rate
    new Chart(document.getElementById('staffOfferChart'), {
      type: 'doughnut',
      data: {
        labels: ['Staff Offers', 'Other'],
        datasets: [{
          data: [{{ $staffOffers ?? 0 }}, {{ max(0, ($totalCandidates ?? 0) - ($staffOffers ?? 0)) }}],
          backgroundColor: ['#60a5fa', '#e5e7eb'],
        }]
      },
      options: { cutout: '70%', plugins: { legend: { position: 'bottom' } } }
    });
  </script>

@endsection