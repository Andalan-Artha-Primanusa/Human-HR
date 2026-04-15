{{-- resources/views/admin/audit_logs/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Audit Logs' ])

@php
  $PRIMARY = '#a77d52';
  $SECOND  = '#8b5e3c';
  $BORD = '#e5e7eb';
@endphp

@section('content')
@once
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden">
    <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round"/>
    </symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round"/>
    </symbol>
  </svg>
@endonce

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER --}}
  <section class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: linear-gradient(90deg, {{ $PRIMARY }}, {{ $SECOND }});"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $SECOND }}, {{ $PRIMARY }});"></div>

      <div class="relative h-full px-5 md:px-6 flex items-center text-white">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white">Audit Logs</h1>
          <p class="text-xs sm:text-sm text-white/90">Jejak perubahan data & aktivitas pengguna.</p>
        </div>
      </div>
    </div>

    {{-- FILTER --}}
    <form method="GET"
      class="mt-3 md:mt-4 grid grid-cols-1 gap-2 md:grid-cols-6 px-3 py-3 md:px-4 md:py-4 shadow-sm"
      role="search" aria-label="Filter Audit Logs"
      style="border-color: {{ $BORD }}">

      <input class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="text" name="q" placeholder="Search...">

      <input class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="text" name="event" placeholder="Event">

      <input class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="text" name="user_id" placeholder="User ID">

      <input class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="text" name="target_type" placeholder="Target">

      <input class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="date" name="from">

      <input class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="date" name="to">

      <div class="md:col-span-6 flex gap-2">
        <button type="submit"
          class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2"
          style="background: {{ $PRIMARY }}; --tw-ring-color: {{ $PRIMARY }};">
          Filter
        </button>

        <a href="#"
          class="px-4 py-2 rounded-lg border border-slate-200 text-sm hover:bg-slate-50">
          Export CSV
        </a>
      </div>
    </form>
  </section>

  {{-- TABLE --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left">Time</th>
            <th class="px-4 py-3 text-left">Event</th>
            <th class="px-4 py-3 text-left">User</th>
            <th class="px-4 py-3 text-left">Target</th>
            <th class="px-4 py-3 text-left">IP</th>
            <th class="px-4 py-3 text-right"></th>
          </tr>
        </thead>

        <tbody class="divide-y">
          @forelse ($items as $it)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $it->created_at }}</td>
            <td class="px-4 py-3">{{ $it->event }}</td>
            <td class="px-4 py-3">{{ $it->user->name ?? '-' }}</td>
            <td class="px-4 py-3">
              <div>{{ $it->target_type }}</div>
              <div class="text-xs text-gray-500">{{ $it->target_id }}</div>
            </td>
            <td class="px-4 py-3">{{ $it->ip }}</td>
            <td class="px-4 py-3 text-right">
              <a class="text-sm font-medium"
                 style="color: {{ $PRIMARY }}"
                 href="{{ route('admin.audit_logs.show', $it->id) }}">
                 Detail
              </a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="text-center py-6 text-gray-500">No data</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

</div>
@endsection