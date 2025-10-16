{{-- resources/views/admin/sites/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites')

@section('content')
<div class="space-y-6">

  {{-- Header panel ala bar biru–merah --}}
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
          <p class="text-slate-600 text-sm">Kelola daftar site/ lokasi operasional.</p>
        </div>
        <a href="{{ route('admin.sites.create') }}" class="btn btn-primary inline-flex items-center gap-2 self-start sm:self-auto">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
          </svg>
          Tambah Site
        </a>
      </div>
    </div>
  </div>

  {{-- Flash message --}}
  @if(session('success'))
    <div class="rounded-xl bg-green-50 text-green-700 px-4 py-3 border border-green-200">
      {{ session('success') }}
    </div>
  @endif

  {{-- Search / Filter --}}
  <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode…"
           class="input">
    <select name="status" class="input">
      <option value="">Semua Status</option>
      <option value="active"   @selected(request('status')==='active')>Active</option>
      <option value="inactive" @selected(request('status')==='inactive')>Inactive</option>
    </select>
    <div class="flex gap-2">
      <button class="btn btn-primary">Cari</button>
      @if(request()->filled('q') || request()->filled('status'))
        <a href="{{ route('admin.sites.index') }}" class="btn btn-ghost">Reset</a>
      @endif
    </div>
  </form>

  {{-- Tabel --}}
  @php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $sites */
  @endphp
  @if(isset($sites) && $sites->count())
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Kode</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Dibuat</th>
            <th class="px-4 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($sites as $site)
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-3">
                <div class="font-medium text-slate-800">
                  <a href="{{ route('admin.sites.show', $site) }}" class="hover:underline">{{ $site->name ?? '—' }}</a>
                </div>
                @if(!empty($site->description))
                  <div class="text-xs text-slate-500 line-clamp-1">{{ $site->description }}</div>
                @endif
              </td>
              <td class="px-4 py-3">
                <span class="font-mono text-slate-700">{{ $site->code ?? '—' }}</span>
              </td>
              <td class="px-4 py-3">
                @php $active = (string)($site->status ?? 'active') === 'active'; @endphp
                <span class="badge {{ $active ? 'badge-green' : 'badge-amber' }}">
                  {{ $active ? 'ACTIVE' : 'INACTIVE' }}
                </span>
              </td>
              <td class="px-4 py-3">
                {{ optional($site->created_at)->format('d M Y') ?? '—' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2">
                  {{-- VIEW / DETAIL --}}
                  <a href="{{ route('admin.sites.show', $site) }}" class="btn btn-ghost btn-sm" title="Lihat detail">
                    View
                  </a>
                  {{-- EDIT --}}
                  <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-outline btn-sm" title="Ubah">
                    Edit
                  </a>
                  {{-- DELETE --}}
                  <form action="{{ route('admin.sites.destroy', $site) }}" method="POST" onsubmit="return confirm('Hapus site ini?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-ghost btn-sm" title="Hapus">Delete</button>
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
    {{-- Empty state --}}
    <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center bg-white">
      <div class="text-slate-700 font-medium">Belum ada data site.</div>
      <div class="text-slate-500 text-sm mt-1">Tambahkan site pertama kamu sekarang.</div>
      <a href="{{ route('admin.sites.create') }}" class="btn btn-primary mt-4">Tambah Site</a>
    </div>
  @endif
</div>
@endsection
