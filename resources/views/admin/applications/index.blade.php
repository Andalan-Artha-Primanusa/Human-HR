{{-- resources/views/admin/applications/index.blade.php --}}
@extends('layouts.app', ['title' => 'Applications'])

@php
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
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
      /* lebar strip merah (mobile) */
      --red-w: 84px;
      background:
        linear-gradient(
          to right,
          {{ $BLUE }} 0%,
          {{ $BLUE }} calc(100% - var(--red-w)),
          {{ $RED }}  calc(100% - var(--red-w)),
          {{ $RED }} 100%
        );
    }
    @media (min-width: 640px) { /* sm: */
      .twotone { --red-w: 144px; }
    }
  </style>
@endonce

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- ===== HEADER + CTA (responsif) ===== --}}
  <section class="relative rounded-2xl border bg-white shadow-sm overflow-hidden" style="border-color: {{ $BORD }}">
    <div class="twotone rounded-t-2xl text-white">
      <div class="px-5 md:px-6 py-6 md:py-7 min-h-[96px] flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight">Applications</h1>
          <p class="text-xs sm:text-sm text-white/90">Daftar semua kandidat &amp; status proses rekrutmen.</p>
        </div>

        <a href="{{ route('admin.jobs.index') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900
                  focus:outline-none focus:ring-2 focus:ring-offset-2 w-full sm:w-auto justify-center"
           style="--tw-ring-color: {{ $BLUE }}">
          <svg class="w-4 h-4" style="color: {{ $BLUE }}">
            <use href="#i-search"/>
          </svg>
          Cari Lowongan
        </a>
      </div>
    </div>

    {{-- ===== FILTER: rapi di mobile, tanpa -mt ===== --}}
    <form method="GET"
          class="mt-4 grid grid-cols-1 gap-2 md:grid-cols-5 px-3 py-3 md:px-4 md:py-4"
          style="border-top:1px solid {{ $BORD }}">
      {{-- q --}}
      <input name="q"
             value="{{ e(request('q','')) }}"
             placeholder="Cari kandidat / posisi / site"
             class="input md:col-span-2"
             autocomplete="off" />

      {{-- stage --}}
      @php
        $stageOptions = [
          ''=>'Semua Stage','applied'=>'Applied','psychotest'=>'Psychotest','hr_iv'=>'HR Interview',
          'user_iv'=>'User Interview','final'=>'Final','offer'=>'Offer','hired'=>'Hired','not_qualified'=>'Not Qualified',
        ];
      @endphp
      <select name="stage" class="input">
        @foreach($stageOptions as $k=>$v)
          <option value="{{ $k }}" @selected(request('stage')===$k)>{{ $v }}</option>
        @endforeach
      </select>

      {{-- site --}}
      @php $siteVal = request('site'); @endphp
      @if(!empty($sites ?? null) && is_iterable($sites))
        <select name="site" class="input">
          <option value="">Semua Site</option>
          @foreach($sites as $code => $name)
            <option value="{{ $code }}" @selected($siteVal===$code)>{{ $code }} — {{ $name }}</option>
          @endforeach
        </select>
      @else
        <input name="site" value="{{ e($siteVal) }}" class="input" placeholder="DBK / POS / SBS">
      @endif

      {{-- actions --}}
      <div class="flex gap-2">
        <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white
                       hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 w-full sm:w-auto justify-center"
                style="background-color:#0f172a; border:1px solid #0f172a; --tw-ring-color: {{ $BLUE }};"
                aria-label="Filter">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
            <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <span>Filter</span>
        </button>

        @if(request()->hasAny(['q','stage','site']))
          <a href="{{ route('admin.applications.index') }}"
             class="rounded-lg border border-slate-200 px-4 py-2 text-sm hover:bg-slate-50 w-full sm:w-auto text-center">
            Reset
          </a>
        @endif
      </div>
    </form>
  </section>

  {{-- ===== TABEL ===== --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="overflow-x-auto">
      @if($apps->count())
        <table class="min-w-[960px] w-full text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 text-left">Kandidat</th>
              <th class="px-4 py-3 text-left">Posisi</th>
              <th class="px-4 py-3 text-left w-40">Divisi</th>
              <th class="px-4 py-3 text-left w-24">Site</th>
              <th class="px-4 py-3 text-center w-32">Stage</th>
              <th class="px-4 py-3 text-center w-28">Overall</th>
              <th class="px-4 py-3 text-left w-28">Dibuat</th>
              <th class="px-4 py-3 text-right w-[320px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($apps as $app)
              @php
                $stage = $app->current_stage ?? 'applied';
                $stageBadge = match($stage){
                  'applied'=>'badge-blue','psychotest'=>'badge-indigo','hr_iv'=>'badge-amber','user_iv'=>'badge-emerald',
                  'final'=>'badge-purple','offer'=>'badge-pink','hired'=>'badge-green','not_qualified'=>'badge-rose',
                  default=>'badge-slate'
                };
                $overall = strtolower($app->overall_status ?? 'active');
                $overallBadge = match($overall){
                  'hired'=>'badge-green','not_qualified'=>'badge-rose','inactive'=>'badge-slate', default=>'badge-blue'
                };
                $candidate = $app->candidate->name ?? ($app->user->name ?? ($app->name ?? '—'));
              @endphp
              <tr class="align-top hover:bg-slate-50/60">
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">{{ e($candidate) }}</div>
                  @if(!empty($app->candidate?->email))
                    <div class="text-xs text-slate-500">{{ e($app->candidate->email) }}</div>
                  @endif
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800">{{ e($app->job->title ?? '—') }}</div>
                  <div class="mt-0.5 text-xs text-slate-500">
                    @if(!empty($app->job?->employment_type))
                      <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                        {{ ucfirst($app->job->employment_type) }}
                      </span>
                    @endif
                    @if(!empty($app->job?->code))
                      <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">#{{ $app->job->code }}</span>
                    @endif
                  </div>
                </td>
                <td class="px-4 py-3">{{ e($app->job->division ?? '—') }}</td>
                <td class="px-4 py-3">
                  <span class="font-mono text-slate-700">{{ e($app->job->site->code ?? $app->job->site_code ?? '—') }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="badge {{ $stageBadge }}">{{ strtoupper(str_replace('_',' ',$stage)) }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span class="badge {{ $overallBadge }}">{{ strtoupper(str_replace('_',' ',$overall)) }}</span>
                </td>
                <td class="px-4 py-3">{{ optional($app->created_at)->format('d M Y') }}</td>
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
                    <form action="{{ route('admin.applications.move', $app) }}" method="POST" class="inline-flex items-center gap-2">
                      @csrf
                      <select name="to" class="input !h-8 !py-1 !px-2 text-xs">
                        @foreach(['applied','psychotest','hr_iv','user_iv','final','offer','hired','not_qualified'] as $opt)
                          <option value="{{ $opt }}" @selected($opt===$stage)>{{ ucwords(str_replace('_',' ',$opt)) }}</option>
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
        <div class="py-16 grid place-content-center text-center">
          <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
            <svg class="w-6 h-6"><use href="#i-plus"/></svg>
          </div>
          <div class="text-slate-700 font-medium">Belum ada data aplikasi.</div>
          <div class="text-slate-500 text-sm mt-1">Coba ubah filter atau cari lowongan.</div>
          <a href="{{ route('admin.jobs.index') }}" class="btn btn-primary mt-3 inline-flex items-center gap-2">
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
      $perPage = max(1,(int)$apps->perPage());
      $current = (int)$apps->currentPage();
      $last    = (int)$apps->lastPage();
      $total   = (int)$apps->total();
      $from    = ($current-1)*$perPage+1;
      $to      = min($current*$perPage,$total);
      $pages=[];
      if($last<=7){ $pages=range(1,$last); }
      else{
        $pages=[1];
        $left=max(2,$current-1); $right=min($last-1,$current+1);
        if($left>2) $pages[]='...';
        for($i=$left;$i<=$right;$i++) $pages[]=$i;
        if($right<$last-1) $pages[]='...';
        $pages[]=$last;
      }
      $pageUrl=function(int $p) use($apps){ return $apps->appends(request()->except('page'))->url($p); };
    @endphp

    <section class="rounded-2xl border bg-white p-4 shadow-sm" style="border-color: {{ $BORD }}">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between text-sm">
        <div class="text-slate-700">
          Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span> dari
          <span class="font-semibold text-slate-900">{{ $total }}</span>
        </div>

        <nav aria-label="Pagination" class="self-center sm:self-auto">
          <ul class="inline-flex items-stretch overflow-hidden rounded-full ring-1 ring-slate-200 bg-white">
            {{-- Prev --}}
            <li>
              @if($current>1)
                <a href="{{ $pageUrl($current-1) }}"
                   class="grid place-items-center h-9 w-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $BLUE }}" aria-label="Previous">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </a>
              @else
                <span class="grid place-items-center h-9 w-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </span>
              @endif
            </li>

            {{-- Pages --}}
            @foreach($pages as $p)
              @if($p==='...')
                <li class="grid place-items-center h-9 px-3 text-slate-500 select-none border-l border-slate-200">…</li>
              @else
                @php $isCur=((int)$p===$current); @endphp
                <li class="grid place-items-center h-9 border-l border-slate-200">
                  @if($isCur)
                    <span class="px-3 h-full inline-flex items-center font-semibold text-slate-900 bg-blue-50">{{ $p }}</span>
                  @else
                    <a href="{{ $pageUrl((int)$p) }}"
                       class="px-3 h-full inline-flex items-center text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $BLUE }}" aria-label="Page {{ $p }}">{{ $p }}</a>
                  @endif
                </li>
              @endif
            @endforeach

            {{-- Next --}}
            <li class="border-l border-slate-200">
              @if($current<$last)
                <a href="{{ $pageUrl($current+1) }}"
                   class="grid place-items-center h-9 w-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $BLUE }}" aria-label="Next">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                </a>
              @else
                <span class="grid place-items-center h-9 w-9 opacity-40 cursor-not-allowed" aria-hidden="true">
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
