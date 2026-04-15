{{-- resources/views/admin/dashboard/manpower.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Manpower Dashboard' ])

@php
  $ACCENT = '#a77d52'; // brown
  $ACCENT_DARK = '#8b5e3c'; // dark brown
  $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')

<div class="mx-auto w-full max-w-[1440px] px-4 py-6 space-y-6">

  {{-- HEADER dengan tema brown seperti Sites index --}}
  <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <div class="relative">
      <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

      <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Manpower Dashboard</h1>
          <p class="text-xs sm:text-sm text-white/90">Ringkasan pipeline recruitment</p>
        </div>
      </div>
    </div>
  </section>

  {{-- KPI dengan styling konsisten --}}
  <section class="grid grid-cols-2 gap-4 md:grid-cols-4">
    <div class="p-5 bg-white border shadow-sm rounded-xl" style="border-color: {{ $BORD }}">
      <p class="text-sm font-medium text-slate-500">Open Jobs</p>
      <p class="mt-2 text-2xl font-bold" style="color: {{ $ACCENT }}">{{ $openJobs ?? '—' }}</p>
    </div>
    <div class="p-5 bg-white border shadow-sm rounded-xl" style="border-color: {{ $BORD }}">
      <p class="text-sm font-medium text-slate-500">Applicants</p>
      <p class="mt-2 text-2xl font-bold" style="color: {{ $ACCENT }}">{{ $activeApps ?? '—' }}</p>
    </div>
    <div class="p-5 bg-white border shadow-sm rounded-xl" style="border-color: {{ $BORD }}">
      <p class="text-sm font-medium text-slate-500">Budget</p>
      <p class="mt-2 text-2xl font-bold" style="color: {{ $ACCENT }}">{{ $budget ?? '—' }}</p>
    </div>
    <div class="p-5 bg-white border shadow-sm rounded-xl" style="border-color: {{ $BORD }}">
      <p class="text-sm font-medium text-slate-500">Fulfillment</p>
      <p class="mt-2 text-2xl font-bold" style="color: {{ $ACCENT }}">{{ $fulfillment ?? '—' }}{{ $fulfillment ? '%' : '' }}</p>
    </div>
  </section>

  {{-- CHART dengan tema brown --}}
  @if(($byStage && count($byStage) > 0) || false)
  <section class="p-6 bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <h2 class="mb-4 font-semibold text-slate-900">Pipeline by Stage</h2>

    <div class="h-[320px]">
      <canvas id="chart"></canvas>
    </div>
  </section>
  @else
  <section class="p-10 text-center bg-white border border-dashed rounded-2xl border-slate-300">
    <div class="grid w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 place-content-center text-slate-400">
      <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h18M3 9h18M3 15h18M3 21h18"/>
      </svg>
    </div>
    <div class="font-medium text-slate-700">Belum ada data aplikasi.</div>
    <div class="mt-1 text-sm text-slate-500">Data akan ditampilkan ketika ada aplikasi yang masuk.</div>
  </section>
  @endif

</div>

{{-- CHART SCRIPT --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if($byStage && count($byStage) > 0)
<script>
const dataStage = @json($byStage);

const labels = Object.keys(dataStage);
const values = Object.values(dataStage);

const ctx = document.getElementById('chart').getContext('2d');

// GRADIENT IKUT WARNA BRAND
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, '#a77d52');
gradient.addColorStop(1, '#e7d3b3');

new Chart(ctx, {
  type: 'bar',
  data: {
    labels: labels,
    datasets: [{
      label: 'Aplikasi',
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
@endif

@endsection