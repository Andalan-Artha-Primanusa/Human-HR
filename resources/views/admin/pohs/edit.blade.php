@extends('layouts.app')

@section('title', 'Edit POH • karir-andalan')

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
@endphp

@section('content')
<div class="mx-auto w-full max-w-[960px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    {{-- HEADER (shared solid-brown page-header) --}}
    <x-admin.page-header eyebrow="MANAGEMENT" title="Edit POH: {{ e($poh->code) }}" description="Perbarui informasi Place of Hire.">
      <a href="{{ route('admin.pohs.index') }}" class="ph-action">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
      </a>
      <button type="submit" form="pohEditForm" class="ph-action ph-action--brand">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        Simpan
      </button>
    </x-admin.page-header>

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
        <form id="pohEditForm" action="{{ route('admin.pohs.update', $poh) }}" method="POST" class="p-6 space-y-5 md:p-7">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nama <span class="text-rose-600">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $poh->name) }}" required
                        class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                        style="--tw-ring-color: {{ $ACCENT }}">
                    @error('name') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Kode <span class="text-rose-600">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $poh->code) }}" required
                        class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                        style="--tw-ring-color: {{ $ACCENT }}">
                    @error('code') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
                <input type="text" name="address" value="{{ old('address', $poh->address) }}"
                    class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
                @error('address') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Keterangan (opsional)</label>
                <textarea name="description" rows="4"
                    class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">{{ old('description', $poh->description) }}</textarea>
                @error('description') <div class="mt-1 text-sm text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" class="mr-2" {{ old('is_active', $poh->is_active) ? 'checked' : '' }}> Aktif
                </label>
            </div>
            <div class="flex items-center justify-between pt-2">
                <div class="flex gap-2">
                    <button type="submit"
                        class="abtn abtn-primary">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.pohs.index') }}"
                        class="abtn abtn-neutral">
                        Batal
                    </a>
                </div>
                <button type="submit" form="pohDeleteForm"
                    class="abtn abtn-danger"
                    style="--tw-ring-color: {{ $ACCENT }}">
                    Hapus
                </button>
            </div>
        </form>
    </section>
    <form id="pohDeleteForm" action="{{ route('admin.pohs.destroy', $poh) }}" method="POST" class="hidden" data-confirm-title="Hapus POH ini?" data-confirm-message="POH yang sudah dipakai profil kandidat mungkin tidak dapat dihapus.">
        @csrf @method('DELETE')
    </form>
</div>
@endsection
