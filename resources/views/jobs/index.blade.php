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
        'border-[#b28a57]', 'border-sky-400', 'border-indigo-400',
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
  [x-cloak] { display: none !important; }
  .career-hero { display:flex; align-items:flex-end; justify-content:space-between; gap:1.5rem; max-width:1360px; margin:0 auto 1.25rem; padding:2rem 2.25rem; background:#fffdf9; border:1px solid #e5e7eb; border-radius:20px; }
  .career-hero__media { position:relative; width:260px; height:150px; overflow:hidden; border-radius:16px; }
  .career-hero__media img { width:100%; height:100%; object-fit:cover; }
  .career-hero__media::after { content:""; position:absolute; inset:0; background:linear-gradient(90deg,rgba(255,255,255,.1),rgba(23,32,51,.08)); }
  .career-eyebrow { margin:0 0 .5rem; color:#98703d; font-size:.72rem; font-weight:800; letter-spacing:.16em; }
  .career-title { margin:0; color:#172033; font-size:clamp(1.85rem,3vw,2.75rem); line-height:1.08; font-weight:800; letter-spacing:-.03em; }
  .career-desc { margin:.7rem 0 0; max-width:620px; color:#64748b; font-size:.98rem; }
  .career-count { display:flex; flex-direction:column; align-items:flex-end; color:#64748b; white-space:nowrap; }
  .career-count strong { color:#98703d; font-size:1.85rem; line-height:1; }
  .career-count span { margin-top:.35rem; font-size:.8rem; }
  .job-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; max-width:1360px; margin:1.5rem auto 0; }
  .job-card { display:flex; min-height:285px; flex-direction:column; border:1px solid #d9bc9d; border-radius:16px; background:#fffaf2; padding:1.5rem; transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease; }
  .job-card:hover { transform:translateY(-3px); border-color:#b58b56; box-shadow:0 14px 30px rgba(152,112,61,.16); }
  .job-card__top { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; }
  .job-card__icon { display:grid; height:44px; width:44px; flex:none; place-items:center; border-radius:12px; background:#b58b56; color:#fff; }
  .job-card__badge { border-radius:999px; background:#faf5ed; padding:.35rem .6rem; color:#98703d; font-size:.65rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
  .job-card h2 { margin:1.25rem 0 .45rem; color:#172033; font-size:1.1rem; line-height:1.3; font-weight:800; }
  .job-card__dept { color:#64748b; font-size:.82rem; font-weight:600; }
  .job-card__meta { display:flex; flex-wrap:wrap; gap:.5rem 1rem; margin-top:1.2rem; color:#64748b; font-size:.78rem; }
  .job-card__meta span { display:inline-flex; align-items:center; gap:.35rem; }
  .job-card__meta svg { color:#b58b56; }
  .job-card__footer { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-top:auto; padding-top:1.25rem; }
  .job-card__date { color:#94a3b8; font-size:.72rem; }
  .job-card__link { display:inline-flex; align-items:center; gap:.35rem; color:#98703d; font-size:.8rem; font-weight:800; }
  .job-card__link svg { transition:transform .2s ease; }
  .job-card:hover .job-card__link svg { transform:translateX(3px); }
  .site-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1.25rem; max-width:1440px; margin:1.5rem auto 0; }
  .site-card { position:relative; display:flex; min-height:270px; flex-direction:column; overflow:hidden; border:1px solid #d9bc9d; border-radius:16px; background:#fffaf2; box-shadow:0 8px 24px rgba(152,112,61,.10); transition:transform .2s ease,box-shadow .2s ease; }
  .site-card:hover { transform:translateY(-3px); box-shadow:0 16px 32px rgba(23,32,51,.11); }
  .site-card__image { position:relative; height:130px; overflow:hidden; }
  .site-card__image img { width:100%; height:100%; object-fit:cover; transition:transform .35s ease; }
  .site-card:hover .site-card__image img { transform:scale(1.04); }
  .site-card__image::after { content:""; position:absolute; inset:0; background:linear-gradient(0deg,rgba(23,32,51,.55),transparent 70%); }
  .site-card__body { display:flex; flex:1; flex-direction:column; padding:1.15rem 1.25rem; }
  .site-card__location { display:flex; align-items:center; gap:.45rem; color:#98703d; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
  .site-card__name { margin:.4rem 0 .3rem; color:#172033; font-size:1.15rem; font-weight:800; }
  .site-card__address { display:-webkit-box; overflow:hidden; color:#64748b; font-size:.78rem; line-height:1.45; -webkit-box-orient:vertical; -webkit-line-clamp:2; }
  .site-card__footer { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-top:auto; padding-top:1rem; }
  .site-card__count { border-radius:999px; background:#faf5ed; padding:.4rem .65rem; color:#98703d; font-size:.7rem; font-weight:800; }
  .site-card__cta { color:#98703d; font-size:.78rem; font-weight:800; }
  .site-skeleton { min-height:270px; border-radius:16px; background:linear-gradient(90deg,#f1f5f9 25%,#e8edf3 50%,#f1f5f9 75%); background-size:200% 100%; animation:site-shimmer 1.5s infinite; }
  @keyframes site-shimmer { to { background-position:-200% 0; } }
  @media (max-width: 1023px) { .job-grid,.site-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
  @media (max-width: 639px) { .career-hero { align-items:flex-start; flex-direction:column; padding:1.5rem; } .career-hero__media { width:100%; height:125px; } .career-count { align-items:flex-start; } .job-grid,.site-grid { grid-template-columns:1fr; } .job-card { min-height:250px; } }
  .jobs-shell {
    background: #ffffff;
    border-radius: 0;
    margin-inline: -1rem;
    padding-inline: 1rem;
    padding-bottom: 1.25rem;
  }
  .jobs-filter-card {
    border: 1px solid #c9a07a;
    background: #ffffff;
    box-shadow: 0 8px 24px rgba(23, 32, 51, .05);
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
    border-color: #b28a57;
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
    background: #b28a57;
    color: white;
    font-weight: 750;
    box-shadow: 0 12px 24px rgba(167, 125, 82, .24);
    transition: transform .16s ease, box-shadow .16s ease, opacity .16s ease;
  }
  .jobs-primary-btn:hover { opacity: .95; transform: translateY(-1px); }
  @media (min-width: 768px) {
    .jobs-shell { margin-inline: -1.5rem; padding-inline: 1.5rem; }
  }
  .jobs-shell { max-width:1360px; margin-inline:auto; padding-inline:0; }
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
  <section class="career-hero" aria-labelledby="jobs-page-title">
    <div>
      <p class="career-eyebrow">CAREER OPPORTUNITIES</p>
      <h1 id="jobs-page-title" class="career-title">Temukan Karier Terbaikmu</h1>
      <p class="career-desc">Jelajahi peluang kerja terbaik dan temukan posisi yang sesuai dengan keahlianmu.</p>
    </div>
    <div class="career-hero__media" aria-hidden="true">
      <img src="{{ asset('assets/banner-abn.png') }}" alt="" loading="eager">
    </div>
    <div class="career-count"><strong>{{ $total }}</strong><span>lowongan tersedia</span></div>
  </section>

  {{-- ================================================================
       FILTER CARD (search + panel)
       ================================================================ --}}
  <section class="jobs-filter-card overflow-hidden rounded-[1.35rem]">
    <div class="p-4 md:p-5">

      <form method="GET" action="{{ route('jobs.index') }}" role="search" class="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
        <label for="job-search" class="sr-only">Cari lowongan</label>
        <div class="relative">
          <svg class="absolute w-5 h-5 -translate-y-1/2 pointer-events-none left-4 top-1/2 text-[#b28a57]">
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

        <div class="grid grid-cols-1 gap-2 sm:flex sm:justify-end">
          <button type="submit" class="h-12 px-5 jobs-primary-btn">
            <svg class="w-4 h-4"><use href="#i-search"/></svg>
            Cari
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
          <a href="{{ $resetUrl }}" class="ml-auto text-[12px] font-semibold text-[#b28a57] hover:underline">Reset semua</a>
        </div>
      @endif

      <div id="filter-panel" class="mt-4 pt-4 border-t border-[#ede4dc]">
        <form method="GET" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5" aria-label="Filter Lowongan">

          <div>
            <label class="mb-1 block text-[10px] font-semibold text-[#8c6843] uppercase tracking-wide">Divisi</label>
            <input name="division" value="{{ e($qDivision) }}" placeholder="Plant / SCM / HRGA"
              class="jobs-field"/>
          </div>

          <div>
            <label class="mb-1 block text-[10px] font-semibold text-[#8c6843] uppercase tracking-wide">Site</label>
            <div class="relative">
              <select name="site" class="jobs-field jobs-select">
                <option value="">Semua site</option>
                @foreach(($sites ?? collect()) as $siteOption)
                  <option value="{{ e($siteOption->code) }}" @selected($qSite === (string) $siteOption->code)>
                    {{ e($siteOption->code . ($siteOption->name ? ' — ' . $siteOption->name : '')) }}
                  </option>
                @endforeach
              </select>
              <svg class="absolute w-4 h-4 -translate-y-1/2 pointer-events-none right-3 top-1/2 text-slate-400"><use href="#i-chevron"/></svg>
            </div>
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
       PREMIUM JOB GRID
       ================================================================ --}}
  @if(!$qSite)
    <section class="mt-6" aria-labelledby="site-list-title">
      <div class="mb-4 flex items-end justify-between gap-3">
        <div>
          <p class="career-eyebrow">EXPLORE BY LOCATION</p>
          <h2 id="site-list-title" class="text-2xl font-extrabold tracking-tight text-[#172033]">Pilih lokasi kerja</h2>
        </div>
        <span class="text-sm text-[#64748B]">{{ $sites->where('jobs_count', '>', 0)->count() }} lokasi</span>
      </div>
      @if($sites->where('jobs_count', '>', 0)->count())
        <div class="site-grid">
          @foreach($sites->where('jobs_count', '>', 0) as $site)
            @php
              $siteCode = trim((string) ($site->code ?? ''));
              $siteName = trim((string) ($site->name ?? ''));
              $siteRegion = trim((string) ($site?->region ?? ''));
              $siteLabel = $siteRegion !== '' ? $siteRegion : ($siteName !== '' ? $siteName : 'Lokasi kerja');
              $siteAddress = trim((string) ($site?->address ?? ''));
              $siteUrl = route('jobs.index', array_merge($keepParams, ['site' => $siteCode]));
            @endphp
            <a href="{{ $siteUrl }}" class="site-card">
              <div class="site-card__image">
                <img src="{{ asset('assets/banner-abn.png') }}" alt="{{ e($siteLabel) }}" loading="lazy">
              </div>
              <div class="site-card__body">
                <div class="site-card__location"><svg class="h-4 w-4"><use href="#i-map"/></svg> Lokasi site</div>
                <h3 class="site-card__name">{{ e($siteLabel) }}</h3>
                <p class="site-card__address">{{ e($siteAddress !== '' ? $siteAddress : ($siteCode !== '' ? $siteCode : 'Lihat lowongan yang tersedia di lokasi ini')) }}</p>
                <div class="site-card__footer">
                  <span class="site-card__count">{{ (int) ($site->jobs_count ?? 0) }} lowongan</span>
                  <span class="site-card__cta">Lihat lowongan di site ini →</span>
                </div>
              </div>
            </a>
          @endforeach
        </div>
      @else
        <section class="jobs-panel rounded-2xl p-12 text-center">
          <h3 class="text-lg font-semibold text-slate-900">Belum ada lokasi dengan lowongan aktif</h3>
          <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau reset filter.</p>
        </section>
      @endif
    </section>
  @elseif($jobs->count())
    <div class="mt-6 flex items-center justify-between gap-3">
      <div>
        <p class="career-eyebrow">LOWONGAN DI SITE TERPILIH</p>
        <h2 class="text-2xl font-extrabold tracking-tight text-[#172033]">Daftar posisi tersedia</h2>
      </div>
      <a href="{{ route('jobs.index', collect($keepParams)->except('site')->all()) }}" class="jobs-soft-btn px-4 py-2.5 text-sm">← Semua site</a>
    </div>
    @php
      $staffLevels = [
        'foreman', 'supervisor', 'superintendent', 'manager', 'analyst',
        'specialist', 'expert', 'lead of', 'section head', 'dept head',
        'project manager', 'pjo', 'staff',
      ];
      $levelGroups = $jobs->getCollection()->groupBy(function ($job) use ($staffLevels) {
          $level = strtolower(trim((string) ($job->level ?? '')));
          $level = str_replace(['_', '-'], ' ', $level);
          $level = preg_replace('/\s+/', ' ', $level) ?: '';
          return in_array($level, $staffLevels, true) ? 'staff' : 'non_staff';
      });
    @endphp
    @foreach(['staff' => 'Staff', 'non_staff' => 'Non Staff'] as $levelKey => $levelLabel)
      @if(($levelGroups[$levelKey] ?? collect())->isNotEmpty())
        <div class="mt-6 flex items-center gap-3">
          <span class="grid h-9 w-9 place-items-center rounded-xl bg-[#b58b56] text-sm font-extrabold text-white">{{ $levelKey === 'staff' ? 'S' : 'NS' }}</span>
          <div>
            <p class="career-eyebrow !mb-0">LEVEL PEKERJAAN</p>
            <h3 class="text-xl font-extrabold tracking-tight text-[#172033]">{{ $levelLabel }}</h3>
          </div>
          <span class="ml-auto rounded-full bg-[#faf5ed] px-3 py-1 text-xs font-bold text-[#98703d]">{{ $levelGroups[$levelKey]->count() }} job</span>
        </div>
        <section class="job-grid" aria-label="Lowongan {{ $levelLabel }}">
      @foreach($levelGroups[$levelKey] as $job)
        @php
          $typeRaw = (string) ($job->employment_type ?? '');
          $typeLabel = match (strtolower($typeRaw)) {
            'fulltime' => 'Fulltime',
            'contract' => 'Contract',
            'intern' => 'Intern',
            default => ($typeRaw !== '' ? ucfirst($typeRaw) : 'Fulltime'),
          };
          $siteLabel = $job->site?->name
              ?? $job->site_name
              ?? $job->site?->code
              ?? $job->site_code
              ?? 'Lokasi belum ditentukan';
          $isOpen = strtolower((string) $job->status) === 'open';
          $meta = $deptMeta($job->division);
        @endphp
        <article class="job-card">
          <div class="job-card__top">
            <span class="job-card__icon"><svg class="h-5 w-5"><use href="#{{ $meta['icon'] }}"/></svg></span>
            <span class="job-card__badge" style="{{ $isOpen ? 'background:#ecfdf5;color:#047857' : 'background:#fef2f2;color:#b91c1c' }}">{{ $isOpen ? 'Aktif' : strtoupper((string) ($job->status ?: 'Ditutup')) }}</span>
          </div>
          <h2>{{ e($job->title) }}</h2>
          <p class="job-card__dept">{{ e($job->division ?: 'General') }}</p>
          <div class="job-card__meta">
            <span><svg class="h-4 w-4"><use href="#i-map"/></svg>{{ e($siteLabel) }}</span>
            <span><svg class="h-4 w-4"><use href="#i-briefcase"/></svg>{{ e($typeLabel) }}</span>
          </div>
          <div class="job-card__footer">
            <span class="job-card__date">{{ $job->created_at ? $job->created_at->format('d M Y') : 'Baru diposting' }}</span>
            <a class="job-card__link" href="{{ route('jobs.show', $job) }}">Lihat Detail <svg class="h-4 w-4"><use href="#i-chevron-right"/></svg></a>
          </div>
        </article>
      @endforeach
        </section>
      @endif
    @endforeach
    @if(method_exists($jobs, 'hasPages') && $jobs->hasPages())
      <div class="mx-auto mt-6 max-w-[1360px]">{{ $jobs->onEachSide(1)->links() }}</div>
    @endif
  @else
    <section class="jobs-panel mx-auto mt-6 max-w-[1360px] rounded-2xl p-12 text-center">
      <div style="background:#faf5ed;border:1px solid #e8d5be" class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full"><svg class="h-6 w-6 text-[#b28a57]"><use href="#i-filter"/></svg></div>
      <h3 class="text-lg font-semibold text-slate-900">Belum ada lowongan</h3>
      <p class="mt-1 text-sm text-slate-500">Coba ubah kata kunci atau filter pencarian.</p>
      <a href="{{ $resetUrl }}" class="mt-5 inline-flex rounded-xl px-5 py-2.5 text-sm font-semibold text-white" style="background:#b58b56">Reset Filter</a>
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

  // Fallback selector untuk halaman yang tidak memuat Alpine.js.
  document.querySelectorAll('[x-data]').forEach((root) => {
    const details = Array.from(root.querySelectorAll('[id^="detail-"]'));
    const showDetail = (id) => {
      details.forEach((detail) => {
        const active = detail.id === `detail-${id}`;
        detail.style.display = active ? '' : 'none';
        detail.removeAttribute('x-cloak');
      });
    };

    if (details.length) showDetail(details[0].id.replace(/^detail-/, ''));

    root.querySelectorAll('button').forEach((button) => {
      const expression = button.getAttribute('@click') || '';
      const match = expression.match(/select\(['"]([^'"]+)['"]\)/);
      if (!match) return;
      button.addEventListener('click', () => showDetail(match[1]));
    });
  });
});
</script>
@endpush

@endsection

