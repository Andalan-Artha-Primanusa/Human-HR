{{-- resources/views/admin/sites/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites · Edit')

@php
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
<div class="mx-auto w-full max-w-[960px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER dua-tone --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-white">
            Edit Site: {{ e($site->code) }}
          </h1>
          <p class="text-white/90 text-xs sm:text-sm">Perbarui informasi site.</p>
        </div>
        <a href="{{ route('admin.sites.index') }}"
           class="hidden sm:inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
           style="--tw-ring-color: {{ $BLUE }}">
          Kembali
        </a>
      </div>
    </div>
  </section>

  {{-- FLASH --}}
  @if(session('success'))
    <div class="rounded-xl bg-green-50 text-green-700 px-4 py-3 border border-green-200">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border border-red-200">{{ session('error') }}</div>
  @endif

  {{-- ERROR SUMMARY --}}
  @if ($errors->any())
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border border-red-200">
      <div class="font-medium">Periksa kembali isian kamu:</div>
      <ul class="mt-1 list-disc list-inside text-sm">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM UPDATE (tanpa nested) --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <form id="siteEditForm" action="{{ route('admin.sites.update', $site) }}" method="POST"
          class="p-6 md:p-7 space-y-5">
      @csrf
      @method('PUT')

      {{-- Nama & Kode --}}
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Nama <span class="text-rose-600">*</span></label>
          <input type="text" name="name" value="{{ old('name', $site->name) }}" required
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('name') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Kode <span class="text-rose-600">*</span></label>
          <input type="text" name="code" value="{{ old('code', $site->code) }}" required
                 placeholder="A–Z, 0–9, - _ ."
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('code') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Region & Timezone --}}
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Region (opsional)</label>
          <input type="text" name="region" value="{{ old('region', $site->region) }}"
                 placeholder="Mis. Kalimantan Timur"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('region') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Timezone (opsional)</label>
          <input type="text" name="timezone" value="{{ old('timezone', $site->timezone) }}"
                 placeholder="Mis. Asia/Makassar"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('timezone') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Address --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
        <input type="text" name="address" value="{{ old('address', $site->address) }}"
               placeholder="Jl. ..."
               class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}">
        @error('address') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- Meta JSON & Notes --}}
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Meta (JSON, opsional)</label>
          <textarea name="meta_json" rows="6"
            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 font-mono text-xs focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $BLUE }}"
            placeholder='{"timezone":"Asia/Makassar","address":"Jl. ..."}'>{{ old('meta_json', $site->meta ? json_encode($site->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : '') }}</textarea>
          @if($errors->has('meta'))
            <div class="text-sm text-rose-600 mt-1">{{ $errors->first('meta') }}</div>
          @endif
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
          <textarea name="notes" rows="6"
            class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
            style="--tw-ring-color: {{ $BLUE }}"
            placeholder="Catatan internal untuk site">{{ old('notes', $site->notes) }}</textarea>
          @error('notes') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Actions (di dalam form) --}}
      <div class="flex items-center justify-between pt-2">
        <div class="flex gap-2">
          <button type="submit"
                  class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2"
                  style="--tw-ring-color: {{ $BLUE }}">
            Simpan Perubahan
          </button>
          <a href="{{ route('admin.sites.index') }}"
             class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
             style="--tw-ring-color: {{ $BLUE }}">
            Batal
          </a>
        </div>
        {{-- tombol hapus trigger form delete di bawah --}}
        <button type="submit" form="siteDeleteForm"
                class="inline-flex items-center rounded-lg border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
                style="--tw-ring-color: {{ $BLUE }}"
                onclick="return confirm('Hapus site ini? Aksi tidak dapat dibatalkan.');">
          Hapus
        </button>
      </div>
    </form>
  </section>

  {{-- FORM DELETE (terpisah, bukan nested) --}}
  <form id="siteDeleteForm" action="{{ route('admin.sites.destroy', $site) }}" method="POST" class="hidden">
    @csrf @method('DELETE')
  </form>
</div>
@endsection
