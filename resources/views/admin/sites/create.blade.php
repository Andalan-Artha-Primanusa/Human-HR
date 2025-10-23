{{-- resources/views/admin/sites/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites · Create')

@php
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
<div class="mx-auto w-full max-w-[960px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER dua-tone + 2 tombol --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-white">Tambah Site</h1>
          <p class="text-white/90 text-xs sm:text-sm">
            Buat site/lokasi baru. <span class="font-semibold">Status otomatis: ACTIVE</span>.
          </p>
        </div>
        <div class="flex gap-2">
          <a href="{{ route('admin.sites.index') }}"
             class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
             style="--tw-ring-color: {{ $BLUE }}">
            Kembali
          </a>
          <button form="siteCreateForm" type="submit"
                  class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2"
                  style="--tw-ring-color: {{ $BLUE }}">
            Simpan
          </button>
        </div>
      </div>
    </div>
  </section>

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

  {{-- FORM: code, name, region, timezone, address, meta_json, notes --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <form id="siteCreateForm" action="{{ route('admin.sites.store') }}" method="POST"
          class="p-6 md:p-7 space-y-5">
      @csrf

      {{-- Kode & Nama --}}
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">
            Kode <span class="text-rose-600">*</span>
          </label>
          <input type="text" name="code" value="{{ old('code') }}" required
                 placeholder="Mis. DBK / SBS (A–Z, 0–9, - _ .)"
                 class="input mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}" autocomplete="off">
          @error('code') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">
            Nama <span class="text-rose-600">*</span>
          </label>
          <input type="text" name="name" value="{{ old('name') }}" required
                 placeholder="Nama Site"
                 class="input mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('name') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Region & Timezone --}}
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Region (opsional)</label>
          <input type="text" name="region" value="{{ old('region') }}"
                 placeholder="Mis. Kalimantan Timur"
                 class="input mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('region') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Timezone (opsional)</label>
          <input type="text" name="timezone" value="{{ old('timezone') }}"
                 placeholder="Mis. Asia/Makassar"
                 class="input mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('timezone') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>

      {{-- Address --}}
      <div>
        <label class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
        <input type="text" name="address" value="{{ old('address') }}"
               placeholder="Jl. ..."
               class="input mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}">
        @error('address') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      {{-- Meta JSON & Notes --}}
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Meta (JSON, opsional)</label>
          <textarea name="meta_json" rows="6"
                    class="input mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 font-mono text-xs focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $BLUE }}"
                    placeholder='{"timezone":"Asia/Makassar","address":"Jl. ..."}'>{{ old('meta_json') }}</textarea>
          {{-- catatan: controller akan decode meta_json jika valid --}}
          @if($errors->has('meta'))
            <div class="text-xs text-rose-600 mt-1">{{ $errors->first('meta') }}</div>
          @endif
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
          <textarea name="notes" rows="6"
                    class="input mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $BLUE }}"
                    placeholder="Catatan internal untuk site">{{ old('notes') }}</textarea>
          @error('notes') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
        </div>
      </div>
    </form>
  </section>
</div>
@endsection
