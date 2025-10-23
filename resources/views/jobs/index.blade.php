{{-- resources/views/jobs/index.blade.php --}}
@extends('layouts.app', ['title' => 'Lowongan'])

@php
use Illuminate\Support\Str;

// ===== Query helpers (whitelisted) =====
$qDivision = trim((string) request('division', ''));
$qSite = trim((string) request('site', ''));
$qCompany = trim((string) request('company', '')); // company code
$qTerm = trim((string) request('term', ''));
$qSort = trim((string) request('sort', ''));
$hasAny = $qDivision || $qSite || $qCompany || $qTerm || $qSort;

// Keep-only params antar form/link
$keepParams = array_filter(
request()->only(['division','site','company','term','sort']),
fn ($v) => filled($v)
);

// Hapus satu param
$rm = fn(string $key) => route('jobs.index', collect($keepParams)->except($key)->all());
$resetUrl = route('jobs.index');
$total = method_exists($jobs, 'total') ? (int) $jobs->total() : (int) $jobs->count();

// ===== Ikon & warna per divisi (base: biru; aksen lain halus) =====
$deptIcons = [
'HR' => ['icon'=>'i-hr', 'bg'=>'bg-sky-50', 'fg'=>'text-sky-700', 'ring'=>'ring-sky-200'],
'Plant' => ['icon'=>'i-plant', 'bg'=>'bg-amber-50', 'fg'=>'text-amber-700', 'ring'=>'ring-amber-200'],
'SCM' => ['icon'=>'i-scm', 'bg'=>'bg-indigo-50', 'fg'=>'text-indigo-700', 'ring'=>'ring-indigo-200'],
'IT' => ['icon'=>'i-it', 'bg'=>'bg-blue-50', 'fg'=>'text-blue-700', 'ring'=>'ring-blue-200'],
'Finance' => ['icon'=>'i-finance', 'bg'=>'bg-slate-50', 'fg'=>'text-slate-700', 'ring'=>'ring-slate-200'],
'QA' => ['icon'=>'i-qa', 'bg'=>'bg-emerald-50', 'fg'=>'text-emerald-700', 'ring'=>'ring-emerald-200'],
'HSE' => ['icon'=>'i-hse', 'bg'=>'bg-cyan-50', 'fg'=>'text-cyan-700', 'ring'=>'ring-cyan-200'],
'GA' => ['icon'=>'i-ga', 'bg'=>'bg-fuchsia-50', 'fg'=>'text-fuchsia-700', 'ring'=>'ring-fuchsia-200'],
'Legal' => ['icon'=>'i-legal', 'bg'=>'bg-violet-50', 'fg'=>'text-violet-700', 'ring'=>'ring-violet-200'],
'Marketing' => ['icon'=>'i-marketing', 'bg'=>'bg-orange-50', 'fg'=>'text-orange-700', 'ring'=>'ring-orange-200'],
'Sales' => ['icon'=>'i-sales', 'bg'=>'bg-teal-50', 'fg'=>'text-teal-700', 'ring'=>'ring-teal-200'],
'R&D' => ['icon'=>'i-rnd', 'bg'=>'bg-rose-50', 'fg'=>'text-rose-700', 'ring'=>'ring-rose-200'],
];
$deptMeta = function (?string $div) use ($deptIcons) {
$key = $div ? strtoupper(trim($div)) : '';
$aliases = ['HRGA'=>'HR','HUMAN RESOURCE'=>'HR','HUMAN RESOURCES'=>'HR','PLANT ENGINEERING'=>'PLANT'];
$key = $aliases[$key] ?? $key;
return $deptIcons[$key] ?? ['icon'=>'i-briefcase','bg'=>'bg-slate-50','fg'=>'text-slate-700','ring'=>'ring-slate-200'];
};

// rail warna kiri item (biru dominan + aksen lembut)
$railColors = ['border-blue-500','border-sky-400','border-indigo-400','border-emerald-400','border-amber-400'];

$extractSkills = function ($job) {
$attrs = $job->getAttributes();
$raw = $job->skills
?? ($attrs['skills'] ?? null)
?? ($job->tags ?? ($attrs['tags'] ?? null))
?? ($job->keywords ?? ($attrs['keywords'] ?? null));

$list = collect();

if (is_array($raw)) {
$list = collect($raw);
} elseif (is_string($raw)) {
$raw = trim($raw);
if ($raw !== '') {
$decoded = json_decode($raw, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
$list = collect($decoded);
} else {
$list = collect(preg_split('/\s*[,;|]\s*/', $raw));
}
}
}

return $list
->map(function ($v) {
if (is_string($v)) return trim($v);
if (is_array($v)) return trim((string) ($v['name'] ?? $v['label'] ?? ''));
return '';
})
->filter()
->unique()
->take(10)
->values();
};

/** Helper audit: nama pembuat & waktu */
$auditWho = function ($job) {
$creatorName = $job->creator->name ?? $job->created_by_name ?? $job->created_by ?? null;
$updaterName = $job->updater->name ?? $job->updated_by_name ?? $job->updated_by ?? null;

return [
'creator' => $creatorName ? Str::limit($creatorName, 40) : null,
'updater' => $updaterName ? Str::limit($updaterName, 40) : null,
];
};
@endphp

@section('content')

@once
{{-- ===== SVG ICONS (once) ===== --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
  {{-- basic --}}
  <symbol id="i-filter" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
  </symbol>
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <circle cx="11" cy="11" r="7" stroke-width="2" />
    <path d="M21 21l-4.3-4.3" stroke-width="2" stroke-linecap="round" />
  </symbol>
  <symbol id="i-rotate" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M20 8A8 8 0 1 0 8 20" />
  </symbol>
  <symbol id="i-x" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M6 18L18 6" />
  </symbol>
  <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z" />
    <path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" stroke-width="1.8" />
  </symbol>
  <symbol id="i-map" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M9 20l-5-2V6l5 2 6-2 5 2v12l-5-2-6 2zM14 4v14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <circle cx="12" cy="12" r="9" stroke-width="2" />
    <path d="M12 7v5l3 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </symbol>
  <symbol id="i-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M6 9l6 6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </symbol>
  {{-- department --}}
  <symbol id="i-hr" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <circle cx="8" cy="8" r="3" stroke-width="1.8" />
    <path d="M2 20a6 6 0 0 1 12 0" stroke-width="1.8" />
    <path d="M16 11h4M18 9v4" stroke-width="1.8" stroke-linecap="round" />
  </symbol>
  <symbol id="i-plant" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <rect x="3" y="10" width="18" height="10" rx="2" stroke-width="1.8" />
    <path d="M7 10V6l3 2V6l3 2V6l3 2" stroke-width="1.8" stroke-linecap="round" />
  </symbol>
  <symbol id="i-scm" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M3 16V8h10v8H3Z" stroke-width="1.8" />
    <path d="M13 13h4l4 3v3h-8v-6Z" stroke-width="1.8" />
    <circle cx="7" cy="19" r="1.5" />
    <circle cx="17" cy="19" r="1.5" />
  </symbol>
  <symbol id="i-it" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <rect x="4" y="4" width="16" height="12" rx="2" stroke-width="1.8" />
    <path d="M8 20h8M10 16v4M14 16v4" stroke-width="1.8" />
  </symbol>
  <symbol id="i-finance" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M3 10h18M5 10V7l7-4 7 4v3M5 10v10h14V10" stroke-width="1.8" />
  </symbol>
  <symbol id="i-qa" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M4 12l4 4 8-8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
  </symbol>
  <symbol id="i-hse" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4Z" stroke-width="1.8" />
  </symbol>
  <symbol id="i-ga" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M3 21h18M6 21V7h12v14M9 10h2m2 0h2" stroke-width="1.8" />
  </symbol>
  <symbol id="i-legal" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M12 3 4 7l8 4 8-4-8-4Z" stroke-width="1.8" />
    <path d="M6 10v4a6 6 0 0 0 12 0v-4" stroke-width="1.8" />
  </symbol>
  <symbol id="i-marketing" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M3 11h6l8-5v14l-8-5H3v-4Z" stroke-width="1.8" />
    <path d="M9 19v2" stroke-width="1.8" />
  </symbol>
  <symbol id="i-sales" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M8 12l4 4 8-8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
    <circle cx="6" cy="6" r="2" />
    <circle cx="18" cy="6" r="2" />
  </symbol>
  <symbol id="i-rnd" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M9 3v5l-4 7a4 4 0 0 0 4 6h6a4 4 0 0 0 4-6l-4-7V3" stroke-width="1.8" />
  </symbol>
</svg>
@endonce

<div class="mx-auto w-full max-w-[1320px] px-4 md:px-6 lg:px-8">
  {{-- ===== Header: biru + aksen merah (tanpa gradient) ===== --}}
  <section class="mb-4 rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="bg-blue-700 text-white">
      <div class="p-4 md:p-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="min-w-0">
          <h1 class="text-xl font-semibold">Pekerjaan yang direkomendasikan untuk kamu</h1>
          <p class="text-sm text-blue-100">Berdasarkan profil dan lamaran kamu</p>
        </div>
{{-- Search (merah) + tombol Filter DI LUAR input --}}
<div class="w-full md:w-[560px] flex items-stretch gap-2">
  {{-- FORM SEARCH --}}
  <form method="GET" action="{{ route('jobs.index') }}" role="search" aria-label="Pencarian Lowongan" class="flex-1">
    <label for="job-search" class="sr-only">Cari lowongan</label>
    <div class="relative">
      {{-- ikon search kiri --}}
      <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-white/90" aria-hidden="true">
        <use href="#i-search" />
      </svg>

      <input
        id="job-search"
        name="term"
        value="{{ e($qTerm) }}"
        placeholder="Cari judul, divisi, site, atau company…"
        class="w-full rounded-xl border border-white/30 bg-white/95 py-2.5 pl-9 pr-28 text-sm text-slate-900 placeholder-slate-500 outline-none
               focus:ring-2 focus:ring-red-300 focus:border-red-400"
        autocomplete="off"
      />

      {{-- tombol submit + clear di dalam area input (kanan) --}}
      <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
        @if($qTerm)
          <a href="{{ $rm('term') }}"
             class="rounded-md border border-white/30 bg-white/0 p-1.5 hover:bg-white/10"
             aria-label="Hapus kata kunci">
            <svg class="h-4 w-4 text-white"><use href="#i-x" /></svg>
          </a>
        @endif
        <button type="submit"
          class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">
          <svg class="h-4 w-4"><use href="#i-search" /></svg>
          <span>Cari</span>
        </button>
      </div>
    </div>

    {{-- keep params --}}
    @foreach(['division','site','company','sort'] as $keep)
      @if(!empty($keepParams[$keep]))
        <input type="hidden" name="{{ $keep }}" value="{{ e($keepParams[$keep]) }}">
      @endif
    @endforeach
  </form>

  {{-- TOMBOL FILTER DI LUAR (merah) --}}
  <button id="btn-filter" type="button"
    class="shrink-0 inline-flex items-center gap-1 rounded-xl border border-red-300 bg-red-600/10 px-4 py-2 text-sm font-semibold text-white hover:bg-red-600/20 focus:outline-none focus:ring-2 focus:ring-red-300"
    aria-expanded="false" aria-controls="filter-panel">
    <svg class="h-4 w-4"><use href="#i-filter" /></svg>
    <span>Filter</span>
  </button>
</div>

      </div>
      <div class="h-1 w-full bg-red-600"></div>
    </div>

    {{-- Panel Filter --}}
    <div id="filter-panel" class="hidden border-t border-slate-200 bg-white p-4 md:p-5">
      <form method="GET" class="grid gap-3 md:grid-cols-6" aria-label="Filter Lowongan">
        <div class="md:col-span-2">
          <label class="mb-1 block text-xs font-semibold text-slate-700 uppercase">Divisi</label>
          <input name="division" value="{{ e($qDivision) }}" placeholder="Plant / SCM / HRGA" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600" />
        </div>
        <div class="md:col-span-1">
          <label class="mb-1 block text-xs font-semibold text-slate-700 uppercase">Site</label>
          <input name="site" value="{{ e($qSite) }}" placeholder="DBK / POS / SBS" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600" />
        </div>
        <div class="md:col-span-2">
          <label class="mb-1 block text-xs font-semibold text-slate-700 uppercase">Company (Code)</label>
          <input name="company" value="{{ e($qCompany) }}" placeholder="ANDALAN / AGR" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600" />
        </div>
        <div class="md:col-span-3">
          <label class="mb-1 block text-xs font-semibold text-slate-700 uppercase">Kata Kunci</label>
          <input name="term" value="{{ e($qTerm) }}" placeholder="Judul/Deskripsi" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-blue-600" />
        </div>
        @if($qSort) <input type="hidden" name="sort" value="{{ e($qSort) }}"> @endif
        <div class="md:col-span-6 mt-1 flex flex-wrap items-center gap-2">
          <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Terapkan</button>
          <a href="{{ $resetUrl }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 hover:bg-slate-50">Reset</a>
        </div>
      </form>
    </div>
  </section>

  {{-- ===== Layout: kiri list + kanan detail ===== --}}
  @if($jobs->count())
  @php $firstId = optional($jobs->first())->id; @endphp

  <section
    x-data="{
        open: @js($firstId),
        select(id){ this.open = id; const d=document.getElementById('detail-'+id); if(!d) return;
          const pr = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
          const top = d.getBoundingClientRect().top + window.scrollY - 80;
          window.scrollTo({top, behavior: pr ? 'auto' : 'smooth'}); }
      }"
    class="grid gap-4 md:grid-cols-[420px,1fr]">

    {{-- LEFT LIST --}}
    <aside class="rounded-2xl border border-slate-200 bg-white shadow-sm md:sticky md:top-4 md:self-start">
      {{-- Header list --}}
      <div class="flex items-center justify-between gap-3 border-b border-slate-200 p-3">
        <div class="min-w-0">
          <div class="text-[12px] font-medium text-slate-500">Hasil</div>
          <div class="text-sm font-semibold text-slate-900 leading-tight">{{ $total }} lowongan ditemukan</div>
        </div>

        {{-- Sort (keep params) --}}
        <form method="GET" class="flex items-center gap-2" aria-label="Urutkan">
          @foreach(['division','site','company','term'] as $keep)
          @if(!empty($keepParams[$keep])) <input type="hidden" name="{{ $keep }}" value="{{ e($keepParams[$keep]) }}"> @endif
          @endforeach

          <div class="relative">
            <select name="sort"
              class="h-9 appearance-none rounded-lg border border-slate-200 pl-3 pr-8 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-600/30 focus:border-blue-600/40 bg-white">
              <option value="">Paling Relevan</option>
              <option value="oldest" @selected($qSort==='oldest' )>Terlama</option>
              <option value="title" @selected($qSort==='title' )>Judul (A–Z)</option>
            </select>
            <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-500">
              <use href="#i-chevron" />
            </svg>
          </div>

          <button class="h-9 rounded-md bg-blue-700 px-3 text-xs font-semibold text-white hover:bg-blue-800">OK</button>
        </form>
      </div>

      {{-- List items --}}
      <ul class="divide-y divide-slate-200 max-h-[70vh] overflow-y-auto">
        @foreach($jobs as $idx => $job)
        @php
        $typeRaw = $job->employment_type ?? '';
        $type = strtoupper($typeRaw ?: '-');
        $typeSlug = \Illuminate\Support\Str::of($typeRaw)->lower()->value();
        $badgeClr = match ($typeSlug) {
        'contract','kontrak' => 'bg-amber-600',
        'intern','magang' => 'bg-emerald-600',
        default => 'bg-blue-700',
        };

        $companyId = $job->company_id;
        $code = $job->code;
        $title = $job->title;
        $division = $job->division;
        $level = $job->level;
        $status = $job->status;
        $openings = (int) $job->openings;
        $skillsCol = $extractSkills($job);
        $createdAt = $job->created_at?->format('Y-m-d H:i');
        $year = $job->created_at?->format('Y');

        // Site = nama (fallback code)
        $siteLabel = $job->site->name
        ?? $job->site_name
        ?? $job->getAttribute('site_name')
        ?? $job->site->code
        ?? $job->site_code
        ?? $job->getAttribute('site_code')
        ?? '—';

        $meta = $deptMeta($division);
        $rail = $railColors[$idx % count($railColors)];
        $who = $auditWho($job);

        $mon = strtoupper(\Illuminate\Support\Str::substr((string)($division ?: $code ?: 'JD'),0,2));
        @endphp

        <li>
          <button type="button"
            @click="select('{{ $job->id }}')"
            :class="open==='{{ $job->id }}' ? 'bg-slate-50 ring-1 ring-slate-200' : 'bg-white hover:bg-slate-50'"
            class="group relative w-full text-left p-3 transition">
            {{-- rail kiri --}}
            <span class="absolute inset-y-0 left-0 w-1.5 rounded-r {{ $rail }}"></span>

            <div class="flex items-start gap-3">
              {{-- avatar/icon divisi --}}
              <span class="grid h-11 w-11 place-items-center rounded-xl ring-1 {{ $meta['bg'] }} {{ $meta['fg'] }} {{ $meta['ring'] }}">
                @if($meta['icon']==='i-briefcase')
                <span class="text-xs font-bold">{{ $mon }}</span>
                @else
                <svg class="h-5 w-5">
                  <use href="#{{ $meta['icon'] }}" />
                </svg>
                @endif
              </span>

              <div class="min-w-0 flex-1">
                {{-- judul + badges (CODE di atas, TYPE) --}}
                <div class="flex items-start justify-between gap-3">
                  <p class="truncate text-[15px] font-semibold text-slate-900">{{ e($title) }}</p>
                  <div class="shrink-0 flex items-center gap-2">
                    @if($code)
                    <span class="inline-flex items-center rounded border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                      {{ e($code) }}
                    </span>
                    @endif
                    <span class="inline-flex h-6 items-center rounded px-2 text-[11px] font-semibold text-white {{ $badgeClr }}">
                      {{ e($type) }}
                    </span>
                  </div>
                </div>

                {{-- META (icons-only, 2 kolom) --}}
                <div class="mt-1.5 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1.5 text-[12px]">
                  {{-- Company --}}
                  <div class="flex items-center gap-1.5" title="Company">
                    <svg class="h-4 w-4 text-slate-600">
                      <use href="#i-briefcase" />
                    </svg>
                    <span class="sr-only">Company:</span>
                    <span class="truncate max-w-[200px] text-slate-800">{{ e($companyId) }}</span>
                  </div>

                  {{-- Site (nama) --}}
                  <div class="flex items-center gap-1.5" title="Site">
                    <svg class="h-4 w-4 text-slate-600">
                      <use href="#i-map" />
                    </svg>
                    <span class="sr-only">Site:</span>
                    <span class="truncate max-w-[200px] text-slate-800">{{ e($siteLabel) }}</span>
                  </div>

                  {{-- Division --}}
                  <div class="flex items-center gap-1.5" title="Division">
                    <svg class="h-4 w-4 text-slate-600">
                      <use href="#i-hr" />
                    </svg>
                    <span class="sr-only">Division:</span>
                    <span class="truncate max-w-[200px] text-slate-800">{{ e($division ?: '—') }}</span>
                  </div>

                  {{-- Level --}}
                  <div class="flex items-center gap-1.5" title="Level">
                    <svg class="h-4 w-4 text-slate-600">
                      <use href="#i-clock" />
                    </svg>
                    <span class="sr-only">Level:</span>
                    <span class="truncate max-w-[200px] text-slate-800">{{ e($level ?: '—') }}</span>
                  </div>

                  {{-- Openings --}}
                  <div class="flex items-center gap-1.5" title="Openings">
                    <svg class="h-4 w-4 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                      <circle cx="10" cy="8" r="3.2" stroke-width="1.8" />
                      <path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                      <path d="M16.5 3.8a3.2 3.2 0 1 1 0 6.4" stroke-width="1.8" />
                    </svg>
                    <span class="sr-only">Openings:</span>
                    <span class="text-slate-800">{{ $openings }}</span>
                  </div>

                  {{-- Status: pill rounded seragam --}}
                  <div class="flex items-center gap-1.5" title="Status">
                    <span class="sr-only">Status:</span>
                    @if(\Illuminate\Support\Str::lower((string)$status) === 'open')
                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-500/10 px-2 py-0.5 text-[10.5px] font-semibold uppercase tracking-wide text-emerald-700">
                      OPEN{{ $year ? ' '.$year : '' }}
                    </span>
                    @else
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10.5px] font-semibold uppercase tracking-wide text-slate-700">
                      {{ strtoupper((string)($status ?: '—')) }}
                    </span>
                    @endif
                  </div>
                </div>

                {{-- skills chips (max 4) --}}
                @if($skillsCol->isNotEmpty())
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                  @foreach($skillsCol->take(4) as $sk)
                  <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] text-slate-700">{{ e($sk) }}</span>
                  @endforeach
                  @if($skillsCol->count() > 4)
                  <span class="text-[10px] text-slate-500">+{{ $skillsCol->count() - 4 }} lagi</span>
                  @endif
                </div>
                @endif

                {{-- footer kecil: only who + created_at --}}
                <div class="mt-2 flex items-center justify-between">
                  <span class="text-[10.5px] text-slate-500">
                    @if(($who['creator'] ?? null) || ($who['updater'] ?? null))
                    @if($who['creator'] ?? null) by {{ e($who['creator']) }} @endif
                    @if($who['updater'] ?? null) (upd {{ e($who['updater']) }}) @endif
                    @endif
                  </span>
                  @if($createdAt)
                  <span class="inline-flex items-center gap-1 text-[11px] text-slate-500">
                    <svg class="h-3.5 w-3.5 text-slate-600">
                      <use href="#i-clock" />
                    </svg>
                    {{ $createdAt }}
                  </span>
                  @endif
                </div>
              </div>

              {{-- chevron --}}
              <svg class="mt-1 h-4 w-4 shrink-0 text-slate-400 transition group-hover:translate-x-0.5">
                <use href="#i-chevron" />
              </svg>
            </div>
          </button>
        </li>
        @endforeach
      </ul>
    </aside>

    {{-- RIGHT DETAIL --}}
    <div class="space-y-5">

      @once
      {{-- Sprite ikon seperlunya --}}
      <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
        <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="3" y="7" width="18" height="13" rx="2" stroke-width="2" />
          <path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" stroke-width="2" />
        </symbol>
        <symbol id="i-map" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M9 5l6 2 6-2v14l-6 2-6-2-6 2V7l6-2z" stroke-width="2" stroke-linejoin="round" />
          <path d="M9 5v14M15 7v14" stroke-width="2" />
        </symbol>
        <symbol id="i-calendar" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2" />
          <path d="M16 2v4M8 2v4M3 10h18" stroke-width="2" stroke-linecap="round" />
        </symbol>
        <symbol id="i-users" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          <circle cx="10" cy="8" r="3" stroke-width="2" />
          <path d="M22 21v-2a4 4 0 0 0-3-3.87" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          <path d="M16.5 3.8a3.2 3.2 0 1 1 0 6.4" stroke-width="2" />
        </symbol>
        <symbol id="i-chip" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <rect x="3" y="7" width="18" height="10" rx="5" stroke-width="2" />
        </symbol>
      </svg>
      @endonce

      @foreach($jobs as $idx => $job)
      @php
      $typeRaw = $job->employment_type ?? '';
      $type = strtoupper($typeRaw ?: '-');
      $typeSlug = \Illuminate\Support\Str::of($typeRaw)->lower()->value();
      $badgeClr = match ($typeSlug) {
      'contract','kontrak' => 'bg-amber-600',
      'intern','magang' => 'bg-emerald-600',
      default => 'bg-blue-700',
      };

      $meta = $deptMeta($job->division);
      $skills = $extractSkills($job);
      $who = $auditWho($job);

      // Site label (nama > code > fallback)
      $siteLabel = $job->site->name
      ?? $job->site_name
      ?? $job->getAttribute('site_name')
      ?? $job->site->code
      ?? $job->site_code
      ?? $job->getAttribute('site_code')
      ?? '—';

      // Keywords: robust (array / JSON / string)
      $kwRaw = $job->getAttribute('keywords');
      if (is_array($kwRaw)) {
      $kwList = $kwRaw;
      } else {
      $kwStr = trim((string) $kwRaw);
      if ($kwStr === '') {
      $kwList = [];
      } else {
      $dec = json_decode($kwStr, true);
      $kwList = is_array($dec) ? $dec : array_filter(array_map('trim', preg_split('/\s*[,;|]\s*/', $kwStr) ?: []));
      }
      }
      @endphp

      <article
        x-show="open==='{{ $job->id }}'"
        x-cloak
        id="detail-{{ $job->id }}"
        class="rounded-2xl border border-slate-200 bg-white shadow-sm ring-1 ring-transparent transition hover:border-slate-300">
        <div class="p-5 md:p-6">
          {{-- HEADER --}}
          <div class="flex items-start gap-4">
            <span class="grid h-12 w-12 place-items-center rounded-full ring-1 {{ $meta['bg'] }} {{ $meta['fg'] }} {{ $meta['ring'] }}">
              <svg class="h-6 w-6">
                <use href="#{{ $meta['icon'] }}" />
              </svg>
            </span>

            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <h2 class="truncate text-xl font-semibold text-slate-900">{{ e($job->title) }}</h2>
                <span class="inline-flex h-6 shrink-0 items-center rounded px-2 text-[11px] font-semibold text-white {{ $badgeClr }}">{{ e($type) }}</span>
              </div>
              <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                @php $isOpen = \Illuminate\Support\Str::lower((string)$job->status) === 'open'; @endphp
                @if($isOpen)
                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-500/10 px-2 py-0.5 text-[10.5px] font-semibold uppercase tracking-wide text-emerald-700">
                  OPEN
                </span>
                @else
                <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10.5px] font-semibold uppercase tracking-wide text-slate-700">
                  {{ strtoupper((string)($job->status ?: '—')) }}
                </span>
                @endif
              </div>

            </div>

            <div class="flex shrink-0 items-center gap-2">
              <a href="{{ route('jobs.show', $job) }}?apply=1#apply"
                class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">
                Lamar
              </a>
            </div>
          </div>

          {{-- META BAR --}}
          <div class="mt-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-3 md:grid-cols-3">
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4 text-slate-500">
                <use href="#i-map" />
              </svg>
              <div class="truncate">
                <p class="text-[11px] uppercase tracking-wide text-slate-500">Lokasi</p>
                <p class="truncate text-sm font-medium text-slate-800">{{ e($siteLabel) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4 text-slate-500">
                <use href="#i-briefcase" />
              </svg>
              <div class="truncate">
                <p class="text-[11px] uppercase tracking-wide text-slate-500">Perusahaan</p>
                <p class="truncate text-sm font-medium text-slate-800">{{ e($job->company_id ?? '—') }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2">
              <svg class="h-4 w-4 text-slate-500">
                <use href="#i-users" />
              </svg>
              <div>
                <p class="text-[11px] uppercase tracking-wide text-slate-500">Kebutuhan</p>
                <p class="text-sm font-medium text-slate-800">{{ (int) $job->openings }} orang</p>
              </div>
            </div>
          </div>

          {{-- SKILLS (DINAIKKAN) --}}
          <div class="mt-5">
            <div class="mb-1 flex items-center gap-2 text-sm font-semibold text-slate-900">
              <svg class="h-4 w-4 text-slate-600">
                <use href="#i-chip" />
              </svg>
              <span>Skills Utama</span>
            </div>
            @if($skills->isEmpty())
            <p class="text-sm text-slate-600">—</p>
            @else
            <div class="flex flex-wrap gap-1.5">
              @foreach($skills as $sk)
              @php $isOdd = $loop->iteration % 2 === 1; @endphp
              <span class="rounded-full border px-2.5 py-1 text-xs shadow-sm
                        {{ $isOdd
                            ? 'border-blue-200 bg-blue-50 text-blue-700'
                            : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                {{ e($sk) }}
              </span>
              @endforeach
            </div>
            @endif
          </div>

          {{-- KEYWORDS: chip hijau --}}
          <div class="mt-3">
            <div class="mb-1 text-sm font-semibold text-slate-900">Keywords</div>
            @if(empty($kwList))
            <p class="text-sm text-slate-600">—</p>
            @else
            <div class="flex flex-wrap gap-1.5">
              @foreach($kwList as $kw)
              <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                {{ e($kw) }}
              </span>
              @endforeach
            </div>
            @endif
          </div>

          {{-- RINGKASAN --}}
          <div class="mt-4 grid gap-3 md:grid-cols-2">
            <dl class="divide-y divide-slate-100 rounded-xl border border-slate-200">
              <div class="flex items-center justify-between gap-4 p-3">
                <dt class="text-xs uppercase tracking-wide text-slate-500">Kode</dt>
                <dd class="truncate text-sm font-medium text-slate-800">{{ e($job->code ?? '—') }}</dd>
              </div>
              <div class="flex items-center justify-between gap-4 p-3">
                <dt class="text-xs uppercase tracking-wide text-slate-500">Divisi</dt>
                <dd class="truncate text-sm font-medium text-slate-800">{{ e($job->division ?? '—') }}</dd>
              </div>
              <div class="flex items-center justify-between gap-4 p-3">
                <dt class="text-xs uppercase tracking-wide text-slate-500">Level</dt>
                <dd class="truncate text-sm font-medium text-slate-800">{{ e($job->level ?? '—') }}</dd>
              </div>
              <div class="flex items-center justify-between gap-4 p-3">
                <dt class="text-xs uppercase tracking-wide text-slate-500">Tipe</dt>
                <dd class="truncate text-sm font-medium text-slate-800">{{ e($job->employment_type ?? '—') }}</dd>
              </div>
            </dl>

            <dl class="divide-y divide-slate-100 rounded-xl border border-slate-200">
              <div class="flex items-center justify-between gap-4 p-3">
                <dt class="text-xs uppercase tracking-wide text-slate-500">Dibuat</dt>
                <dd class="truncate text-sm text-slate-800">
                  <span class="font-medium">{{ e($who['creator'] ?? ($job->created_by ?? '')) }}</span>
                  <span>{{ optional($job->created_at)->format('Y-m-d H:i') }}</span>
                </dd>
              </div>
              <div class="flex items-center justify-between gap-4 p-3">
                <dt class="text-xs uppercase tracking-wide text-slate-500">Diubah</dt>
                <dd class="truncate text-sm text-slate-800">
                  <span class="font-medium">{{ e($who['updater'] ?? ($job->updated_by ?? '')) }}</span>
                  <span>{{ optional($job->updated_at)->format('Y-m-d H:i') }}</span>
                </dd>
              </div>
            </dl>
          </div>

          {{-- DESKRIPSI (collapsible) --}}
          @php $desc = $job->getAttributes()['description'] ?? null; @endphp
          @if($desc)
          <div class="mt-5">
            <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-900">
              <svg class="h-4 w-4 text-slate-600">
                <use href="#i-calendar" />
              </svg>
              <span>Deskripsi Pekerjaan</span>
            </div>
            <div x-data="{ open:false }" class="relative">
              <div :class="open ? 'max-h-none' : 'max-h-40'"
                class="prose prose-slate max-w-none overflow-hidden transition-[max-height] duration-300 prose-a:text-blue-700">
                {!! $desc !!}
              </div>
              <div x-show="!open" x-cloak class="pointer-events-none absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-white to-transparent"></div>
              <button type="button" @click="open=!open"
                class="mt-2 text-sm font-medium text-blue-700 hover:underline">
                <span x-show="!open">Tampilkan selengkapnya</span>
                <span x-show="open" x-cloak>Tutup deskripsi</span>
              </button>
            </div>
          </div>
          @endif

        </div>
      </article>
      @endforeach
    </div>
  </section>

  {{-- Pagination ringkas --}}
  @php
  $perPage = method_exists($jobs,'perPage') ? (int) $jobs->perPage() : max(1, (int) ($jobs->count() ?: 1));
  $current = method_exists($jobs,'currentPage') ? (int) $jobs->currentPage() : 1;
  $from = ($current - 1) * $perPage + 1;
  $to = min($current * $perPage, $total);
  @endphp
  <nav class="mt-4 rounded-2xl border border-slate-200 bg-white p-3 md:p-4 shadow-sm" aria-label="Navigasi halaman">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-sm">
      <div class="text-slate-700">Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span> dari <span class="font-semibold text-slate-900">{{ $total }}</span></div>
      <div class="select-none">{{ $jobs->onEachSide(1)->links() }}</div>
    </div>
  </nav>
  @else
  {{-- Empty state --}}
  <section class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
    <div class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full border border-slate-200">
      <svg class="h-5 w-5 text-slate-500">
        <use href="#i-filter" />
      </svg>
    </div>
    <h3 class="text-lg font-semibold text-slate-900">Belum ada hasil</h3>
    <p class="mt-1 text-sm text-slate-600">Coba ubah filter atau reset untuk melihat semua lowongan.</p>
    <div class="mt-4 flex items-center justify-center gap-2">
      <a href="{{ $resetUrl }}" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800">Reset Filter</a>
      <button id="btn-filter-empty" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-900 hover:bg-slate-50">Buka Filter</button>
    </div>
  </section>
  @endif
</div>

@push('scripts')
<script>
  // Toggle panel filter
  document.addEventListener('DOMContentLoaded', () => {
    const pnl = document.getElementById('filter-panel');
    const btn = document.getElementById('btn-filter');
    const btnEmpty = document.getElementById('btn-filter-empty');

    function togglePanel(forceOpen) {
      if (!pnl) return;
      const hidden = pnl.classList.contains('hidden');
      const willOpen = typeof forceOpen === 'boolean' ? forceOpen : hidden;
      pnl.classList.toggle('hidden', !willOpen);
    }
    btn && btn.addEventListener('click', () => togglePanel());
    btnEmpty && btnEmpty.addEventListener('click', () => togglePanel(true));
  });
</script>
@endpush
@endsection