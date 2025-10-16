@extends('layouts.app')

@section('title', 'Sites')

@section('content')
<div class="space-y-6">
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
          <p class="text-slate-600 text-sm">Daftar lokasi operasional (hanya yang <span class="font-medium">aktif</span>).</p>
        </div>
        <form method="GET" class="flex gap-2">
          <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / kode / region…" class="input" autocomplete="off">
          <button class="btn btn-primary">Cari</button>
          @if($q !== '') <a href="{{ route('sites.index') }}" class="btn btn-ghost">Reset</a> @endif
        </form>
      </div>
    </div>
  </div>

  @if($sites->count())
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Kode</th>
            <th class="px-4 py-3 text-left">Region</th>
            <th class="px-4 py-3 text-left">TZ</th>
            <th class="px-4 py-3 text-left hidden md:table-cell">Alamat</th>
            <th class="px-4 py-3 text-left">Dibuat</th>
            <th class="px-4 py-3 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($sites as $site)
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-3">
                <a href="{{ route('sites.show', $site) }}" class="font-medium text-slate-800 hover:underline">
                  {{ $site->name ?? '—' }}
                </a>
              </td>
              <td class="px-4 py-3"><span class="font-mono text-slate-700">{{ $site->code }}</span></td>
              <td class="px-4 py-3">{{ $site->region ?: '—' }}</td>
              <td class="px-4 py-3">{{ $site->timezone ?: '—' }}</td>
              <td class="px-4 py-3 hidden md:table-cell" title="{{ $site->address }}">
                {{ \Illuminate\Support\Str::limit($site->address ?? '—', 40) }}
              </td>
              <td class="px-4 py-3">{{ optional($site->created_at)->format('d M Y') ?? '—' }}</td>
              <td class="px-4 py-3 text-right">
                <a href="{{ route('sites.show', $site) }}" class="btn btn-ghost btn-sm">Lihat</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $sites->links() }}</div>
  @else
    <div class="rounded-2xl border border-dashed border-slate-300 p-10 text-center bg-white">
      <div class="text-slate-700 font-medium">Belum ada site aktif.</div>
    </div>
  @endif
</div>
@endsection
