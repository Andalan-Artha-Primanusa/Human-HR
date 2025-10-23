{{-- resources/views/profile/edit.blade.php --}}
@extends('layouts.app', ['title' => 'Profile'])

@php
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1120px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER: dua-tone, tidak nempel ke form --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex items-center">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white">Profile</h1>
          <p class="text-xs sm:text-sm text-white/90">Kelola informasi akun & keamanan.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- CARD: Update Profile Information --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
      <h2 class="text-base font-semibold text-slate-800">Profile Information</h2>
      <p class="text-sm text-slate-500">Perbarui nama, email, dan foto profil Anda.</p>
    </div>
    <div class="p-5 sm:p-6">
      <div class="max-w-xl">
        @include('profile.partials.update-profile-information-form')
      </div>
    </div>
  </section>

  {{-- CARD: Update Password --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
      <h2 class="text-base font-semibold text-slate-800">Update Password</h2>
      <p class="text-sm text-slate-500">Gunakan sandi yang kuat & unik.</p>
    </div>
    <div class="p-5 sm:p-6">
      <div class="max-w-xl">
        @include('profile.partials.update-password-form')
      </div>
    </div>
  </section>

  {{-- CARD: Delete Account --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
      <h2 class="text-base font-semibold text-slate-800">Delete Account</h2>
      <p class="text-sm text-slate-500">Tindakan ini permanen. Harap berhati-hati.</p>
    </div>
    <div class="p-5 sm:p-6">
      <div class="max-w-xl">
        @include('profile.partials.delete-user-form')
      </div>
    </div>
  </section>

</div>
@endsection
