{{-- resources/views/admin/applications/index.blade.php --}}
@extends('layouts.app', ['title' => 'Applications'])

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
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
            <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14" />
            </symbol>
            <symbol id="i-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 5l7 7-7 7" />
            </symbol>
            <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </symbol>
            <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </symbol>
          </svg>

          {{-- CSS kecil untuk header 2-tone yang fleksibel --}}
          <style>
            .twotone {
              --accent-w: 84px; /* lebar strip accent (mobile) */
              background:
                linear-gradient(
                  to right,
                  {{ $ACCENT }} 0%,
                  {{ $ACCENT }} calc(100% - var(--accent-w)),
                  {{ $ACCENT_DARK }}  calc(100% - var(--accent-w)),
                  {{ $ACCENT_DARK }} 100%
                );
            }
            @media (min-width: 640px) { .twotone { --accent-w: 144px; } }
          </style>
    @endonce

    @php
        // === Pretty labels (selaras controller)
        $PRETTY = [
            'applied' => 'Applied',
            'screening' => 'Screening CV/Berkas Lamaran',
            'psychotest' => 'Psikotest',
            'hr_iv' => 'HR Interview',
            'user_iv' => 'User Interview',
            'user_trainer_iv' => 'User/Trainer Interview',
            'offer' => 'OL',
            'mcu' => 'MCU',
            'mobilisasi' => 'Mobilisasi',
            'ground_test' => 'Ground Test',
            'hired' => 'Hired',
            'not_qualified' => 'Not Lolos',
        ];

        // === Options filter stage
        $stageOptions = ['' => 'Semua Stage'] + $PRETTY;

        // === Badge mapping
        $stageBadgeMap = [
            'applied' => 'badge-blue',
            'screening' => 'badge-sky',
            'psychotest' => 'badge-indigo',
            'hr_iv' => 'badge-amber',
            'user_iv' => 'badge-emerald',
            'user_trainer_iv' => 'badge-lime',
            'offer' => 'badge-pink',
            'mcu' => 'badge-cyan',
            'mobilisasi' => 'badge-orange',
            'ground_test' => 'badge-purple',
            'hired' => 'badge-green',
            'not_qualified' => 'badge-rose',
        ];
    @endphp

    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- ===== HEADER + CTA (responsif) ===== --}}
      <section class="relative overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="text-white twotone rounded-t-2xl">
          <div class="px-5 md:px-6 py-6 md:py-7 min-h-[96px] flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Applications</h1>
              <p class="text-xs sm:text-sm text-white/90">Daftar semua kandidat &amp; status proses rekrutmen.</p>
            </div>

            <a href="{{ route('admin.jobs.index') }}"
               class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
               style="--tw-ring-color: {{ $ACCENT }}">
              <svg class="w-4 h-4" style="color: {{ $ACCENT }}"><use href="#i-search"/></svg>
              Cari Lowongan
            </a>
          </div>
        </div>

        {{-- ===== FILTER ===== --}}
        <div class="p-6 border-t md:p-7" style="border-color: {{ $BORD }}; background: linear-gradient(180deg, #faf7f4, #ffffff);">
          <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-5">
            {{-- q --}}
            <input name="q"
                   value="{{ e(request('q', '')) }}"
                   placeholder="Cari kandidat / posisi / site"
                   class="input md:col-span-2"
                   autocomplete="off" />

            {{-- stage --}}
            <select name="stage" class="input">
              @foreach($stageOptions as $k => $v)
                <option value="{{ $k }}" @selected(request('stage') === $k)>{{ $v }}</option>
              @endforeach
            </select>

            {{-- site --}}
            @php $siteVal = request('site'); @endphp
            @if(!empty($sites ?? null) && is_iterable($sites))
                  <select name="site" class="input">
                    <option value="">Semua Site</option>
                    @foreach($sites as $code => $name)
                          <option value="{{ $code }}" @selected($siteVal === $code)>{{ $code }} — {{ $name }}</option>
                    @endforeach
                  </select>
            @else
                  <input name="site" value="{{ e($siteVal) }}" class="input" placeholder="DBK / POS / SBS">
            @endif

            {{-- actions --}}
            <div class="flex gap-2">
              <button type="submit"
                      class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2"
                      style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }}); --tw-ring-color: {{ $ACCENT }};"
                      aria-label="Filter">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
                  <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span>Filter</span>
              </button>

              @if(request()->hasAny(['q', 'stage', 'site']))
                <a href="{{ route('admin.applications.index') }}"
                   class="w-full px-4 py-2 text-sm text-center border rounded-lg border-slate-200 hover:bg-slate-50">
                  Reset
                </a>
              @endif
            </div>
          </form>
        </div>
      </section>

      {{-- ===== TABEL ===== --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200" style="border-color: {{ $BORD }}">
        <div class="overflow-x-auto">
          @if($apps->count())
            <table class="min-w-[960px] w-full text-sm">
              <thead class="text-white" style="background: linear-gradient(90deg, {{ $ACCENT }}, {{ $ACCENT_DARK }});">
                <tr>
                  <th class="px-4 py-3 font-semibold text-left">Kandidat</th>
                  <th class="px-4 py-3 font-semibold text-left">Posisi</th>
                  <th class="w-40 px-4 py-3 font-semibold text-left">Divisi</th>
                  <th class="w-24 px-4 py-3 font-semibold text-left">Site</th>
                  <th class="w-40 px-4 py-3 font-semibold text-center">Stage</th>
                  <th class="px-4 py-3 font-semibold text-center w-28">Overall</th>
                  <th class="px-4 py-3 font-semibold text-left w-28">Dibuat</th>
                  <th class="px-4 py-3 text-right w-[360px] font-semibold">Aksi</th>
                </tr>
              </thead>

              <tbody class="text-black divide-y divide-slate-100">
                @foreach($apps as $app)
                      @php
                        $stageKey = $app->current_stage ?? 'applied';
                        $stageLabel = $PRETTY[$stageKey] ?? strtoupper(str_replace('_', ' ', $stageKey));
                        $stageBadge = $stageBadgeMap[$stageKey] ?? 'badge-slate';

                        $overall = strtolower($app->overall_status ?? 'active');
                        $overallBadge = match ($overall) {
                            'hired' => 'badge-green',
                            'not_qualified' => 'badge-rose',
                            'inactive' => 'badge-slate',
                            default => 'badge-blue'
                        };

                        $candidate = $app->candidate->name ?? ($app->user->name ?? ($app->name ?? '—'));
                      @endphp

                      <tr class="align-top" style="background-color: #faf9f7;" onmouseover="this.style.backgroundColor='#f8f5f2'" onmouseout="this.style.backgroundColor='#faf9f7'">
                        <td class="px-4 py-3">
                          <div class="font-medium text-black">{{ e($candidate) }}</div>
                          @if(!empty($app->candidate?->email))
                            <div class="text-xs text-black">{{ e($app->candidate->email) }}</div>
                          @endif
                        </td>

                        <td class="px-4 py-3">
                          <div class="font-medium text-black">{{ e($app->job->title ?? '—') }}</div>
                          <div class="mt-0.5 text-xs text-black">
                            @if(!empty($app->job?->employment_type))
                                  <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                                    {{ ucfirst($app->job->employment_type) }}
                                  </span>
                            @endif
                            @if(!empty($app->job?->code))
                                  <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                                    #{{ $app->job->code }}
                                  </span>
                            @endif
                          </div>
                        </td>

                        <td class="px-4 py-3 text-black">{{ e($app->job->division ?? '—') }}</td>

                        <td class="px-4 py-3">
                          <span class="font-mono text-black">{{ e($app->job->site->code ?? $app->job->site_code ?? '—') }}</span>
                        </td>

                        <td class="px-4 py-3 text-center">
                          <span class="badge {{ $stageBadge }}">{{ $stageLabel }}</span>
                        </td>

                        <td class="px-4 py-3 text-center">
                          <span class="badge {{ $overallBadge }}">{{ strtoupper(str_replace('_', ' ', $overall)) }}</span>
                        </td>

                        <td class="px-4 py-3 text-black">{{ optional($app->created_at)->format('d M Y') }}</td>

                        <td class="px-4 py-3">
                          <div class="flex justify-end gap-2">
                            <a class="btn btn-outline btn-sm inline-flex items-center gap-1.5"
                               target="_blank" href="{{ route('jobs.show', $app->job ?? 0) }}">
                              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-width="2" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3" stroke-width="2"/>
                              </svg>
                              Lihat Job
                            </a>

                            {{-- Dropdown pindah stage (pakai key baru) --}}
                            <form action="{{ route('admin.applications.move', $app) }}" method="POST" class="inline-flex items-center gap-2">
                              @csrf
                              <select name="to" class="input !h-8 !py-1 !px-2 text-xs">
                                @foreach(array_keys($PRETTY) as $opt)
                                      <option value="{{ $opt }}" @selected($opt === $stageKey)>{{ $PRETTY[$opt] }}</option>
                                @endforeach
                              </select>
                              <button class="btn btn-primary btn-sm inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4"><use href="#i-arrow"/></svg>
                                Pindah
                              </button>
                            </form>
                          </div>
                        </td>
                      </tr>
                @endforeach
              </tbody>
            </table>

          @else
            {{-- EMPTY STATE --}}
            <div class="py-12 text-center">
              <div class="inline-flex items-center justify-center w-12 h-12 mb-3 border border-dashed rounded-2xl border-slate-300 text-slate-400">
                <svg class="w-6 h-6"><use href="#i-search"/></svg>
              </div>
              <div class="font-medium text-slate-700">Belum ada data aplikasi.</div>
              <div class="mt-1 text-sm text-slate-500">Coba ubah filter atau cari lowongan.</div>
              <a href="{{ route('admin.jobs.index') }}" class="inline-flex items-center gap-2 mt-4 btn btn-primary">
                <svg class="w-4 h-4"><use href="#i-search"/></svg>
                Cari Lowongan
              </a>
            </div>
          @endif
        </div>
      </section>

      {{-- ===== PAGINATION custom ringkas ===== --}}
      @if($apps->count())
        @php
            $perPage = max(1, (int) $apps->perPage());
            $current = (int) $apps->currentPage();
            $last = (int) $apps->lastPage();
            $total = (int) $apps->total();
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
            $pageUrl = function (int $p) use ($apps) {
                return $apps->appends(request()->except('page'))->url($p); };
        @endphp

        <section class="p-4 bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
          <div class="flex flex-col gap-3 text-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="text-slate-700">
              Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span> dari
              <span class="font-semibold text-slate-900">{{ $total }}</span>
            </div>

            <nav aria-label="Pagination" class="self-center sm:self-auto">
              <ul class="inline-flex items-stretch overflow-hidden bg-white rounded-full ring-1 ring-slate-200">
                {{-- Prev --}}
                <li>
                  @if($current > 1)
                    <a href="{{ $pageUrl($current - 1) }}"
                       class="grid place-items-center h-9 w-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}" aria-label="Previous">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </a>
                  @else
                    <span class="grid cursor-not-allowed place-items-center h-9 w-9 opacity-40" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </span>
                  @endif
                </li>

                {{-- Pages --}}
                @foreach($pages as $p)
                      @if($p === '...')
                        <li class="grid px-3 border-l select-none place-items-center h-9 text-slate-500 border-slate-200">…</li>
                      @else
                        @php $isCur = ((int) $p === $current); @endphp
                        <li class="grid border-l place-items-center h-9 border-slate-200">
                          @if($isCur)
                            <span class="inline-flex items-center h-full px-3 font-semibold text-slate-900 bg-slate-100">{{ $p }}</span>
                          @else
                            <a href="{{ $pageUrl((int) $p) }}"
                               class="inline-flex items-center h-full px-3 text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2"
                               style="--tw-ring-color: {{ $ACCENT }}" aria-label="Page {{ $p }}">{{ $p }}</a>
                          @endif
                        </li>
                      @endif
                @endforeach

                {{-- Next --}}
                <li class="border-l border-slate-200">
                  @if($current < $last)
                    <a href="{{ $pageUrl($current + 1) }}"
                       class="grid place-items-center h-9 w-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}" aria-label="Next">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </a>
                  @else
                    <span class="grid cursor-not-allowed place-items-center h-9 w-9 opacity-40" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
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
