{{-- resources/views/jobs/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Lowongan • karir-andalan')

@php
    use Illuminate\Support\Str;

    // ===== Query helpers =====
    $qDivision = trim((string) request('division', ''));
    $qSite     = trim((string) request('site', ''));
    $qCompany  = trim((string) request('company', ''));
    $qTerm     = trim((string) request('term', ''));
    $qType     = trim((string) request('type', ''));
    $qSort     = trim((string) request('sort', ''));
    $hasAny    = $qDivision || $qSite || $qCompany || $qTerm || $qType || $qSort;

    $keepParams = array_filter(
        request()->only(['division', 'site', 'company', 'term', 'type', 'sort']),
        fn($v) => filled($v)
    );

    $rm       = fn(string $key) => route('jobs.index', collect($keepParams)->except($key)->all());
    $resetUrl = route('jobs.index');
    $total    = method_exists($jobs, 'total') ? (int) $jobs->total() : (int) $jobs->count();
    $activeFilterCount = count(array_filter([$qDivision, $qSite, $qCompany, $qTerm, $qType]));
    $typeOptions = ['fulltime' => 'Fulltime', 'contract' => 'Contract', 'intern' => 'Intern'];

    // ===== Dept meta =====
    $deptIcons = [
        'HR'        => ['icon' => 'i-hr',       'bg' => 'bg-sky-50',     'fg' => 'text-sky-700',     'ring' => 'ring-sky-200'],
        'PLANT'     => ['icon' => 'i-plant',     'bg' => 'bg-amber-50',   'fg' => 'text-amber-700',   'ring' => 'ring-amber-200'],
        'SCM'       => ['icon' => 'i-scm',       'bg' => 'bg-indigo-50',  'fg' => 'text-indigo-700',  'ring' => 'ring-indigo-200'],
        'IT'        => ['icon' => 'i-it',        'bg' => 'bg-blue-50',    'fg' => 'text-blue-700',    'ring' => 'ring-blue-200'],
        'FINANCE'   => ['icon' => 'i-finance',   'bg' => 'bg-slate-50',   'fg' => 'text-slate-700',   'ring' => 'ring-slate-200'],
        'QA'        => ['icon' => 'i-qa',        'bg' => 'bg-emerald-50', 'fg' => 'text-emerald-700', 'ring' => 'ring-emerald-200'],
        'HSE'       => ['icon' => 'i-hse',       'bg' => 'bg-cyan-50',    'fg' => 'text-cyan-700',    'ring' => 'ring-cyan-200'],
        'GA'        => ['icon' => 'i-ga',        'bg' => 'bg-fuchsia-50', 'fg' => 'text-fuchsia-700', 'ring' => 'ring-fuchsia-200'],
        'LEGAL'     => ['icon' => 'i-legal',     'bg' => 'bg-violet-50',  'fg' => 'text-violet-700',  'ring' => 'ring-violet-200'],
        'MARKETING' => ['icon' => 'i-marketing', 'bg' => 'bg-orange-50',  'fg' => 'text-orange-700',  'ring' => 'ring-orange-200'],
        'SALES'     => ['icon' => 'i-sales',     'bg' => 'bg-teal-50',    'fg' => 'text-teal-700',    'ring' => 'ring-teal-200'],
        'R&D'       => ['icon' => 'i-rnd',       'bg' => 'bg-rose-50',    'fg' => 'text-rose-700',    'ring' => 'ring-rose-200'],
    ];

    $deptMeta = function (?string $div) use ($deptIcons) {
        $key = $div ? strtoupper(trim($div)) : '';
        $aliases = [
            'HRGA' => 'HR', 'HUMAN RESOURCE' => 'HR', 'HUMAN RESOURCES' => 'HR',
            'PLANT ENGINEERING' => 'PLANT',
        ];
        $key = $aliases[$key] ?? $key;
        return $deptIcons[$key] ?? [
            'icon' => 'i-briefcase',
            'bg'   => 'bg-stone-50',
            'fg'   => 'text-stone-700',
            'ring' => 'ring-stone-200',
        ];
    };

    $railColors = [
        'border-[#a77d52]', 'border-sky-400', 'border-indigo-400',
        'border-emerald-400', 'border-amber-400',
    ];

    $extractSkills = function ($job) {
        $attrs = $job->getAttributes();
        $raw   = $job->skills
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
                if (is_array($v))  return trim((string) ($v['name'] ?? $v['label'] ?? ''));
                return '';
            })
            ->filter()->unique()->take(10)->values();
    };

    $auditWho = function ($job) {
        return [
            'creator' => Str::limit((string) ($job->creator->name ?? $job->created_by_name ?? $job->created_by ?? ''), 40) ?: null,
            'updater' => Str::limit((string) ($job->updater->name ?? $job->updated_by_name ?? $job->updated_by ?? ''), 40) ?: null,
        ];
    };
@endphp

@push('head')
<style>
  .jobs-shell {
    background: #ffffff;
    border-radius: 0;
    margin-inline: -1rem;
    padding-inline: 1rem;
    padding-bottom: 1.25rem;
  }
  .jobs-filter-card {
    border: 1px solid #c9a07a;
    background:
      linear-gradient(135deg, rgba(167,125,82,.14), rgba(255,250,245,.96) 45%, rgba(167,125,82,.1));
    box-shadow: 0 18px 42px rgba(92, 61, 30, .09);
  }
  .jobs-panel,
  .jobs-detail {
    border: 1px solid #eadccd;
    background: #ffffff;
    box-shadow: 0 18px 45px rgba(92, 61, 30, .08);
  }
  .jobs-field {
    height: 2.75rem;
    width: 100%;
    border-radius: .9rem;
    border: 1px solid #dfc9b0;
    background: #fff;
    padding: 0 .9rem;
    font-size: .875rem;
    color: #1f2937;
    outline: none;
    transition: border-color .16s ease, box-shadow .16s ease, background .16s ease;
  }
  .jobs-field:focus {
    border-color: #a77d52;
    box-shadow: 0 0 0 4px rgba(167, 125, 82, .16);
  }
  .jobs-select { appearance: none; padding-right: 2.2rem; }
  .jobs-search-input {
    height: 3rem;
    border-radius: 1rem;
    padding-left: 3.25rem !important;
    padding-right: 2.75rem !important;
    font-size: .95rem;
  }
  .jobs-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    border-radius: 999px;
    border: 1px solid #dfc9b0;
    background: #fffaf5;
    padding: .35rem .7rem;
    font-size: .72rem;
    font-weight: 650;
    color: #6f4d2f;
  }
  .jobs-soft-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    border-radius: .9rem;
    border: 1px solid #dfc9b0;
    background: #fff8f0;
    color: #6f4d2f;
    font-weight: 700;
    transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
  }
  .jobs-soft-btn:hover {
    background: #fdf0e4;
    box-shadow: 0 10px 24px rgba(92, 61, 30, .11);
    transform: translateY(-1px);
  }
  .jobs-primary-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    border-radius: .9rem;
    background: #a77d52;
    color: white;
    font-weight: 750;
    box-shadow: 0 12px 24px rgba(167, 125, 82, .24);
    transition: transform .16s ease, box-shadow .16s ease, opacity .16s ease;
  }
  .jobs-primary-btn:hover { opacity: .95; transform: translateY(-1px); }
  @media (min-width: 768px) {
    .jobs-shell { margin-inline: -1.5rem; padding-inline: 1.5rem; }
  }
</style>
@endpush

@section('content')

@once
{{-- ================================================================
     SVG SPRITE — inlined once per page
     ================================================================ --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
  {{-- UI icons --}}
  <symbol id="i-filter"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4"/></symbol>
  <symbol id="i-search"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2"/><path d="M21 21l-4.3-4.3" stroke-width="2" stroke-linecap="round"/></symbol>
  <symbol id="i-x"             viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" stroke-linecap="round" d="M6 6l12 12M6 18L18 6"/></symbol>
  <symbol id="i-chevron"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 9l6 6 6-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-chevron-left"  viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-calendar"      viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" stroke-width="2"/><path d="M16 2v4M8 2v4M3 10h18" stroke-width="2" stroke-linecap="round"/></symbol>
  <symbol id="i-chip"          viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="7" width="18" height="10" rx="5" stroke-width="2"/></symbol>
  {{-- Department icons --}}
  <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" stroke-width="1.8"/></symbol>
  <symbol id="i-map"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 20l-5-2V6l5 2 6-2 5 2v12l-5-2-6 2zM14 4v14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-clock"     viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M12 7v5l3 2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-users"     viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="8" r="3" stroke-width="2"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16.5 3.8a3.2 3.2 0 1 1 0 6.4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-hr"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="8" cy="8" r="3" stroke-width="1.8"/><path d="M2 20a6 6 0 0 1 12 0" stroke-width="1.8"/><path d="M16 11h4M18 9v4" stroke-width="1.8" stroke-linecap="round"/></symbol>
  <symbol id="i-plant"     viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="10" width="18" height="10" rx="2" stroke-width="1.8"/><path d="M7 10V6l3 2V6l3 2V6l3 2" stroke-width="1.8" stroke-linecap="round"/></symbol>
  <symbol id="i-scm"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 16V8h10v8H3Z" stroke-width="1.8"/><path d="M13 13h4l4 3v3h-8v-6Z" stroke-width="1.8"/><circle cx="7" cy="19" r="1.5"/><circle cx="17" cy="19" r="1.5"/></symbol>
  <symbol id="i-it"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="4" y="4" width="16" height="12" rx="2" stroke-width="1.8"/><path d="M8 20h8M10 16v4M14 16v4" stroke-width="1.8"/></symbol>
  <symbol id="i-finance"   viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 10h18M5 10V7l7-4 7 4v3M5 10v10h14V10" stroke-width="1.8"/></symbol>
  <symbol id="i-qa"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 12l4 4 8-8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-hse"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3l7 4v5c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V7l7-4Z" stroke-width="1.8"/></symbol>
  <symbol id="i-ga"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 21h18M6 21V7h12v14M9 10h2m2 0h2" stroke-width="1.8"/></symbol>
  <symbol id="i-legal"     viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3 4 7l8 4 8-4-8-4Z" stroke-width="1.8"/><path d="M6 10v4a6 6 0 0 0 12 0v-4" stroke-width="1.8"/></symbol>
  <symbol id="i-marketing" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 11h6l8-5v14l-8-5H3v-4Z" stroke-width="1.8"/><path d="M9 19v2" stroke-width="1.8"/></symbol>
  <symbol id="i-sales"     viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M8 12l4 4 8-8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="6" cy="6" r="2"/><circle cx="18" cy="6" r="2"/></symbol>
  <symbol id="i-rnd"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M9 3v5l-4 7a4 4 0 0 0 4 6h6a4 4 0 0 0 4-6l-4-7V3" stroke-width="1.8"/></symbol>
</svg>
@endonce

<div class="jobs-shell">
<div class="mx-auto w-full max-w-[1480px] px-4 md:px-6 lg:px-8">

  {{-- ================================================================
       HEADER (shared page-header) + SEARCH + FILTER
       ================================================================ --}}
  <section class="page-header" aria-labelledby="jobs-page-title">
    <div class="page-header__inner">
      <div class="page-header__copy">
        <p class="page-header__eyebrow">Lowongan Kerja</p>
        <h1 id="jobs-page-title" class="page-header__title">Cari lowongan yang paling cocok</h1>
        <p class="page-header__desc">Gunakan pencarian dan filter cepat untuk menemukan posisi berdasarkan nama lowongan, site, company, divisi, atau tipe kerja.</p>
      </div>

      <div class="page-header__actions">
        <button data-filter-toggle type="button" aria-expanded="false" aria-controls="filter-panel" class="ph-action">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M7 12h10M10 18h4"/></svg>
          Filter
          @if($hasAny)
            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#a77d52] text-[10px] font-bold text-white">
              {{ $activeFilterCount }}
            </span>
          @endif
        </button>
      </div>
    </div>
  </section>

  {{-- ================================================================
       FILTER CARD (search + panel)
       ================================================================ --}}
  <section class="jobs-filter-card overflow-hidden rounded-[1.35rem]">
    <div class="p-4 md:p-5">

      <form method="GET" action="{{ route('jobs.index') }}" role="search" class="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
        <label for="job-search" class="sr-only">Cari lowongan</label>
        <div class="relative">
          <svg class="absolute w-5 h-5 -translate-y-1/2 pointer-events-none left-4 top-1/2 text-[#a77d52]">
            <use href="#i-search"/>
          </svg>
          <input
            id="job-search" name="term" value="{{ e($qTerm) }}"
            placeholder="Cari posisi, kode lowongan, site, company…"
            autocomplete="off"
            class="jobs-field jobs-search-input"/>
          @if($qTerm)
            <a href="{{ $rm('term') }}"
               class="absolute grid transition -translate-y-1/2 bg-white border rounded-full right-3 top-1/2 h-7 w-7 place-items-center border-slate-200 hover:bg-slate-100"
               aria-label="Hapus kata kunci">
              <svg class="h-3.5 w-3.5 text-slate-500"><use href="#i-x"/></svg>
            </a>
          @endif
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:justify-end">
          <button type="submit" class="h-12 px-5 jobs-primary-btn">
            <svg class="w-4 h-4"><use href="#i-search"/></svg>
            Cari
          </button>
          <button data-filter-toggle type="button" aria-expanded="false" aria-controls="filter-panel" class="h-12 px-5 jobs-soft-btn">
            <svg class="w-4 h-4"><use href="#i-filter"/></svg>
            Filter
          </button>
        </div>

        @foreach(['division','site','company','type','sort'] as $keep)
          @if(!empty($keepParams[$keep]))
            <input type="hidden" name="{{ $keep }}" value="{{ e($keepParams[$keep]) }}">
          @endif
        @endforeach
      </form>

      @if($hasAny)
        <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-[#ede4dc]">
          <span class="text-[11px] font-semibold text-[#8c6843] uppercase tracking-wide">Aktif:</span>
          @foreach(['division' => 'Divisi', 'site' => 'Site', 'company' => 'Company', 'type' => 'Tipe', 'term' => 'Kata kunci'] as $fk => $fl)
            @if(!empty($keepParams[$fk]))
              <a href="{{ $rm($fk) }}"
                 class="jobs-chip hover:bg-[#fdf0e4] transition">
                {{ $fl }}: {{ e($fk === 'type' ? ($typeOptions[$keepParams[$fk]] ?? $keepParams[$fk]) : $keepParams[$fk]) }}
                <svg class="w-3 h-3 opacity-60"><use href="#i-x"/></svg>
              </a>
            @endif
          @endforeach
          <a href="{{ $resetUrl }}" class="ml-auto text-[12px] font-semibold text-[#a77d52] hover:underline">Reset semua</a>
        </div>
      @endif

      <div id="filter-panel" class="{{ $hasAny ? '' : 'hidden' }} mt-4 pt-4 border-t border-[#ede4dc]">
        <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5" aria-label="Filter Lowongan">

          <div>
            <label class="mb-1 block text-[10px] font-semibold text-[#8c6843] uppercase tracking-wide">Divisi</label>
            <input name="division" value="{{ e($qDivision) }}" placeholder="Plant / SCM / HRGA"
              class="jobs-field"/>
          </div>

          <div>
            <label class="mb-1 block text-[10px] font-semibold text-[#8c6843] uppercase tracking-wide">Site</label>
            <input name="site" value="{{ e($qSite) }}" placeholder="DBK / POS / SBS"
              class="jobs-field"/>
          </div>

          <div>
            <label class="mb-1 block text-[10px] font-semibold text-[#8c6843] uppercase tracking-wide">Company</label>
            <input name="company" value="{{ e($qCompany) }}" placeholder="ANDALAN / AGR"
              class="jobs-field"/>
          </div>

          <div>
            <label class="mb-1 block text-[10px] font-semibold text-[#8c6843] uppercase tracking-wide">Tipe Kerja</label>
            <div class="relative">
              <select name="type" class="jobs-field jobs-select">
                <option value="">Semua tipe</option>
                @foreach($typeOptions as $value => $label)
                  <option value="{{ $value }}" @selected($qType === $value)>{{ $label }}</option>
                @endforeach
              </select>
              <svg class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400">
                <use href="#i-chevron"/>
              </svg>
            </div>
          </div>

          @if($qTerm)<input type="hidden" name="term" value="{{ e($qTerm) }}">@endif
          @if($qSort)<input type="hidden" name="sort" value="{{ e($qSort) }}">@endif

          <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-1">
            <button class="flex-1 px-4 jobs-primary-btn h-11">Terapkan</button>
            <a href="{{ $resetUrl }}" class="px-4 jobs-soft-btn h-11">Reset</a>
          </div>

        </form>
      </div>

    </div>
  </section>

  {{-- ================================================================
       MAIN BODY
       ================================================================ --}}
  @if($jobs->count())
    @php $firstId = optional($jobs->first())->id; @endphp

    <section
      x-data="{
        open: @js($firstId),
        select(id) {
          this.open = id;
          const el = document.getElementById('detail-' + id);
          if (!el) return;
          const pr = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
          window.scrollTo({
            top: el.getBoundingClientRect().top + window.scrollY - 80,
            behavior: pr ? 'auto' : 'smooth'
          });
        }
      }"
      class="mt-5 grid gap-5 lg:grid-cols-[minmax(360px,520px)_1fr]">

      {{-- ==============================================================
           LEFT: JOB LIST
           ============================================================== --}}
      <aside class="jobs-panel overflow-hidden rounded-[1.35rem] lg:sticky lg:top-4 lg:self-start">

        {{-- List header --}}
        <div class="flex items-center justify-between gap-3 border-b border-[#efe2d6] bg-[#fffaf5] px-4 py-3">
          <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-[#8c6843]">Hasil pencarian</p>
            <p class="text-sm font-bold leading-tight text-slate-900">{{ $total }} lowongan tersedia</p>
          </div>

          <form method="GET" class="flex items-center gap-2" aria-label="Urutkan">
            @foreach(['division','site','company','term','type'] as $keep)
              @if(!empty($keepParams[$keep]))
                <input type="hidden" name="{{ $keep }}" value="{{ e($keepParams[$keep]) }}">
              @endif
            @endforeach
            <div class="relative">
              {{-- <select name="sort"
                class="h-9 appearance-none rounded-lg border border-slate-200 bg-white pl-3 pr-7
                       text-sm text-slate-800 focus:outline-none focus:ring-2
                       focus:ring-[#a77d52]/30 focus:border-[#c9a07a]">
                <option value="latest" @selected($qSort === '' || $qSort === 'latest')>Terbaru</option>
                <option value="oldest" @selected($qSort === 'oldest')>Terlama</option>
                <option value="title"  @selected($qSort === 'title')>Judul (A–Z)</option>
              </select> --}}
              <svg class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400">
                <use href="#i-chevron"/>
              </svg>
            </div>
              <button
                class="px-3 text-xs jobs-primary-btn h-9">
                OK
              </button>
          </form>
        </div>

        {{-- Job items --}}
        <ul class="max-h-[78vh] space-y-2 overflow-y-auto p-2">
          @foreach($jobs as $idx => $job)
            @php
              $typeRaw  = $job->employment_type ?? '';
              $type     = strtoupper($typeRaw ?: '-');
              $typeSlug = Str::of($typeRaw)->lower()->value();
              $badgeClr = match ($typeSlug) {
                'contract', 'kontrak' => 'bg-amber-600',
                'intern', 'magang'    => 'bg-emerald-600',
                default               => 'bg-[#a77d52]',
              };

              $rail = $railColors[$idx % count($railColors)];

              $prettyStage = [
                'applied'         => 'Screening',
                'screening'       => 'Screening',
                'psychotest'      => 'Psychological Test',
                'psychological_test' => 'Psychological Test',
                'hr_iv'           => 'HR Interview',
                'post_test'       => 'Post Test',
                'user_iv'         => 'User Interview',
                'user_trainer_iv' => 'User Interview',
                'offer'           => 'Offering Letter (OL)',
                'mcu'             => 'Medical Check Up',
                'mobilisasi'      => 'Mobilisasi (Travel)',
                'ground_test'     => 'Skill Test',
                'skill_test'      => 'Skill Test',
                'hired'           => 'Finish',
                'onsite'          => 'Finish',
                'not_qualified'   => 'Finish',
                'rejected'        => 'Finish',
                'finish'          => 'Finish',
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
              $myApp = Auth::check() ? ($job->applications->first() ?? null) : null;
            @endphp

            <li>
              <button type="button"
                @click="select('{{ $job->id }}')"
                :class="open === '{{ $job->id }}'
                  ? 'bg-[#fdf6ef] ring-1 ring-inset ring-[#c9a07a]/40'
                  : 'bg-white hover:bg-slate-50'"
                class="relative w-full overflow-hidden rounded-2xl px-3.5 py-3.5 text-left transition group">

                {{-- Coloured left rail --}}
                <span class="absolute inset-y-0 left-0 w-[3px] rounded-r {{ $rail }}"></span>

                <div class="flex items-start gap-3 pl-1">

                  {{-- Job number --}}
                  <span class="grid h-11 w-11 shrink-0 place-items-center rounded-2xl border border-[#eadccd] bg-white text-[11px] font-bold text-[#6f4d2f] shadow-sm">
                    {{ ($jobs->firstItem() ?? 1) + $idx }}
                  </span>

                  <div class="flex-1 min-w-0">

                    {{-- Title row --}}
                    <div class="flex items-start justify-between gap-2">
                      <p class="min-w-0 flex-1 line-clamp-2 text-[14px] font-bold text-slate-950 leading-snug">
                        {{ e($job->title) }}
                      </p>
                      <div class="shrink-0 flex max-w-[46%] items-center gap-1.5">
                        @if($job->code)
                          <span class="rounded border border-slate-200 bg-slate-50
                                       px-1.5 py-0.5 text-[10px] font-medium text-slate-600 max-w-[130px] truncate">
                            {{ e($job->code) }}
                          </span>
                        @endif
                        <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold text-white {{ $badgeClr }}">
                          {{ e($type) }}
                        </span>
                      </div>
                    </div>

                  </div>

                  <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-300
                              group-hover:text-[#a77d52] group-hover:translate-x-0.5 transition">
                    <use href="#i-chevron-right"/>
                  </svg>

                </div>
              </button>
            </li>
          @endforeach
        </ul>

      </aside>

      {{-- ==============================================================
           RIGHT: JOB DETAIL
           ============================================================== --}}
      <div class="space-y-4">
        @foreach($jobs as $idx => $job)
          @php
            $typeRaw  = $job->employment_type ?? '';
            $type     = strtoupper($typeRaw ?: '-');
            $typeSlug = Str::of($typeRaw)->lower()->value();
            $badgeClr = match ($typeSlug) {
              'contract', 'kontrak' => 'bg-amber-600',
              'intern', 'magang'    => 'bg-emerald-600',
              default               => 'bg-[#a77d52]',
            };

            $meta   = $deptMeta($job->division);
            $skills = $extractSkills($job);
            $who    = $auditWho($job);
            $isOpen = strtolower((string) $job->status) === 'open';

            $siteLabel = $job->site->name
                      ?? $job->site_name
                      ?? $job->getAttribute('site_name')
                      ?? $job->site->code
                      ?? $job->site_code
                      ?? $job->getAttribute('site_code')
                      ?? '—';

            $kwRaw = $job->getAttribute('keywords');
            if (is_array($kwRaw)) {
                $kwList = $kwRaw;
            } else {
                $kwStr = trim((string) $kwRaw);
                if ($kwStr === '') {
                    $kwList = [];
                } else {
                    $dec    = json_decode($kwStr, true);
                    $kwList = is_array($dec)
                        ? $dec
                        : array_filter(array_map('trim', preg_split('/\s*[,;|]\s*/', $kwStr) ?: []));
                }
            }

            $desc = $job->getAttributes()['description'] ?? null;
          @endphp

          <article
            x-show="open === '{{ $job->id }}'"
            x-cloak
            id="detail-{{ $job->id }}"
            class="jobs-detail overflow-hidden rounded-[1.35rem]">

            <div class="bg-gradient-to-br from-[#a77d52] to-[#7b5637] px-5 py-5 text-white md:px-6">

              {{-- ── Header ── --}}
              <div class="flex flex-col gap-4 sm:flex-row sm:items-start">

                <span class="grid w-12 h-12 text-white shrink-0 place-items-center rounded-xl ring-1 bg-white/15 ring-white/25">
                  <svg class="w-5 h-5"><use href="#{{ $meta['icon'] }}"/></svg>
                </span>

                <div class="flex-1 min-w-0">
                  <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-2xl font-bold leading-tight">{{ e($job->title) }}</h2>
                    <span class="rounded-full bg-white/15 px-2.5 py-1 text-[11px] font-bold text-white ring-1 ring-white/30">
                      {{ e($type) }}
                    </span>
                    @if($isOpen)
                      <span class="rounded-full border border-white/30 bg-white/15
                                   px-2.5 py-1 text-[10.5px] font-bold text-white uppercase tracking-wide">
                        OPEN
                      </span>
                    @else
                      <span class="rounded-full border border-white/30 bg-white/15
                                   px-2.5 py-1 text-[10.5px] font-bold text-white uppercase tracking-wide">
                        {{ strtoupper($job->status ?? '—') }}
                      </span>
                    @endif
                  </div>
                  @if($job->code || $job->division)
                    <p class="mt-2 text-sm text-white/80">
                      @if($job->code)Kode: {{ e($job->code) }}@endif
                      @if($job->code && $job->division) &nbsp;·&nbsp; @endif
                      @if($job->division){{ e($job->division) }}@endif
                    </p>
                  @endif
                </div>

                <a href="{{ route('jobs.show', $job) }}?apply=1#apply"
                   class="shrink-0 rounded-2xl bg-white px-5 py-3 text-sm font-bold text-[#7b5637]
                          shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-[#fff8f0] focus:outline-none">
                  Lamar →
                </a>

              </div>
            </div>

            <div class="p-5 md:p-6">

              {{-- ── Meta bar ── --}}
              <div class="grid gap-3 sm:grid-cols-3">
                @foreach([
                  ['i-map',       'Lokasi',     e($siteLabel)],
                  ['i-briefcase', 'Perusahaan', e($job->company->name ?? $job->company->code ?? '—')],
                  ['i-users',     'Kebutuhan',  ((int) $job->openings) . ' orang'],
                ] as [$ico, $lbl, $val])
                  <div class="flex items-center gap-2.5 rounded-2xl border border-[#e8d5be]
                               bg-[#fffaf5] px-3.5 py-3">
                    <svg class="h-4 w-4 shrink-0 text-[#a77d52]"><use href="#{{ $ico }}"/></svg>
                    <div class="min-w-0">
                      <p class="text-[10px] font-semibold text-[#8c6843] uppercase tracking-wide">{{ $lbl }}</p>
                      <p class="truncate text-sm font-bold text-[#5c3d1e]">{{ $val }}</p>
                    </div>
                  </div>
                @endforeach
              </div>

              {{-- ── Skills ── --}}
              @if($skills->isNotEmpty())
                <div class="mt-5">
                  <div class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-slate-800">
                    <svg class="w-4 h-4 text-slate-500"><use href="#i-chip"/></svg>
                    Skills Utama
                  </div>
                  <div class="flex flex-wrap gap-1.5">
                    @foreach($skills as $sk)
                      <span class="rounded-full border px-2.5 py-1 text-[11px] shadow-sm
                                   {{ $loop->iteration % 2 === 1
                                     ? 'border-blue-200 bg-blue-50 text-blue-700'
                                     : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                        {{ e($sk) }}
                      </span>
                    @endforeach
                  </div>
                </div>
              @endif

              {{-- ── Keywords ── --}}
              @if(!empty($kwList))
                <div class="mt-3">
                  <p class="mb-2 text-sm font-semibold text-slate-800">Keywords</p>
                  <div class="flex flex-wrap gap-1.5">
                    @foreach($kwList as $kw)
                      <span class="rounded-full border border-emerald-200 bg-emerald-50
                                   px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                        {{ e($kw) }}
                      </span>
                    @endforeach
                  </div>
                </div>
              @endif

              {{-- ── Detail tables ── --}}
              <div class="grid gap-3 mt-5 md:grid-cols-2">

                <dl class="overflow-hidden text-sm border divide-y divide-slate-100 rounded-xl border-slate-200">
                  @foreach([
                    ['Kode',  e($job->code ?? '—')],
                    ['Divisi',e($job->division ?? '—')],
                    ['Level', e($job->level ?? '—')],
                    ['Tipe',  e($job->employment_type ?? '—')],
                  ] as [$dk, $dv])
                    <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                      <dt class="text-[11px] uppercase tracking-wide text-slate-500 shrink-0">{{ $dk }}</dt>
                      <dd class="font-medium truncate text-slate-800">{{ $dv }}</dd>
                    </div>
                  @endforeach
                </dl>

                <dl class="overflow-hidden text-sm border divide-y divide-slate-100 rounded-xl border-slate-200">
                  <div class="flex items-start justify-between gap-4 px-3 py-2.5">
                    <dt class="text-[11px] uppercase tracking-wide text-slate-500 shrink-0 mt-0.5">Dibuat</dt>
                    <dd class="min-w-0 text-right">
                      <p class="font-medium truncate text-slate-800">{{ e($who['creator'] ?? ($job->created_by ?? '—')) }}</p>
                      <p class="text-[11px] text-slate-500">{{ optional($job->created_at)->format('Y-m-d H:i') }}</p>
                    </dd>
                  </div>
                  <div class="flex items-start justify-between gap-4 px-3 py-2.5">
                    <dt class="text-[11px] uppercase tracking-wide text-slate-500 shrink-0 mt-0.5">Diubah</dt>
                    <dd class="min-w-0 text-right">
                      <p class="font-medium truncate text-slate-800">{{ e($who['updater'] ?? ($job->updated_by ?? '—')) }}</p>
                      <p class="text-[11px] text-slate-500">{{ optional($job->updated_at)->format('Y-m-d H:i') }}</p>
                    </dd>
                  </div>
                  <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                    <dt class="text-[11px] uppercase tracking-wide text-slate-500 shrink-0">Kebutuhan</dt>
                    <dd class="font-medium text-slate-800">{{ (int) $job->openings }} orang</dd>
                  </div>
                </dl>

              </div>

              {{-- ── Deskripsi (collapsible) ── --}}
              @if($desc)
                <div class="mt-5">
                  <div class="mb-2 flex items-center gap-1.5 text-sm font-semibold text-slate-800">
                    <svg class="w-4 h-4 text-slate-500"><use href="#i-calendar"/></svg>
                    Deskripsi Pekerjaan
                  </div>
                  <div class="prose prose-sm prose-slate max-w-none prose-a:text-[#a77d52] prose-headings:text-slate-800">
                    {!! $desc !!}
                  </div>
                </div>
              @endif

            </div>
          </article>
        @endforeach
      </div>

    </section>

    {{-- ================================================================
         PAGINATION
         ================================================================ --}}
    @php
      $perPage = method_exists($jobs, 'perPage')     ? (int) $jobs->perPage()     : max(1, (int) $jobs->count());
      $current = method_exists($jobs, 'currentPage') ? (int) $jobs->currentPage() : 1;
      $last    = method_exists($jobs, 'lastPage')    ? (int) $jobs->lastPage()    : 1;
      $total   = method_exists($jobs, 'total')       ? (int) $jobs->total()       : (int) $jobs->count();
      $from    = ($current - 1) * $perPage + 1;
      $to      = min($current * $perPage, $total);

      if ($last <= 7) {
          $pages = range(1, $last);
      } else {
          $pages = [1];
          $left  = max(2, $current - 1);
          $right = min($last - 1, $current + 1);
          if ($left > 2)        $pages[] = '...';
          for ($i = $left; $i <= $right; $i++) $pages[] = $i;
          if ($right < $last-1) $pages[] = '...';
          $pages[] = $last;
      }

      $pageUrl = fn(int $p) => method_exists($jobs, 'url')
          ? $jobs->url($p)
          : request()->fullUrlWithQuery(['page' => $p]);
    @endphp

    <section class="jobs-panel mt-5 rounded-[1.35rem] px-4 py-3">
      <div class="flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">

        <p class="text-slate-600">
          Menampilkan
          <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
          dari
          <span class="font-semibold text-slate-900">{{ $total }}</span> lowongan
        </p>

        @if($last > 1)
          <nav aria-label="Pagination">
            <ul class="inline-flex items-stretch overflow-hidden bg-white border select-none rounded-xl border-slate-200">

              {{-- Prev --}}
              <li>
                @if($current > 1)
                  <a href="{{ $pageUrl($current - 1) }}" aria-label="Sebelumnya"
                     class="grid place-items-center w-9 h-9 hover:bg-[#fdf6ef] transition focus:outline-none">
                    <svg class="w-4 h-4 text-slate-600"><use href="#i-chevron-left"/></svg>
                  </a>
                @else
                  <span class="grid cursor-not-allowed place-items-center w-9 h-9 opacity-30" aria-hidden="true">
                    <svg class="w-4 h-4 text-slate-600"><use href="#i-chevron-left"/></svg>
                  </span>
                @endif
              </li>

              {{-- Page numbers --}}
              @foreach($pages as $p)
                @if($p === '...')
                  <li class="grid border-l place-items-center w-9 h-9 border-slate-200 text-slate-400">…</li>
                @else
                  @php $isCur = (int) $p === $current; @endphp
                  <li class="border-l border-slate-200">
                    @if($isCur)
                      <span style="background:#a77d52"
                            class="grid text-sm font-semibold text-white place-items-center w-9 h-9">
                        {{ $p }}
                      </span>
                    @else
                      <a href="{{ $pageUrl((int) $p) }}" aria-label="Halaman {{ $p }}"
                         class="grid place-items-center w-9 h-9 text-sm text-slate-700
                                hover:bg-[#fdf6ef] transition focus:outline-none">
                        {{ $p }}
                      </a>
                    @endif
                  </li>
                @endif
              @endforeach

              {{-- Next --}}
              <li class="border-l border-slate-200">
                @if($current < $last)
                  <a href="{{ $pageUrl($current + 1) }}" aria-label="Berikutnya"
                     class="grid place-items-center w-9 h-9 hover:bg-[#fdf6ef] transition focus:outline-none">
                    <svg class="w-4 h-4 text-slate-600"><use href="#i-chevron-right"/></svg>
                  </a>
                @else
                  <span class="grid cursor-not-allowed place-items-center w-9 h-9 opacity-30" aria-hidden="true">
                    <svg class="w-4 h-4 text-slate-600"><use href="#i-chevron-right"/></svg>
                  </span>
                @endif
              </li>

            </ul>
          </nav>
        @endif

      </div>
    </section>

  @else

    {{-- ================================================================
         EMPTY STATE
         ================================================================ --}}
    <section class="jobs-panel mt-5 rounded-[1.35rem] p-12 text-center">
      <div style="background:#fdf6ef;border:1px solid #e8d5be"
           class="grid mx-auto mb-4 rounded-full h-14 w-14 place-items-center">
        <svg class="h-6 w-6 text-[#a77d52]"><use href="#i-filter"/></svg>
      </div>
      <h3 class="text-lg font-semibold text-slate-900">Belum ada hasil</h3>
      <p class="mt-1 text-sm text-slate-500">
        Coba ubah filter atau reset untuk melihat semua lowongan.
      </p>
      <div class="flex items-center justify-center gap-3 mt-5">
        <a href="{{ $resetUrl }}"
           style="background:#a77d52"
           class="rounded-xl px-5 py-2.5 text-sm font-semibold text-white hover:opacity-90 transition">
          Reset Filter
        </a>
        <button id="btn-filter-empty"
          class="rounded-xl border border-[#dfc9b0] bg-[#fdf6ef] px-5 py-2.5
                 text-sm font-medium text-[#7a5c36] hover:bg-[#faecdb] transition">
          Buka Filter
        </button>
      </div>
    </section>

  @endif

</div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const pnl      = document.getElementById('filter-panel');
  const buttons  = document.querySelectorAll('[data-filter-toggle]');
  const btnEmpty = document.getElementById('btn-filter-empty');

  const togglePanel = (forceOpen) => {
    if (!pnl) return;
    const willOpen = typeof forceOpen === 'boolean'
      ? forceOpen
      : pnl.classList.contains('hidden');
    pnl.classList.toggle('hidden', !willOpen);
    buttons.forEach((button) => button.setAttribute('aria-expanded', String(willOpen)));
  };

  buttons.forEach((button) => button.addEventListener('click', () => togglePanel()));
  btnEmpty && btnEmpty.addEventListener('click', () => togglePanel(true));
});
</script>
@endpush

@endsection
