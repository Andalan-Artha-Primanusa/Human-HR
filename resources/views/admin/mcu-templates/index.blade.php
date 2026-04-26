@extends('layouts.app')

@section('title', 'Admin · MCU Templates • karir-andalan')

@php
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
      <div class="relative">
        <div class="w-full h-20 sm:h-24 bg-[#a77d52]"></div>
        <div class="absolute inset-y-0 right-0 w-24 sm:w-36 bg-[#8b5e3c]"></div>

        <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">MCU Templates</h1>
            <p class="text-xs sm:text-sm text-white/90">Atur template dan isi default untuk surat undangan MCU.</p>
          </div>
          <a href="{{ route('admin.mcu-templates.create') }}"
             class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
             style="--tw-ring-color: {{ $ACCENT }}">
            <svg class="w-4 h-4" style="color: {{ $ACCENT }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/></svg>
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
                    <a href="{{ route('admin.mcu-templates.edit', $tpl) }}" class="p-2 text-slate-400 hover:text-slate-600 transition">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.mcu-templates.destroy', $tpl) }}" method="POST" onsubmit="return confirm('Hapus template ini?')">
                      @csrf @method('DELETE')
                      <button type="submit" class="p-2 text-slate-400 hover:text-red-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
        <a href="{{ route('admin.mcu-templates.create') }}" class="inline-flex items-center px-4 py-2 mt-4 text-sm font-semibold text-white bg-slate-900 rounded-lg">
          Tambah Template
        </a>
      </div>
    @endif
    </div>
@endsection
