{{-- resources/views/admin/sites/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites · Create')

@php
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[960px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- HEADER dua-tone + 2 tombol --}}
      <section class="relative bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
          <div class="absolute inset-0 rounded-t-2xl" style="background: linear-gradient(135deg, {{ $ACCENT }}, {{ $ACCENT_DARK }})"></div>
          <div class="absolute inset-y-0 right-0 w-24 rounded-tr-2xl sm:w-36" style="background: {{ $ACCENT_DARK }}"></div>

          <div class="relative flex flex-col h-full gap-3 px-5 md:px-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white md:text-3xl">Tambah Site</h1>
              <p class="text-xs text-white/90 sm:text-sm">
                Buat site/lokasi baru. <span class="font-semibold">Status otomatis: ACTIVE</span>.
              </p>
            </div>
            <div class="flex gap-2">
              <a href="{{ route('admin.sites.index') }}"
                 class="inline-flex items-center px-4 py-2 text-sm font-semibold bg-white border rounded-lg border-slate-200 text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
                 style="--tw-ring-color: {{ $ACCENT }}">
                Kembali
              </a>
              <button form="siteCreateForm" type="submit"
                      class="inline-flex items-center rounded-lg bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-offset-2"
                      style="--tw-ring-color: {{ $ACCENT }}">
                Simpan
              </button>
            </div>
          </div>
        </div>
      </section>

      {{-- ERROR SUMMARY --}}
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

      {{-- FORM: code, name, region, timezone, address, meta_json, notes --}}
      <section class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <form id="siteCreateForm" action="{{ route('admin.sites.store') }}" method="POST"
              class="p-6 space-y-5 md:p-7">
          @csrf

          {{-- Kode & Nama --}}
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-slate-700">
                Kode <span class="text-rose-600">*</span>
              </label>
              <input type="text" name="code" value="{{ old('code') }}" required
                     placeholder="Mis. DBK / SBS (A–Z, 0–9, - _ .)"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}" autocomplete="off">
              @error('code') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700">
                Nama <span class="text-rose-600">*</span>
              </label>
              <input type="text" name="name" value="{{ old('name') }}" required
                     placeholder="Nama Site"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('name') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>
          </div>

          {{-- Region, Timezone, Latitude, Longitude --}}
          <div class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
            <div>
              <label class="block text-sm font-medium text-slate-700">Region (opsional)</label>
              <input type="text" name="region" value="{{ old('region') }}"
                     placeholder="Mis. Kalimantan Timur"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('region') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Timezone (opsional)</label>
              <input type="text" name="timezone" value="{{ old('timezone') }}"
                     placeholder="Mis. Asia/Makassar"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('timezone') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Latitude (opsional)</label>
              <input type="number" step="any" name="latitude" value="{{ old('latitude') }}"
                     placeholder="-6.2000000"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('latitude') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Longitude (opsional)</label>
              <input type="number" step="any" name="longitude" value="{{ old('longitude') }}"
                     placeholder="106.8000000"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('longitude') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>
          </div>

          {{-- Address --}}
          <div>
            <label class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
            <input type="text" name="address" value="{{ old('address') }}"
                   placeholder="Jl. ..."
                   class="w-full px-3 py-2 mt-1 text-sm border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $ACCENT }}">
            @error('address') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
          </div>

          {{-- Meta JSON & Notes --}}
          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label class="block text-sm font-medium text-slate-700">Meta (JSON, opsional)</label>
              <textarea name="meta_json" rows="6"
                        class="w-full px-3 py-2 mt-1 font-mono text-xs border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
                        style="--tw-ring-color: {{ $ACCENT }}"
                        placeholder='{"timezone":"Asia/Makassar","address":"Jl. ..."}'>{{ old('meta_json') }}</textarea>
              {{-- catatan: controller akan decode meta_json jika valid --}}
              @if($errors->has('meta'))
                <div class="mt-1 text-xs text-rose-600">{{ $errors->first('meta') }}</div>
              @endif
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
              <textarea name="notes" rows="6"
                        class="w-full px-3 py-2 mt-1 text-sm border rounded-lg input border-slate-200 focus:outline-none focus:ring-2"
                        style="--tw-ring-color: {{ $ACCENT }}"
                        placeholder="Catatan internal untuk site">{{ old('notes') }}</textarea>
              @error('notes') <div class="mt-1 text-xs text-rose-600">{{ $message }}</div> @enderror
            </div>
          </div>
        </form>
      </section>
    </div>
@endsection
