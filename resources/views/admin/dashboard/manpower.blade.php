{{-- resources/views/admin/dashboard/manpower.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Manpower Dashboard' ])

@php
  $PRIMARY = '#a77d52'; // 🔥 warna utama baru
  $SECOND  = '#8b5e3c'; // 🔥 shade lebih gelap biar kontras
  $BORD = '#e5e7eb';

  $byStage = $byStage ?? [
    'Screening' => 12,
    'Interview' => 8,
    'Offering' => 4,
    'Hired' => 2,
  ];
@endphp

@section('content')

<div class="mx-auto w-full max-w-[1440px] px-4 py-6 space-y-6">

  {{-- HEADER --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-24 rounded-t-2xl overflow-hidden">

      {{-- 🔥 GRADIENT HEADER BARU --}}
      <div class="absolute inset-0"
           style="background: linear-gradient(90deg, {{ $PRIMARY }}, {{ $SECOND }});">
      </div>

      {{-- aksen kanan --}}
      <div class="absolute right-0 w-32 h-full opacity-30"
           style="background: #000;">
      </div>

      <div class="relative h-full flex items-center px-6 text-white">
        <div>
          <h1 class="text-2xl font-bold">Manpower Dashboard</h1>
          <p class="text-sm opacity-90">Ringkasan pipeline recruitment</p>
        </div>
      </div>
    </div>
  </section>

  {{-- KPI --}}
  <section class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="p-4 bg-white rounded-xl border shadow-sm">
      <p class="text-sm text-gray-500">Open Jobs</p>
      <p class="text-xl font-bold">{{ $openJobs ?? 10 }}</p>
    </div>
    <div class="p-4 bg-white rounded-xl border shadow-sm">
      <p class="text-sm text-gray-500">Applicants</p>
      <p class="text-xl font-bold">{{ $activeApps ?? 25 }}</p>
    </div>
    <div class="p-4 bg-white rounded-xl border shadow-sm">
      <p class="text-sm text-gray-500">Budget</p>
      <p class="text-xl font-bold">{{ $budget ?? 50 }}</p>
    </div>
    <div class="p-4 bg-white rounded-xl border shadow-sm">
      <p class="text-sm text-gray-500">Fulfillment</p>
      <p class="text-xl font-bold">{{ $fulfillment ?? 70 }}%</p>
    </div>
  </section>

  {{-- CHART --}}
  <section class="bg-white rounded-2xl border p-5 shadow-sm" style="border-color: {{ $BORD }}">
    <h2 class="font-semibold text-gray-800 mb-4">Pipeline by Stage</h2>

    <div class="h-[320px]">
      <canvas id="chart"></canvas>
    </div>
  </section>

</div>

{{-- CHART SCRIPT --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const dataStage = @json($byStage);

const labels = Object.keys(dataStage);
const values = Object.values(dataStage);

const ctx = document.getElementById('chart').getContext('2d');

// 🔥 GRADIENT IKUT WARNA BRAND
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, '#a77d52');
gradient.addColorStop(1, '#e7d3b3');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Applications',
      data: values,
      backgroundColor: gradient,
      borderRadius: 12,
      barThickness: 42,
      hoverBackgroundColor: '#8b5e3c'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,

    animation: {
      duration: 1200,
      easing: 'easeOutQuart'
    },

    plugins: {
      legend: { display: false },

      tooltip: {
        backgroundColor: '#111827',
        titleColor: '#fff',
        bodyColor: '#e5e7eb',
        padding: 10,
        cornerRadius: 8,
        displayColors: false
      }
    },

    scales: {
      x: {
        grid: { display: false },
        ticks: {
          color: '#6b7280'
        }
      },
      y: {
        beginAtZero: true,
        grid: {
          color: '#f1f5f9'
        },
        ticks: {
          color: '#6b7280',
          stepSize: 5
        }
      }
    }
  }
});
</script>

@endsection