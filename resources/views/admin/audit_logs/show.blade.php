{{-- resources/views/admin/audit_logs/show.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Audit Log Detail'])

@php
    $BORD = '#e5e7eb';
    $PRIMARY = '#a77d52';

    $before = is_array($log->before) ? $log->before : [];
    $after  = is_array($log->after) ? $log->after : [];
    $diffPresent = !empty($before) || !empty($after);
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[1200px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

        <x-admin.page-header title="Audit Log Detail" description="Rincian jejak perubahan data dan aktivitas pengguna.">
            <a href="{{ route('admin.audit_logs.index') }}" class="ph-action">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </x-admin.page-header>

        {{-- META CARD --}}
        <section class="overflow-hidden bg-white border rounded-2xl" style="border-color: {{ $BORD }}; border-radius: 1rem;">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <svg class="w-4 h-4 text-[#a77d52]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Informasi Umum
                </div>
            </div>
            <div class="grid gap-px bg-slate-100 sm:grid-cols-2 lg:grid-cols-2">
                <div class="bg-white p-5">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Waktu</div>
                    <div class="mt-1 text-sm font-semibold text-slate-800">{{ optional($log->created_at)->format('d M Y H:i:s') }}</div>
                </div>
                <div class="bg-white p-5">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Event</div>
                    <div class="mt-1">
                        <span class="inline-flex rounded-full bg-[#fffaf5] px-3 py-1 text-sm font-semibold text-[#8b5e3c] ring-1 ring-inset ring-[#ead8c5]">{{ $log->event }}</span>
                    </div>
                </div>
                <div class="bg-white p-5">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">User</div>
                    <div class="mt-1 text-sm font-semibold text-slate-800">{{ $log->user->name ?? '-' }}</div>
                    <div class="text-xs text-slate-400">ID: {{ $log->user_id ?? '-' }} · {{ $log->user->email ?? '' }}</div>
                </div>
                <div class="bg-white p-5">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Target</div>
                    <div class="mt-1 text-sm font-semibold text-slate-800">{{ $log->target_type ?? '-' }}</div>
                    <div class="text-xs text-slate-400">ID: {{ $log->target_id ?? '-' }}</div>
                </div>
                <div class="bg-white p-5 sm:col-span-2">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">IP / User Agent</div>
                    <div class="mt-1 text-sm font-semibold text-slate-800">{{ $log->ip ?? '-' }}</div>
                    <div class="mt-1 break-words text-xs text-slate-400">{{ $log->user_agent ?? '-' }}</div>
                </div>
            </div>
        </section>

        {{-- DIFF CARD --}}
        <section class="overflow-hidden bg-white border rounded-2xl" style="border-color: {{ $BORD }}; border-radius: 1rem;">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-2 text-sm font-semibold text-slate-800">
                    <svg class="w-4 h-4 text-[#a77d52]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Perubahan Data
                </div>
            </div>

            @if($diffPresent)
                <div class="grid gap-5 p-5 md:p-6 md:grid-cols-2">
                    {{-- BEFORE --}}
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-slate-100 text-xs font-bold text-slate-500">S</span>
                            Sebelum
                        </div>
                        <pre class="max-h-[420px] overflow-auto rounded-xl border border-slate-200 bg-[#fbf9f7] p-4 text-xs leading-relaxed text-slate-700 font-mono">{{ json_encode($before, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                    {{-- AFTER --}}
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700">
                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-emerald-100 text-xs font-bold text-emerald-700">N</span>
                            Sesudah
                        </div>
                        <pre class="max-h-[420px] overflow-auto rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 text-xs leading-relaxed text-slate-700 font-mono">{{ json_encode($after, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="mt-3 text-sm font-semibold text-slate-700">Tidak ada data perubahan</div>
                    <div class="mt-1 text-sm text-slate-500">Entri ini tidak menyimpan snapshot sebelum/sesudah.</div>
                </div>
            @endif
        </section>

    </div>
@endsection