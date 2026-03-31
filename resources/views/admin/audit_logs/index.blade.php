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

<div class="mx-auto w-full max-w-[1440px] px-4 py-6 space-y-6">

  {{-- HEADER --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-24 rounded-t-2xl overflow-hidden">

      {{-- 🔥 GRADIENT BRAND --}}
      <div class="absolute inset-0"
           style="background: linear-gradient(90deg, {{ $PRIMARY }}, {{ $SECOND }});">
      </div>

      {{-- aksen kanan --}}
      <div class="absolute right-0 w-32 h-full opacity-30 bg-black"></div>

      <div class="relative h-full px-6 flex items-center text-white">
        <div>
          <h1 class="text-2xl font-bold">Audit Logs</h1>
          <p class="text-sm opacity-90">Jejak perubahan data & aktivitas pengguna.</p>
        </div>
      </div>
    </div>

    {{-- FILTER --}}
    <form method="GET"
      class="mt-4 grid md:grid-cols-6 gap-3 rounded-xl border bg-white p-4 shadow-sm"
      style="border-color: {{ $BORD }}">

      <input class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="text" name="q" placeholder="Search...">

      <input class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="text" name="event" placeholder="Event">

      <input class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="text" name="user_id" placeholder="User ID">

      <input class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="text" name="target_type" placeholder="Target">

      <input class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="date" name="from">

      <input class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}"
        type="date" name="to">

      <div class="md:col-span-6 flex gap-2">
        <button type="submit"
          class="px-4 py-2 text-white rounded-lg font-semibold"
          style="background: {{ $PRIMARY }}">
          Filter
        </button>

        <a href="#"
          class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50">
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