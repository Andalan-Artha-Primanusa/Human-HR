{{-- resources/views/admin/companies/show.blade.php --}}
@extends('layouts.app', ['title' => $record->name])

@php
  $ACCENT = '#a77d52'; // brown
  $ACCENT_DARK = '#8b5e3c'; // dark brown
  $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
@once
  {{-- Sprite ikon opsional (panah) --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
    <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M5 12h14M13 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
  </svg>
@endonce

<div class="mx-auto w-full max-w-[960px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER dua-tone --}}
  <section class="relative bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
      <div class="absolute inset-0" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

      <div class="relative flex items-center h-full px-5 md:px-6">
        <div class="min-w-0">
          <h1 class="text-2xl font-semibold tracking-tight text-white md:text-3xl">{{ e($record->name) }}</h1>
          <p class="text-sm text-white/90">Profil perusahaan & ringkasan atribut.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- DETAIL CARD --}}
  <section class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <div class="p-6 space-y-4 md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]">
      <div class="flex flex-wrap items-center gap-3">
        <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-sm text-slate-700">
          CODE: <span class="ml-1 font-mono font-medium text-slate-900">{{ e($record->code) }}</span>
        </span>

        @php $isActive = strtolower((string)$record->status) === 'active'; @endphp
        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset
                     {{ $isActive ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
          {{ strtoupper(e($record->status ?? 'unknown')) }}
        </span>
      </div>

      @if($record->legal_name)
        <div class="text-slate-700">
          <div class="text-xs tracking-wide uppercase text-slate-500">Legal Name</div>
          <div class="mt-0.5">{{ e($record->legal_name) }}</div>
        </div>
      @endif

      <div class="grid gap-3 text-sm md:grid-cols-2">
        <div class="space-y-1">
          <div class="text-xs tracking-wide uppercase text-slate-500">Email</div>
          <div class="text-slate-800">{{ e($record->email ?: '—') }}</div>
        </div>
        <div class="space-y-1">
          <div class="text-xs tracking-wide uppercase text-slate-500">Phone</div>
          <div class="text-slate-800">{{ e($record->phone ?: '—') }}</div>
        </div>
        <div class="space-y-1">
          <div class="text-xs tracking-wide uppercase text-slate-500">Website</div>
          @php $web = $record->website; @endphp
          <div class="text-slate-800">
            @if($web)
              <a href="{{ $web }}" target="_blank" rel="noopener" class="text-[#8b5e3c] hover:underline">{{ e($web) }}</a>
            @else
              —
            @endif
          </div>
        </div>
        <div class="space-y-1">
          <div class="text-xs tracking-wide uppercase text-slate-500">Jobs</div>
          <div class="text-slate-800">{{ (int)($record->jobs_count ?? 0) }}</div>
        </div>
      </div>

      @if($record->address)
        <div class="space-y-1">
          <div class="text-xs tracking-wide uppercase text-slate-500">Address</div>
          <div class="text-sm whitespace-pre-line text-slate-800">{{ $record->address }}</div>
        </div>
      @endif
    </div>
  </section>

  {{-- ACTIONS --}}
  <section class="flex items-center gap-3">
    <a href="{{ route('admin.companies.edit', $record) }}"
       class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-offset-2"
       style="--tw-ring-color: {{ $ACCENT }}">
      Edit
    </a>
    <form method="POST" action="{{ route('admin.companies.destroy', $record) }}"
          onsubmit="return confirm('Delete this company?')" class="inline">
      @csrf @method('DELETE')
      <button type="submit"
              class="inline-flex items-center px-4 py-2 text-sm bg-white border rounded-lg border-slate-200 text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
              style="--tw-ring-color: {{ $ACCENT }}">
        Delete
      </button>
    </form>
  </section>
</div>
@endsection
