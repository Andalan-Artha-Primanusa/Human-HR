@extends('layouts.app')

@section('title', 'Admin · MCU Templates • karir-andalan')

@php
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
    @once
      <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
        <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
        </symbol>
        <symbol id="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
          <circle cx="12" cy="12" r="3" stroke-width="2"/>
        </symbol>
        <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-width="2" stroke-linecap="round" d="M12 20h9"/>
          <path stroke-width="2" stroke-linecap="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
        </symbol>
        <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-width="2" stroke-linecap="round" d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
        </symbol>
      </svg>
    @endonce
    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
      <div class="relative">
        <div class="w-full h-20 sm:h-24 bg-[#a77d52]"></div>

        <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">MCU Templates</h1>
            <p class="text-xs sm:text-sm text-white/90">Atur template dan isi default untuk surat undangan MCU.</p>
          </div>
          <a href="{{ route('admin.mcu-templates.create') }}"
             class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
             style="--tw-ring-color: {{ $ACCENT }}">
            <svg class="w-4 h-4" style="color: {{ $ACCENT }}"><use href="#i-plus"/></svg>
            Tambah Template
          </a>
        </div>
      </div>
    </section>

    @if(session('ok'))
      <div class="px-4 py-3 border rounded-2xl border-emerald-200 bg-emerald-50 text-emerald-700">
        {{ session('ok') }}
      </div>
    @endif

    @if($templates->count())
      <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
        <table class="min-w-full text-sm">
          <thead class="text-white bg-[#a77d52]">
            <tr>
              <th class="px-4 py-3 text-left">Nama Template</th>
              <th class="px-4 py-3 text-left">Perusahaan</th>
              <th class="px-4 py-3 text-left">Penanda Tangan</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="w-1 px-4 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($templates as $tpl)
              <tr class="transition hover:bg-[#f8f5f2]">
                <td class="px-4 py-3 font-medium text-slate-800">{{ $tpl->name }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $tpl->company_name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $tpl->signer_name ?? '—' }}</td>
                <td class="px-4 py-3">
                  @if($tpl->is_active)
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800">
                      AKTIF
                    </span>
                  @else
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-600">
                      DRAFT
                    </span>
                  @endif
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admin.mcu-templates.preview', $tpl) }}" target="_blank" class="abtn-icon" title="Preview PDF">
                      <svg class="w-5 h-5"><use href="#i-eye"/></svg>
                    </a>
                    <a href="{{ route('admin.mcu-templates.edit', $tpl) }}" class="abtn-icon" aria-label="Edit">
                      <svg class="w-5 h-5"><use href="#i-edit"/></svg>
                    </a>
                    <form action="{{ route('admin.mcu-templates.destroy', $tpl) }}" method="POST" onsubmit="return confirm('Hapus template ini?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="abtn-icon abtn-icon-danger" aria-label="Hapus">
                        <svg class="w-5 h-5"><use href="#i-trash"/></svg>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="mt-4">
        {{ $templates->links() }}
      </div>
    @else
      <div class="p-12 text-center bg-white border border-dashed rounded-2xl border-slate-300">
        <p class="text-slate-500">Belum ada template MCU. Silakan tambah template pertama Anda.</p>
        <a href="{{ route('admin.mcu-templates.create') }}" class="abtn abtn-primary mt-4">
          <svg class="w-4 h-4"><use href="#i-plus"/></svg>
          Tambah Template
        </a>
      </div>
    @endif
    </div>
@endsection
