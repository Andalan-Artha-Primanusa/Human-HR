{{-- resources/views/admin/companies/edit.blade.php --}}
@extends('layouts.app', ['title' => 'Edit Company'])

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
      <div class="absolute inset-0" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex items-center">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-white">Edit Company</h1>
          <p class="text-sm text-white/90">Perbarui detail perusahaan.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- FORM (tanpa nested form) --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <form method="POST" action="{{ route('admin.companies.update', $record) }}" class="p-5 md:p-6 space-y-6">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Code --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Code <span class="text-rose-600">*</span></label>
          <input name="code" value="{{ old('code', $record->code) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}" required autocomplete="off">
          @error('code')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Name --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Name <span class="text-rose-600">*</span></label>
          <input name="name" value="{{ old('name', $record->name) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}" required autocomplete="off">
          @error('name')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Legal Name --}}
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-slate-700">Legal Name</label>
          <input name="legal_name" value="{{ old('legal_name', $record->legal_name) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('legal_name')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Email --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Email</label>
          <input name="email" type="email" value="{{ old('email', $record->email) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('email')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Phone --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Phone</label>
          <input name="phone" value="{{ old('phone', $record->phone) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('phone')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Website --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Website</label>
          <input name="website" type="url" value="{{ old('website', $record->website) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}" placeholder="https://example.com">
          @error('website')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Logo Path --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Logo Path</label>
          <input name="logo_path" value="{{ old('logo_path', $record->logo_path) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}" placeholder="storage/logos/acme.png">
          @error('logo_path')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Address --}}
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-slate-700">Address</label>
          <textarea name="address" rows="3"
                    class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $BLUE }}">{{ old('address', $record->address) }}</textarea>
          @error('address')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- City --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">City</label>
          <input name="city" value="{{ old('city', $record->city) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('city')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Province --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Province</label>
          <input name="province" value="{{ old('province', $record->province) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('province')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Country --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Country</label>
          <input name="country" value="{{ old('country', $record->country) }}"
                 class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $BLUE }}">
          @error('country')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>

        {{-- Status --}}
        <div>
          <label class="block text-sm font-medium text-slate-700">Status</label>
          <select name="status"
                  class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                  style="--tw-ring-color: {{ $BLUE }}">
            <option value="active"   @selected(old('status', $record->status) === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $record->status) === 'inactive')>Inactive</option>
          </select>
          @error('status')<div class="text-sm text-rose-600 mt-1">{{ $message }}</div>@enderror
        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2"
                style="--tw-ring-color: {{ $BLUE }}">
          Save
        </button>
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
           style="--tw-ring-color: {{ $BLUE }}">
          Cancel
        </a>
      </div>
    </form>
  </section>
</div>
@endsection
