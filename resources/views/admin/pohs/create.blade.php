@extends('layouts.admin')

@section('title', 'Tambah POH')

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
@endphp

@section('content')
<div class="mx-auto w-full max-w-[960px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <section class="relative bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
            <div class="absolute inset-0 rounded-t-2xl" style="background: linear-gradient(135deg, {{ $ACCENT }}, {{ $ACCENT_DARK }})"></div>
            <div class="absolute inset-y-0 right-0 w-24 rounded-tr-2xl sm:w-36" style="background: {{ $ACCENT_DARK }}"></div>
            <div class="relative flex items-center justify-between h-full gap-3 px-5 md:px-6">
                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold tracking-tight text-white md:text-3xl">Tambah POH</h1>
                    <p class="text-xs text-white/90 sm:text-sm">Buat Place of Hire baru.</p>
                </div>
                <a href="{{ route('admin.pohs.index') }}"
                   class="items-center hidden px-4 py-2 text-sm font-semibold bg-white border rounded-lg sm:inline-flex border-slate-200 text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
                    Kembali
                </a>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="px-4 py-3 text-green-700 border border-green-200 rounded-xl bg-green-50">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="px-4 py-3 text-red-700 border border-red-200 rounded-xl bg-red-50">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="px-4 py-3 text-red-700 border border-red-200 rounded-xl bg-red-50">
            <div class="font-medium">Periksa kembali isian kamu:</div>
            <ul class="mt-1 text-sm list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <form id="pohCreateForm" action="{{ route('admin.pohs.store') }}" method="POST" class="p-6 space-y-5 md:p-7">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                        style="--tw-ring-color: {{ $ACCENT }}">
                    @error('name') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kode <span class="text-rose-600">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required
                        class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                        style="--tw-ring-color: {{ $ACCENT }}">
                    @error('code') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
                <input type="text" name="address" value="{{ old('address') }}"
                    class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
                @error('address') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Keterangan (opsional)</label>
                <textarea name="description" rows="4"
                    class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">{{ old('description') }}</textarea>
                @error('description') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" class="mr-2" {{ old('is_active', 1) ? 'checked' : '' }}> Aktif
                </label>
            </div>
            <div class="flex items-center justify-between pt-2">
                <button type="submit"
                    class="inline-flex items-center rounded-lg bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
                    Simpan
                </button>
                <a href="{{ route('admin.pohs.index') }}"
                    class="inline-flex items-center px-4 py-2 text-sm font-semibold bg-white border rounded-lg border-slate-200 text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
                    Batal
                </a>
            </div>
        </form>
    </section>
</div>
@endsection
