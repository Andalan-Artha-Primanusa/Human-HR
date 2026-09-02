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
            <symbol id="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
              <circle cx="12" cy="12" r="3" stroke-width="2"/>
            </symbol>
            <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="11" cy="11" r="7" stroke-width="2"/>
              <path d="M21 21l-3.5-3.5" stroke-width="2" stroke-linecap="round"/>
            </symbol>
            <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round"/>
            </symbol>
            <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round"/>
            </symbol>
            <symbol id="i-download" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M12 3v12m0 0 4-4m-4 4-4-4"/>
              <path stroke-linecap="round" stroke-width="2" d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>
            </symbol>
          </svg>
    @endonce

    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

{{-- HEADER --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative">
          <div class="w-full h-20 sm:h-24 bg-[#a77d52]"></div>

          <div class="relative flex items-center h-full px-5 text-white md:px-6">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Audit Logs</h1>
              <p class="text-xs sm:text-sm text-white/90">Jejak perubahan data & aktivitas pengguna.</p>
            </div>
          </div>
        </div>

        {{-- FILTER --}}
        <div class="p-6 border-t md:p-7 bg-white" style="border-color: {{ $BORD }}">
        <form method="GET"
          class="grid grid-cols-1 gap-3 md:grid-cols-6"
          role="search" aria-label="Filter Audit Logs">

          <input class="w-full h-11 px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="text" name="q" placeholder="Search..." value="{{ e($filters['q'] ?? '') }}" autocomplete="off">

          <input class="w-full h-11 px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="text" name="event" placeholder="Event" value="{{ e($filters['event'] ?? '') }}" autocomplete="off">

          <input class="w-full h-11 px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="text" name="user_id" placeholder="User ID" value="{{ e($filters['userId'] ?? '') }}" autocomplete="off">

          <input class="w-full h-11 px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="text" name="target_type" placeholder="Target" value="{{ e($filters['targetType'] ?? '') }}" autocomplete="off">

          <input class="w-full h-11 px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="date" name="from" value="{{ e($filters['dateFrom'] ?? '') }}">

          <input class="w-full h-11 px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $PRIMARY }}"
            type="date" name="to" value="{{ e($filters['dateTo'] ?? '') }}">

          <div class="flex gap-2 md:col-span-6">
            <button type="submit" class="abtn abtn-primary">
              <svg class="w-4 h-4 text-white"><use href="#i-search"/></svg>
              Filter
            </button>

            <a href="{{ route('admin.audit_logs.export', request()->query()) }}" class="abtn abtn-neutral">
              <svg class="w-4 h-4"><use href="#i-download"/></svg>
              Export CSV
            </a>

            @if(
                filled($filters['q'] ?? '') ||
                filled($filters['event'] ?? '') ||
                filled($filters['userId'] ?? '') ||
                filled($filters['targetType'] ?? '') ||
                filled($filters['dateFrom'] ?? '') ||
                filled($filters['dateTo'] ?? '')
            )
              <a href="{{ route('admin.audit_logs.index') }}" class="abtn abtn-neutral">
                Reset
              </a>
            @endif
          </div>
        </form>
        </div>
      </section>

      {{-- TABLE --}}
      <section class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="text-white bg-[#a77d52]">
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
                      <a class="abtn abtn-sm abtn-secondary"
                         href="{{ route('admin.audit_logs.show', $it->id) }}">
                        <svg class="w-4 h-4"><use href="#i-eye"/></svg>
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

      {{-- PAGINATION (cursor) --}}
      @if(!empty($items) && $items->count() > 0 && is_object($items) && method_exists($items, 'hasPages') && $items->hasPages())
        <section class="p-3 mt-4 bg-white border shadow-sm rounded-2xl border-slate-200 md:p-4">
          <div class="flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
            <div class="text-slate-700">
              Menampilkan <span class="font-semibold text-slate-900">{{ $items->count() }}</span> entri
            </div>
            <nav class="ml-auto" aria-label="Pagination">
              <ul class="inline-flex items-stretch overflow-hidden bg-white border rounded-xl border-slate-200">
                <li>
                  @if($items->onFirstPage())
                    <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </span>
                  @else
                    <a href="{{ $items->previousPageUrl() }}"
                       class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $PRIMARY }}" aria-label="Sebelumnya">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </a>
                  @endif
                </li>
                <li class="border-l border-slate-200">
                  @if($items->hasMorePages())
                    <a href="{{ $items->nextPageUrl() }}"
                       class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $PRIMARY }}" aria-label="Berikutnya">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </a>
                  @else
                    <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </span>
                  @endif
                </li>
              </ul>
            </nav>
          </div>
        </section>
      @endif

    </div>
@endsection