{{-- resources/views/admin/offers/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Offers' ])

@php
  $BLUE = '#a77d52'; // 🔥 ganti dari biru ke coklat
  $RED  = '#a77d52'; // 🔥 samakan biar konsisten
  $BORD = '#e5e7eb';
  $DARK = '#a77d52'; // 🔥 tombol ikut tema
@endphp


@section('content')
@once
  {{-- Sprite ikon kecil untuk pagination --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
    <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
  </svg>
@endonce

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER dua-tone + FILTER --}}
  <section class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative">
      <div class="h-20 sm:h-24 w-full" style="background: linear-gradient(90deg, {{ $BLUE }}, {{ $RED }});"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $RED }}, {{ $BLUE }});"></div>

      <div class="absolute inset-0 flex flex-col gap-3 px-5 md:px-6 py-4 sm:flex-row sm:items-center sm:justify-between text-white">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white">Offers</h1>
          <p class="text-xs sm:text-sm text-white/90">Daftar draft/final offer untuk kandidat.</p>
        </div>
      </div>
    </div>

    @php
      $q = $q ?? request('q');
      $selStatus = $status ?? request('status');
      $opts = [
        ''          => 'Semua Status',
        'draft'     => 'Draft',
        'sent'      => 'Sent',
        'accepted'  => 'Accepted',
        'rejected'  => 'Rejected',
      ];
    @endphp

{{-- FILTER FORM --}}
<form method="GET"
  class="mt-3 md:mt-4 grid grid-cols-1 gap-2 md:grid-cols-[1fr_auto_auto] px-3 py-3 md:px-4 md:py-4 shadow-sm"
      role="search" aria-label="Filter Offers"
      style="border-color: {{ $BORD }}">

  {{-- SEARCH --}}
  <div class="col-span-2 md:col-span-1">
    <label class="sr-only" for="q">Cari</label>
    <div class="relative">
      <input id="q" name="q" value="{{ e($q) }}"
             class="w-full md:max-w-md rounded-lg border border-slate-200 px-3 py-2 pl-9 text-sm focus:outline-none focus:ring-2"
             style="--tw-ring-color: {{ $BLUE }}"
             placeholder="Cari kandidat..."
             autocomplete="off">

      {{-- ICON --}}
      <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
          <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
          <path d="M21 21l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </span>
    </div>
  </div>

  {{-- STATUS --}}
 <select name="status"
        class="w-full md:w-[180px] rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $BLUE }}">
    @foreach($opts as $k => $v)
      <option value="{{ $k }}" @selected($selStatus===$k)>{{ $v }}</option>
    @endforeach
  </select>

  {{-- BUTTON FILTER --}}
  <button type="submit"
          class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white
                 hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 md:shrink-0"
          style="background-color: {{ $DARK }}; border:1px solid {{ $DARK }}; --tw-ring-color: {{ $BLUE }};"
          aria-label="Filter">

    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
      <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
      <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
    </svg>

    <span>Filter</span>
  </button>

  {{-- RESET --}}
  @if(request()->filled('q') || request()->filled('status'))
    <a href="{{ route('admin.offers.index') }}"
       class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50 text-slate-900">
      Reset
    </a>
  @endif

  </section>
  </section>

  {{-- FLASH --}}
  @if(session('success'))
    <div class="rounded-xl bg-green-50 text-green-700 px-4 py-3 border border-green-200">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="rounded-xl bg-red-50 text-red-700 px-4 py-3 border border-red-200">{{ session('error') }}</div>
  @endif

{{-- TABEL --}}
@php
  $PRIMARY = '#a77d52';
@endphp

<section class="rounded-2xl border bg-white shadow-md" style="border-color: {{ $BORD }}">
  <div class="overflow-x-auto">
    @if(($offers->count() ?? 0) > 0)
      <table class="min-w-[980px] w-full text-sm">
        
        {{-- HEADER --}}
        <thead class="bg-gradient-to-r from-[#f8f5f2] to-white text-[#6b4f3a]">
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

        {{-- BODY --}}
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
                'accepted' => 'bg-green-50 text-green-700',
                'rejected' => 'bg-rose-50 text-rose-700',
                'sent'     => 'bg-blue-50 text-blue-700',
                default    => 'bg-amber-50 text-amber-700',
              };
            @endphp

            <tr class="align-top hover:bg-[#f8f5f2] transition group">
              
              {{-- KANDIDAT --}}
              <td class="px-4 py-3">
                <div class="font-semibold text-slate-900">{{ e($user) }}</div>
                @if($email)
                  <div class="text-xs text-slate-500">{{ e($email) }}</div>
                @endif
              </td>

              {{-- POSISI --}}
              <td class="px-4 py-3">
                <div class="text-slate-800 font-medium">{{ e($title) }}</div>
                @if(!empty($app?->job?->code))
                  <div class="mt-0.5 text-xs text-slate-500">#{{ e($app->job->code) }}</div>
                @endif
              </td>

              {{-- SITE --}}
              <td class="px-4 py-3">
                <span class="font-mono text-slate-700 bg-slate-100 px-2 py-1 rounded-md">
                  {{ e($site) }}
                </span>
              </td>

              {{-- GROSS --}}
              <td class="px-4 py-3 text-center font-semibold text-[#a77d52]">
                Rp {{ $gross }}
              </td>

              {{-- ALLOWANCE --}}
              <td class="px-4 py-3 text-center font-semibold text-slate-700">
                Rp {{ $allow }}
              </td>

              {{-- STATUS --}}
              <td class="px-4 py-3 text-center">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                  {{ strtoupper($offer->status ?? 'draft') }}
                </span>
              </td>

              {{-- TANGGAL --}}
              <td class="px-4 py-3 text-slate-600">
                {{ optional($offer->created_at)->format('d M Y') ?? '—' }}
              </td>

              {{-- AKSI --}}
              <td class="px-4 py-3">
                <div class="flex justify-end gap-2 opacity-80 group-hover:opacity-100 transition">
                  
                  @if(Route::has('admin.offers.pdf'))
                    <a 
                      href="{{ route('admin.offers.pdf', $offer) }}"
                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 
                      hover:border-[{{ $PRIMARY }}] hover:text-[{{ $PRIMARY }}] 
                      transition text-sm"
                    >
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"
                          stroke="currentColor" stroke-width="2"/>
                        <path d="M14 2v6h6" stroke="currentColor" stroke-width="2"/>
                      </svg>
                      PDF
                    </a>
                  @endif

                </div>
              </td>

            </tr>
          @endforeach
        </tbody>
      </table>

    @else

      {{-- EMPTY STATE --}}
      <div class="py-16 grid place-content-center text-center">
        <div class="mx-auto w-12 h-12 rounded-2xl bg-[#f8f5f2] grid place-content-center text-[#a77d52] mb-3">
          <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M19.5 7.5 13.5 1.5H7A2.5 2.5 0 0 0 4.5 4v16A2.5 2.5 0 0 0 7 22.5h10A2.5 2.5 0 0 0 19.5 20V7.5Z"/>
          </svg>
        </div>
        <div class="text-slate-700 font-medium">Belum ada offer.</div>
        <div class="text-slate-500 text-sm mt-1">Coba ubah filter atau buat offer baru.</div>
      </div>

    @endif
  </div>
</section>

  {{-- PAGINATION (kapsul custom) --}}
  @php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $offers */
    $hasData = (int) $offers->total() > 0;
  @endphp

  @if($hasData)
    @php
      $perPage = max(1, (int) $offers->perPage());
      $current = (int) $offers->currentPage();
      $last    = (int) $offers->lastPage();
      $total   = (int) $offers->total();
      $from    = ($current - 1) * $perPage + 1;
      $to      = min($current * $perPage, $total);

      $pages = [];
      if ($last <= 7) {
        $pages = range(1, $last);
      } else {
        $pages = [1];
        $left  = max(2, $current - 1);
        $right = min($last - 1, $current + 1);
        if ($left > 2) $pages[] = '...';
        for ($i = $left; $i <= $right; $i++) $pages[] = $i;
        if ($right < $last - 1) $pages[] = '...';
        $pages[] = $last;
      }

      $pageUrl = function (int $p) use ($offers) {
        return $offers->appends(request()->except('page'))->url($p);
      };
    @endphp

    <section class="rounded-2xl border border-slate-200 bg-white p-3 md:p-4 shadow-sm">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-sm">
        <div class="text-slate-700">
          Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
          dari <span class="font-semibold text-slate-900">{{ $total }}</span>
        </div>
        <div class="hidden md:block text-slate-700">
          Showing <span class="font-semibold text-slate-900">{{ $from }}</span>
          to <span class="font-semibold text-slate-900">{{ $to }}</span>
          of <span class="font-semibold text-slate-900">{{ $total }}</span> results
        </div>

        <nav class="ml-auto" aria-label="Pagination">
          <ul class="inline-flex items-stretch overflow-hidden rounded-xl border border-slate-200 bg-white">
            {{-- Prev --}}
            <li>
              @if($current > 1)
                <a href="{{ $pageUrl($current - 1) }}"
                   class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $BLUE }}" aria-label="Previous">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </a>
              @else
                <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </span>
              @endif
            </li>

            {{-- Pages --}}
            @foreach($pages as $p)
              @if($p === '...')
                <li class="grid place-items-center px-3 h-9 text-slate-500 select-none">…</li>
              @else
                @php $isCur = ((int)$p === $current); @endphp
                <li class="grid place-items-center h-9">
                  @if($isCur)
                    <span class="px-3 h-full inline-flex items-center font-semibold text-slate-900 bg-slate-100 border-l border-slate-200 select-none">{{ $p }}</span>
                  @else
                    <a href="{{ $pageUrl((int)$p) }}"
                       class="px-3 h-full inline-flex items-center text-slate-700 hover:bg-slate-50 border-l border-slate-200 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $BLUE }}" aria-label="Page {{ $p }}">{{ $p }}</a>
                  @endif
                </li>
              @endif
            @endforeach

            {{-- Next --}}
            <li class="border-l border-slate-200">
              @if($current < $last)
                <a href="{{ $pageUrl($current + 1) }}"
                   class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $BLUE }}" aria-label="Next">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                </a>
              @else
                <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                </span>
              @endif
            </li>
          </ul>
        </nav>
      </div>
    </section>
  @endif
</div>
@endsection
