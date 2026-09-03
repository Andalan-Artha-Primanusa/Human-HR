{{-- resources/views/admin/sites/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites · Edit • karir-andalan')

@php
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[960px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- HEADER (shared solid-brown page-header) --}}
      <x-admin.page-header eyebrow="MANAGEMENT" title="Edit Site: {{ e($site->code) }}" description="Perbarui informasi site.">
        <a href="{{ route('admin.sites.index') }}" class="ph-action">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali
        </a>
        <button type="submit" form="siteEditForm" class="ph-action ph-action--brand">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          Simpan
        </button>
      </x-admin.page-header>

      {{-- FLASH --}}
      @if(session('success'))
        <div class="rounded-xl bg-green-50 text-green-700 px-4 py-3 border border-green-200">{{ session('success') }}</div>
      @endif
      @if(session('error'))
        <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border border-red-200">{{ session('error') }}</div>
      @endif

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

      {{-- FORM UPDATE (tanpa nested) --}}
      <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
        <form id="siteEditForm" action="{{ route('admin.sites.update', $site) }}" method="POST"
              class="p-6 md:p-7 space-y-5">
          @csrf
          @method('PUT')

          {{-- Nama & Kode --}}
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700">Nama <span class="text-rose-600">*</span></label>
              <input type="text" name="name" value="{{ old('name', $site->name) }}" required
                     class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('name') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Kode <span class="text-rose-600">*</span></label>
              <input type="text" name="code" value="{{ old('code', $site->code) }}" required
                     placeholder="A–Z, 0–9, - _ ."
                     class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('code') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div>

          {{-- Region, Timezone, Latitude, Longitude --}}
          <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700">Region (opsional)</label>
              <input type="text" name="region" value="{{ old('region', $site->region) }}"
                     placeholder="Mis. Kalimantan Timur"
                     class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('region') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Timezone (opsional)</label>
              <input type="text" name="timezone" value="{{ old('timezone', $site->timezone) }}"
                     placeholder="Mis. Asia/Makassar"
                     class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('timezone') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Latitude (opsional)</label>
              <input type="number" step="any" id="latInput" name="latitude" value="{{ old('latitude', $site->latitude) }}"
                     placeholder="-6.2000000"
                     class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('latitude') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Longitude (opsional)</label>
              <input type="number" step="any" id="lngInput" name="longitude" value="{{ old('longitude', $site->longitude) }}"
                     placeholder="106.8000000"
                     class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}">
              @error('longitude') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div>

          {{-- Interactive Map --}}
          <div class="space-y-2">
            <label class="block text-sm font-medium text-slate-700">Pilih Lokasi di Map</label>
            <div id="map" class="w-full h-72 rounded-xl border border-slate-200 z-0"></div>
            <p class="text-[11px] text-slate-500 italic">Klik pada peta untuk memindahkan marker.</p>
          </div>

          {{-- Address --}}
          <div>
            <label class="block text-sm font-medium text-slate-700">Alamat (opsional)</label>
            <input type="text" name="address" value="{{ old('address', $site->address) }}"
                   placeholder="Jl. ..."
                   class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $ACCENT }}">
            @error('address') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
          </div>

          {{-- Meta JSON & Notes --}}
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-slate-700">Meta (JSON, opsional)</label>
              <textarea name="meta_json" rows="6"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 font-mono text-xs focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $ACCENT }}"
                placeholder='{"timezone":"Asia/Makassar","address":"Jl. ..."}'>{{ old('meta_json', $site->meta ? json_encode($site->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</textarea>
              @if($errors->has('meta'))
                <div class="text-sm text-rose-600 mt-1">{{ $errors->first('meta') }}</div>
              @endif
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
              <textarea name="notes" rows="6"
                class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $ACCENT }}"
                placeholder="Catatan internal untuk site">{{ old('notes', $site->notes) }}</textarea>
              @error('notes') <div class="text-sm text-rose-600 mt-1">{{ $message }}</div> @enderror
            </div>
          </div>

          {{-- Actions (di dalam form) --}}
          <div class="flex items-center justify-between pt-2">
            <div class="flex gap-2">
              <button type="submit"
                      class="abtn abtn-primary">
                Simpan Perubahan
              </button>
              <a href="{{ route('admin.sites.index') }}"
                 class="abtn abtn-neutral">
                Batal
              </a>
            </div>
            {{-- tombol hapus trigger form delete di bawah --}}
            <button type="submit" form="siteDeleteForm"
                    class="abtn abtn-danger"
                    style="--tw-ring-color: {{ $ACCENT }}">
              Hapus
            </button>
          </div>
        </form>
      </section>

      {{-- FORM DELETE (terpisah, bukan nested) --}}
      <form id="siteDeleteForm" action="{{ route('admin.sites.destroy', $site) }}" method="POST" class="hidden" data-confirm-title="Hapus site ini?" data-confirm-message="Site yang sudah dipakai lowongan atau kandidat mungkin tidak dapat dihapus.">
        @csrf @method('DELETE')
      </form>
    </div>

    {{-- Leaflet CSS & JS --}}
    @push('head')
      <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    @endpush

    @push('scripts')
      <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
            const latInput = document.getElementById('latInput');
            const lngInput = document.getElementById('lngInput');

            // Default: Jakarta center if empty
            let defaultLat = -6.2000000;
            let defaultLng = 106.816666;

            if (latInput.value && lngInput.value) {
                defaultLat = parseFloat(latInput.value);
                defaultLng = parseFloat(lngInput.value);
            }

            const map = L.map('map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            let marker = L.marker([defaultLat, defaultLng], {
                draggable: true
            }).addTo(map);

            // Update inputs on marker drag
            marker.on('dragend', function(e) {
                const position = marker.getLatLng();
                latInput.value = position.lat.toFixed(7);
                lngInput.value = position.lng.toFixed(7);
            });

            // Update marker on click map
            map.on('click', function(e) {
                const position = e.latlng;
                marker.setLatLng(position);
                latInput.value = position.lat.toFixed(7);
                lngInput.value = position.lng.toFixed(7);
            });

            // Sync marker with manual input
            const syncMarker = () => {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng)) {
                    const newPos = new L.LatLng(lat, lng);
                    marker.setLatLng(newPos);
                    map.panTo(newPos);
                }
            };

            latInput.addEventListener('change', syncMarker);
            lngInput.addEventListener('change', syncMarker);
        });
      </script>
    @endpush
@endsection
