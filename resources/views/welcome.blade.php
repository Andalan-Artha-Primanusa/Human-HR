<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Karier PT Andalan Artha Primanusa | Human Careers — Lowongan Terverifikasi</title>
  <meta name="description" content="Temukan lowongan kerja terverifikasi di PT Andalan Artha Primanusa. Proses rekrutmen transparan, update status real-time, dan karier profesional bersama tim Andalan.">
  <meta name="keywords" content="lowongan kerja Andalan, karier PT Andalan, rekrutmen Andalan, Human Careers, pekerjaan Jakarta, loker terbaru">
  <meta name="author" content="PT Andalan Artha Primanusa">
  <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
  <meta name="googlebot" content="index, follow">
  <meta name="theme-color" content="#a77d52">
  <link rel="canonical" href="{{ url()->current() }}">

  <meta property="og:site_name" content="Human Careers — PT Andalan">
  <meta property="og:locale" content="id_ID">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Karier PT Andalan Artha Primanusa | Human Careers">
  <meta property="og:description" content="Lowongan terverifikasi & proses rekrutmen transparan bersama PT Andalan Artha Primanusa. Lamar sekarang dan pantau status lamaran secara real-time.">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="{{ asset('storage/media/og-careers.jpg') }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="Human Careers — Portal Karier Resmi PT Andalan Artha Primanusa">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@andalan">
  <meta name="twitter:creator" content="@andalan">
  <meta name="twitter:title" content="Karier PT Andalan Artha Primanusa | Human Careers">
  <meta name="twitter:description" content="Lamar kerja cepat & pantau status real-time. Lowongan terverifikasi dari PT Andalan Artha Primanusa.">
  <meta name="twitter:image" content="{{ asset('storage/media/og-careers.jpg') }}">
  <meta name="twitter:image:alt" content="Human Careers — Portal Karier Resmi PT Andalan">

  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"></noscript>

  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">

  <link rel="preload" as="image" href="{{ asset('assets/banner-abn.png') }}" fetchpriority="high">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  @php
  $jobs           = $jobs          ?? collect();
  $myApps         = $myApps        ?? collect();
  $myAppsSummary  = $myAppsSummary ?? ['total' => ($myApps->count() ?? 0), 'byStatus' => collect()];
  $myAppsProgress = $myAppsProgress ?? collect();
  $sitesSimple    = $sitesSimple   ?? collect();

  $jobsCollection = ($jobs instanceof \Illuminate\Pagination\LengthAwarePaginator)
    ? $jobs->getCollection()
    : collect($jobs);

  $filteredJobs = $jobsCollection->when(
    ($jobsCollection->first()?->getAttributes() ?? null) && array_key_exists('status', $jobsCollection->first()->getAttributes()),
    fn($c) => $c->where('status', 'open'),
    fn($c) => $c
  );

  $byDivision = isset($byDivision) && $byDivision instanceof \Illuminate\Support\Collection
    ? $byDivision
    : $filteredJobs->groupBy('division')->map->count()->sortDesc()
      ->mapWithKeys(fn($v, $k) => [$k ?: 'Tanpa Divisi' => (int)$v]);

  $brandBlue     = '#1d4ed8';
  $brandRed      = '#dc2626';
  $brandBlack    = '#a77d52';
  $brandGray     = '#e5e7eb';
  $brandGrayDark = '#000000';
  $bgMain        = '#f5efe8';
  $bgChip        = '#ede5dc';
  $textSoft      = '#6b4f3a';
  @endphp
{{-- ================= STRUCTURED DATA FINAL ================= --}}

@php
$schema = [];

/** ORGANIZATION */
$schema[] = [
  "@context" => "https://schema.org",
  "@type" => "Organization",
  "@id" => url('/') . "#organization",
  "name" => "PT Andalan Artha Primanusa",
  "alternateName" => "Andalan",
  "url" => url('/'),
  "logo" => [
    "@type" => "ImageObject",
    "url" => asset('assets/ddd.png'),
    "width" => 400,
    "height" => 160
  ],
  "image" => asset('storage/media/og-careers.jpg'),
  "description" => "Portal karier resmi PT Andalan Artha Primanusa. Lowongan terverifikasi dan proses transparan.",
  "email" => "hr@andalan.co.id",
  "address" => [
    "@type" => "PostalAddress",
    "streetAddress" => "Jl. Plaju No.11, Kebon Melati, Tanah Abang",
    "addressLocality" => "Jakarta Pusat",
    "postalCode" => "10230",
    "addressRegion" => "DKI Jakarta",
    "addressCountry" => "ID"
  ],
  "sameAs" => [
    "https://andalan.co.id",
    "https://www.linkedin.com/company/andalan",
    "https://www.instagram.com/andalan"
  ]
];

/** WEBSITE */
$schema[] = [
  "@context" => "https://schema.org",
  "@type" => "WebSite",
  "@id" => url('/') . "#website",
  "name" => "Human Careers — PT Andalan",
  "url" => url('/'),
  "inLanguage" => "id-ID",
  "publisher" => ["@id" => url('/') . "#organization"],
  "potentialAction" => [
    "@type" => "SearchAction",
    "target" => url('/jobs') . "?q={search_term_string}",
    "query-input" => "required name=search_term_string"
  ]
];

/** WEBPAGE */
$schema[] = [
  "@context" => "https://schema.org",
  "@type" => "WebPage",
  "@id" => url()->current() . "#webpage",
  "url" => url()->current(),
  "name" => "Karier PT Andalan | Human Careers",
  "description" => "Temukan lowongan kerja terbaik di PT Andalan.",
  "inLanguage" => "id-ID",
  "isPartOf" => ["@id" => url('/') . "#website"],
  "about" => ["@id" => url('/') . "#organization"],
  "dateModified" => now()->toIso8601String()
];

/** BREADCRUMB */
$schema[] = [
  "@context" => "https://schema.org",
  "@type" => "BreadcrumbList",
  "@id" => url()->current() . "#breadcrumb",
  "itemListElement" => [
    [
      "@type" => "ListItem",
      "position" => 1,
      "name" => "Beranda",
      "item" => route('welcome')
    ],
    [
      "@type" => "ListItem",
      "position" => 2,
      "name" => "Karier",
      "item" => url()->current()
    ]
  ]
];

/** NAVIGATION */
$schema[] = [
  "@context" => "https://schema.org",
  "@type" => "SiteNavigationElement",
  "name" => ["Lowongan", "Masuk", "Daftar", "FAQ"],
  "url" => [
    url('/jobs'),
    url('/login'),
    url('/register'),
    url('/faq')
  ]
];

/** JOB LIST */
if(isset($filteredJobs) && $filteredJobs->isNotEmpty()){
  $jobsList = $filteredJobs->take(10)->values()->map(function($job, $i){
    return [
      "@type" => "ListItem",
      "position" => $i + 1,
      "item" => [
        "@type" => "JobPosting",
        "@id" => route('jobs.show', $job),
        "title" => $job->title ?? '',
        "description" => \Illuminate\Support\Str::limit(strip_tags($job->description ?? ''), 200),
        "datePosted" => optional($job->created_at)->toDateString(),
        "validThrough" => optional($job->deadline_at ?? $job->created_at?->addMonths(3))->toDateString(),
        "employmentType" => "FULL_TIME",
        "hiringOrganization" => [
          "@type" => "Organization",
          "name" => "PT Andalan Artha Primanusa",
          "sameAs" => url('/')
        ],
        "jobLocation" => [
          "@type" => "Place",
          "address" => [
            "@type" => "PostalAddress",
            "addressLocality" => $job->site?->name ?? 'Jakarta',
            "addressCountry" => "ID"
          ]
        ],
        "url" => route('jobs.show', $job)
      ]
    ];
  });

  $schema[] = [
    "@context" => "https://schema.org",
    "@type" => "ItemList",
    "name" => "Lowongan Kerja PT Andalan",
    "url" => url('/jobs'),
    "numberOfItems" => $filteredJobs->count(),
    "itemListElement" => $jobsList
  ];
}
@endphp

<script type="application/ld+json">
{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

  <style>
    :root {
      --blue:       #1d4ed8;
      --red:        #dc2626;
      --brand:      #a77d52;
      --brand-bg:   #f5efe8;
      --brand-chip: #ede5dc;
      --brand-text: #6b4f3a;
      --gray:       #e5e7eb;
    }
    html, body {
      font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
      scroll-behavior: smooth;
    }
    .ring-focus:focus { outline: none; box-shadow: 0 0 0 3px rgba(29,78,216,.35); }
    *:focus-visible   { outline: 3px solid var(--blue); outline-offset: 2px; }
    .card-hover { transition: transform .25s ease, box-shadow .25s ease; }
    .card-hover:hover { transform: translateY(-3px); box-shadow: 0 16px 48px rgba(0,0,0,.1); }
    details > summary { list-style: none; cursor: pointer; }
    details > summary::-webkit-details-marker { display: none; }
    .dropdown[open] > summary svg { transform: rotate(180deg); }
    .to-top { position: fixed; right: 1rem; bottom: 5rem; z-index: 50; display: none; }
    .to-top.show { display: block; }
    .toast { position: fixed; right: 1rem; bottom: 1rem; z-index: 60; display: none; min-width: 240px; max-width: 320px; }
    .toast.show { display: block; animation: slideUp .25s ease; }
    @keyframes slideUp {
      from { transform: translateY(12px); opacity: 0; }
      to   { transform: translateY(0);    opacity: 1; }
    }
    .marquee { position: relative; overflow: hidden; }
    .marquee__track {
      display: flex; gap: .75rem; width: max-content;
      animation: marquee-scroll 28s linear infinite; will-change: transform;
    }
    .marquee:hover .marquee__track { animation-play-state: paused; }
    @keyframes marquee-scroll {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }
    @media (prefers-reduced-motion: reduce) { .marquee__track { animation: none; } }
    .qlink-card {
      border: 1px solid #e5e7eb; border-radius: 1rem; padding: 1.25rem 1.5rem; background: #fff;
      transition: box-shadow .2s ease, transform .2s ease, background .2s ease;
      text-decoration: none; display: flex; flex-direction: column; gap: .35rem;
    }
    .qlink-card:hover { background: #fafaf9; transform: translateY(-2px); box-shadow: 0 8px 28px rgba(167,125,82,.15); }
    .qlink-title { font-size: .95rem; font-weight: 700; color: var(--brand); margin: 0; }
    .qlink-desc  { font-size: .8rem; color: #6b7280; margin: 0; }
  </style>
</head>

<body class="antialiased bg-white text-zinc-900">

  <a href="#maincontent"
    class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-blue-600 focus:text-white focus:rounded focus:px-3 focus:py-2">
    Lewati ke konten utama
  </a>

  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true">
    <symbol id="i-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></g></symbol>
    <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></g></symbol>
    <symbol id="i-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="6 9 12 15 18 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></g></symbol>
    <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1"/></g></symbol>
    <symbol id="i-arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></g></symbol>
    <symbol id="i-apply" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></g></symbol>
    <symbol id="i-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18" stroke-width="2"/></symbol>
  </svg>

  {{-- HEADER --}}
  <header class="sticky top-0 z-50 border-b bg-white/90 backdrop-blur" style="border-color: {{ $brandGray }}">
    <div class="flex items-center justify-between h-16 px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="flex items-center gap-3">
        <button id="btn-nav" class="p-2 md:hidden text-zinc-700 hover:text-black ring-focus"
          aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="nav-mobile">
          <svg class="w-5 h-5" aria-hidden="true"><use href="#i-menu"/></svg>
        </button>
        <a href="{{ route('welcome') }}" class="flex items-center gap-2 font-extrabold tracking-tight"
          style="color: {{ $brandBlack }}" aria-label="Human Careers — Beranda">
          <img src="{{ asset('assets/ddd.png') }}" alt="Logo Human Careers"
            class="object-contain w-auto h-20 mt-4 md:h-20" width="160" height="80" loading="eager">
        </a>
      </div>

      <form action="{{ route('jobs.index') }}" method="GET"
        class="items-center flex-1 hidden max-w-lg mx-6 md:flex" role="search" autocomplete="off">
        <label for="search-desk" class="sr-only">Cari lowongan</label>
        <div class="relative w-full">
          <input id="search-desk" name="q" type="search"
            placeholder="Cari posisi, divisi, atau kata kunci…"
            class="w-full px-10 py-2 bg-white border outline-none rounded-xl text-zinc-800 placeholder-zinc-500 focus:ring-2"
            style="border-color: {{ $brandGray }}; --tw-ring-color: {{ $brandBlue }};" inputmode="search">
          <span class="absolute -translate-y-1/2 left-3 top-1/2 text-zinc-500" aria-hidden="true">
            <svg class="w-5 h-5"><use href="#i-search"/></svg>
          </span>
        </div>
      </form>

      <nav class="items-center hidden gap-6 text-sm md:flex" aria-label="Navigasi utama">
        <a href="{{ route('jobs.index') }}" class="hover:opacity-80" style="color: {{ $brandBlack }}">Lowongan</a>
        @auth
        <a href="{{ route('applications.mine') }}" class="hover:opacity-80" style="color: {{ $brandBlack }}">Lamaran</a>
        <details class="relative dropdown">
          <summary class="flex items-center gap-2 cursor-pointer select-none hover:opacity-80"
            style="color: {{ $brandBlack }}" aria-haspopup="menu" aria-expanded="false">
            @php $uname = auth()->user()->name ?? auth()->user()->email ?? 'Pengguna'; $ini = strtoupper(mb_substr($uname,0,1)); @endphp
            <span class="inline-grid text-xs font-bold border rounded-full place-items-center w-9 h-9"
              style="background: rgba(29,78,216,.08); color: {{ $brandBlue }}; border-color: rgba(29,78,216,.3);"
              aria-hidden="true">{{ $ini }}</span>
            <svg class="w-4 h-4 transition" aria-hidden="true"><use href="#i-chevron"/></svg>
          </summary>
          <div class="absolute right-0 p-2 mt-2 bg-white border shadow-2xl w-60 rounded-xl"
            style="border-color: {{ $brandGray }}" role="menu">
            <div class="px-2 py-1.5 text-[11px] text-zinc-500">Masuk sebagai</div>
            <div class="px-2 pb-2 text-sm truncate text-zinc-800">{{ $uname }}</div>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-zinc-100" role="menuitem">Profil</a>
            <a href="{{ route('applications.mine') }}" class="block px-3 py-2 text-sm rounded-lg hover:bg-zinc-100" role="menuitem">Lamaran Saya</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1" role="none">
              @csrf
              <button type="submit" class="w-full px-3 py-2 text-sm text-left rounded-lg hover:bg-zinc-50"
                style="color: {{ $brandRed }};" role="menuitem">Keluar</button>
            </form>
          </div>
        </details>
        @else
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 hover:opacity-80" style="color: {{ $brandBlack }}">
          <svg class="w-4 h-4" aria-hidden="true"><use href="#i-user"/></svg> Masuk
        </a>
        <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-xl text-white font-semibold hover:opacity-90"
          style="background: {{ $brandBlue }};">Daftar</a>
        @endauth
      </nav>
    </div>

    <div id="nav-mobile" class="hidden bg-white border-t md:hidden"
      style="border-color: {{ $brandGray }}" aria-label="Menu navigasi mobile">
      <div class="px-4 py-3 space-y-3">
        <form action="{{ route('jobs.index') }}" method="GET" role="search" autocomplete="off">
          <label for="search-mob" class="sr-only">Cari lowongan</label>
          <div class="relative w-full">
            <input id="search-mob" name="q" type="search"
              placeholder="Cari posisi, divisi, atau kata kunci…"
              class="w-full py-2 pl-10 pr-3 bg-white border rounded-lg outline-none text-zinc-800 placeholder-zinc-500 focus:ring-2"
              style="border-color: {{ $brandGray }}; --tw-ring-color: {{ $brandBlue }};" inputmode="search">
            <span class="absolute -translate-y-1/2 left-3 top-1/2 text-zinc-500" aria-hidden="true">
              <svg class="w-5 h-5"><use href="#i-search"/></svg>
            </span>
          </div>
        </form>
        <a href="{{ route('jobs.index') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Lowongan</a>
        @auth
        <a href="{{ route('applications.mine') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Lamaran</a>
        <hr style="border-color: {{ $brandGray }}">
        <a href="{{ route('profile.edit') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Profil</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full px-2 py-2 text-left rounded-lg" style="color: {{ $brandRed }};">Keluar</button>
        </form>
        @else
        <div class="flex items-center gap-3">
          <a href="{{ route('login') }}" class="inline-flex items-center gap-1 px-3 py-2 border rounded-lg hover:bg-zinc-100"
            style="border-color: {{ $brandGray }};">
            <svg class="w-4 h-4" aria-hidden="true"><use href="#i-user"/></svg> Masuk
          </a>
          <a href="{{ route('register') }}" class="px-3 py-2 font-semibold text-white rounded-lg hover:opacity-90"
            style="background: {{ $brandRed }};">Daftar</a>
        </div>
        @endauth
      </div>
    </div>

    <div class="bg-white border-t" style="border-color: {{ $brandGray }}">
      <nav class="flex items-center h-10 px-4 mx-auto text-xs max-w-7xl sm:px-6 lg:px-8 text-zinc-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center gap-2" itemscope itemtype="https://schema.org/BreadcrumbList">
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a href="{{ route('welcome') }}" class="hover:text-zinc-800" itemprop="item">
              <span itemprop="name">Beranda</span></a>
            <meta itemprop="position" content="1">
          </li>
          <li aria-hidden="true">/</li>
          <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <span class="text-zinc-800" aria-current="page" itemprop="name">Karier</span>
            <meta itemprop="position" content="2">
          </li>
        </ol>
      </nav>
    </div>
  </header>

  {{-- MAIN --}}
  <main id="maincontent">

    {{-- HERO --}}
    <section aria-label="Selamat datang di Human Careers">
      <div class="relative overflow-hidden">
        <img src="{{ asset('assets/banner-abn.png') }}"
          alt="Build Your Career With Andalan — tim profesional PT Andalan Artha Primanusa"
          class="w-full h-[440px] object-cover"
          width="1440" height="440" fetchpriority="high" decoding="async">
        <div class="absolute inset-0 flex items-center justify-end bg-black/45">
          <div class="max-w-2xl px-10 text-right text-white">
            <h1 class="mb-4 text-4xl font-bold leading-tight md:text-5xl">
              Welcome to<br>Andalan Career
            </h1>
            <p class="text-base leading-relaxed md:text-lg">
              Join with us and be part of a team committed to growth, professionalism, and excellence.
            </p>
            <p class="mt-3 text-base leading-relaxed md:text-lg">
              Explore our career opportunities and find the path that matches your aspirations with PT Andalan Artha Primanusa.
            </p>
            <div class="flex flex-wrap justify-end gap-3 mt-6">
              <a href="{{ route('jobs.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm bg-white hover:bg-zinc-100 transition"
                style="color: {{ $brandBlack }}">Lihat Lowongan</a>
              @guest
              <a href="{{ route('register') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white hover:opacity-90 transition"
                style="background: {{ $brandBlue }}">Daftar Sekarang</a>
              @endguest
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- QUICK LINKS --}}
    <section aria-label="Menu cepat" style="padding: 2.5rem 1.5rem; background: {{ $bgMain }}">
      <div class="mx-auto max-w-7xl">
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem">
          <a href="{{ route('login') }}" class="qlink-card">
            <span class="qlink-title">🔐 Masuk</span>
            <span class="qlink-desc">Akses akun Anda</span>
          </a>
          <a href="{{ route('register') }}" class="qlink-card">
            <span class="qlink-title">📝 Daftar</span>
            <span class="qlink-desc">Buat akun baru gratis</span>
          </a>
          <a href="{{ route('jobs.index') }}" class="qlink-card">
            <span class="qlink-title">💼 Lowongan</span>
            <span class="qlink-desc">Lihat semua posisi terbuka</span>
          </a>
          <a href="/faq" class="qlink-card">
            <span class="qlink-title">❓ FAQ</span>
            <span class="qlink-desc">Panduan &amp; pertanyaan umum</span>
          </a>
        </div>
      </div>
    </section>

    {{-- SITES MARQUEE --}}
    @php
    $sitesCol  = ($sitesSimple instanceof \Illuminate\Support\Collection) ? $sitesSimple : collect($sitesSimple ?? []);
    $sitesNorm = $sitesCol->filter(fn($s) => !empty($s['name']))
      ->map(function($s) {
        $name  = (string)($s['name'] ?? '—');
        $dot   = preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', (string)($s['dot'] ?? '')) ? $s['dot'] : '#a77d52';
        $param = $s['code'] ?? $s['id'] ?? $name;
        return ['name' => $name, 'dot' => $dot, 'param' => $param];
      })->values();
    $sitesDup = $sitesNorm->concat($sitesNorm);
    @endphp

    <section class="border-b" style="border-color: {{ $brandGray }}; background: {{ $bgMain }}" aria-label="Lokasi Site">
      <div class="px-6 py-8 mx-auto max-w-7xl lg:px-8">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold" style="color: #1f2937">Lokasi Site</h2>
        </div>
        @if($sitesNorm->isNotEmpty())
        <div class="marquee" role="region" aria-label="Daftar lokasi site — hover untuk berhenti">
          <div class="marquee__track">
            @foreach($sitesDup as $s)
            <div class="shrink-0">
              <a href="{{ route('jobs.index', ['site' => $s['param']]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 transition-all duration-200 border rounded-full hover:shadow-md hover:-translate-y-px"
                style="border-color: {{ $brandGray }}; background: {{ $bgChip }}; color: {{ $textSoft }};"
                aria-label="Lihat lowongan site {{ $s['name'] }}">
                <span class="inline-block w-2.5 h-2.5 rounded-full flex-shrink-0"
                  style="background: {{ $s['dot'] }}" aria-hidden="true"></span>
                <span class="text-sm whitespace-nowrap">{{ $s['name'] }}</span>
              </a>
            </div>
            @endforeach
          </div>
        </div>
        <noscript>
          <ul class="flex flex-wrap gap-2 mt-3" role="list">
            @foreach($sitesNorm as $s)
            <li>
              <a href="{{ route('jobs.index', ['site' => $s['param']]) }}"
                class="inline-flex items-center gap-2 px-4 py-2 border rounded-full"
                style="border-color: {{ $brandGray }}; background: {{ $bgChip }}; color: {{ $textSoft }};">
                <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $s['dot'] }}"></span>
                <span class="text-sm">{{ $s['name'] }}</span>
              </a>
            </li>
            @endforeach
          </ul>
        </noscript>
        @else
        <p class="text-sm" style="color: {{ $textSoft }}">Belum ada data site.</p>
        @endif
      </div>
    </section>

    {{-- TAHAPAN REKRUTMEN --}}
    <section class="py-10 bg-white" aria-label="Tahapan proses rekrutmen PT Andalan">
      <div class="px-6 mx-auto max-w-7xl">
        <div class="overflow-hidden transition duration-300 border shadow-sm rounded-2xl hover:shadow-md" style="border-color: {{ $brandGray }}">
          <div class="grid md:grid-cols-2">
            <div class="relative">
              <img src="{{ asset('assets/foto1.png') }}"
                class="w-full h-full object-cover min-h-[320px]"
                alt="Tim HR PT Andalan berkolaborasi dalam proses rekrutmen yang transparan dan profesional"
                width="640" height="480" loading="lazy" decoding="async">
              <div class="absolute bottom-0 w-full p-5 text-sm text-white"
                style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                <p class="font-semibold">Lingkungan kerja kolaboratif &amp; profesional</p>
                <p class="text-xs opacity-80">Kami menghargai proses yang transparan &amp; adil</p>
              </div>
            </div>
            <div class="p-6 space-y-6 md:p-10">
              <div>
                <h2 class="text-xl font-bold md:text-2xl" style="color:#1f2937">Tahapan Rekrutmen</h2>
                <p class="mt-1 text-sm" style="color:#6b7280">Proses seleksi kami dirancang transparan, cepat, dan profesional.</p>
              </div>
              @php
              $steps = [
                ['Pengajuan Lamaran', 'Kirim CV & data diri melalui sistem kami',       '1–2 hari'],
                ['Penyaringan CV',    'Tim HR akan meninjau kesesuaian kandidat',        '2–3 hari'],
                ['Wawancara / Tes',   'Interview HR & user + tes kemampuan (jika ada)', '3–5 hari'],
                ['Penawaran Kerja',   'Kandidat terpilih akan menerima offering letter','1–2 hari'],
                ['Onboarding',        'Mulai bekerja & orientasi perusahaan',           'Hari pertama'],
              ];
              @endphp
              <ol class="space-y-4" aria-label="Langkah-langkah rekrutmen">
                @foreach($steps as $i => [$title, $desc, $time])
                <li class="flex gap-4 p-3 transition rounded-xl hover:bg-gray-50">
                  <div class="flex items-center justify-center text-sm font-bold text-white rounded-full shadow w-9 h-9 shrink-0"
                    style="background:#a77d52" aria-hidden="true">{{ $i + 1 }}</div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <p class="text-sm font-semibold" style="color:#1f2937">{{ $title }}</p>
                      <span class="text-xs" style="color:#9ca3af">{{ $time }}</span>
                    </div>
                    <p class="mt-1 text-xs" style="color:#6b7280">{{ $desc }}</p>
                  </div>
                </li>
                @endforeach
              </ol>
              <p class="pt-4 text-xs border-t" style="color:#9ca3af; border-color:#e5e7eb;">
                *Durasi dapat berbeda tergantung posisi &amp; jumlah pelamar
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

<section class="px-6 py-10 mx-auto max-w-7xl lg:px-8" aria-label="Lowongan kerja terbaru PT Andalan">
  <div class="border shadow-sm rounded-2xl" style="border-color: #e5e7eb; background: {{ $bgMain }}">
    
    {{-- HEADER --}}
    <div class="flex items-center justify-between p-6 border-b" style="border-color: #e5e7eb">
      <h2 class="font-semibold" style="color: #1f2937">Lowongan Terbaru</h2>
      <a href="{{ route('jobs.index') }}"
        class="text-sm font-medium hover:opacity-80"
        style="color: #a77d52">
        Lihat semua →
      </a>
    </div>

    <div class="p-5">
      @if(method_exists($jobs,'count') ? $jobs->count() === 0 : ($jobs->isEmpty() ?? true))
        <p style="color: {{ $textSoft }}">Belum ada lowongan saat ini.</p>
      @else

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

        @foreach ($jobs as $job)
        @php
          $excerpt = \Illuminate\Support\Str::limit(strip_tags($job->description ?? ''), 120);
          $site    = $job->site ?? null;
        @endphp

        <article class="border rounded-xl card-hover"
          style="border-color: #e5e7eb; background: {{ $bgChip }}">

          <div class="p-5">

            {{-- HEADER --}}
            <div class="flex items-start gap-3">
              <div class="p-2.5 rounded-lg text-white shrink-0" style="background: #a77d52">
                <svg class="w-5 h-5"><use href="#i-briefcase"/></svg>
              </div>

              <div class="min-w-0">
                <a href="{{ route('jobs.show', $job) }}"
                  class="block font-semibold hover:opacity-80"
                  style="color: #1f2937">
                  {{ $job->title }}
                </a>

                {{-- 🔥 SITE + ADDRESS + REGION (FIXED) --}}
                <p class="text-[11px] mt-0.5" style="color: #6b4f3a">

                  {{-- SITE --}}
                  <span class="font-medium text-black">
                    {{ $site->name ?? 'Site tidak tersedia' }}
                  </span>

                  {{-- ADDRESS --}}
                  <span>
                    • {{ $site->region ?? 'Alamat belum tersedia' }}
                  </span>

                  {{-- REGION --}}
                  @if(!empty($site?->region))
                    , {{ $site->region }}
                  @endif

                  {{-- DATE --}}
                  • Diposting {{ optional($job->created_at)->diffForHumans() }}

                </p>

              </div>
            </div>

            {{-- DESKRIPSI --}}
            @if(!empty($excerpt))
              <p class="mt-3 text-sm line-clamp-2" style="color: #6b4f3a">
                {{ $excerpt }}
              </p>
            @endif

            {{-- ACTION --}}
            <div class="flex items-center justify-between mt-4">

              <a href="{{ route('jobs.show', $job) }}"
                class="inline-flex items-center gap-1.5 text-sm font-medium hover:opacity-80"
                style="color: #a77d52">
                Detail
                <svg class="w-4 h-4"><use href="#i-arrow-right"/></svg>
              </a>

              @auth
              <form action="{{ route('applications.store', $job) }}" method="POST"
                onsubmit="return confirm('Lamar posisi ini?')">
                @csrf
                <button type="submit"
                  class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-lg text-white"
                  style="background: #a77d52">
                  <svg class="w-4 h-4"><use href="#i-apply"/></svg>
                  Lamar
                </button>
              </form>
              @else
              <a href="{{ route('login') }}?intended={{ urlencode(route('jobs.show',$job)) }}"
                class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-lg text-white"
                style="background: #a77d52">
                <svg class="w-4 h-4"><use href="#i-user"/></svg>
                Masuk
              </a>
              @endauth

            </div>

          </div>
        </article>

        @endforeach

      </div>

      {{-- PAGINATION --}}
      @if(method_exists($jobs,'withQueryString'))
        <div class="mt-6">
          {{ $jobs->withQueryString()->links() }}
        </div>
      @endif

      @endif
    </div>
  </div>
</section>
  </main>

  {{-- FOOTER --}}
  <footer style="background: {{ $brandGrayDark }};" aria-label="Footer situs Human Careers">
    <div class="px-6 mx-auto text-white max-w-7xl lg:px-8 py-14">
      <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <img src="{{ asset('assets/dddd.png') }}" alt="Logo Human Careers — PT Andalan Artha Primanusa"
            class="object-contain w-auto h-32 mb-3 md:h-40" width="160" height="160" loading="lazy">
          <p class="text-sm leading-relaxed text-zinc-300">
            Portal karier resmi PT Andalan Artha Primanusa. Transparan, profesional, dan terpercaya untuk seluruh pencari kerja.
          </p>
        </div>
        <nav aria-label="Navigasi footer">
          <h3 class="mb-3 font-semibold">Navigasi</h3>
          <ul class="space-y-2 text-sm text-zinc-300">
            <li><a href="{{ route('jobs.index') }}" class="hover:text-white">Lowongan</a></li>
            @auth
            <li><a href="{{ route('applications.mine') }}" class="hover:text-white">Lamaran Saya</a></li>
            <li><a href="{{ route('profile.edit') }}" class="hover:text-white">Profil</a></li>
            @else
            <li><a href="{{ route('login') }}" class="hover:text-white">Masuk</a></li>
            <li><a href="{{ route('register') }}" class="hover:text-white">Daftar</a></li>
            @endauth
          </ul>
        </nav>
        <nav aria-label="Info perusahaan">
          <h3 class="mb-3 font-semibold">Perusahaan</h3>
          <ul class="space-y-2 text-sm text-zinc-300">
            <li><a href="#" class="hover:text-white">Tentang Kami</a></li>
            <li><a href="#" class="hover:text-white">Kebijakan Privasi</a></li>
            <li><a href="#" class="hover:text-white">Syarat &amp; Ketentuan</a></li>
            <li><a href="#" class="hover:text-white">Etika Rekrutmen</a></li>
          </ul>
        </nav>
        <div>
          <h3 class="mb-3 font-semibold">Kontak</h3>
          <p class="mb-2 text-sm text-zinc-300">
            Email: <a href="mailto:hr@andalan.co.id" class="underline hover:text-white">hr@andalan.co.id</a>
          </p>
          <address class="text-sm not-italic leading-relaxed text-zinc-300">
            PT Andalan Artha Primanusa<br>
            Jl. Plaju No.11, Kebon Melati,<br>
            Tanah Abang, Jakarta Pusat 10230,<br>
            DKI Jakarta, Indonesia
          </address>
          <div class="flex gap-3 mt-4" aria-label="Media sosial PT Andalan">
            <a href="https://andalan.co.id" target="_blank" rel="noopener noreferrer"
              class="p-2 transition rounded-lg bg-white/10 hover:bg-white/20" aria-label="Website resmi PT Andalan" title="Website">
              <svg class="w-5 h-5" aria-hidden="true"><use href="#i-globe"/></svg>
            </a>
            <a href="https://instagram.com/andalan" target="_blank" rel="noopener noreferrer"
              class="p-2 transition rounded-lg bg-white/10 hover:bg-pink-500" aria-label="Instagram PT Andalan" title="Instagram">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M7.75 2C4.57 2 2 4.57 2 7.75v8.5C2 19.43 4.57 22 7.75 22h8.5c3.18 0 5.75-2.57 5.75-5.75v-8.5C22 4.57 19.43 2 16.25 2h-8.5zm4.25 5.5a4.75 4.75 0 110 9.5 4.75 4.75 0 010-9.5zm6-1.25a1.25 1.25 0 11-2.5 0 1.25 1.25 0 012.5 0z"/>
              </svg>
            </a>
            <a href="https://linkedin.com/company/andalan" target="_blank" rel="noopener noreferrer"
              class="p-2 transition rounded-lg bg-white/10 hover:bg-blue-600" aria-label="LinkedIn PT Andalan" title="LinkedIn">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4.98 3.5C4.98 4.88 3.86 6 2.49 6 1.12 6 0 4.88 0 3.5S1.12 1 2.49 1c1.37 0 2.49 1.12 2.49 2.5zM0 8h5v16H0V8zm7.5 0h4.7v2.2h.07c.65-1.23 2.25-2.5 4.63-2.5 4.95 0 5.86 3.25 5.86 7.48V24h-5v-7.9c0-1.88-.03-4.3-2.62-4.3-2.63 0-3.03 2.05-3.03 4.16V24h-5V8z"/>
              </svg>
            </a>
            <a href="https://tiktok.com/@andalan" target="_blank" rel="noopener noreferrer"
              class="p-2 transition rounded-lg bg-white/10 hover:bg-zinc-800" aria-label="TikTok PT Andalan" title="TikTok">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M12 2h3a5 5 0 005 5v3a8 8 0 01-5-1.5v7.5a6 6 0 11-6-6h1v3h-1a3 3 0 103 3V2z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
      <div class="flex flex-col items-center justify-between gap-4 pt-6 mt-12 text-sm border-t md:flex-row border-white/20">
        <p class="text-center text-zinc-400 md:text-left">&copy; {{ date('Y') }} PT Andalan Artha Primanusa Tbk. Seluruh Hak Dilindungi.</p>
        <p class="text-xs text-zinc-500">Powered by Human Careers System Andalan</p>
      </div>
    </div>
  </footer>

  <button id="toTop" class="p-3 bg-white border rounded-full shadow to-top hover:shadow-md ring-focus"
    style="border-color: {{ $brandGray }}" aria-label="Kembali ke atas halaman">
    <svg class="w-5 h-5 text-zinc-700" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M12 5l-7 7m7-7 7 7M12 5v14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </button>

  <script>
    (function () {
      'use strict';
      var btnNav = document.getElementById('btn-nav');
      var navMob = document.getElementById('nav-mobile');
      if (btnNav && navMob) {
        btnNav.addEventListener('click', function () {
          var exp = this.getAttribute('aria-expanded') === 'true';
          this.setAttribute('aria-expanded', String(!exp));
          navMob.classList.toggle('hidden');
        });
      }
      var toTop = document.getElementById('toTop');
      if (toTop) {
        window.addEventListener('scroll', function () {
          toTop.classList.toggle('show', window.scrollY > 320);
        }, { passive: true });
        toTop.addEventListener('click', function () {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }
      window.showToast = function () {
        var t = document.getElementById('toast');
        if (!t) return;
        t.classList.add('show');
        setTimeout(function () { t.classList.remove('show'); }, 3500);
      };
    })();
  </script>

</body>
</html>