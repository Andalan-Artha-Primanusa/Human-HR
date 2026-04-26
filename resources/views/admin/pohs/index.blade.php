@extends('layouts.admin')

@section('title', 'Admin · POH • karir-andalan')

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">
<section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
            <div class="relative">
                <div class="w-full h-20 sm:h-24 bg-[#a77d52]"></div>
 
                <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">POH</h1>
                        <p class="text-xs sm:text-sm text-white/90">Kelola daftar Place of Hire (POH).</p>
                    </div>
                    <a href="{{ route('admin.pohs.create') }}"
                       class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
                       style="--tw-ring-color: #a77d52">
                        <svg class="w-4 h-4" style="color: #a77d52"><use href="#i-plus"/></svg>
                        Tambah POH
                    </a>
                </div>
            </div>

            <div class="p-6 border-t md:p-7 bg-white" style="border-color: {{ $BORD }}">
                <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_200px_auto] md:items-end" role="search" aria-label="Filter POH">
                    <label class="sr-only" for="q">Cari</label>
                    <input id="q" type="text" name="q" value="{{ e(request('q', $q ?? '')) }}" placeholder="Cari nama / kode / alamat…"
                           class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $ACCENT }}" autocomplete="off">

                    <div></div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                        <button class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[#a77d52] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
                                style="--tw-ring-color: {{ $ACCENT }}">
                            <svg class="w-4 h-4"><use href="#i-search"/></svg>
                            Cari
                        </button>
                        @if(request()->filled('q'))
                            <a href="{{ route('admin.pohs.index') }}" class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50">Reset</a>
                        @endif
                    </div>
                </form>
            </div>
        </section>

        @if(session('success'))
            <div class="px-4 py-3 border rounded-2xl border-emerald-200 bg-emerald-50 text-emerald-700">
                {{ e(session('success')) }}
            </div>
        @endif
        @if(session('error'))
            <div class="px-4 py-3 text-red-700 border border-red-200 rounded-2xl bg-red-50">
                {{ e(session('error')) }}
            </div>
        @endif

        @if(isset($pohs) && $pohs->count())
            <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="text-white bg-[#a77d52]">
                        <tr>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Alamat</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="w-1 px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pohs as $poh)
                            <tr class="transition hover:bg-[#f8f5f2]">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800">{{ e($poh->name ?? '—') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-mono text-slate-700">{{ e($poh->code ?? '—') }}</span>
                                </td>
                                <td class="px-4 py-3">{{ e($poh->address ?: '—') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $poh->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                                        {{ $poh->is_active ? 'ACTIVE' : 'INACTIVE' }}
                                    </span>
                                </td>
                                <td class="px-2 py-2">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.pohs.edit', $poh) }}" class="rounded-lg px-3 py-1.5 text-xs hover:bg-slate-50 border border-slate-200 inline-flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-slate-700"><use href="#i-edit"/></svg>
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.pohs.destroy', $poh) }}" method="POST" onsubmit="return confirm('Hapus POH ini?');">
                                            @csrf @method('DELETE')
                                            <button class="rounded-lg px-3 py-1.5 text-xs hover:bg-red-50 border border-slate-200 inline-flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-slate-700"><use href="#i-trash"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION: kapsul putih --}}
            <section class="p-3 mt-4 bg-white border shadow-sm rounded-2xl border-slate-200 md:p-4">
                <div class="flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
                    <div class="text-slate-700">
                        Menampilkan <span class="font-semibold text-slate-900">{{ $pohs->firstItem() }}–{{ $pohs->lastItem() }}</span>
                        dari <span class="font-semibold text-slate-900">{{ $pohs->total() }}</span>
                    </div>
                    <div class="ml-auto">{{ $pohs->links() }}</div>
                </div>
            </section>
        @else
            <div class="p-8 text-center bg-white border border-slate-200 rounded-2xl text-slate-500">Belum ada data POH.</div>
        @endif
    </div>

    {{-- SVG Symbols --}}
    <svg style="display: none;">
        <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </symbol>
        <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </symbol>
        <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 20h9"></path>
            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
        </symbol>
        <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </symbol>
    </svg>
@endsection
