@extends('layouts.app', [ 'title' => 'Admin · Offers' ])

@section('content')
  {{-- ===== Header ala bar biru–merah ===== --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm mb-5 overflow-hidden">
    <div class="h-2">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width:90%"></div>
        <div class="h-full bg-red-500"  style="width:10%"></div>
      </div>
    </div>
    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Offers</h1>
          <p class="text-sm text-slate-600">Daftar draft/final offer untuk kandidat.</p>
        </div>

        {{-- Filter Bar (q + status) --}}
        <form method="GET" class="grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3">
          <input
            name="q"
            value="{{ $q ?? request('q') }}"
            class="input col-span-2 md:col-span-2"
            placeholder="Cari kandidat / job…"
            autocomplete="off"
          >
          @php
            $opts = [
              ''          => 'Semua Status',
              'draft'     => 'Draft',
              'sent'      => 'Sent',
              'accepted'  => 'Accepted',
              'rejected'  => 'Rejected',
            ];
            $selStatus = $status ?? request('status');
          @endphp
          <select name="status" class="input">
            @foreach($opts as $k => $v)
              <option value="{{ $k }}" @selected($selStatus===$k)>{{ $v }}</option>
            @endforeach
          </select>

          <div class="md:col-span-3 flex items-center gap-2">
            <button class="btn btn-primary">Filter</button>
            @if(request()->filled('q') || request()->filled('status'))
              <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost">Reset</a>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ===== Tabel ===== --}}
  <div class="card">
    <div class="card-body overflow-x-auto">
      @if(($offers->count() ?? 0) > 0)
        <table class="table min-w-[960px]">
          <thead>
            <tr>
              <th class="th">Kandidat</th>
              <th class="th">Posisi</th>
              <th class="th">Site</th>
              <th class="th text-center">Gross</th>
              <th class="th text-center">Allowance</th>
              <th class="th text-center">Status</th>
              <th class="th text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($offers as $offer)
              @php
                $app   = $offer->application;
                $user  = $app?->user?->name ?? '—';
                $title = $app?->job?->title ?? '—';
                $site  = $app?->job?->site?->code ?? '—';
                $gross = number_format((float)($offer->salary['gross'] ?? 0), 0, ',', '.');
                $allow = number_format((float)($offer->salary['allowance'] ?? 0), 0, ',', '.');

                $badge = match($offer->status){
                  'accepted' => 'badge-green',
                  'rejected' => 'badge-rose',
                  'sent'     => 'badge-blue',
                  default    => 'badge-amber',
                };
              @endphp
              <tr class="align-top">
                <td class="td font-medium text-slate-900">{{ $user }}</td>
                <td class="td">{{ $title }}</td>
                <td class="td">{{ $site }}</td>
                <td class="td text-center">Rp {{ $gross }}</td>
                <td class="td text-center">Rp {{ $allow }}</td>
                <td class="td text-center">
                  <span class="badge {{ $badge }}">{{ strtoupper($offer->status ?? 'draft') }}</span>
                </td>
                <td class="td">
                  <div class="flex justify-end gap-2">
                    @if(Route::has('admin.offers.pdf'))
                      <a class="btn btn-outline btn-sm" href="{{ route('admin.offers.pdf', $offer) }}">PDF</a>
                    @endif
                    {{-- Tambahkan aksi lain di sini bila perlu (Edit/Detail) --}}
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        {{-- Empty State --}}
        <div class="py-16 grid place-content-center text-center">
          <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 0 0 4.5 4v16A2.5 2.5 0 0 0 7 22.5h10A2.5 2.5 0 0 0 19.5 20V7.5Z"/>
            </svg>
          </div>
          <div class="text-slate-600">Belum ada offer.</div>
        </div>
      @endif
    </div>
  </div>

  {{-- Pagination --}}
  <div class="mt-6">
    {{ method_exists($offers, 'withQueryString') ? $offers->withQueryString()->links() : (method_exists($offers, 'links') ? $offers->links() : '') }}
  </div>
@endsection
