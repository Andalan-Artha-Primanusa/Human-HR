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

      {{-- HEADER (shared solid-brown page-header) --}}
      <x-admin.page-header eyebrow="MANAGEMENT" title="{{ e($record->name) }}" description="Profil perusahaan & ringkasan atribut.">
        <form method="POST" action="{{ route('admin.companies.destroy', $record) }}"
              onsubmit="return confirm('Delete this company?')" class="inline">
          @csrf @method('DELETE')
          <button type="submit" class="ph-action">
            Delete
          </button>
        </form>
        <a href="{{ route('admin.companies.edit', $record) }}" class="ph-action ph-action--brand">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z"/><path d="M20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
          Edit
        </a>
      </x-admin.page-header>

      {{-- DETAIL CARD --}}
      <section class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="p-6 space-y-4 bg-white md:p-7">
          <div class="flex flex-wrap items-center gap-3">
            <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-sm text-slate-700">
              CODE: <span class="ml-1 font-mono font-medium text-slate-900">{{ e($record->code) }}</span>
            </span>

            @php $isActive = strtolower((string) $record->status) === 'active'; @endphp
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
              <div class="text-slate-800">{{ (int) ($record->jobs_count ?? 0) }}</div>
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
    </div>
@endsection
