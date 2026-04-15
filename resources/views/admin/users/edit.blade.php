@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
@php
  $ACCENT = '#a77d52';
  $ACCENT_DARK = '#8b5e3c';
  $BORD = '#e5e7eb';
@endphp

<div class="mx-auto w-full max-w-[1100px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">
  <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <div class="relative">
      <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

      <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Edit User</h1>
          <p class="text-xs sm:text-sm text-white/90">Perbarui data akun pengguna dengan tema konsisten admin.</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
           style="--tw-ring-color: {{ $ACCENT }}">
          Kembali ke Users
        </a>
      </div>
    </div>
  </section>

  @if ($errors->any())
    <div class="px-4 py-3 border rounded-xl border-rose-200 bg-rose-50 text-rose-800">
      <div class="mb-1 font-semibold">Periksa kembali isian berikut:</div>
      <ul class="ml-5 text-sm list-disc">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <div class="p-6 border-t md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]" style="border-color: {{ $BORD }}">
      <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid max-w-2xl gap-4">
        @csrf @method('PATCH')

        <div>
          <label class="block mb-1 text-sm text-slate-700">Name</label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}"
                 class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $ACCENT }}">
        </div>

        <div>
          <label class="block mb-1 text-sm text-slate-700">Email</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}"
                 class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $ACCENT }}">
        </div>

        @if(\Illuminate\Support\Facades\Schema::hasColumn('users','id_employe'))
        <div>
          <label class="block mb-1 text-sm text-slate-700">
            ID Employe <span class="text-xs text-slate-400">(opsional, unik)</span>
          </label>
          <input type="text" name="id_employe"
                 value="{{ old('id_employe', $user->id_employe) }}"
                 class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $ACCENT }}">
          @error('id_employe')
            <div class="mt-1 text-sm text-rose-600">{{ $message }}</div>
          @enderror
        </div>
        @endif

        <div>
          <label class="block mb-1 text-sm text-slate-700">Password <span class="text-slate-400">(isi untuk ganti)</span></label>
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
              <option value="{{ $opt }}" @selected(old('role', (isset($user->role)?$user->role:($user->getRoleNames()->first() ?? ''))) === $opt)>
                {{ ucfirst($opt) }}
              </option>
            @endforeach
          </select>
        </div>

        @if(\Illuminate\Support\Facades\Schema::hasColumn('users','active'))
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
          <input type="hidden" name="active" value="0">
          <input type="checkbox" name="active" value="1" id="active" class="rounded border-slate-300"
                 {{ old('active', (int)($user->active ?? 1)) ? 'checked' : '' }}>
          Active
        </label>
        @endif

        <div class="flex flex-col gap-2 mt-2 sm:flex-row sm:justify-end">
          <a href="{{ route('admin.users.index') }}"
             class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50">
            Cancel
          </a>
          <button class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
                  style="--tw-ring-color: {{ $ACCENT }}">
            Update User
          </button>
        </div>
      </form>
    </div>
  </section>
</div>
@endsection
