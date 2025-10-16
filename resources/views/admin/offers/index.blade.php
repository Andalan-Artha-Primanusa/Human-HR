{{-- resources/views/admin/offers/index.blade.php --}}
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
          <div class="col-span-2 md:col-span-2">
            <label class="sr-only" for="q">Cari</label>
            <div class="relative">
              <input
                id="q"
                name="q"
                value="{{ $q ?? request('q') }}"
                class="input w-full pl-9"
                placeholder="Cari kandidat / job…"
                autocomplete="off"
              >
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M21 21l-3.5-3.5"/>
                </svg>
              </span>
            </div>
          </div>

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
            <button class="btn btn-primary inline-flex items-center gap-2">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M21 21l-3.5-3.5"/>
              </svg>
              Filter
            </button>
            @if(request()->filled('q') || request()->filled('status'))
              <a href="{{ route('admin.offers.index') }}" class="btn btn-ghost">Reset</a>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ===== Flash ===== --}}
  @if(session('success'))
    <div class="rounded-xl bg-green-50 text-green-700 px-4 py-3 border border-green-200 mb-4">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border border-red-200 mb-4">{{ session('error') }}</div>
  @endif

  {{-- ===== Tabel ===== --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="p-0 overflow-x-auto">
      @if(($offers->count() ?? 0) > 0)
        <table class="min-w-[980px] w-full text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 text-left">Kandidat</th>
              <th class="px-4 py-3 text-left">Posisi</th>
              <th class="px-4 py-3 text-left w-24">Site</th>
              <th class="px-4 py-3 text-center w-32">Gross</th>
              <th class="px-4 py-3 text-center w-36">Allowance</th>
              <th class="px-4 py-3 text-center w-28">Status</th>
              <th class="px-4 py-3 text-left w-32">Dibuat</th>
              <th class="px-4 py-3 text-right w-40">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($offers as $offer)
              @php
                $app    = $offer->application;
                $user   = $app?->user?->name ?? $app?->candidate?->name ?? ($offer->candidate_name ?? '—');
                $email  = $app?->candidate?->email ?? null;
                $title  = $app?->job?->title ?? '—';
                $site   = $app?->job?->site?->code ?? $app?->job?->site_code ?? '—';
                $grossV = (float) (\Illuminate\Support\Arr::get($offer->salary, 'gross', 0));
                $allowV = (float) (\Illuminate\Support\Arr::get($offer->salary, 'allowance', 0));
                $gross  = number_format($grossV, 0, ',', '.');
                $allow  = number_format($allowV, 0, ',', '.');

                $badge = match($offer->status){
                  'accepted' => 'badge-green',
                  'rejected' => 'badge-rose',
                  'sent'     => 'badge-blue',
                  default    => 'badge-amber',
                };
              @endphp
              <tr class="align-top hover:bg-slate-50/60">
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">{{ $user }}</div>
                  @if($email)
                    <div class="text-xs text-slate-500">{{ $email }}</div>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <div class="text-slate-800">{{ $title }}</div>
                  @if(!empty($app?->job?->code))
                    <div class="mt-0.5 text-xs text-slate-500">#{{ $app->job->code }}</div>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <span class="font-mono text-slate-700">{{ $site }}</span>
                </td>
                <td class="px-4 py-3 text-center">Rp {{ $gross }}</td>
                <td class="px-4 py-3 text-center">Rp {{ $allow }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="badge {{ $badge }}">{{ strtoupper($offer->status ?? 'draft') }}</span>
                </td>
                <td class="px-4 py-3">
                  {{ optional($offer->created_at)->format('d M Y') ?? '—' }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    @if(Route::has('admin.offers.pdf'))
                      <a class="btn btn-outline btn-sm inline-flex items-center gap-1.5" href="{{ route('admin.offers.pdf', $offer) }}">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                          <path stroke-width="2" stroke-linecap="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path stroke-width="2" d="M14 2v6h6"/>
                        </svg>
                        PDF
                      </a>
                    @endif
                    {{-- Tambah aksi lain (Detail/Edit) jika route tersedia --}}
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
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 0 0 4.5 4v16A2.5 2.5 0 0 0 7 22.5h10A2.5 2.5 0 0 0 19.5 20V7.5Z"/>
            </svg>
          </div>
          <div class="text-slate-700 font-medium">Belum ada offer.</div>
          <div class="text-slate-500 text-sm mt-1">Coba ubah filter atau buat offer baru.</div>
        </div>
      @endif
    </div>
  </div>

  {{-- Pagination --}}
  <div class="mt-6">
    {{ method_exists($offers, 'withQueryString') ? $offers->withQueryString()->links() : (method_exists($offers, 'links') ? $offers->links() : '') }}
  </div>
@endsection
