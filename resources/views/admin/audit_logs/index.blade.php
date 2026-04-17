{{-- resources/views/admin/audit_logs/index.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Audit Logs'])

@php
    $PRIMARY = '#a77d52';
    $SECOND = '#8b5e3c';
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
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
          <div class="absolute inset-0 rounded-t-2xl" style="background: linear-gradient(90deg, {{ $PRIMARY }}, {{ $SECOND }});"></div>
          <div class="absolute inset-y-0 right-0 w-24 rounded-tr-2xl sm:w-36" style="background: linear-gradient(90deg, {{ $SECOND }}, {{ $PRIMARY }});"></div>

          <div class="relative flex items-center h-full px-5 text-white md:px-6">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Audit Logs</h1>
              <p class="text-xs sm:text-sm text-white/90">Jejak perubahan data & aktivitas pengguna.</p>
            </div>
          </div>
        </div>

        {{-- FILTER --}}
        <div class="p-6 border-t md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]" style="border-color: {{ $BORD }}">
        <form method="GET"
          class="grid grid-cols-1 gap-3 md:grid-cols-6"
          role="search" aria-label="Filter Audit Logs">

          <input class="px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="text" name="q" placeholder="Search...">

          <input class="px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="text" name="event" placeholder="Event">

          <input class="px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="text" name="user_id" placeholder="User ID">

          <input class="px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="text" name="target_type" placeholder="Target">

          <input class="px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="date" name="from">

          <input class="px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="date" name="to">

          <div class="flex gap-2 md:col-span-6">
            <button type="submit"
              class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
              style="--tw-ring-color: {{ $PRIMARY }};">
              Filter
            </button>

            <a href="#"
              class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50">
              Export CSV
            </a>
          </div>
        </form>
        </div>
      </section>

      {{-- TABLE --}}
      <section class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="text-white bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)]">
              <tr>
                <th class="px-4 py-3 text-left">Time</th>
                <th class="px-4 py-3 text-left">Event</th>
                <th class="px-4 py-3 text-left">User</th>
                <th class="px-4 py-3 text-left">Target</th>
                <th class="px-4 py-3 text-left">IP</th>
                <th class="px-4 py-3 text-right"></th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
              @forelse ($items as $it)
                  <tr class="transition hover:bg-[#f8f5f2]">
                    <td class="px-4 py-3">{{ $it->created_at }}</td>
                    <td class="px-4 py-3">{{ $it->event }}</td>
                    <td class="px-4 py-3">{{ $it->user->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                      <div>{{ $it->target_type }}</div>
                      <div class="text-xs text-slate-500">{{ $it->target_id }}</div>
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
                    <td colspan="6" class="p-6">
                      <div class="p-10 text-center border border-dashed rounded-2xl border-slate-300">
                        <div class="font-medium text-slate-700">Belum ada data.</div>
                        <div class="mt-1 text-sm text-slate-500">Coba ubah filter pencarian.</div>
                      </div>
                    </td>
                  </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>

    </div>
@endsection