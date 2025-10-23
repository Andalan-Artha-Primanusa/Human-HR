{{-- resources/views/admin/audit_logs/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Audit Logs' ])

@php
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200
  $DARK = '#0f172a'; // gelap untuk tombol
@endphp

@section('content')
@once
  {{-- Sprite ikon kecil untuk pagination dan search --}}
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

  {{-- HEADER dua-tone + info migrasi --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0 rounded-t-2xl" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 rounded-tr-2xl w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex items-center">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-white">Audit Logs</h1>
          <p class="text-sm text-white/90">Jejak perubahan data & aktivitas pengguna.</p>
        </div>
      </div>
    </div>

    @if (!empty($tableMissing) && $tableMissing)
      <div class="p-4 md:p-5">
        <div class="rounded-lg border border-amber-200 bg-amber-50 text-amber-800 px-4 py-3">
          Tabel <code>audit_logs</code> belum ada. Jalankan <code>php artisan migrate</code>.
        </div>
      </div>
    @endif

    {{-- FILTERS (muncul hanya jika tabel ada) --}}
    @if (empty($tableMissing) || !$tableMissing)
      @php
        $q          = $filters['q'] ?? '';
        $event      = $filters['event'] ?? '';
        $userId     = $filters['userId'] ?? '';
        $targetType = $filters['targetType'] ?? '';
        $dateFrom   = $filters['dateFrom'] ?? '';
        $dateTo     = $filters['dateTo'] ?? '';
      @endphp

      <form method="GET"
            class="mt-3 md:mt-4 grid grid-cols-1 md:grid-cols-6 gap-2 md:gap-3 rounded-xl border bg-white px-3 py-3 md:px-4 md:py-4 shadow-sm"
            role="search" aria-label="Filter Audit Logs" style="border-color: {{ $BORD }}">
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 md:col-span-2"
               style="--tw-ring-color: {{ $BLUE }}"
               type="text" name="q" value="{{ e($q) }}" placeholder="Cari target_id / IP / User-Agent" autocomplete="off">
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}"
               type="text" name="event" value="{{ e($event) }}" placeholder="Event (created/updated/deleted)" autocomplete="off">
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}"
               type="text" name="user_id" value="{{ e($userId) }}" placeholder="User ID" autocomplete="off">
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}"
               type="text" name="target_type" value="{{ e($targetType) }}" placeholder="Target Type" autocomplete="off">
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}"
               type="date" name="from" value="{{ e($dateFrom) }}">
        <input class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}"
               type="date" name="to" value="{{ e($dateTo) }}">

        <div class="md:col-span-6 flex flex-wrap items-center gap-2 pt-1">
          {{-- Tombol Filter: gelap + ikon putih inline (tanpa currentColor) --}}
          <button type="submit"
                  class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white
                         hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2"
                  style="background-color: {{ $DARK }}; border:1px solid {{ $DARK }}; --tw-ring-color: {{ $BLUE }};">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
              <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>Filter</span>
          </button>

          @if(Route::has('admin.audit_logs.export'))
            <a href="{{ route('admin.audit_logs.export') }}"
               class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm text-slate-900 hover:bg-slate-50 focus:outline-none focus:ring-2"
               style="--tw-ring-color: {{ $BLUE }}">
              Export CSV
            </a>
          @endif
        </div>
      </form>
    @endif
  </section>

  {{-- LIST & TABLE (hanya jika tabel ada) --}}
  @if (empty($tableMissing) || !$tableMissing)
    <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 text-slate-700">
            <tr>
              <th class="px-4 py-3 text-left w-48">Time</th>
              <th class="px-4 py-3 text-left w-32">Event</th>
              <th class="px-4 py-3 text-left w-48">User</th>
              <th class="px-4 py-3 text-left">Target</th>
              <th class="px-4 py-3 text-left w-40">IP</th>
              <th class="px-4 py-3 text-right w-28"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @forelse ($items as $it)
              <tr class="hover:bg-slate-50/60">
                <td class="px-4 py-3 whitespace-nowrap text-slate-800">{{ e($it->created_at) }}</td>
                <td class="px-4 py-3 text-slate-800">{{ e($it->event) }}</td>
                <td class="px-4 py-3 text-slate-800">{{ e($it->user->name ?? '-') }}</td>
                <td class="px-4 py-3">
                  <div class="text-slate-800">{{ e($it->target_type ?? '-') }}</div>
                  <div class="text-slate-500 text-xs">{{ e($it->target_id ?? '-') }}</div>
                </td>
                <td class="px-4 py-3 text-slate-800">{{ e($it->ip ?? '-') }}</td>
                <td class="px-4 py-3 text-right">
                  <a class="text-blue-600 hover:underline" href="{{ route('admin.audit_logs.show', $it->id) }}">Detail</a>
                </td>
              </tr>
            @empty
              <tr>
                <td class="px-4 py-6 text-center text-slate-500" colspan="6">Belum ada data.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </section>

    {{-- PAGINATION kapsul custom --}}
    @php
      /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $items */
      $hasData = ($items->count() ?? 0) > 0;
    @endphp

    @if($hasData)
      @php
        $perPage = max(1, (int) $items->perPage());
        $current = (int) $items->currentPage();
        $last    = (int) $items->lastPage();
        $total   = (int) $items->total();
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

        $pageUrl = function (int $p) use ($items) {
          return $items->appends(request()->except('page'))->url($p);
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
  @endif
</div>
@endsection
