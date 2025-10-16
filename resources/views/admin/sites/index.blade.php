{{-- resources/views/admin/sites/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites')

@section('content')
<div class="space-y-6">
  {{-- HEADER: panel biru–merah --}}
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
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Sites</h1>
          <p class="text-slate-600 text-sm">Kelola daftar site / lokasi operasional.</p>
        </div>
        <a href="{{ route('admin.sites.create') }}"
           class="btn btn-primary inline-flex items-center gap-2 self-start sm:self-auto">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
          </svg>
          Tambah Site
        </a>
      </div>
    </div>
  </div>

  {{-- FLASH --}}
  @if(session('success') || session('ok'))
    <div class="rounded-2xl border border-green-200 bg-green-50 text-green-700 px-4 py-3">
      {{ session('success') ?? session('ok') }}
    </div>
  @endif
  @if(session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 text-red-700 px-4 py-3">
      {{ session('error') }}
    </div>
  @endif

  {{-- FILTER --}}
  <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode…"
           class="input" autocomplete="off">
    <select name="status" class="input">
      <option value="">Semua Status</option>
      <option value="active"   @selected(request('status')==='active')>Active</option>
      <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
    </select>
    <div class="flex gap-2">
      <button class="btn btn-primary inline-flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <circle cx="11" cy="11" r="7" stroke-width="2"/>
          <path stroke-linecap="round" stroke-width="2" d="M21 21l-3.5-3.5"/>
        </svg>
        Cari
      </button>
      @if(request()->filled('q') || request()->filled('status'))
        <a href="{{ route('admin.sites.index') }}" class="btn btn-ghost">Reset</a>
      @endif
    </div>
  </form>

  {{-- TABEL --}}
  @php /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $sites */ @endphp
  @if(isset($sites) && $sites->count())
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Kode</th>
            <th class="px-4 py-3 text-left">Region</th>
            <th class="px-4 py-3 text-left">TZ</th>
            <th class="px-4 py-3 text-left hidden md:table-cell">Alamat</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Dibuat</th>
            <th class="px-4 py-3 text-right w-1">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($sites as $site)
            <tr class="hover:bg-slate-50/60">
              {{-- Nama --}}
              <td class="px-4 py-3">
                <div class="font-medium text-slate-800">
                  <a href="{{ route('admin.sites.show', $site) }}" class="hover:underline">
                    {{ $site->name ?? '—' }}
                  </a>
                </div>
              </td>

              {{-- Kode --}}
              <td class="px-4 py-3">
                <span class="font-mono text-slate-700">{{ $site->code ?? '—' }}</span>
              </td>

              {{-- Region --}}
              <td class="px-4 py-3">
                {{ $site->region ?: '—' }}
              </td>

              {{-- Timezone --}}
              <td class="px-4 py-3">
                {{ $site->timezone ?: '—' }}
              </td>

              {{-- Alamat (truncate + tooltip) --}}
              <td class="px-4 py-3 hidden md:table-cell" title="{{ $site->address }}">
                {{ \Illuminate\Support\Str::limit($site->address ?? '—', 40) }}
              </td>

              {{-- Status --}}
              <td class="px-4 py-3">
                @php
                  $active = isset($site->is_active)
                    ? (bool)$site->is_active
                    : ((string)($site->status ?? 'active') === 'active');
                @endphp
                <span class="badge {{ $active ? 'badge-green' : 'badge-amber' }}">
                  {{ $active ? 'ACTIVE' : 'INACTIVE' }}
                </span>
              </td>

              {{-- Dibuat --}}
              <td class="px-4 py-3">
                {{ optional($site->created_at)->format('d M Y') ?? '—' }}
              </td>

              {{-- Aksi --}}
              <td class="px-2 py-2">
                <div class="flex items-center justify-end gap-1.5">
                  {{-- Toggle aktif (jika route tersedia) --}}
                  @if(Route::has('admin.sites.toggle'))
                    <form action="{{ route('admin.sites.toggle', $site) }}" method="POST"
                          onsubmit="return confirm('Ubah status site?')">
                      @csrf @method('PATCH')
                      <button class="btn btn-ghost btn-xs">
                        {{ $active ? 'Nonaktifkan' : 'Aktifkan' }}
                      </button>
                    </form>
                  @endif

                  <a href="{{ route('admin.sites.show', $site) }}"
                     class="btn btn-ghost btn-xs inline-flex items-center gap-1.5" title="Lihat">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-linecap="round" stroke-width="2" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                      <circle cx="12" cy="12" r="3" stroke-width="2"/>
                    </svg>
                    View
                  </a>

                  <a href="{{ route('admin.sites.edit', $site) }}"
                     class="btn btn-outline btn-xs inline-flex items-center gap-1.5" title="Ubah">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path stroke-width="2" stroke-linecap="round" d="M12 20h9"/>
                      <path stroke-width="2" stroke-linecap="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
                    </svg>
                    Edit
                  </a>

                  <form action="{{ route('admin.sites.destroy', $site) }}" method="POST"
                        onsubmit="return confirm('Hapus site ini?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-ghost btn-xs inline-flex items-center gap-1.5" title="Hapus">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-width="2" stroke-linecap="round" d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
                      </svg>
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if(method_exists($sites, 'links'))
      <div class="mt-4">{{ $sites->withQueryString()->links() }}</div>
    @endif
  @else
    {{-- EMPTY STATE --}}
    <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center bg-white">
      <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z"/>
        </svg>
      </div>
      <div class="text-slate-700 font-medium">Belum ada data site.</div>
      <div class="text-slate-500 text-sm mt-1">Tambahkan site pertama kamu sekarang.</div>
      <a href="{{ route('admin.sites.create') }}" class="btn btn-primary mt-4 inline-flex items-center gap-2">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
        </svg>
        Tambah Site
      </a>
    </div>
  @endif
</div>
@endsection
