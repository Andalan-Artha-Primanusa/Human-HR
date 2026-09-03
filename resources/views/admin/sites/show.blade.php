{{-- resources/views/admin/sites/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Site · ' . ($site->code ?? 'Site') . ' • karir-andalan')

@php
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
    <div class="mx-auto w-full max-w-[1120px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- HEADER (shared solid-brown page-header) --}}
      <x-admin.page-header eyebrow="MANAGEMENT" title="{{ e($site->name ?? '—') }} ({{ e($site->code) }})" description="Detail Site">
        <a href="{{ route('admin.sites.index') }}" class="ph-action">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
          Kembali
        </a>

        @if(Route::has('admin.sites.toggle'))
          <form method="POST" action="{{ route('admin.sites.toggle', $site) }}"
                data-confirm-title="Ubah status site?"
                data-confirm-message="Status site akan diperbarui dan memengaruhi pilihan lokasi kerja."
                class="inline">
            @csrf
            <button type="submit" class="ph-action">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 3v12"/><path stroke-width="2" stroke-linecap="round" d="M5 10 12 3l7 7"/><path stroke-width="2" stroke-linecap="round" d="M5 21h14"/></svg>
              {{ $site->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
            </button>
          </form>
        @endif

        <a href="{{ route('admin.sites.edit', $site) }}" class="ph-action ph-action--brand">
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>

        <form method="POST" action="{{ route('admin.sites.destroy', $site) }}"
              data-confirm-title="Hapus site ini?"
              data-confirm-message="Site yang sudah dipakai lowongan atau kandidat mungkin tidak dapat dihapus."
              class="inline">
          @csrf @method('DELETE')
          <button type="submit" class="ph-action">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/></svg>
            Hapus
          </button>
        </form>
      </x-admin.page-header>

      {{-- Ringkasan angka --}}
      <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="p-5 bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
          <div class="text-sm text-slate-500">Jumlah Jobs</div>
          <div class="flex items-end justify-between mt-1">
            <div class="text-2xl font-semibold text-slate-800">{{ (int) ($site->jobs_count ?? 0) }}</div>
            <div class="text-xs text-slate-400">relasi: jobs</div>
          </div>
        </div>
        <div class="p-5 bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
          <div class="text-sm text-slate-500">User Terkait</div>
          <div class="flex items-end justify-between mt-1">
            <div class="text-2xl font-semibold text-slate-800">{{ (int) ($site->users_count ?? 0) }}</div>
            <div class="text-xs text-slate-400">relasi: users</div>
          </div>
        </div>
        <div class="p-5 bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
          <div class="text-sm text-slate-500">Config Items</div>
          <div class="flex items-end justify-between mt-1">
            <div class="text-2xl font-semibold text-slate-800">{{ (int) ($site->configs_count ?? 0) }}</div>
            <div class="text-xs text-slate-400">relasi: configs</div>
          </div>
        </div>
      </section>

      {{-- Detail Utama --}}
      <section class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
          <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-slate-800">Informasi Site</h2>
            <span class="text-xs text-slate-500">Terakhir diperbarui: {{ optional($site->updated_at)->format('d M Y H:i') ?? '-' }}</span>
          </div>
        </div>
        <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <div class="text-sm text-slate-500">Kode</div>
            <div class="mt-1 text-lg font-medium text-slate-800">{{ e($site->code) }}</div>
          </div>
          <div>
            <div class="text-sm text-slate-500">Nama</div>
            <div class="mt-1 text-lg font-medium text-slate-800">{{ e($site->name) }}</div>
          </div>
          <div>
            <div class="text-sm text-slate-500">Status</div>
            <div class="mt-1">
              @if(($site->is_active ?? false))
                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">Aktif</span>
              @else
                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">Nonaktif</span>
              @endif
            </div>
          </div>
          <div>
            <div class="text-sm text-slate-500">Region</div>
            <div class="mt-1 text-lg font-medium text-slate-800">{{ $site->region ?: '—' }}</div>
          </div>
          <div>
            <div class="text-sm text-slate-500">Timezone</div>
            <div class="mt-1 text-lg font-medium text-slate-800">{{ $site->timezone ?: '—' }}</div>
          </div>

          <div>
  <div class="text-sm text-slate-500">Latitude</div>
  <div class="mt-1 text-lg font-medium text-slate-800">
    {{ $site->latitude ?? '—' }}
  </div>
</div>

<div>
  <div class="text-sm text-slate-500">Longitude</div>
  <div class="mt-1 text-lg font-medium text-slate-800">
    {{ $site->longitude ?? '—' }}
  </div>
</div>
          @if(!empty($site->address))
            <div class="sm:col-span-2 lg:col-span-3">
              <div class="text-sm text-slate-500">Alamat</div>
              <div class="mt-1 leading-relaxed text-slate-800">{{ $site->address }}</div>
            </div>
          @endif
        </div>
      </section>

      {{-- Notes & Meta --}}
      @if(!empty($site->notes) || !empty($site->meta))
        <section class="grid gap-4 lg:grid-cols-2">
          @if(!empty($site->notes))
            <div class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
              <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
                <h2 class="text-base font-semibold text-slate-800">Catatan</h2>
              </div>
              <div class="p-5">
                <div class="text-sm whitespace-pre-line text-slate-800">{{ $site->notes }}</div>
              </div>
            </div>
          @endif

          @if(!empty($site->meta))
            <div class="bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
              <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
                <h2 class="text-base font-semibold text-slate-800">Meta</h2>
              </div>
              <div class="p-5">
                @php $metaArr = is_array($site->meta) ? $site->meta : (json_decode($site->meta ?? '[]', true) ?: []); @endphp
                <pre class="p-4 overflow-auto text-xs border bg-slate-50 rounded-xl" style="border-color: {{ $BORD }}">{{ json_encode($metaArr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
              </div>
            </div>
          @endif
        </section>
      @endif

      {{-- Timestamps --}}
      <div class="text-xs text-slate-500">
        Dibuat: {{ optional($site->created_at)->format('d M Y H:i') ?? '-' }} ·
        Diperbarui: {{ optional($site->updated_at)->format('d M Y H:i') ?? '-' }}
      </div>
    </div>
@endsection
