{{-- resources/views/admin/sites/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites · Create')

@section('content')
<div class="space-y-6">
  {{-- HEADER: panel biru–merah, 2 tombol --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>

    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Tambah Site</h1>
          <p class="text-slate-600 text-sm">
            Buat site/lokasi baru. <span class="font-medium">Status otomatis: ACTIVE</span>.
          </p>
        </div>
        <div class="flex gap-2">
          <a href="{{ route('admin.sites.index') }}" class="btn btn-ghost">Kembali</a>
          <button form="siteCreateForm" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </div>
  </div>

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
  <form id="siteCreateForm" action="{{ route('admin.sites.store') }}" method="POST"
        class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 space-y-5">
    @csrf

    {{-- Kode & Nama --}}
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">
          Kode <span class="text-red-500">*</span>
        </label>
        <input type="text" name="code" value="{{ old('code') }}" required
               placeholder="Mis. DBK / SBS (A–Z, 0–9, - _ .)"
               class="input mt-1">
        @error('code') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">
          Nama <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name') }}" required
               placeholder="Nama Site"
               class="input mt-1">
        @error('name') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Region & Timezone --}}
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Region (opsional)</label>
        <input type="text" name="region" value="{{ old('region') }}"
               placeholder="Mis. Kalimantan Timur"
               class="input mt-1">
        @error('region') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Timezone (opsional)</label>
        <input type="text" name="timezone" value="{{ old('timezone') }}"
               placeholder="Mis. Asia/Makassar"
               class="input mt-1">
        @error('timezone') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Address --}}
    <div>
      <label class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
      <input type="text" name="address" value="{{ old('address') }}"
             placeholder="Jl. ..."
             class="input mt-1">
      @error('address') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Meta JSON & Notes --}}
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Meta (JSON, opsional)</label>
        <textarea name="meta_json" rows="6" class="input mt-1 font-mono text-xs"
          placeholder='{"timezone":"Asia/Makassar","address":"Jl. ..."}'>{{ old('meta_json') }}</textarea>
        {{-- catatan: controller akan decode meta_json jika valid --}}
        @if($errors->has('meta'))
          <div class="text-xs text-red-600 mt-1">{{ $errors->first('meta') }}</div>
        @endif
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
        <textarea name="notes" rows="6" class="input mt-1"
          placeholder="Catatan internal untuk site">{{ old('notes') }}</textarea>
        @error('notes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>
  </form>
</div>
@endsection
