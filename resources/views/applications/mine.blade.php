{{-- resources/views/applications/mine.blade.php --}}
@extends('layouts.app', ['title' => 'Lamaran Saya'])

@php
  $BLUE  = '#1d4ed8'; // solid blue
  $RED   = '#dc2626'; // solid red
  $BORD  = '#e5e7eb';

  $stageOrder = ['applied','psychotest','hr_iv','user_iv','final','offer','hired'];
  $pretty = [
    'applied'=>'Pengajuan Berkas','psychotest'=>'Psikotes','hr_iv'=>'HR Interview',
    'user_iv'=>'User Interview','final'=>'Final','offer'=>'Offering',
    'hired'=>'Diterima','rejected'=>'Ditolak'
  ];

  $summary = [
    'total'    => $apps->total(),
    'active'   => $apps->getCollection()->where('overall_status','active')->count(),
    'hired'    => $apps->getCollection()->where('overall_status','hired')->count(),
    'rejected' => $apps->getCollection()->where('overall_status','rejected')->count(),
  ];

  $progressOf = function($app) use ($stageOrder){
    $key = strtolower($app->current_stage ?? 'applied');
    $idx = array_search($key,$stageOrder,true); if($idx===false) $idx=0;
    $max = max(count($stageOrder)-1,1);
    if(($app->overall_status ?? '')==='rejected'){
      return min(100, max(40, (int)round($idx/$max*100)));
    }
    return (int)round($idx/$max*100);
  };

  $badge = function($overall) {
    return match(strtolower((string)$overall)) {
      'hired'    => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
      'rejected' => 'bg-slate-100 text-slate-700 ring-slate-200',
      'active'   => 'bg-blue-50 text-blue-700 ring-blue-200',
      default    => 'bg-zinc-50 text-zinc-700 ring-zinc-200',
    };
  };
@endphp

@section('content')
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-brief" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" stroke-width="1.8"/>
  </symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M12 7v5l3 2" stroke-width="2" stroke-linecap="round"/>
  </symbol>
  <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M4 12l5 5 11-11" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
</svg>

<div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8 py-6">

  {{-- Flash --}}
  @if(session('ok') || session('warn'))
    <div class="mb-4 space-y-2">
      @if(session('ok'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('ok') }}</div>
      @endif
      @if(session('warn'))
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">{{ session('warn') }}</div>
      @endif
    </div>
  @endif

  {{-- Header lebar + pita biru/merah --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="flex h-2 w-full">
      <div class="flex-1" style="background: {{ $BLUE }}"></div>
      <div class="w-32" style="background: {{ $RED }}"></div>
    </div>

    <div class="p-5 md:p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
          <h1 class="text-3xl font-semibold text-slate-900 tracking-tight">Lamaran Saya</h1>
          <p class="text-sm text-slate-600">Pantau progres seleksi kamu dengan jelas.</p>
        </div>
        <a href="{{ route('jobs.index') }}"
           class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white"
           style="background: {{ $BLUE }}">Cari Lowongan</a>
      </div>

      {{-- Stats bar lebar --}}
      <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
          <div class="text-xs text-slate-500">Total</div>
          <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $summary['total'] }}</div>
        </div>
        <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
          <div class="text-xs text-slate-500">Aktif</div>
          <div class="mt-1 text-2xl font-semibold" style="color: {{ $BLUE }}">{{ $summary['active'] }}</div>
        </div>
        <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
          <div class="text-xs text-slate-500">Hired</div>
          <div class="mt-1 text-2xl font-semibold text-emerald-700">{{ $summary['hired'] }}</div>
        </div>
        <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
          <div class="text-xs text-slate-500">Rejected</div>
          <div class="mt-1 text-2xl font-semibold text-slate-800">{{ $summary['rejected'] }}</div>
        </div>
      </div>
    </div>
  </div>

  @if($apps->count())
    {{-- Grid melebar sampai 2xl --}}
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
      @foreach($apps as $app)
        @php
          $job = $app->job;
          $title = $job->title ?? '—';
          $site  = $job?->site?->code ?? '—';
          $div   = $job->division ?? '—';
          $overall = $app->overall_status ?? 'active';
          $currKey = strtolower($app->current_stage ?? 'applied');
          $pct = $progressOf($app);
          $badgeClass = $badge($overall);
        @endphp

        <div class="relative rounded-2xl border bg-white shadow-sm transition hover:shadow-md" style="border-color: {{ $BORD }}">
          <div class="absolute inset-x-0 top-0 h-1.5 rounded-t-2xl"
               style="background: {{ $overall==='rejected' ? $RED : $BLUE }}"></div>

          <div class="p-5">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <a href="{{ route('jobs.show', $app->job_id) }}"
                   class="block truncate text-[18px] font-semibold text-slate-900 hover:underline">{{ $title }}</a>
                <div class="mt-1 text-sm text-slate-600">{{ $div }} · {{ $site }}</div>
              </div>
              <span class="rounded-full px-2 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $badgeClass }}">
                {{ strtoupper($overall) }}
              </span>
            </div>

            <div class="mt-3">
              <div class="flex items-center justify-between text-xs text-slate-600">
                <span>Saat ini:
                  <span class="font-medium text-slate-900">{{ $pretty[$currKey] ?? ucfirst($currKey) }}</span>
                </span>
                <span>{{ $pct }}%</span>
              </div>
              <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                <div class="h-full rounded-full"
                     style="width: {{ $pct }}%; background: {{ $overall==='rejected' ? $RED : $BLUE }}"></div>
              </div>
            </div>

            {{-- Pills tahapan (single-line wrap) --}}
            <div class="mt-3 flex flex-wrap gap-1">
              @php $visited = collect($app->stages ?? [])->pluck('stage_key')->map(fn($v)=>strtolower($v))->all(); @endphp
              @foreach($stageOrder as $key)
                @php
                  $done = in_array($key,$visited,true) && $key!=='hired';
                  $isNow = $key === $currKey;
                @endphp
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] ring-1 ring-inset
                  {{ $done ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' :
                     ($isNow ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-slate-50 text-slate-700 ring-slate-200') }}">
                  @if($done)
                    <svg class="mr-1 h-3.5 w-3.5"><use href="#i-check"/></svg>
                  @elseif($isNow)
                    <svg class="mr-1 h-3.5 w-3.5"><use href="#i-clock"/></svg>
                  @endif
                  {{ $pretty[$key] }}
                </span>
              @endforeach
              @if(($app->overall_status ?? '')==='rejected')
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700 ring-1 ring-inset ring-slate-200">
                  Ditolak
                </span>
              @endif
            </div>

            <div class="mt-4 flex items-center justify-between text-xs text-slate-600">
              <span>Diajukan: {{ optional($app->created_at)->format('d M Y') }}</span>
              <a href="{{ route('jobs.show', $app->job_id) }}"
                 class="rounded-lg border px-3 py-1.5 text-slate-900 hover:bg-slate-50"
                 style="border-color: {{ $BORD }}">Detail</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Pagination info --}}
    <div class="mt-6 flex items-center justify-between text-sm text-slate-600">
      @php
        $from = ($apps->currentPage()-1)*$apps->perPage()+1;
        $to = min($apps->currentPage()*$apps->perPage(), $apps->total());
      @endphp
      <div>Menampilkan <span class="font-medium text-slate-900">{{ $from }}–{{ $to }}</span> dari
        <span class="font-medium text-slate-900">{{ $apps->total() }}</span> lamaran.</div>
      <div>{{ $apps->links() }}</div>
    </div>
  @else
    {{-- Empty state --}}
    <div class="mt-6 grid place-items-center rounded-2xl border border-dashed" style="border-color: {{ $BORD }}">
      <div class="w-full max-w-3xl text-center px-8 py-12 bg-white">
        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full border" style="border-color: {{ $BORD }}">
          <svg class="h-5 w-5 text-slate-500"><use href="#i-brief"/></svg>
        </div>
        <h3 class="mt-3 text-xl font-semibold text-slate-900">Belum ada lamaran</h3>
        <p class="mt-1 text-sm text-slate-600">Mulai dengan memilih lowongan yang tersedia.</p>
        <a href="{{ route('jobs.index') }}"
           class="mt-4 inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white"
           style="background: {{ $RED }}">Lihat Lowongan</a>
      </div>
    </div>
  @endif
</div>
@endsection
