{{-- resources/views/admin/sites/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites · Edit')

@section('content')
<div class="p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Edit Site: {{ $site->code }}</h1>
      <p class="text-slate-500 text-sm">Perbarui informasi site.</p>
    </div>
    <a href="{{ route('admin.sites.index') }}" class="btn">Kembali</a>
  </div>

  @if(session('success'))
    <div class="rounded-xl bg-green-50 text-green-700 px-4 py-3 border border-green-200">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border border-red-200">{{ session('error') }}</div>
  @endif

  @if ($errors->any())
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border border-red-200">
      <div class="font-medium">Periksa kembali isian kamu:</div>
      <ul class="mt-1 list-disc list-inside text-sm">
        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- FORM UPDATE (ID: siteEditForm) --}}
  <form id="siteEditForm" action="{{ route('admin.sites.update', $site) }}" method="POST"
        class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">
    @csrf
    @method('PUT')

    {{-- Nama & Kode --}}
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Nama <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $site->name) }}" required
               class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Kode <span class="text-red-500">*</span></label>
        <input type="text" name="code" value="{{ old('code', $site->code) }}" required
               placeholder="A–Z, 0–9, - _ ."
               class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('code') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Region & Timezone --}}
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Region (opsional)</label>
        <input type="text" name="region" value="{{ old('region', $site->region) }}"
               placeholder="Mis. Kalimantan Timur"
               class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('region') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Timezone (opsional)</label>
        <input type="text" name="timezone" value="{{ old('timezone', $site->timezone) }}"
               placeholder="Mis. Asia/Makassar"
               class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('timezone') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Address --}}
    <div>
      <label class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
      <input type="text" name="address" value="{{ old('address', $site->address) }}"
             placeholder="Jl. ..."
             class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
      @error('address') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
    </div>

    {{-- Meta JSON & Notes --}}
    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Meta (JSON, opsional)</label>
        <textarea name="meta_json" rows="6"
          class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 font-mono text-xs"
          placeholder='{"timezone":"Asia/Makassar","address":"Jl. ..."}'>{{ old('meta_json', $site->meta ? json_encode($site->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : '') }}</textarea>
        @if($errors->has('meta'))
          <div class="text-sm text-red-600 mt-1">{{ $errors->first('meta') }}</div>
        @endif
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
        <textarea name="notes" rows="6"
          class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500"
          placeholder="Catatan internal untuk site">{{ old('notes', $site->notes) }}</textarea>
        @error('notes') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Actions (tombol update di form ini) --}}
    <div class="flex items-center justify-between pt-2">
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      <a href="{{ route('admin.sites.index') }}" class="btn">Batal</a>
    </div>
  </form>

  {{-- FORM DELETE (TERPISAH, BUKAN NESTED) --}}
  <form id="siteDeleteForm" action="{{ route('admin.sites.destroy', $site) }}" method="POST" class="hidden">
    @csrf @method('DELETE')
  </form>

  {{-- Tombol hapus yang men-trigger form delete di atas --}}
  <div class="flex">
    <button type="submit" form="siteDeleteForm"
            class="btn btn-danger"
            onclick="return confirm('Hapus site ini? Aksi tidak dapat dibatalkan.');">
      Hapus
    </button>
  </div>
</div>
@endsection
