{{-- resources/views/admin/candidates/index.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Candidates'])

@php
// THEME (solid)
$BLUE = '#1d4ed8'; // blue-700
$RED = '#dc2626'; // red-600
$BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
@once
{{-- Sprite ikon --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <circle cx="11" cy="11" r="7" stroke-width="2" />
    <path d="M21 21l-3.5-3.5" stroke-width="2" stroke-linecap="round" />
  </symbol>
  <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </symbol>
  <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </symbol>
</svg>
@endonce

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER + SEARCH (dipisah, ada spasi; pola sama seperti Applications) --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    {{-- dua-tone bar --}}
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex items-center">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white">Candidate</h1>
          <p class="text-xs sm:text-sm text-white/90">Daftar kandidat yang telah mengisi profil.</p>
        </div>
      </div>
    </div>

    {{-- SEARCH FORM: pakai mt-3/md:mt-4 (bukan -mt) agar tidak nempel --}}
    <form method="GET"
      class="mt-3 md:mt-4 grid grid-cols-1 gap-2 md:grid-cols-[1fr_auto] px-3 py-3 md:px-4 md:py-4 shadow-sm"
      role="search" aria-label="Cari kandidat" style="border-color: {{ $BORD }}">
      <label class="sr-only" for="q">Cari</label>
      <input
        id="q"
        type="text"
        name="q"
        value="{{ e($q ?? '') }}"
        placeholder="Cari nama / email / HP / NIK"
        class="input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $BORD }}"
        autocomplete="off">
        
  <button type="submit"
  class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white
         hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 md:shrink-0"
  style="background-color:#0f172a; border:1px solid #0f172a; --tw-ring-color:#0f172a;"
  aria-label="Filter">
  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
    <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
  </svg>
  <span>Filter</span>
</button>


    </form>
  </section>

  {{-- TABEL (footer dipisah; tidak nempel) --}}
  <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    @if($profiles->count())
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 text-left">Nama</th>
            <th class="px-4 py-3 text-left">Email</th>
            <th class="px-4 py-3 text-left">HP</th>
            <th class="px-4 py-3 text-left">NIK</th>
            <th class="px-4 py-3 text-center">Tr / Emp / Ref</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach($profiles as $p)
          <tr class="hover:bg-slate-50/60">
            <td class="px-4 py-3">
              <div class="font-medium text-slate-900">{{ e($p->full_name) }}</div>
              <div class="text-xs text-slate-500">Updated: {{ e(optional($p->updated_at)->format('d M Y H:i')) }}</div>
            </td>
            <td class="px-4 py-3">{{ e($p->email) }}</td>
            <td class="px-4 py-3">{{ e($p->phone) }}</td>
            <td class="px-4 py-3">{{ e($p->nik) }}</td>
            <td class="px-4 py-3 text-center">
              {{ (int)$p->trainings_count }} / {{ (int)$p->employments_count }} / {{ (int)$p->references_count }}
            </td>
            <td class="px-4 py-3 text-right">
              <a href="{{ route('admin.candidates.show', $p) }}"
                class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-900 hover:bg-slate-50">
                Lihat
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    {{-- EMPTY STATE --}}
    <div class="p-10 text-center">
      <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z" />
        </svg>
      </div>
      <div class="text-slate-700 font-medium">Belum ada data.</div>
      <div class="text-slate-500 text-sm mt-1">Coba ubah kata kunci pencarian.</div>
    </div>
    @endif
  </section>

  {{-- PAGINATION CARD TERPISAH --}}
  @if($profiles->count())
  @php
  $perPage = max(1, (int) $profiles->perPage());
  $current = (int) $profiles->currentPage();
  $last = (int) $profiles->lastPage();
  $total = (int) $profiles->total();
  $from = ($current - 1) * $perPage + 1;
  $to = min($current * $perPage, $total);

  $pages = [];
  if ($last <= 7) {
    $pages=range(1, $last);
    } else {
    $pages=[1];
    $left=max(2, $current - 1);
    $right=min($last - 1, $current + 1);
    if ($left> 2) $pages[] = '...';
    for ($i = $left; $i <= $right; $i++) $pages[]=$i;
      if ($right < $last - 1) $pages[]='...' ;
      $pages[]=$last;
      }

      $pageUrl=function (int $p) use ($profiles) {
      return $profiles->appends(request()->except('page'))->url($p);
      };
      @endphp

      <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between text-sm">
          <div class="text-slate-700 flex items-center gap-4">
            <span>Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span> dari <span class="font-semibold text-slate-900">{{ $total }}</span></span>
            <span class="hidden sm:inline">Showing <span class="font-semibold text-slate-900">{{ $from }}</span> to <span class="font-semibold text-slate-900">{{ $to }}</span> of <span class="font-semibold text-slate-900">{{ $total }}</span> results</span>
          </div>

          <nav aria-label="Pagination">
            <ul class="inline-flex items-stretch overflow-hidden rounded-full ring-1 ring-slate-200 bg-white">
              {{-- Prev --}}
              <li>
                @if($current > 1)
                <a href="{{ $pageUrl($current - 1) }}"
                  class="grid place-items-center h-9 w-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                  style="--tw-ring-color:#1d4ed8" aria-label="Previous">
                  <svg class="h-4 w-4 text-slate-700">
                    <use href="#i-chevron-left" />
                  </svg>
                </a>
                @else
                <span class="grid place-items-center h-9 w-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                  <svg class="h-4 w-4 text-slate-700">
                    <use href="#i-chevron-left" />
                  </svg>
                </span>
                @endif
              </li>

              {{-- Pages --}}
              @foreach($pages as $p)
              @if($p === '...')
              <li class="grid place-items-center h-9 px-3 text-slate-500 select-none border-l border-slate-200">…</li>
              @else
              @php $isCur = ((int)$p === $current); @endphp
              <li class="grid place-items-center h-9 border-l border-slate-200">
                @if($isCur)
                <span class="px-3 h-full inline-flex items-center font-semibold text-slate-900 bg-blue-50">{{ $p }}</span>
                @else
                <a href="{{ $pageUrl((int)$p) }}"
                  class="px-3 h-full inline-flex items-center text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2"
                  style="--tw-ring-color:#1d4ed8" aria-label="Page {{ $p }}">{{ $p }}</a>
                @endif
              </li>
              @endif
              @endforeach

              {{-- Next --}}
              <li class="border-l border-slate-200">
                @if($current < $last)
                  <a href="{{ $pageUrl($current + 1) }}"
                  class="grid place-items-center h-9 w-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                  style="--tw-ring-color:#1d4ed8" aria-label="Next">
                  <svg class="h-4 w-4 text-slate-700">
                    <use href="#i-chevron-right" />
                  </svg>
                  </a>
                  @else
                  <span class="grid place-items-center h-9 w-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                    <svg class="h-4 w-4 text-slate-700">
                      <use href="#i-chevron-right" />
                    </svg>
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