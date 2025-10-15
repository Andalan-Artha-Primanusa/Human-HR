@extends('layouts.app')

@section('title', 'Admin · Sites')

@section('content')
<div class="p-6 space-y-6">
  {{-- Header --}}
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Sites</h1>
      <p class="text-slate-500 text-sm">Kelola daftar site/ lokasi operasional.</p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.sites.create') }}" class="btn btn-primary inline-flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
        </svg>
        Tambah Site
      </a>
    </div>
  </div>

  {{-- Flash message --}}
  @if(session('success'))
    <div class="rounded-xl bg-green-50 text-green-700 px-4 py-3 border border-green-200">
      {{ session('success') }}
    </div>
  @endif

  {{-- Search (opsional) --}}
  <form method="GET" class="flex items-center gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode…"
           class="w-full sm:w-64 rounded-lg border-slate-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
    <button class="btn btn-secondary">Cari</button>
    @if(request()->filled('q'))
      <a href="{{ route('admin.sites.index') }}" class="btn">Reset</a>
    @endif
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
                <div class="font-medium text-slate-800">{{ $site->name ?? '—' }}</div>
                @if(!empty($site->description))
                  <div class="text-xs text-slate-500 line-clamp-1">{{ $site->description }}</div>
                @endif
              </td>
              <td class="px-4 py-3">
                <span class="font-mono text-slate-700">{{ $site->code ?? '—' }}</span>
              </td>
              <td class="px-4 py-3">
                @php $active = (string)($site->status ?? 'active') === 'active'; @endphp
                <span class="badge {{ $active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                  {{ $active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-4 py-3">
                {{ optional($site->created_at)->format('d M Y') ?? '—' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-end gap-2">
                  <a href="{{ route('admin.sites.edit', $site->id) }}" class="btn btn-sm btn-primary">Edit</a>
                  <form action="{{ route('admin.sites.destroy', $site->id) }}" method="POST" onsubmit="return confirm('Hapus site ini?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    @if(method_exists($sites, 'links'))
      <div class="mt-4">{{ $sites->links() }}</div>
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
