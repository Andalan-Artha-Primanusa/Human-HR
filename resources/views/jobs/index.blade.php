{{-- resources/views/jobs/index.blade.php --}}
@extends('layouts.app', ['title' => 'Lowongan'])

@php
  // ===== Query helpers
  $qDivision = trim((string) request('division', ''));
  $qSite     = trim((string) request('site', ''));
  $qTerm     = trim((string) request('term', ''));
  $qType     = trim((string) request('type', ''));
  $qSort     = trim((string) request('sort', ''));
  $hasAny    = $qDivision || $qSite || $qTerm || $qType || $qSort;

  $rm = fn(string $key) => route('jobs.index', collect(request()->query())->except($key)->all());
  $resetUrl = route('jobs.index');
  $total = method_exists($jobs, 'total') ? $jobs->total() : $jobs->count();

  // ===== Ikon & warna per divisi (boleh kamu tambah)
  $deptIcons = [
    'HR'        => ['icon'=>'i-hr',        'bg'=>'bg-blue-50',  'fg'=>'text-blue-700',  'ring'=>'ring-blue-200'],
    'Plant'     => ['icon'=>'i-plant',     'bg'=>'bg-red-50',   'fg'=>'text-red-700',   'ring'=>'ring-red-200'],
    'SCM'       => ['icon'=>'i-scm',       'bg'=>'bg-red-50',   'fg'=>'text-red-700',   'ring'=>'ring-red-200'],
    'IT'        => ['icon'=>'i-it',        'bg'=>'bg-blue-50',  'fg'=>'text-blue-700',  'ring'=>'ring-blue-200'],
    'Finance'   => ['icon'=>'i-finance',   'bg'=>'bg-blue-50',  'fg'=>'text-blue-700',  'ring'=>'ring-blue-200'],
    'QA'        => ['icon'=>'i-qa',        'bg'=>'bg-red-50',   'fg'=>'text-red-700',   'ring'=>'ring-red-200'],
    'HSE'       => ['icon'=>'i-hse',       'bg'=>'bg-red-50',   'fg'=>'text-red-700',   'ring'=>'ring-red-200'],
    'GA'        => ['icon'=>'i-ga',        'bg'=>'bg-blue-50',  'fg'=>'text-blue-700',  'ring'=>'ring-blue-200'],
    'Legal'     => ['icon'=>'i-legal',     'bg'=>'bg-blue-50',  'fg'=>'text-blue-700',  'ring'=>'ring-blue-200'],
    'Marketing' => ['icon'=>'i-marketing', 'bg'=>'bg-blue-50',  'fg'=>'text-blue-700',  'ring'=>'ring-blue-200'],
    'Sales'     => ['icon'=>'i-sales',     'bg'=>'bg-blue-50',  'fg'=>'text-blue-700',  'ring'=>'ring-blue-200'],
    'R&D'       => ['icon'=>'i-rnd',       'bg'=>'bg-blue-50',  'fg'=>'text-blue-700',  'ring'=>'ring-blue-200'],
  ];
  $deptMeta = function (?string $div) use ($deptIcons) {
      $key = $div ? strtoupper(trim($div)) : '';
      // normalisasi beberapa alias
      $aliases = ['HRGA'=>'HR','HUMAN RESOURCE'=>'HR','HUMAN RESOURCES'=>'HR','PLANT ENGINEERING'=>'PLANT'];
      $key = $aliases[$key] ?? $key;
      return $deptIcons[$key] ?? ['icon'=>'i-briefcase','bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200'];
  };

  // Quick chips
  $chipsDiv  = array_keys($deptIcons);
  $chipsType = ['fulltime'=>'Full-time','contract'=>'Contract','intern'=>'Intern'];
@endphp

@section('content')

{{-- ===== SVG ICONS (sekali) ===== --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  {{-- basic --}}
  <symbol id="i-filter" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4"/></symbol>
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke-width="2" stroke-linecap="round"/></symbol>
  <symbol id="i-rotate" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M20 8A8 8 0 1 0 8 20"/></symbol>
  <symbol id="i-x" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6"/></symbol>
  <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" stroke-width="1.8"/></symbol>
  <symbol id="i-map" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 20l-5-2V6l5 2 6-2 5 2v12l-5-2-6 2zM14 4v14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M12 7v5l3 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 9l6 6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>

  {{-- department icons (simple) --}}
  <symbol id="i-hr" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="3" stroke-width="1.8"/><path d="M2 20a6 6 0 0 1 12 0" stroke-width="1.8"/><path d="M16 11h4M18 9v4" stroke-width="1.8" stroke-linecap="round"/></symbol>
  <symbol id="i-plant" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="10" width="18" height="10" rx="2" stroke-width="1.8"/><path d="M7 10V6l3 2V6l3 2V6l3 2" stroke-width="1.8" stroke-linecap="round"/></symbol>
  <symbol id="i-scm" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 16V8h10v8H3Z" stroke-width="1.8"/><path d="M13 13h4l4 3v3h-8v-6Z" stroke-width="1.8"/><circle cx="7" cy="19" r="1.5"/><circle cx="17" cy="19" r="1.5"/></symbol>
  <symbol id="i-it" viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="4" y="4" width="16" height="12" rx="2" stroke-width="1.8"/><path d="M8 20h8M10 16v4M14 16v4" stroke-width="1.8"/></symbol>
  <symbol id="i-finance" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 10h18M5 10V7l7-4 7 4v3M5 10v10h14V10" stroke-width="1.8"/></symbol>
  <symbol id="i-qa" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 12l4 4 8-8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-hse" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4Z" stroke-width="1.8"/></symbol>
  <symbol id="i-ga" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 21h18M6 21V7h12v14M9 10h2m2 0h2" stroke-width="1.8"/></symbol>
  <symbol id="i-legal" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3 4 7l8 4 8-4-8-4Z" stroke-width="1.8"/><path d="M6 10v4a6 6 0 0 0 12 0v-4" stroke-width="1.8"/></symbol>
  <symbol id="i-marketing" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 11h6l8-5v14l-8-5H3v-4Z" stroke-width="1.8"/><path d="M9 19v2" stroke-width="1.8"/></symbol>
  <symbol id="i-sales" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 12l4 4 8-8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="6" cy="6" r="2"/><circle cx="18" cy="6" r="2"/></symbol>
  <symbol id="i-rnd" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 3v5l-4 7a4 4 0 0 0 4 6h6a4 4 0 0 0 4-6l-4-7V3" stroke-width="1.8"/></symbol>
</svg>

{{-- ===== Container lebar ===== --}}
<div class="mx-auto w-full max-w-[1320px] px-4 md:px-6 lg:px-8">

  {{-- ===== Header ===== --}}
  <div class="mb-6 rounded-2xl border border-gray-200 bg-white shadow-sm">
    <div class="h-1.5 w-full rounded-t-2xl bg-blue-700"></div>
    <div class="p-4 md:p-6">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
          <div class="inline-flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-800">Lowongan</div>
          <h1 class="mt-2 text-2xl font-semibold text-slate-900">Lowongan Tersedia</h1>
          <p class="text-sm text-slate-600">Ada <span class="font-semibold text-blue-700">{{ $total }}</span> lowongan aktif untuk kamu jelajahi.</p>
        </div>

        {{-- Search --}}
        <form method="GET" action="{{ route('jobs.index') }}" class="w-full md:w-[560px]" role="search">
          <div class="relative">
            <input name="term" value="{{ $qTerm }}" placeholder="Cari judul, divisi, site, atau deskripsi…"
                   class="w-full rounded-xl border border-gray-200 py-2.5 pl-11 pr-32 text-sm text-slate-900 placeholder-slate-500 outline-none focus:ring-2 focus:ring-blue-700"/>
            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500"><svg class="h-5 w-5"><use href="#i-search"/></svg></span>
            <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2">
              @if($qTerm)
                <a href="{{ $rm('term') }}" class="rounded border border-gray-200 p-1.5 hover:bg-slate-50" aria-label="Hapus kata kunci"><svg class="h-4 w-4"><use href="#i-x"/></svg></a>
              @endif
              <button class="rounded-lg bg-blue-700 px-4 py-1.5 text-sm font-semibold text-white">Cari</button>
            </div>
          </div>
          @foreach(['division','site','type','sort'] as $keep)
            @if(request()->filled($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif
          @endforeach
        </form>
      </div>

      {{-- Actions --}}
      <div class="mt-4 flex flex-wrap items-center gap-2">
        <button id="btn-filter" type="button" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-slate-900 hover:bg-slate-50">
          <svg class="h-4 w-4"><use href="#i-filter"/></svg> Filter <svg class="h-4 w-4 opacity-70"><use href="#i-chevron"/></svg>
        </button>
        <form method="GET" action="{{ $resetUrl }}">
          <button class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-3 py-1.5 text-sm text-slate-900 hover:bg-slate-50"><svg class="h-4 w-4"><use href="#i-rotate"/></svg> Reset</button>
        </form>
        <form method="GET" class="ml-auto flex items-center gap-2">
          @foreach(['division','site','term','type'] as $keep) @if(request()->filled($keep)) <input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}"> @endif @endforeach
          <label for="sort" class="text-xs text-slate-500">Urutkan</label>
          <select id="sort" name="sort" class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm">
            <option value="">Terbaru</option>
            <option value="oldest" @selected($qSort==='oldest')>Terlama</option>
            <option value="title" @selected($qSort==='title')>Judul (A–Z)</option>
          </select>
          <button class="rounded-lg bg-red-600 px-3 py-1.5 text-sm font-medium text-white">Terapkan</button>
        </form>
      </div>

      {{-- Chips aktif --}}
      @if($hasAny)
        <div class="mt-3 flex flex-wrap gap-2">
          @if($qDivision)<a href="{{ $rm('division') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-xs text-slate-900">Divisi: <b>{{ $qDivision }}</b> <svg class="h-3.5 w-3.5"><use href="#i-x"/></svg></a>@endif
          @if($qSite)<a href="{{ $rm('site') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-xs text-slate-900">Site: <b>{{ $qSite }}</b> <svg class="h-3.5 w-3.5"><use href="#i-x"/></svg></a>@endif
          @if($qType)<a href="{{ $rm('type') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-xs text-slate-900">Tipe: <b>{{ strtoupper($qType) }}</b> <svg class="h-3.5 w-3.5"><use href="#i-x"/></svg></a>@endif
          @if($qTerm)<a href="{{ $rm('term') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-xs text-slate-900">Keyword: <b>{{ $qTerm }}</b> <svg class="h-3.5 w-3.5"><use href="#i-x"/></svg></a>@endif
          @if($qSort)<a href="{{ $rm('sort') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 px-3 py-1 text-xs text-slate-900">Sort: <b>{{ strtoupper($qSort) }}</b> <svg class="h-3.5 w-3.5"><use href="#i-x"/></svg></a>@endif
        </div>
      @endif
    </div>
  </div>

  {{-- ===== Quick Filters (dengan ikon) ===== --}}
  <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
    @foreach($chipsDiv as $c)
      @php $meta = $deptMeta($c); @endphp
      <a href="{{ route('jobs.index', array_merge(request()->except('division'), ['division'=>$c])) }}"
         class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:border-blue-300 hover:bg-blue-50">
        <span class="grid h-7 w-7 place-items-center rounded-full ring-1 {{ $meta['bg'] }} {{ $meta['fg'] }} {{ $meta['ring'] }}">
          <svg class="h-4 w-4"><use href="#{{ $meta['icon'] }}"/></svg>
        </span>{{ $c }}
      </a>
    @endforeach
    @foreach($chipsType as $k=>$label)
      <a href="{{ route('jobs.index', array_merge(request()->except('type'), ['type'=>$k])) }}"
         class="flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 hover:border-red-300 hover:bg-red-50">
        <span class="grid h-7 w-7 place-items-center rounded-full ring-1 bg-red-50 text-red-700 ring-red-200">
          <svg class="h-4 w-4"><use href="#i-briefcase"/></svg>
        </span>{{ $label }}
      </a>
    @endforeach
  </div>

  {{-- ===== Panel Filter (toggle) ===== --}}
  <div id="filter-panel" class="mb-6 hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm md:p-6">
    <form method="GET" class="grid gap-4 md:grid-cols-4">
      <div>
        <label for="division" class="mb-1 block text-xs font-medium text-slate-600">Divisi</label>
        <input id="division" name="division" value="{{ $qDivision }}" placeholder="Plant / SCM / HRGA"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-700"/>
      </div>
      <div>
        <label for="site" class="mb-1 block text-xs font-medium text-slate-600">Site</label>
        <input id="site" name="site" value="{{ $qSite }}" placeholder="DBK / POS / SBS"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-700"/>
      </div>
      <div>
        <label for="type" class="mb-1 block text-xs font-medium text-slate-600">Tipe Kerja</label>
        <select id="type" name="type" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
          <option value="">—</option>
          <option value="fulltime" @selected($qType==='fulltime')>Full-time</option>
          <option value="contract" @selected($qType==='contract')>Contract</option>
          <option value="intern"   @selected($qType==='intern')>Intern</option>
        </select>
      </div>
      <div>
        <label for="term" class="mb-1 block text-xs font-medium text-slate-600">Kata Kunci</label>
        <input id="term" name="term" value="{{ $qTerm }}" placeholder="Judul/Deskripsi"
               class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-700"/>
      </div>
      @if($qSort) <input type="hidden" name="sort" value="{{ $qSort }}"> @endif
      <div class="md:col-span-4 mt-1 flex items-center gap-2">
        <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white">Terapkan Filter</button>
        <a href="{{ $resetUrl }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-slate-900 hover:bg-slate-50">Reset</a>
      </div>
    </form>
  </div>

  {{-- ===== GRID KARTU ===== --}}
  @if($jobs->count())
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
      @foreach($jobs as $idx => $job)
        @php
          $type     = strtoupper($job->employment_type ?? '-');
          $site     = $job->site?->code ?? '—';
          $division = $job->division ?? '—';
          $excerpt  = \Illuminate\Support\Str::limit(strip_tags($job->description), 140);
          $meta     = $deptMeta($division);
          $accent   = $idx % 2 === 0 ? 'bg-blue-700' : 'bg-red-600';
        @endphp

        <div class="group relative rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md">
          <span class="absolute left-0 top-0 h-full w-1.5 rounded-l-2xl {{ $accent }}"></span>
          <div class="p-5">
            <div class="flex items-start gap-3">
              <span class="grid h-10 w-10 place-items-center rounded-full ring-1 {{ $meta['bg'] }} {{ $meta['fg'] }} {{ $meta['ring'] }}">
                <svg class="h-5 w-5"><use href="#{{ $meta['icon'] }}"/></svg>
              </span>
              <div class="min-w-0 flex-1">
                <a href="{{ route('jobs.show', $job) }}" class="block truncate text-[17px] font-semibold text-slate-900 hover:underline">
                  {{ $job->title }}
                </a>
                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-600">
                  <span class="inline-flex items-center gap-1"><svg class="h-4 w-4"><use href="#i-briefcase"/></svg>{{ $division }}</span>
                  <span class="inline-flex items-center gap-1"><svg class="h-4 w-4"><use href="#i-map"/></svg>{{ $site }}</span>
                  <span class="inline-flex items-center gap-1"><svg class="h-4 w-4"><use href="#i-clock"/></svg>{{ optional($job->created_at)->diffForHumans() }}</span>
                </div>
              </div>
              <span class="inline-flex h-6 items-center rounded bg-blue-700 px-2 text-[11px] font-semibold text-white">{{ $type }}</span>
            </div>

            @if($excerpt)
              <p class="mt-3 line-clamp-3 text-sm text-slate-700">{{ $excerpt }}</p>
            @endif

            <div class="mt-4 flex items-center justify-between text-sm">
              <span class="inline-flex items-center gap-1 text-slate-500">
                <svg class="h-4 w-4"><use href="#i-briefcase"/></svg> Openings: {{ (int) $job->openings }}
              </span>
              <a href="{{ route('jobs.show', $job) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-slate-900 hover:bg-slate-50">Detail</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{-- Pagination info --}}
    <div class="mt-6 flex items-center justify-between text-sm text-slate-600">
      <div>
        @php
          $from = ($jobs->currentPage() - 1) * $jobs->perPage() + 1;
          $to   = min($jobs->currentPage() * $jobs->perPage(), $total);
        @endphp
        Menampilkan <span class="font-medium text-slate-900">{{ $from }}–{{ $to }}</span> dari
        <span class="font-medium text-slate-900">{{ $total }}</span> lowongan.
      </div>
      <div>{{ $jobs->links() }}</div>
    </div>
  @else
    <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center shadow-sm">
      <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full border border-gray-200">
        <svg class="h-5 w-5 text-slate-500"><use href="#i-filter"/></svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-900">Belum ada hasil</h3>
      <p class="mt-1 text-sm text-slate-600">Coba ubah filter atau reset untuk melihat semua lowongan.</p>
      <div class="mt-4 flex items-center justify-center gap-2">
        <a href="{{ $resetUrl }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white">Reset Filter</a>
        <button id="btn-filter-empty" class="rounded-lg border border-gray-200 px-4 py-2 text-sm text-slate-900 hover:bg-slate-50">Buka Filter</button>
      </div>
    </div>
  @endif
</div>

{{-- ===== JS ===== --}}
<script>
  (function(){
    const pnl = document.getElementById('filter-panel');
    document.getElementById('btn-filter')?.addEventListener('click', ()=>pnl?.classList.toggle('hidden'));
    document.getElementById('btn-filter-empty')?.addEventListener('click', ()=>{
      pnl?.classList.remove('hidden');
      window.scrollTo({ top: pnl.getBoundingClientRect().top + window.scrollY - 80, behavior:'smooth' });
    });
  })();
</script>
@endsection
