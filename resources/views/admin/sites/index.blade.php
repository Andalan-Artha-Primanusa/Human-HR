{{-- resources/views/admin/sites/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Admin · Sites • karir-andalan')

@php
    // THEME (solid)
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
    $BORD = '#e5e7eb'; // slate-200
@endphp

@section('content')
    @once
          {{-- Sprite ikon yang dipakai di halaman --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
            <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
            </symbol>
            <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="11" cy="11" r="7" stroke-width="2"/>
              <path d="M21 21l-3.5-3.5" stroke-width="2" stroke-linecap="round"/>
            </symbol>
            <symbol id="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
              <circle cx="12" cy="12" r="3" stroke-width="2"/>
            </symbol>
            <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="2" stroke-linecap="round" d="M12 20h9"/>
              <path stroke-width="2" stroke-linecap="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
            </symbol>
            <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="2" stroke-linecap="round" d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
            </symbol>
            <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
          </svg>
    @endonce

    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
      <div class="relative">
        <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});"></div>
        <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $ACCENT_DARK }}, {{ $ACCENT }});"></div>

        <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
          <div class="min-w-0">
            <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Sites</h1>
            <p class="text-xs sm:text-sm text-white/90">Kelola daftar site / lokasi operasional.</p>
          </div>
          <a href="{{ route('admin.sites.create') }}"
             class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
             style="--tw-ring-color: {{ $ACCENT }}">
            <svg class="w-4 h-4" style="color: {{ $ACCENT }}"><use href="#i-plus"/></svg>
            Tambah Site
          </a>
        </div>
      </div>

      {{-- FILTER menyatu di dalam kartu header --}}
      <div class="p-6 border-t md:p-7 bg-[linear-gradient(180deg,_#faf7f4,_#ffffff)]" style="border-color: {{ $BORD }}">
        <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_200px_auto] md:items-end" role="search" aria-label="Filter Sites">
          <label class="sr-only" for="q">Cari</label>
          <input id="q" type="text" name="q" value="{{ e(request('q', '')) }}" placeholder="Cari nama / kode…"
                 class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                 style="--tw-ring-color: {{ $ACCENT }}" autocomplete="off">

          <label class="sr-only" for="status">Status</label>
          <select id="status" name="status"
                  class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                  style="--tw-ring-color: {{ $ACCENT }}">
            <option value="">Semua Status</option>
            <option value="active"   @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
          </select>

          <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
            <button class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
              <svg class="w-4 h-4"><use href="#i-search"/></svg>
              Cari
            </button>
            @if(request()->filled('q') || request()->filled('status'))
                  <a href="{{ route('admin.sites.index') }}" class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50">
                    Reset
                  </a>
            @endif
          </div>
        </form>
      </div>
    </section>


      {{-- FLASH --}}
      @if(session('success') || session('ok'))
        <div class="px-4 py-3 border rounded-2xl border-emerald-200 bg-emerald-50 text-emerald-700">
          {{ e(session('success') ?? session('ok')) }}
        </div>
      @endif
      @if(session('error'))
        <div class="px-4 py-3 text-red-700 border border-red-200 rounded-2xl bg-red-50">
          {{ e(session('error')) }}
        </div>
      @endif
      {{-- TABEL --}}
      @php /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection $sites */ @endphp
      @if(isset($sites) && $sites->count())
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
          <table class="min-w-full text-sm">
            <thead class="text-white bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)]">
              <tr>
                <th class="px-4 py-3 text-left">Nama</th>
                <th class="px-4 py-3 text-left">Kode</th>
                <th class="px-4 py-3 text-left">Region</th>
                <th class="px-4 py-3 text-left">TZ</th>
                <th class="hidden px-4 py-3 text-left md:table-cell">Alamat</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Dibuat</th>
                <th class="w-1 px-4 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              @foreach($sites as $site)
                @php
                    $active = isset($site->is_active)
                        ? (bool) $site->is_active
                        : (strtolower((string) ($site->status ?? 'active')) === 'active');
                @endphp
                <tr class="transition hover:bg-[#f8f5f2]">
                  {{-- Nama --}}
                  <td class="px-4 py-3">
                    <div class="font-medium text-slate-800">
                      <a href="{{ route('admin.sites.show', $site) }}" class="hover:underline">
                        {{ e($site->name ?? '—') }}
                      </a>
                    </div>
                  </td>

                  {{-- Kode --}}
                  <td class="px-4 py-3">
                    <span class="font-mono text-slate-700">{{ e($site->code ?? '—') }}</span>
                  </td>

                  {{-- Region --}}
                  <td class="px-4 py-3">
                    {{ e($site->region ?: '—') }}
                  </td>

                  {{-- Timezone --}}
                  <td class="px-4 py-3">
                    {{ e($site->timezone ?: '—') }}
                  </td>

                  {{-- Alamat --}}
                  <td class="hidden px-4 py-3 md:table-cell" title="{{ e($site->address ?? '') }}">
                    {{ \Illuminate\Support\Str::limit((string) ($site->address ?? '—'), 40) }}
                  </td>

                  {{-- Status --}}
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset
                                 {{ $active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                      {{ $active ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                  </td>

                  {{-- Dibuat --}}
                  <td class="px-4 py-3">
                    {{ e(optional($site->created_at)->format('d M Y') ?? '—') }}
                  </td>

                  {{-- Aksi --}}
                  <td class="px-2 py-2">
                    <div class="flex items-center justify-end gap-1.5">
                      @if(Route::has('admin.sites.toggle'))
                        <form action="{{ route('admin.sites.toggle', $site) }}" method="POST"
                              onsubmit="return confirm('Ubah status site?')">
                          @csrf @method('PATCH')
                          <button class="rounded-lg px-3 py-1.5 text-xs hover:bg-slate-50 border border-slate-200">Toggle</button>
                        </form>
                      @endif

                      <a href="{{ route('admin.sites.show', $site) }}"
                         class="rounded-lg px-3 py-1.5 text-xs hover:bg-slate-50 border border-slate-200 inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-700"><use href="#i-eye"/></svg>
                        View
                      </a>

                      <a href="{{ route('admin.sites.edit', $site) }}"
                         class="rounded-lg px-3 py-1.5 text-xs hover:bg-slate-50 border border-slate-200 inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-700"><use href="#i-edit"/></svg>
                        Edit
                      </a>

                      <form action="{{ route('admin.sites.destroy', $site) }}" method="POST"
                            onsubmit="return confirm('Hapus site ini?');">
                        @csrf @method('DELETE')
                        <button class="rounded-lg px-3 py-1.5 text-xs hover:bg-red-50 border border-slate-200 inline-flex items-center gap-1.5">
                          <svg class="w-4 h-4 text-slate-700"><use href="#i-trash"/></svg>
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

        {{-- PAGINATION: kapsul putih seperti halaman lain --}}
        @php
            $perPage = method_exists($sites, 'perPage') ? max(1, (int) $sites->perPage()) : max(1, (int) $sites->count());
            $current = method_exists($sites, 'currentPage') ? (int) $sites->currentPage() : 1;
            $last = method_exists($sites, 'lastPage') ? (int) $sites->lastPage() : 1;
            $total = method_exists($sites, 'total') ? (int) $sites->total() : (int) $sites->count();
            $from = ($current - 1) * $perPage + 1;
            $to = min($current * $perPage, $total);

            $pages = [];
            if ($last <= 7) {
                $pages = range(1, $last);
            } else {
                $pages = [1];
                $left = max(2, $current - 1);
                $right = min($last - 1, $current + 1);
                if ($left > 2)
                    $pages[] = '...';
                for ($i = $left; $i <= $right; $i++)
                    $pages[] = $i;
                if ($right < $last - 1)
                    $pages[] = '...';
                $pages[] = $last;
            }

            $pageUrl = function (int $p) use ($sites) {
                return method_exists($sites, 'appends')
                    ? $sites->appends(request()->except('page'))->url($p)
                    : request()->fullUrlWithQuery(['page' => $p]);
            };
        @endphp

        <section class="p-3 mt-4 bg-white border shadow-sm rounded-2xl border-slate-200 md:p-4">
          <div class="flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
            <div class="text-slate-700">
              Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
              dari <span class="font-semibold text-slate-900">{{ $total }}</span>
            </div>
            <nav class="ml-auto" aria-label="Pagination">
              <ul class="inline-flex items-stretch overflow-hidden bg-white border rounded-xl border-slate-200">
                {{-- Prev --}}
                <li>
                  @if($current > 1)
                    <a href="{{ $pageUrl($current - 1) }}"
                       class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}" aria-label="Sebelumnya">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </a>
                  @else
                    <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </span>
                  @endif
                </li>

                {{-- Pages --}}
                @foreach($pages as $p)
                      @if($p === '...')
                        <li class="grid px-3 select-none place-items-center h-9 text-slate-500">…</li>
                      @else
                        @php $isCur = ((int) $p === $current); @endphp
                        <li class="grid place-items-center h-9">
                          @if($isCur)
                            <span class="inline-flex items-center h-full px-3 font-semibold border-l select-none text-slate-900 bg-slate-100 border-slate-200">{{ $p }}</span>
                          @else
                            <a href="{{ $pageUrl((int) $p) }}"
                               class="inline-flex items-center h-full px-3 border-l text-slate-700 hover:bg-slate-50 border-slate-200 focus:outline-none focus:ring-2"
                               style="--tw-ring-color: {{ $ACCENT }}" aria-label="Halaman {{ $p }}">{{ $p }}</a>
                          @endif
                        </li>
                      @endif
                @endforeach

                {{-- Next --}}
                <li class="border-l border-slate-200">
                  @if($current < $last)
                    <a href="{{ $pageUrl($current + 1) }}"
                       class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}" aria-label="Berikutnya">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </a>
                  @else
                    <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </span>
                  @endif
                </li>
              </ul>
            </nav>
          </div>
        </section>
      @else
        {{-- EMPTY STATE --}}
        <section class="p-10 text-center bg-white border border-dashed rounded-2xl border-slate-300">
          <div class="grid w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 place-content-center text-slate-400">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z"/>
            </svg>
          </div>
          <div class="font-medium text-slate-700">Belum ada data site.</div>
          <div class="mt-1 text-sm text-slate-500">Tambahkan site pertama kamu sekarang.</div>
          <a href="{{ route('admin.sites.create') }}"
             class="inline-flex items-center gap-2 px-4 py-2 mt-4 text-sm font-semibold text-white rounded-lg bg-slate-900 focus:outline-none focus:ring-2"
             style="--tw-ring-color: {{ $ACCENT }}">
            <svg class="w-4 h-4"><use href="#i-plus"/></svg>
            Tambah Site
          </a>
        </section>
      @endif
    </div>
@endsection
