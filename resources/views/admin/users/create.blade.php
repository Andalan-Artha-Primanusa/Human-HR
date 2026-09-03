@extends('layouts.app')

@section('title', 'Create User • karir-andalan')

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[1100px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">
      <x-admin.page-header
        eyebrow="Manajemen Pengguna"
        title="Buat User"
        description="Buat akun pengguna baru dengan tema konsisten admin.">
        <a href="{{ route('admin.users.index') }}" class="ph-action">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali
        </a>
        <button type="submit" form="userCreateForm" class="ph-action ph-action--brand">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          Simpan
        </button>
      </x-admin.page-header>

      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="p-6 border-t md:p-7 bg-white" style="border-color: {{ $BORD }}">
          <form id="userCreateForm" method="POST" action="{{ route('admin.users.store') }}" class="grid max-w-2xl gap-4">
            @csrf

            <div>
              <label class="block mb-1 text-sm text-slate-700">Name</label>
              <input type="text" name="name" value="{{ old('name') }}"
                     class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}" required>
              @error('name')
                <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
              @enderror
            </div>

            <div>
              <label class="block mb-1 text-sm text-slate-700">Email</label>
              <input type="email" name="email" value="{{ old('email') }}"
                     class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}" required>
              @error('email')
                <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
              @enderror
            </div>

            @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'id_employe'))
                <div>
                  <label class="block mb-1 text-sm text-slate-700">
                    ID Employe <span class="text-xs text-slate-400">(opsional, unik)</span>
                  </label>
                  <input type="text" name="id_employe" value="{{ old('id_employe') }}"
                         class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                         style="--tw-ring-color: {{ $ACCENT }}">
                  @error('id_employe')
                    <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
                  @enderror
                </div>
            @endif

            <div>
              <label class="block mb-1 text-sm text-slate-700">Password <span class="text-slate-400">(kosongkan = random)</span></label>
              <input type="password" name="password"
                     class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}">
            </div>

            <div>
              <label class="block mb-1 text-sm text-slate-700">Role</label>
              <select name="role"
                      class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                      style="--tw-ring-color: {{ $ACCENT }}">
                <option value="">— Pilih Role —</option>
                @foreach(($roleOptions ?? []) as $opt)
                      <option value="{{ $opt }}" @selected(old('role') === $opt)>{{ ucfirst($opt) }}</option>
                @endforeach
              </select>
            </div>

            @if(\Illuminate\Support\Facades\Schema::hasColumn('users', 'active'))
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                  <input type="hidden" name="active" value="0">
                  <input type="checkbox" name="active" value="1" id="active" class="rounded border-slate-300"
                         {{ old('active', '1') ? 'checked' : '' }}>
                  Active
                </label>
            @endif

            <div class="flex flex-col gap-2 mt-2 sm:flex-row sm:justify-end">
              <a href="{{ route('admin.users.index') }}"
                 class="abtn abtn-neutral">
                Cancel
              </a>
              <button class="abtn abtn-primary">
                Simpan
              </button>
            </div>
          </form>
        </div>
      </section>
    </div>
@endsection