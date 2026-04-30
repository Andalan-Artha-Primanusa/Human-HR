{{-- resources/views/admin/candidates/index.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Candidates'])

@php
    // THEME (solid)
    $ACCENT = '#a77d52'; // brown
    $ACCENT_DARK = '#8b5e3c'; // dark brown
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

      {{-- HEADER + SEARCH --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative">
          <div class="w-full h-20 sm:h-24 bg-[#a77d52]"></div>

          <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Candidates</h1>
              <p class="text-xs sm:text-sm text-white/90">Daftar kandidat yang telah mengisi profil.</p>
            </div>
          </div>
        </div>

        {{-- SEARCH FORM --}}
        <div class="p-6 border-t md:p-7 bg-white" style="border-color: {{ $BORD }}">
          <form method="GET"
            class="grid grid-cols-1 gap-4 md:grid-cols-[1fr_auto_auto] md:items-end"
            role="search" aria-label="Cari kandidat">

            <label class="sr-only" for="q">Cari</label>
            <input
              id="q"
              type="text"
              name="q"
              value="{{ e($q ?? '') }}"
              placeholder="Cari nama / email / HP / NIK"
              class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl input border-slate-200 focus:outline-none focus:ring-2"
              style="--tw-ring-color: {{ $ACCENT }}"
              autocomplete="off">

            <label class="sr-only" for="job_id">Posisi</label>
            <select id="job_id" name="job_id"
                class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl input border-slate-200 focus:outline-none focus:ring-2"
                style="--tw-ring-color: {{ $ACCENT }}">
                <option value="">Semua Posisi</option>
                @foreach($jobs ?? [] as $id => $title)
                    <option value="{{ $id }}" @selected(($jobId ?? '') == $id)>{{ $title }}</option>
                @endforeach
            </select>

            <button type="submit"
              class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[#a77d52] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
              style="--tw-ring-color: {{ $ACCENT }}"
              aria-label="Filter">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
                <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
              </svg>
              <span>Filter</span>
            </button>

            @if(filled($q ?? ''))
                  <a href="{{ route('admin.candidates.index') }}"
                     class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50">
                    Reset
                  </a>
            @endif
          </form>
        </div>
      </section>

      {{-- TABEL (footer dipisah; tidak nempel) --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
        @if($profiles->count())
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm">
                <thead class="text-white bg-[#a77d52]">
                  <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">HP</th>
                    <th class="px-4 py-3 text-left">NIK</th>
                    <th class="px-4 py-3 text-left">Posisi yang Dilamar</th>
                    <th class="px-4 py-3 text-center">Tr / Emp / Ref</th>
                    <th class="px-4 py-3"></th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                  @foreach($profiles as $p)
                      <tr class="transition hover:bg-[#f8f5f2]">
                        <td class="px-4 py-3">
                          <div class="font-medium text-slate-900">{{ e($p->full_name) }}</div>
                          <div class="text-xs text-slate-500">Updated: {{ e(optional($p->updated_at)->format('d M Y H:i')) }}</div>
                        </td>
                        <td class="px-4 py-3">{{ e($p->email) }}</td>
                        <td class="px-4 py-3">{{ e($p->phone) }}</td>
                        <td class="px-4 py-3">{{ e($p->nik) }}</td>
                        <td class="px-4 py-3">
                          @php
                            $jobsApplied = $p->user && $p->user->jobApplications ? $p->user->jobApplications->pluck('job.title')->unique()->filter()->values() : collect();
                          @endphp
                          @if($jobsApplied->isNotEmpty())
                            <ul class="list-disc ml-4">
                              @foreach($jobsApplied as $jobTitle)
                                <li>{{ $jobTitle }}</li>
                              @endforeach
                            </ul>
                          @else
                            <span class="text-slate-400">-</span>
                          @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                          {{ (int) $p->trainings_count }} / {{ (int) $p->employments_count }} / {{ (int) $p->references_count }}
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
            <div class="p-10 m-6 text-center border border-dashed rounded-2xl border-slate-300">
              <div class="grid w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 place-content-center text-slate-400">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z" />
                </svg>
              </div>
              <div class="font-medium text-slate-700">Belum ada data.</div>
              <div class="mt-1 text-sm text-slate-500">Coba ubah kata kunci pencarian.</div>
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

            $pageUrl = function (int $p) use ($profiles) {
                return $profiles->appends(request()->except('page'))->url($p);
            };
          @endphp

              <section class="p-4 mt-4 bg-white border shadow-sm rounded-2xl border-slate-200">
                <div class="flex items-center justify-between text-sm">
                  <div class="flex items-center gap-4 text-slate-700">
                    <span>Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span> dari <span class="font-semibold text-slate-900">{{ $total }}</span></span>
                  </div>

                  <nav aria-label="Pagination">
                    <ul class="inline-flex items-stretch overflow-hidden bg-white rounded-full ring-1 ring-slate-200">
                      {{-- Prev --}}
                      <li>
                        @if($current > 1)
                            <a href="{{ $pageUrl($current - 1) }}"
                              class="grid place-items-center h-9 w-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                              style="--tw-ring-color:{{ $ACCENT }}" aria-label="Previous">
                              <svg class="w-4 h-4 text-slate-700">
                                <use href="#i-chevron-left" />
                              </svg>
                            </a>
                        @else
                            <span class="grid cursor-not-allowed place-items-center h-9 w-9 opacity-40" aria-hidden="true">
                              <svg class="w-4 h-4 text-slate-700">
                                <use href="#i-chevron-left" />
                              </svg>
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
                                      style="--tw-ring-color:{{ $ACCENT }}" aria-label="Page {{ $p }}">{{ $p }}</a>
                                @endif
                              </li>
                          @endif
                      @endforeach

                      {{-- Next --}}
                      <li class="border-l border-slate-200">
                        @if($current < $last)
                              <a href="{{ $pageUrl($current + 1) }}"
                              class="grid place-items-center h-9 w-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                              style="--tw-ring-color:{{ $ACCENT }}" aria-label="Next">
                              <svg class="w-4 h-4 text-slate-700">
                                <use href="#i-chevron-right" />
                              </svg>
                              </a>
                          @else
                              <span class="grid cursor-not-allowed place-items-center h-9 w-9 opacity-40" aria-hidden="true">
                                <svg class="w-4 h-4 text-slate-700">
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