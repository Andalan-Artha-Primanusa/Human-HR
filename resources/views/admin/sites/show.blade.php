{{-- resources/views/admin/sites/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Site · ' . ($site->code ?? 'Site'))

@php
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1120px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER: bar dua-tone biru/merah --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-white">
            {{ e($site->name ?? '—') }}
            <span class="text-white/70 font-normal">({{ e($site->code) }})</span>
          </h1>
          <div class="mt-1 flex flex-wrap items-center gap-2 text-xs sm:text-sm">
            <span class="text-white/90">Detail Site /</span>
            <a href="{{ route('dashboard') }}" class="text-white/80 hover:text-white">Dashboard</a>
            <span class="text-white/70">/</span>
            <a href="{{ route('admin.sites.index') }}" class="text-white/80 hover:text-white">Sites</a>
            <span class="text-white/70">/</span>
            <span class="text-white font-medium">{{ e($site->code ?? 'Detail') }}</span>

            {{-- Chip status --}}
            @if($site->is_active)
              <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white/90 text-emerald-700 ring-1 ring-emerald-200">
                Aktif
              </span>
            @else
              <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white/90 text-slate-700 ring-1 ring-slate-200">
                Nonaktif
              </span>
            @endif
          </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('admin.sites.index') }}"
             class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
             style="--tw-ring-color: {{ $BLUE }}">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M15 19l-7-7 7-7" stroke="#334155" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Kembali
          </a>

          @if(Route::has('admin.sites.toggle'))
            <form method="POST" action="{{ route('admin.sites.toggle', $site) }}"
                  onsubmit="return confirm('Ubah status site?');" class="inline-flex">
              @csrf @method('PATCH')
              <button type="submit"
                      class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
                      style="--tw-ring-color: {{ $BLUE }}">
                {{ $site->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </form>
          @endif

          <a href="{{ route('admin.sites.edit', $site) }}"
             class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2"
             style="--tw-ring-color: {{ $BLUE }}">
            {{-- Icon pensil: outline putih --}}
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M12 20h9" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
              <path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
            </svg>
            Edit
          </a>

          <form method="POST" action="{{ route('admin.sites.destroy', $site) }}"
                onsubmit="return confirm('Hapus site ini? Aksi tidak dapat dibatalkan.');" class="inline-flex">
            @csrf @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-offset-2"
                    style="--tw-ring-color: {{ $BLUE }}">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 002 2h8a2 2 0 002-2l1-14" stroke="#be123c" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  {{-- Ringkasan angka --}}
  <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <div class="p-5 rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
      <div class="text-slate-500 text-sm">Jumlah Jobs</div>
      <div class="mt-1 flex items-end justify-between">
        <div class="text-2xl font-semibold text-slate-800">{{ (int)($site->jobs_count ?? 0) }}</div>
        <div class="text-xs text-slate-400">relasi: jobs</div>
      </div>
    </div>
    <div class="p-5 rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
      <div class="text-slate-500 text-sm">User Terkait</div>
      <div class="mt-1 flex items-end justify-between">
        <div class="text-2xl font-semibold text-slate-800">{{ (int)($site->users_count ?? 0) }}</div>
        <div class="text-xs text-slate-400">relasi: users</div>
      </div>
    </div>
    <div class="p-5 rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
      <div class="text-slate-500 text-sm">Config Items</div>
      <div class="mt-1 flex items-end justify-between">
        <div class="text-2xl font-semibold text-slate-800">{{ (int)($site->configs_count ?? 0) }}</div>
        <div class="text-xs text-slate-400">relasi: configs</div>
      </div>
    </div>
  </section>

  {{-- Detail Utama --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-800">Informasi Site</h2>
        <span class="text-xs text-slate-500">Terakhir diperbarui: {{ optional($site->updated_at)->format('d M Y H:i') ?? '-' }}</span>
      </div>
    </div>
    <div class="p-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div>
        <div class="text-slate-500 text-sm">Kode</div>
        <div class="mt-1 text-lg font-medium text-slate-800">{{ e($site->code) }}</div>
      </div>
      <div>
        <div class="text-slate-500 text-sm">Nama</div>
        <div class="mt-1 text-lg font-medium text-slate-800">{{ e($site->name) }}</div>
      </div>
      <div>
        <div class="text-slate-500 text-sm">Status</div>
        <div class="mt-1">
          @if(($site->is_active ?? false))
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">Aktif</span>
          @else
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Nonaktif</span>
          @endif
        </div>
      </div>
      <div>
        <div class="text-slate-500 text-sm">Region</div>
        <div class="mt-1 text-lg font-medium text-slate-800">{{ $site->region ?: '—' }}</div>
      </div>
      <div>
        <div class="text-slate-500 text-sm">Timezone</div>
        <div class="mt-1 text-lg font-medium text-slate-800">{{ $site->timezone ?: '—' }}</div>
      </div>
      @if(!empty($site->address))
        <div class="sm:col-span-2 lg:col-span-3">
          <div class="text-slate-500 text-sm">Alamat</div>
          <div class="mt-1 text-slate-800 leading-relaxed">{{ $site->address }}</div>
        </div>
      @endif
    </div>
  </section>

  {{-- Notes & Meta --}}
  @if(!empty($site->notes) || !empty($site->meta))
    <section class="grid gap-4 lg:grid-cols-2">
      @if(!empty($site->notes))
        <div class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
          <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
            <h2 class="text-base font-semibold text-slate-800">Catatan</h2>
          </div>
          <div class="p-5">
            <div class="text-sm text-slate-800 whitespace-pre-line">{{ $site->notes }}</div>
          </div>
        </div>
      @endif

      @if(!empty($site->meta))
        <div class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
          <div class="px-5 py-4 border-b" style="border-color: {{ $BORD }}">
            <h2 class="text-base font-semibold text-slate-800">Meta</h2>
          </div>
          <div class="p-5">
            @php $metaArr = is_array($site->meta) ? $site->meta : (json_decode($site->meta ?? '[]', true) ?: []); @endphp
            <pre class="text-xs bg-slate-50 rounded-xl p-4 border overflow-auto" style="border-color: {{ $BORD }}">{{ json_encode($metaArr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
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
