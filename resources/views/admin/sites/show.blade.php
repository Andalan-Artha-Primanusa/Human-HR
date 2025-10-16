{{-- resources/views/admin/sites/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Detail Site · ' . ($site->code ?? 'Site'))

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

  {{-- HEADER: bar biru–merah --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>

    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">
            {{ $site->name ?? '—' }} <span class="text-slate-400">({{ $site->code }})</span>
          </h1>
          <div class="mt-1 flex flex-wrap items-center gap-2 text-sm">
            <span class="text-slate-600">Detail Site /</span>
            <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-slate-700">Dashboard</a>
            <span class="text-slate-400">/</span>
            <a href="{{ route('admin.sites.index') }}" class="text-slate-500 hover:text-slate-700">Sites</a>
            <span class="text-slate-400">/</span>
            <span class="text-slate-700 font-medium">{{ $site->code ?? 'Detail' }}</span>

            {{-- Chip status --}}
            @if($site->is_active)
              <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">
                Aktif
              </span>
            @else
              <span class="ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                Nonaktif
              </span>
            @endif
          </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('admin.sites.index') }}"
             class="btn btn-ghost inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
          </a>

          @hasSection('can_toggle') @endif
          @if(Route::has('admin.sites.toggle'))
            <form method="POST" action="{{ route('admin.sites.toggle', $site) }}"
                  onsubmit="return confirm('Ubah status site?');">
              @csrf @method('PATCH')
              <button type="submit" class="btn btn-ghost inline-flex items-center gap-2">
                {{ $site->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
              </button>
            </form>
          @endif

          <a href="{{ route('admin.sites.edit', $site) }}"
             class="btn btn-primary inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-width="2" stroke-linecap="round" d="M12 20h9"/>
              <path stroke-width="2" stroke-linecap="round" d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4 12.5-12.5z"/>
            </svg>
            Edit
          </a>

          <form method="POST" action="{{ route('admin.sites.destroy', $site) }}"
                onsubmit="return confirm('Hapus site ini? Aksi tidak dapat dibatalkan.');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-ghost inline-flex items-center gap-2 text-red-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-width="2" stroke-linecap="round" d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 002 2h8a2 2 0 002-2l1-14"/>
              </svg>
              Hapus
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Ringkasan angka (withCount) --}}
  <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="text-slate-500 text-sm">Jumlah Jobs</div>
      <div class="mt-1 flex items-end justify-between">
        <div class="text-2xl font-semibold text-slate-800">
          {{ $site->jobs_count ?? 0 }}
        </div>
        <div class="text-xs text-slate-400">relasi: jobs</div>
      </div>
    </div>
    <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="text-slate-500 text-sm">User Terkait</div>
      <div class="mt-1 flex items-end justify-between">
        <div class="text-2xl font-semibold text-slate-800">
          {{ $site->users_count ?? 0 }}
        </div>
        <div class="text-xs text-slate-400">relasi: users</div>
      </div>
    </div>
    <div class="p-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="text-slate-500 text-sm">Config Items</div>
      <div class="mt-1 flex items-end justify-between">
        <div class="text-2xl font-semibold text-slate-800">
          {{ $site->configs_count ?? 0 }}
        </div>
        <div class="text-xs text-slate-400">relasi: configs</div>
      </div>
    </div>
  </section>

  {{-- Detail Utama --}}
  <section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
      <h2 class="text-base font-semibold text-slate-800">Informasi Site</h2>
      <span class="text-xs text-slate-400">Terakhir diperbarui: {{ optional($site->updated_at)->format('d M Y H:i') ?? '-' }}</span>
    </div>
    <div class="p-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div>
        <div class="text-slate-500 text-sm">Kode</div>
        <div class="mt-1 text-lg font-medium text-slate-800">{{ $site->code }}</div>
      </div>
      <div>
        <div class="text-slate-500 text-sm">Nama</div>
        <div class="mt-1 text-lg font-medium text-slate-800">{{ $site->name }}</div>
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
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="px-5 py-4 border-b border-slate-200">
        <h2 class="text-base font-semibold text-slate-800">Catatan</h2>
      </div>
      <div class="p-5">
        {{-- Jika notes mengandung line-break, biar tetap rapi --}}
        <div class="text-sm text-slate-800 whitespace-pre-line">{{ $site->notes }}</div>
      </div>
    </div>
    @endif

    @if(!empty($site->meta))
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
      <div class="px-5 py-4 border-b border-slate-200">
        <h2 class="text-base font-semibold text-slate-800">Meta</h2>
      </div>
      <div class="p-5">
        @php $metaArr = is_array($site->meta) ? $site->meta : (json_decode($site->meta ?? '[]', true) ?: []); @endphp
        <pre class="text-xs bg-slate-50 rounded-xl p-4 border border-slate-200 overflow-auto">{{ json_encode($metaArr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>
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
