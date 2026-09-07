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
            <symbol id="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/>
              <circle cx="12" cy="12" r="3" stroke-width="2"/>
            </symbol>
            <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="12" cy="8" r="4" stroke-width="2"/>
              <path stroke-linecap="round" stroke-width="2" d="M4 21a8 8 0 0 1 16 0"/>
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
          
    @endonce

    @php
        // === Pretty labels (selaras controller)
        $PRETTY = [
            'applied' => 'Screening',
            'screening' => 'Screening',
            'psychotest' => 'Psychological Test',
            'psychological_test' => 'Psychological Test',
            'hr_iv' => 'HR Interview',
            'post_test' => 'Post Test',
            'user_iv' => 'User Interview',
            'user_trainer_iv' => 'User Interview',
            'offer' => 'Offering Letter (OL)',
            'mcu' => 'Medical Check Up',
            'mobilisasi' => 'Mobilisasi (Travel)',
            'ground_test' => 'Skill Test',
            'skill_test' => 'Skill Test',
            'onsite' => 'Finish',
            'hired' => 'Finish',
            'not_qualified' => 'Finish',
            'finish' => 'Finish',
        ];

        // === Options filter stage
        $stageOptions = ['' => 'Semua Stage'] + $PRETTY;

        // === Badge mapping
        $stageBadgeMap = [
            'applied' => 'badge-blue',
            'screening' => 'badge-sky',
            'psychotest' => 'badge-indigo',
            'psychological_test' => 'badge-indigo',
            'hr_iv' => 'badge-amber',
            'post_test' => 'badge-violet',
            'user_iv' => 'badge-emerald',
            'user_trainer_iv' => 'badge-lime',
            'offer' => 'badge-pink',
            'mcu' => 'badge-cyan',
            'mobilisasi' => 'badge-orange',
            'ground_test' => 'badge-purple',
            'skill_test' => 'badge-purple',
            'hired' => 'badge-green',
            'not_qualified' => 'badge-rose',
            'finish' => 'badge-green',
        ];
        $stageAlias = [
            'applied' => 'screening',
            'psychotest' => 'psychological_test',
            'user_trainer_iv' => 'user_iv',
            'ground_test' => 'skill_test',
            'onsite' => 'finish',
            'hired' => 'finish',
            'not_qualified' => 'finish',
            'rejected' => 'finish',
        ];
    @endphp

    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- ===== HEADER — shared component ===== --}}
      <x-admin.page-header title="Applications" description="Daftar semua kandidat &amp; status proses rekrutmen.">
        <a href="{{ route('admin.jobs.index') }}" class="ph-action">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          Cari Lowongan
        </a>
      </x-admin.page-header>

      {{-- ===== FILTER / TOOLBAR ===== --}}
      <section class="overflow-hidden bg-white border rounded-2xl" style="border-color: {{ $BORD }}; border-radius: 1rem;">
        <div class="p-6 md:p-6 bg-white">
          <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-5">
            {{-- q --}}
            <input name="q"
                   value="{{ e(request('q', '')) }}"
                   placeholder="Cari kandidat / posisi / site"
                   class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2 md:col-span-2"
                   style="--tw-ring-color: {{ $ACCENT }}"
                   autocomplete="off" />

            {{-- stage --}}
            <select name="stage"
                    class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
              @foreach($stageOptions as $k => $v)
                <option value="{{ $k }}" @selected(request('stage') === $k)>{{ $v }}</option>
              @endforeach
            </select>

            {{-- site --}}
            @php $siteVal = request('site'); @endphp
            @if(!empty($sites ?? null) && is_iterable($sites))
                  <select name="site"
                          class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                          style="--tw-ring-color: {{ $ACCENT }}">
                    <option value="">Semua Site</option>
                    @foreach($sites as $code => $name)
                          <option value="{{ $code }}" @selected($siteVal === $code)>{{ $code }} — {{ $name }}</option>
                    @endforeach
                  </select>
            @else
                  <input name="site" value="{{ e($siteVal) }}"
                         class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                         style="--tw-ring-color: {{ $ACCENT }}" placeholder="DBK / POS / SBS">
            @endif

            {{-- actions --}}
            <div class="flex gap-2">
              <button type="submit" class="abtn abtn-primary w-full" aria-label="Filter">
                <svg class="w-4 h-4 text-white"><use href="#i-search"/></svg>
                <span>Filter</span>
              </button>

              @if(request()->hasAny(['q', 'stage', 'site']))
                <a href="{{ route('admin.applications.index') }}" class="abtn abtn-neutral w-full">
                  Reset
                </a>
              @endif
            </div>
          </form>
        </div>
      </section>

      @if(empty($selectedJob))
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          @forelse($jobCards as $job)
            <article class="flex flex-col bg-white border shadow-sm rounded-xl border-slate-200">
              <div class="p-4 border-b border-slate-100">
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0">
                    <div class="font-mono text-xs text-slate-500">{{ e($job->code) }}</div>
                    <h2 class="mt-1 text-base font-bold leading-snug text-slate-950">{{ e($job->title) }}</h2>
                  </div>
                  <span class="badge {{ strtolower((string) $job->status) === 'open' ? 'badge-green' : 'badge-slate' }}">
                    {{ strtoupper(e($job->status)) }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-1.5 mt-3 text-xs text-slate-600">
                  @if(filled($job->division))
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 font-semibold">{{ e($job->division) }}</span>
                  @endif
                  @if($job->site)
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5">{{ e($job->site->code) }} - {{ e($job->site->name) }}</span>
                  @endif
                  <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5">Openings {{ (int) $job->openings }}</span>
                </div>
              </div>

              <div class="grid grid-cols-4 gap-1.5 px-4 py-3">
                <div class="rounded-lg bg-[#f8f5f2] px-2 py-2 text-center">
                  <div class="text-[11px] leading-none text-slate-500">Total</div>
                  <div class="mt-1 text-base font-bold text-slate-950">{{ (int) $job->applicants_count }}</div>
                </div>
                <div class="rounded-lg bg-blue-50 px-2 py-2 text-center">
                  <div class="text-[11px] leading-none text-blue-700">Aktif</div>
                  <div class="mt-1 text-base font-bold text-blue-800">{{ (int) $job->active_count }}</div>
                </div>
                <div class="rounded-lg bg-emerald-50 px-2 py-2 text-center">
                  <div class="text-[11px] leading-none text-emerald-700">Hired</div>
                  <div class="mt-1 text-base font-bold text-emerald-800">{{ (int) $job->hired_count }}</div>
                </div>
                <div class="rounded-lg bg-rose-50 px-2 py-2 text-center">
                  <div class="text-[11px] leading-none text-rose-700">Reject</div>
                  <div class="mt-1 text-base font-bold text-rose-800">{{ (int) $job->rejected_count }}</div>
                </div>
              </div>

              <div class="flex gap-2 px-4 pb-4 mt-auto">
                <a class="abtn abtn-sm abtn-primary flex-1 justify-center" href="{{ route('admin.applications.index', array_merge(request()->except(['job', 'page', 'jobs_page']), ['job' => $job->id])) }}">
                  <svg class="w-4 h-4"><use href="#i-user"/></svg>
                  Lihat Kandidat
                </a>
                <a class="abtn abtn-sm abtn-secondary" target="_blank" href="{{ route('jobs.show', $job) }}">
                  <svg class="w-4 h-4"><use href="#i-eye"/></svg>
                  Job
                </a>
              </div>
            </article>
          @empty
            <section class="p-10 text-center bg-white border border-dashed shadow-sm md:col-span-2 xl:col-span-3 rounded-2xl border-slate-300">
              <div class="inline-flex items-center justify-center w-12 h-12 mb-3 border rounded-2xl border-slate-200 text-slate-400">
                <svg class="w-6 h-6"><use href="#i-search"/></svg>
              </div>
              <div class="font-medium text-slate-700">Belum ada lowongan yang punya lamaran.</div>
              <div class="mt-1 text-sm text-slate-500">Coba ubah filter atau cek Kanban Board.</div>
            </section>
          @endforelse
        </section>

        @if($jobCards->count())
          <section class="p-4 bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
            {{ $jobCards->links() }}
          </section>
        @endif
      @else
        <section class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <a href="{{ route('admin.applications.index', request()->except(['job', 'page'])) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-[#8b5e3c] hover:underline">
                <svg class="w-4 h-4"><use href="#i-chevron-left"/></svg>
                Kembali ke lowongan
              </a>
              <h2 class="mt-2 text-xl font-bold text-slate-950">{{ e($selectedJob->title) }}</h2>
              <div class="mt-1 text-sm text-slate-500">{{ e($selectedJob->code) }} · {{ e($selectedJob->division) }} · {{ e($selectedJob->site?->code) }}</div>
            </div>
            <a class="abtn abtn-secondary" target="_blank" href="{{ route('jobs.show', $selectedJob) }}">
              <svg class="w-4 h-4"><use href="#i-eye"/></svg>
              Lihat Job
            </a>
          </div>
        </section>

        <section class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200" style="border-color: {{ $BORD }}">
          <div class="overflow-x-auto">
            @if($apps->count())
              <table class="min-w-[960px] w-full text-sm">
              <thead class="text-white bg-[#a77d52]">
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
                        $stageKey = strtolower((string) ($app->current_stage ?? 'screening'));
                        $stageKey = $stageAlias[$stageKey] ?? $stageKey;
                        $stageLabel = $PRETTY[$stageKey] ?? strtoupper(str_replace('_', ' ', $stageKey));
                        $stageBadge = $stageBadgeMap[$stageKey] ?? 'badge-slate';

                        $overall = strtolower($app->overall_status ?? 'active');
                        $overallBadge = match ($overall) {
                            'hired' => 'badge-green',
                            'not_qualified' => 'badge-rose',
                            'inactive' => 'badge-slate',
                            default => 'badge-blue'
                        };

                        $profile = $app->user?->candidateProfile;
                        $candidate = $profile?->full_name ?: ($app->user->name ?? 'Kandidat');
                        $candidateEmail = $profile?->email ?: ($app->user->email ?? null);
                      @endphp

                      <tr class="align-top transition hover:bg-[#f8f5f2]">
                        <td class="px-4 py-3">
                          <div class="font-medium text-black">
                            @if($profile && Route::has('admin.candidates.show'))
                              <a href="{{ route('admin.candidates.show', $profile) }}" target="_blank" class="hover:underline">{{ e($candidate) }}</a>
                            @else
                              {{ e($candidate) }}
                            @endif
                          </div>
                          @if($candidateEmail)
                            <div class="text-xs text-black">{{ e($candidateEmail) }}</div>
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
                          <div class="flex flex-wrap justify-end gap-1.5">
                            @if($profile && Route::has('admin.candidates.show'))
                              <a class="abtn abtn-sm abtn-secondary"
                                 target="_blank" href="{{ route('admin.candidates.show', $profile) }}">
                                <svg class="w-4 h-4"><use href="#i-user"/></svg>
                                Profil
                              </a>
                            @endif

                            <a class="abtn abtn-sm abtn-secondary"
                               target="_blank" href="{{ route('jobs.show', $app->job ?? 0) }}">
                              <svg class="w-4 h-4"><use href="#i-eye"/></svg>
                              Lihat Job
                            </a>

                            {{-- Dropdown pindah stage (pakai key baru) --}}
                            <form action="{{ route('admin.applications.move', $app) }}" method="POST" class="inline-flex items-center gap-1.5">
                              @csrf
                              <select name="to"
                                      class="w-full px-2 py-1.5 text-xs bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                                      style="--tw-ring-color: {{ $ACCENT }}">
                                @foreach(array_keys($PRETTY) as $opt)
                                      <option value="{{ $opt }}" @selected($opt === $stageKey)>{{ $PRETTY[$opt] }}</option>
                                @endforeach
                              </select>
                              <button class="abtn abtn-xs abtn-primary">
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
              <div class="py-12 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 mb-3 border border-dashed rounded-2xl border-slate-300 text-slate-400">
                  <svg class="w-6 h-6"><use href="#i-search"/></svg>
                </div>
                <div class="font-medium text-slate-700">Belum ada kandidat untuk filter ini.</div>
                <div class="mt-1 text-sm text-slate-500">Coba ubah stage atau kembali ke daftar lowongan.</div>
              </div>
            @endif
          </div>
        </section>
      @endif

      {{-- ===== PAGINATION custom ringkas ===== --}}
      @if(!empty($selectedJob) && $apps->count())
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
              <ul class="inline-flex items-stretch overflow-hidden bg-white border rounded-xl border-slate-200">
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
