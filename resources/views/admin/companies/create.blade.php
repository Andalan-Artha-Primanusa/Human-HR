{{-- resources/views/admin/companies/create.blade.php --}}
@extends('layouts.app', ['title' => 'Create Company'])

@php
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[960px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- HEADER dua-tone --}}
      <section class="relative bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative h-20 overflow-hidden sm:h-24 rounded-t-2xl">
          <div class="absolute inset-0" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
          <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

          <div class="relative flex items-center h-full px-5 md:px-6">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white md:text-3xl">Create Company</h1>
              <p class="text-sm text-white/90">Tambahkan perusahaan baru ke sistem.</p>
            </div>
          </div>
        </div>
      </section>

      {{-- FORM (kartu terpisah, ada jarak) --}}
      <section class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <form method="POST" action="{{ route('admin.companies.store') }}" class="p-6 md:p-7 space-y-6 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]">
          @csrf

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            {{-- Code --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Code <span class="text-rose-600">*</span></label>
              <input name="code" value="{{ old('code', $record->code) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}" required autocomplete="off">
              @error('code')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Name --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Name <span class="text-rose-600">*</span></label>
              <input name="name" value="{{ old('name', $record->name) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}" required autocomplete="off">
              @error('name')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Legal Name --}}
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700">Legal Name</label>
              <input name="legal_name" value="{{ old('legal_name', $record->legal_name) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}">
              @error('legal_name')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Email --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Email</label>
              <input name="email" type="email" value="{{ old('email', $record->email) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}">
              @error('email')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Phone --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Phone</label>
              <input name="phone" value="{{ old('phone', $record->phone) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}">
              @error('phone')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Website --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Website</label>
              <input name="website" type="url" value="{{ old('website', $record->website) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}" placeholder="https://example.com">
              @error('website')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Logo Path --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Logo Path</label>
              <input name="logo_path" value="{{ old('logo_path', $record->logo_path) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}" placeholder="storage/logos/acme.png">
              @error('logo_path')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Address --}}
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700">Address</label>
              <textarea name="address" rows="3"
                        class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                        style="--tw-ring-color: {{ $ACCENT }}">{{ old('address', $record->address) }}</textarea>
              @error('address')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- City --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">City</label>
              <input name="city" value="{{ old('city', $record->city) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}">
              @error('city')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Province --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Province</label>
              <input name="province" value="{{ old('province', $record->province) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}">
              @error('province')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Country --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Country</label>
              <input name="country" value="{{ old('country', $record->country) }}"
                     class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                     style="--tw-ring-color: {{ $ACCENT }}">
              @error('country')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>

            {{-- Status --}}
            <div>
              <label class="block text-sm font-medium text-slate-700">Status</label>
              <select name="status"
                      class="w-full px-3 py-2 mt-1 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
                      style="--tw-ring-color: {{ $ACCENT }}">
                <option value="active"   @selected(old('status', $record->status) === 'active')>Active</option>
                <option value="inactive" @selected(old('status', $record->status) === 'inactive')>Inactive</option>
              </select>
              @error('status')<div class="mt-1 text-sm text-rose-600">{{ $message }}</div>@enderror
            </div>
          </div>

          {{-- Actions --}}
          <div class="flex items-center gap-3 pt-2">
            <button type="submit"
              class="inline-flex items-center justify-center gap-2 rounded-lg bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] px-4 py-2 text-sm font-semibold text-white hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-offset-2"
              style="--tw-ring-color: {{ $ACCENT }}">
              Save
            </button>
            <a href="{{ url()->previous() }}"
               class="inline-flex items-center px-4 py-2 text-sm bg-white border rounded-lg border-slate-200 text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
               style="--tw-ring-color: {{ $ACCENT }}">
              Cancel
            </a>
          </div>
        </form>
      </section>
    </div>
@endsection
