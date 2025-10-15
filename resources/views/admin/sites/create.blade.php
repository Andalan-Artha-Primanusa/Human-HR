@extends('layouts.app')

@section('title', 'Admin · Sites · Create')

@section('content')
<div class="p-6 space-y-6">
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Tambah Site</h1>
      <p class="text-slate-500 text-sm">Buat site/lokasi baru.</p>
    </div>
    <a href="{{ route('admin.sites.index') }}" class="btn">Kembali</a>
  </div>

  {{-- Errors --}}
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

  <form action="{{ route('admin.sites.store') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">
    @csrf

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" required
               class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('name') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Kode</label>
        <input type="text" name="code" value="{{ old('code') }}" placeholder="Mis. DBK / POS / SBS"
               class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('code') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Status</label>
        <select name="status" class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
          <option value="active" {{ old('status','active') === 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Deskripsi (opsional)</label>
        <input type="text" name="description" value="{{ old('description') }}"
               class="mt-1 w-full rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500">
        @error('description') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    <div class="flex items-center justify-end gap-2">
      <a href="{{ route('admin.sites.index') }}" class="btn">Batal</a>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </form>
</div>
@endsection
