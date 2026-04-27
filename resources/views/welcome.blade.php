<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  {{-- ===== PRIMARY SEO - lebih deskriptif & keyword-rich ===== --}}
  <title>@yield('title', 'karir-andalan')</title>
  <meta name="description" content="Temukan lowongan kerja terbaru di PT Andalan Artha Primanusa. Proses rekrutmen transparan, pantau status lamaran secara real-time, dan bergabunglah bersama tim profesional Andalan.">
  <meta name="keywords" content="lowongan kerja Andalan, karier PT Andalan, rekrutmen 2025, Human Careers, loker Jakarta, pekerjaan terbaru Andalan, loker terbuka">
  <meta name="author" content="PT Andalan Artha Primanusa">
  <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
  <meta name="googlebot" content="index, follow">
  <meta name="theme-color" content="#a77d52">
  <link rel="canonical" href="{{ url()->current() }}">

  {{-- ===== OPEN GRAPH ===== --}}
  <meta property="og:site_name" content="Human Careers - PT Andalan Artha Primanusa">
  <meta property="og:locale" content="id_ID">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Lowongan Kerja PT Andalan Artha Primanusa 2025 | Human Careers">
  <meta property="og:description" content="Lowongan kerja terverifikasi & proses rekrutmen transparan. Lamar sekarang dan pantau status lamaran real-time bersama PT Andalan Artha Primanusa.">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="{{ asset('storage/media/og-careers.jpg') }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="Human Careers - Portal Karier Resmi PT Andalan Artha Primanusa">

  {{-- ===== TWITTER CARD ===== --}}
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Lowongan Kerja PT Andalan Artha Primanusa | Human Careers">
  <meta name="twitter:description" content="Lamar kerja cepat & pantau status real-time. Lowongan terverifikasi dari PT Andalan Artha Primanusa.">
  <meta name="twitter:image" content="{{ asset('storage/media/og-careers.jpg') }}">
  <meta name="twitter:image:alt" content="Human Careers - Portal Karier Resmi PT Andalan">

  {{-- ===== DNS & FONT PRELOAD ===== --}}
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap"></noscript>

  {{-- ===== FAVICON ===== --}}
  <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
  <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">

  {{-- ===== PRELOAD HERO ===== --}}
  <link rel="preload" as="image" href="{{ asset('assets/banner-abn.png') }}" fetchpriority="high">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  {{-- ===== BLADE VARIABLES ===== --}}
  @php
    $jobs = $jobs ?? collect();
    $myApps = $myApps ?? collect();
    $myAppsSummary = $myAppsSummary ?? ['total' => 0, 'byStatus' => collect()];
    $myAppsProgress = $myAppsProgress ?? collect();
    $sitesSimple = $sitesSimple ?? collect();

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
        : $filteredJobs->groupBy('division')
            ->map(fn($items) => $items->count())
            ->sortDesc()
            ->mapWithKeys(fn($v, $k) => [$k ?: 'Tanpa Divisi' => (int) $v]);

  @endphp

  {{-- ===== STRUCTURED DATA ===== --}}
  @php
    $schema = [];

    // 1. Organization
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
        "description" => "Portal karier resmi pt andalan artha primanusa. Lowongan terverifikasi dan proses rekrutmen transparan.",
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

    // 2. WebSite - SearchAction format EntryPoint yang benar
    $schema[] = [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "@id" => url('/') . "#website",
        "name" => "Human Careers - PT Andalan Artha Primanusa",
        "url" => url('/'),
        "inLanguage" => "id-ID",
        "publisher" => ["@id" => url('/') . "#organization"],
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => [
                "@type" => "EntryPoint",
                "urlTemplate" => url('/jobs') . "?q={search_term_string}"
            ],
            "query-input" => "required name=search_term_string"
        ]
    ];

    // 3. WebPage
    $schema[] = [
        "@context" => "https://schema.org",
        "@type" => "WebPage",
        "@id" => url()->current() . "#webpage",
        "url" => url()->current(),
        "name" => "Lowongan Kerja PT Andalan Artha Primanusa 2025 | Human Careers",
        "description" => "Temukan lowongan kerja terbaru di PT Andalan Artha Primanusa. Proses rekrutmen transparan dan profesional.",
        "inLanguage" => "id-ID",
        "isPartOf" => ["@id" => url('/') . "#website"],
        "about" => ["@id" => url('/') . "#organization"],
        "breadcrumb" => ["@id" => url()->current() . "#breadcrumb"],
        "dateModified" => now()->toIso8601String()
    ];

    // 4. BreadcrumbList
    $schema[] = [
        "@context" => "https://schema.org",
        "@type" => "BreadcrumbList",
        "@id" => url()->current() . "#breadcrumb",
        "itemListElement" => [
            ["@type" => "ListItem", "position" => 1, "name" => "Beranda", "item" => route('welcome')],
            ["@type" => "ListItem", "position" => 2, "name" => "Lowongan Kerja", "item" => url()->current()]
        ]
    ];

    // 5. SiteNavigationElement
    $schema[] = [
        "@context" => "https://schema.org",
        "@type" => "SiteNavigationElement",
        "name" => ["Lowongan", "Masuk", "Daftar", "FAQ"],
        "url" => [url('/jobs'), url('/login'), url('/register'), url('/faq')]
    ];

    // 6. ItemList JobPosting
    if (isset($filteredJobs) && $filteredJobs->isNotEmpty()) {
        $jobsList = $filteredJobs->take(10)->values()->map(function ($job, $i) {
            return [
                "@type" => "ListItem",
                "position" => $i + 1,
                "item" => [
                    "@type" => "JobPosting",
                    "@id" => route('jobs.show', $job),
                    "title" => $job->title ?? '',
                    "description" => \Illuminate\Support\Str::limit(strip_tags($job->description ?? ''), 250),
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
            "name" => "Lowongan Kerja Terbaru PT Andalan Artha Primanusa",
            "url" => url('/jobs'),
            "numberOfItems" => $filteredJobs->count(),
            "itemListElement" => $jobsList
        ];
    }

    // 7. FAQPage - boost rich result di Google
    $schema[] = [
        "@context" => "https://schema.org",
        "@type" => "FAQPage",
        "mainEntity" => [
            [
                "@type" => "Question",
                "name" => "Bagaimana cara melamar kerja di PT Andalan Artha Primanusa?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Daftarkan akun Anda di Human Careers, pilih posisi yang sesuai, lalu klik tombol Lamar. Proses sepenuhnya online dan transparan."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Apakah proses rekrutmen PT Andalan gratis?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Ya, seluruh proses rekrutmen di PT Andalan Artha Primanusa sepenuhnya gratis. Kami tidak memungut biaya apapun dari pelamar."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Berapa lama proses seleksi berlangsung?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Proses seleksi umumnya berlangsung 7-14 hari kerja, mulai dari pengajuan lamaran hingga penawaran kerja, tergantung posisi dan jumlah pelamar."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Bagaimana cara memantau status lamaran?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Setelah login, buka menu Lamaran Saya untuk melihat status terkini lamaran Anda secara real-time."
                ]
            ]
        ]
    ];
  @endphp

  <script type="application/ld+json">
  {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
  </script>
  <script>
    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function () {
      var btnNavToggle = document.getElementById('btn-nav-toggle');
      var mobileMenu = document.getElementById('mobile-menu');
      var mobileMenuPanel = document.getElementById('mobile-menu-panel');
      var btnMenuClose = document.getElementById('btn-menu-close');
      var mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
      function openMenu() {
        mobileMenu.classList.remove('pointer-events-none', 'opacity-0');
        mobileMenu.classList.add('pointer-events-auto', 'opacity-100');
        mobileMenuPanel.classList.remove('-translate-x-full');
        mobileMenuPanel.classList.add('translate-x-0');
        document.body.classList.add('overflow-hidden');
      }
      function closeMenu() {
        mobileMenu.classList.add('pointer-events-none', 'opacity-0');
        mobileMenu.classList.remove('pointer-events-auto', 'opacity-100');
        mobileMenuPanel.classList.add('-translate-x-full');
        mobileMenuPanel.classList.remove('translate-x-0');
        document.body.classList.remove('overflow-hidden');
      }
      if (btnNavToggle && mobileMenu && mobileMenuPanel) {
        btnNavToggle.addEventListener('click', openMenu);
      }
      if (btnMenuClose) {
        btnMenuClose.addEventListener('click', closeMenu);
      }
      if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMenu);
      }
      // ESC key to close
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeMenu();
      });
    });
  </script>

  <style>

    html, body {
      font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, sans-serif;
      scroll-behavior: smooth;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    /* Aksesibilitas focus */
    *:focus-visible {
      outline: 3px solid #1d4ed8;
      outline-offset: 3px;
      border-radius: 4px;
    }

    /* Card hover */
    .card-hover {
      transition: transform .25s cubic-bezier(.22,.68,0,1.2), box-shadow .25s ease;
    }
    .card-hover:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 48px rgba(167,125,82,.18);
    }

    /* Details / dropdown */
    details > summary { list-style: none; cursor: pointer; }
    details > summary::-webkit-details-marker { display: none; }
    .dropdown[open] > summary .chevron-icon { transform: rotate(180deg); }
    .chevron-icon { transition: transform .2s ease; }

    /* Back to top */
    #toTop {
      position: fixed;
      right: 1.25rem;
      bottom: 5rem;
      z-index: 50;
      opacity: 0;
      pointer-events: none;
      transform: translateY(10px);
      transition: opacity .3s ease, transform .3s ease;
    }
    #toTop.show {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0);
    }

    /* Toast notifikasi */
    .toast {
      position: fixed;
      right: 1rem;
      bottom: 1rem;
      z-index: 60;
      opacity: 0;
      pointer-events: none;
      min-width: 240px;
      max-width: 320px;
      transform: translateY(10px);
      transition: opacity .25s ease, transform .25s ease;
    }
    .toast.show {
      opacity: 1;
      pointer-events: auto;
      transform: translateY(0);
    }

    /* Marquee */
    .marquee {
      position: relative;
      overflow: hidden;
      mask-image: linear-gradient(to right, transparent 0%, black 6%, black 94%, transparent 100%);
      -webkit-mask-image: linear-gradient(to right, transparent 0%, black 6%, black 94%, transparent 100%);
    }
    .marquee__track {
      display: flex;
      gap: .75rem;
      width: max-content;
      animation: marquee-scroll 32s linear infinite;
      will-change: transform;
    }
    .marquee:hover .marquee__track,
    .marquee:focus-within .marquee__track { animation-play-state: paused; }
    @keyframes marquee-scroll {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }
    @media (prefers-reduced-motion: reduce) {
      .marquee__track { animation: none; }
    }

    /* Quick link cards */
    .qlink-card {
      border: 1px solid #e5e7eb;
      border-radius: 1rem;
      padding: 1.25rem 1.5rem;
      background: #fff;
      transition: box-shadow .2s ease, transform .2s ease, border-color .2s ease;
      text-decoration: none;
      display: flex;
      flex-direction: column;
      gap: .35rem;
    }
    .qlink-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 32px rgba(167,125,82,.15);
      border-color: #a77d52;
    }
    .qlink-title { font-size: .95rem; font-weight: 700; color: #a77d52; margin: 0; }
    .qlink-desc  { font-size: .8rem; color: #6b7280; margin: 0; }

    /* Badge */
    .badge {
      display: inline-flex;
      align-items: center;
      font-size: .65rem;
      font-weight: 700;
      letter-spacing: .04em;
      text-transform: uppercase;
      padding: .2rem .55rem;
      border-radius: 999px;
    }
    .badge-open { background: #dcfce7; color: #15803d; }
    .badge-new  { background: #dbeafe; color: #1d4ed8; }
  </style>
</head>

<body class="antialiased bg-white text-zinc-900">

  {{-- Skip nav untuk screen reader --}}
  <a href="#maincontent"
    class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-blue-600 focus:text-white focus:rounded-lg focus:px-4 focus:py-2 focus:shadow-lg focus:text-sm focus:font-semibold">
    Lewati ke konten utama
  </a>

  {{-- SVG sprite --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true">
    <symbol id="i-menu"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></g></symbol>
    <symbol id="i-search"      viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></g></symbol>
    <symbol id="i-chevron"     viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="6 9 12 15 18 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    <symbol id="i-user"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></g></symbol>
    <symbol id="i-briefcase"   viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1"/></g></symbol>
    <symbol id="i-arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></g></symbol>
    <symbol id="i-apply"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></g></symbol>
    <symbol id="i-globe"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M3 12h18M12 3c2.5 2.5 2.5 15 0 18M12 3c-2.5 2.5-2.5 15 0 18" stroke-width="2"/></symbol>
    <symbol id="i-help"        viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9.6 9a2.4 2.4 0 1 1 3.76 2c-.86.56-1.36 1.02-1.36 2"/><circle cx="12" cy="17" r=".8" fill="currentColor" stroke="none"/></g></symbol>
    <symbol id="i-map-pin"     viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/></g></symbol>
    <symbol id="i-clock"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></g></symbol>
    <symbol id="i-arrow-up"    viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 5l-7 7m7-7 7 7M12 5v14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  </svg>

  {{-- ============================================================
       HEADER
  ============================================================ --}}
  {{-- ============================================================
       HEADER
  ============================================================ --}}
  <header class="sticky top-0 z-50 transition-all duration-300 border-b bg-white/80 backdrop-blur-md border-slate-200/60" id="site-header" style="margin-top:0;padding-top:0;">
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16 md:h-20">
        
        {{-- Logo & Mobile Trigger --}}
        <div class="flex items-center gap-4">
          <button id="btn-nav-toggle" class="p-2 transition-colors md:hidden rounded-xl text-slate-600 hover:bg-slate-100" aria-label="Toggle menu">
            <svg class="w-6 h-6"><use href="#i-menu"/></svg>
          </button>
          <a href="{{ route('welcome') }}" class="flex items-center group">
            <img src="{{ asset('assets/logofix.png') }}" alt="Logo" class="object-contain w-auto h-10 transition-transform duration-300 md:h-12 group-hover:scale-105">
          </a>
        </div>

        {{-- Desktop Search (Premium) --}}
        <div class="flex-1 hidden max-w-md mx-8 md:flex">
          <form action="{{ route('jobs.index') }}" method="GET" class="relative w-full group">
            <input type="search" name="q" placeholder="Cari posisi atau lokasi..." 
              class="w-full pl-11 pr-4 py-2.5 bg-slate-100/50 border border-slate-200/60 rounded-2xl text-sm focus:bg-white focus:ring-2 focus:ring-[#a77d52]/20 focus:border-[#a77d52] transition-all outline-none"
              value="{{ request('q') }}">
            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#a77d52] transition-colors">
              <svg class="w-4 h-4"><use href="#i-search"/></svg>
            </div>
          </form>
        </div>

        {{-- Desktop Nav --}}
        <nav class="items-center hidden gap-1 md:flex">
          <a href="{{ route('jobs.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-[#a77d52] transition-colors rounded-xl hover:bg-slate-50">Lowongan</a>
          
          @auth
            <div class="w-px h-6 mx-2 bg-slate-200"></div>
            <details class="relative group">
              <summary class="flex items-center gap-3 py-1 pl-2 pr-1 list-none transition-colors cursor-pointer rounded-2xl hover:bg-slate-50">
                <div class="hidden text-right lg:block">
                  <div class="text-xs font-bold leading-none text-slate-900">{{ auth()->user()->name }}</div>
                  <div class="text-[10px] text-slate-500 mt-0.5 capitalize">{{ auth()->user()->role ?? 'Pelamar' }}</div>
                </div>
                <div class="w-9 h-9 rounded-xl bg-[#a77d52] flex items-center justify-center text-white font-bold shadow-sm">
                  {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <svg class="w-4 h-4 transition-transform text-slate-400 group-open:rotate-180"><use href="#i-chevron"/></svg>
              </summary>
              <div class="absolute right-0 z-50 w-56 py-2 mt-2 bg-white border shadow-xl border-slate-200 rounded-2xl animate-in fade-in slide-in-from-top-2">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                  <svg class="w-4 h-4 text-slate-400"><use href="#i-user"/></svg>
                  Profil Saya
                </a>
                <a href="{{ route('applications.mine') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                  <svg class="w-4 h-4 text-slate-400"><use href="#i-briefcase"/></svg>
                  Lamaran Saya
                </a>
                <div class="my-2 border-t border-slate-100"></div>
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4"><path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H6.75A2.25 2.25 0 004.5 5.25v13.5A2.25 2.25 0 006.75 21H13.5a2.25 2.25 0 002.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3M12 5v14" stroke="currentColor" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Keluar
                  </button>
                </form>
              </div>
            </details>
          @else
            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-[#a77d52] transition-colors rounded-xl hover:bg-slate-50">Masuk</a>
            <a href="{{ route('register') }}" class="ml-2 px-5 py-2.5 text-sm font-bold text-white bg-[#a77d52] rounded-2xl shadow-md hover:shadow-lg hover:brightness-110 transition-all">Daftar Gratis</a>
          @endauth
        </nav>

        {{-- Mobile Search Trigger (Hidden on Desktop) --}}
        <div class="md:hidden">
          <button id="btn-search-mobile" class="p-2 text-slate-600">
            <svg class="w-6 h-6"><use href="#i-search"/></svg>
          </button>
        </div>
      </div>
    </div>

    {{-- Mobile Menu (Premium Slide-out) --}}
    <div id="mobile-menu" class="fixed inset-0 z-[100] pointer-events-none opacity-0 transition-all duration-300">
      <div class="absolute inset-0 transition-opacity bg-white !bg-opacity-100 !backdrop-blur-none" id="mobile-menu-overlay"></div>
      <div class="absolute inset-0 flex flex-col w-full h-full transition-transform -translate-x-full bg-white !bg-opacity-100 !backdrop-blur-none shadow-lg" id="mobile-menu-panel" style="max-width:100vw; border-top-left-radius: 1.25rem; border-top-right-radius: 1.25rem;">
        <div class="flex items-center justify-between p-4 border-b" style="height:64px;min-height:64px;">
          <img src="{{ asset('assets/logofix.png') }}" alt="Logo" class="w-auto h-8">
          <button id="btn-menu-close" class="p-2 transition-colors rounded-lg hover:bg-[#a77d52]/10">
            <svg class="w-6 h-6 text-[#a77d52]"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
        <style>
          #mobile-menu-panel { background: #fff !important; border-bottom: 2px solid #a77d52; box-shadow: 0 8px 32px 0 rgba(167,125,82,0.10) !important; }
          #mobile-menu-panel .border-b { border-bottom: 2px solid #a77d52 !important; }
        </style>
        
        <div class="flex flex-col items-center justify-center flex-1 p-4 space-y-2">
          <div class="mb-6">
            <form action="{{ route('jobs.index') }}" method="GET" class="relative">
              <input type="search" name="q" placeholder="Cari lowongan..." class="w-full pl-10 pr-4 py-3 bg-slate-100 border-none rounded-2xl text-sm focus:ring-2 focus:ring-[#a77d52]/30 outline-none">
            <div class="w-full max-w-xs mb-6">
            </div>

            <div class="flex flex-col w-full max-w-xs gap-3">
              <a href="{{ route('jobs.index') }}" class="flex items-center justify-center gap-2 px-4 py-4 rounded-2xl border border-[#a77d52] bg-white text-[#a77d52] font-bold text-lg hover:bg-[#a77d52]/10 transition-colors">
                <svg class="w-5 h-5"><use href="#i-briefcase"/></svg>
                Lowongan Kerja
              </a>
              <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 px-4 py-4 rounded-2xl border border-[#a77d52] bg-white text-[#a77d52] font-bold text-lg hover:bg-[#a77d52]/10 transition-colors">Masuk</a>
              <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 px-4 py-4 rounded-2xl border border-[#a77d52] bg-[#a77d52] text-white font-bold text-lg hover:brightness-110 transition-all">Daftar</a>
            </div>
        </div>
      </div>
    </div>
  </header>

  {{-- ============================================================
       MAIN CONTENT
  ============================================================ --}}
  <main id="maincontent">

    {{-- ===== HERO ===== --}}
    <section aria-labelledby="hero-heading">
      <div class="relative overflow-hidden">
        <img src="{{ asset('assets/banner-abn.png') }}"
          alt="Bergabunglah bersama tim profesional PT Andalan Artha Primanusa"
          class="w-full h-[440px] object-cover"
          width="1440" height="440"
          fetchpriority="high"
          decoding="async">
        <div class="absolute inset-0 flex items-start justify-end pt-8 md:pt-12"
          style="background: linear-gradient(to right, transparent 15%, rgba(0,0,0,.65) 100%)">
          <div class="max-w-2xl px-8 text-right text-white md:px-14">
            <div class="mb-0 -mt-4 md:-mt-6">
              <h1 id="hero-heading" class="text-3xl font-extrabold leading-tight md:text-5xl">
                WELCOME TO<br>
                <span class="text-2xl md:text-4xl" style="color: #a57c50">ANDALAN CARRER</span>
              </h1>
            </div>
            <div class="mt-12 space-y-2">
              <p class="text-sm leading-relaxed md:text-base opacity-90">
                Bergabunglah bersama tim yang berkomitmen pada pertumbuhan, profesionalisme, dan keunggulan.
              </p>
              <p class="text-sm leading-relaxed opacity-75 md:text-base">
                Temukan peluang karier yang sesuai dengan aspirasi Anda bersama PT Andalan Artha Primanusa.
              </p>
            </div>
            <div class="flex flex-wrap justify-end gap-3 mt-6">
              <a href="{{ route('jobs.index') }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm bg-white hover:bg-zinc-100 transition shadow"
                style="color: #a77d52">
                <svg class="w-4 h-4" aria-hidden="true"><use href="#i-briefcase"/></svg>
                Lihat Lowongan
              </a>
              @guest
                  <a href="{{ route('register') }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white hover:opacity-90 transition shadow"
                    style="background: #1d4ed8">
                    Daftar Sekarang
                  </a>
              @endguest
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- ===== QUICK LINKS ===== --}}
    <section aria-label="Menu cepat" style="padding: 2.5rem 1.5rem; background: #dfe6da">
      <div class="mx-auto max-w-7xl">
        <h2 class="sr-only">Menu Cepat</h2>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem">
          <a href="{{ route('login') }}" class="qlink-card">
            <span class="inline-flex items-center gap-2 qlink-title">
              <svg class="w-4 h-4" aria-hidden="true"><use href="#i-user"/></svg>
              Masuk
            </span>
            <span class="qlink-desc">Akses akun Anda</span>
          </a>
          <a href="{{ route('register') }}" class="qlink-card">
            <span class="inline-flex items-center gap-2 qlink-title">
              <svg class="w-4 h-4" aria-hidden="true"><use href="#i-apply"/></svg>
              Daftar
            </span>
            <span class="qlink-desc">Buat akun baru gratis</span>
          </a>
          <a href="{{ route('jobs.index') }}" class="qlink-card">
            <span class="inline-flex items-center gap-2 qlink-title">
              <svg class="w-4 h-4" aria-hidden="true"><use href="#i-briefcase"/></svg>
              Lowongan
            </span>
            <span class="qlink-desc">Lihat semua posisi terbuka</span>
          </a>
          <a href="/faq" class="qlink-card">
            <span class="inline-flex items-center gap-2 qlink-title">
              <svg class="w-4 h-4" aria-hidden="true"><use href="#i-help"/></svg>
              FAQ
            </span>
            <span class="qlink-desc">Panduan &amp; pertanyaan umum</span>
          </a>
        </div>
      </div>
    </section>

    {{-- ===== LOKASI SITE DENGAN PETA INTERAKTIF ===== --}}
    @php
        $sitesCol = ($sitesSimple instanceof \Illuminate\Support\Collection) ? $sitesSimple : collect($sitesSimple ?? []);
        $sitesNorm = $sitesCol->filter(fn($s) => !empty($s['name']))
            ->map(function ($s) {
                $name = (string) ($s['name'] ?? '-');
                $dot = preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', (string) ($s['dot'] ?? '')) ? $s['dot'] : '#a77d52';
                $param = $s['code'] ?? $s['id'] ?? $name;
                return ['name' => $name, 'dot' => $dot, 'param' => $param];
            })->values();
        $sitesDup = $sitesNorm->concat($sitesNorm);
    @endphp

    <section class="border-b" style="border-color: #f4f0eb; background: #dfe6da"
      aria-labelledby="sites-heading">
      <div class="px-6 py-8 mx-auto max-w-7xl lg:px-8">
        <h2 id="sites-heading" class="mb-4 text-base font-semibold" style="color: #1f2937">Lokasi Site</h2>

        @if($sitesWithCoords->isNotEmpty())
              {{-- PETA INTERAKTIF --}}
              <div id="sites-map" class="w-full mb-6 overflow-hidden border shadow-md h-96 rounded-2xl"
                style="border-color: #f4f0eb" role="region" aria-label="Peta lokasi site PT Andalan Artha Primanusa">
              </div>

              {{-- Marquee animasi (aria-hidden, navigasi lewat list di bawah) --}}
              <div class="marquee" role="presentation" aria-hidden="true">
                <div class="marquee__track">
                  @foreach($sitesDup as $s)
                      <div class="shrink-0">
                        <span class="inline-flex items-center gap-2 px-4 py-2 border rounded-full"
                          style="border-color: #f4f0eb; background: #ede5dc; color: #6b4f3a;">
                          <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0"
                            style="background: {{ $s['dot'] }}"></span>
                          <span class="text-sm whitespace-nowrap">{{ $s['name'] }}</span>
                        </span>
                      </div>
                  @endforeach
                </div>
              </div>
              {{-- List yang bisa difokus untuk aksesibilitas --}}
              <ul class="flex flex-wrap gap-2 mt-3" role="list" aria-label="Filter lowongan berdasarkan lokasi site">
                @foreach($sitesNorm as $s)
                    <li>
                      <a href="{{ route('jobs.index', ['site' => $s['param']]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 transition border rounded-full hover:shadow-md"
                        style="border-color: #f4f0eb; background: #ede5dc; color: #6b4f3a;"
                        aria-label="Filter lowongan di site {{ $s['name'] }}">
                        <span class="inline-block w-2.5 h-2.5 rounded-full shrink-0"
                          style="background: {{ $s['dot'] }}" aria-hidden="true"></span>
                        <span class="text-sm">{{ $s['name'] }}</span>
                      </a>
                    </li>
                @endforeach
              </ul>
        @else
              <p class="text-sm" style="color: #6b4f3a">Belum ada data site.</p>
        @endif
      </div>
    </section>

    {{-- LEAFLET MAPS SCRIPT & STYLE --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sitesData = @json($sitesWithCoords ?? []);
        const mapContainer = document.getElementById('sites-map');

        if (!mapContainer || sitesData.length === 0) return;

        // Hitung center peta dari rata-rata semua koordinat
        const avgLat = sitesData.reduce((sum, s) => sum + s.latitude, 0) / sitesData.length;
        const avgLng = sitesData.reduce((sum, s) => sum + s.longitude, 0) / sitesData.length;

        // Inisialisasi peta
        const map = L.map('sites-map').setView([avgLat, avgLng], 5);

        // Tambahkan tile layer (OpenStreetMap)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          maxZoom: 19,
        }).addTo(map);

        // Tambahkan marker untuk setiap site
        const markers = [];
        sitesData.forEach(function (site) {
          const marker = L.circleMarker([site.latitude, site.longitude], {
            radius: 10,
            fillColor: site.dot || '#a77d52',
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8,
          })
          .bindPopup(
            `<div style="font-size: 13px; font-weight: 500; color: #1f2937;">
              ${site.name}
            </div>
            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">Lat: ${site.latitude}, Long: ${site.longitude}</p>
            <a href="{{ route('jobs.index') }}?site=${site.param}"
              style="display: inline-block; margin-top: 6px; padding: 4px 8px; background: #a77d52; color: white; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: 600;">
              Lihat Lowongan
            </a>`
          )
          .addTo(map);
          markers.push(marker);
        });

        // Zoom otomatis ke semua marker
        if (markers.length > 0) {
          const group = new L.featureGroup(markers);
          map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }
      });
    </script>

    {{-- ===== TAHAPAN REKRUTMEN ===== --}}
    <section class="py-12 bg-white" aria-labelledby="rekrutmen-heading">
      <div class="px-6 mx-auto max-w-7xl lg:px-8">
        <div class="overflow-hidden transition duration-300 border shadow-sm rounded-2xl hover:shadow-md"
          style="border-color: #f4f0eb">
          <div class="grid md:grid-cols-2">

            {{-- Gambar --}}
            <div class="relative">
              <img src="{{ asset('assets/foto1.png') }}"
                class="w-full h-full object-cover min-h-[320px]"
                alt="Tim HR PT Andalan Artha Primanusa berkolaborasi dalam proses rekrutmen yang transparan"
                width="640" height="480"
                loading="lazy" decoding="async">
              <div class="absolute bottom-0 w-full p-5 text-sm text-white"
                style="background: linear-gradient(to top, rgba(0,0,0,.85), transparent)">
                <p class="font-semibold">Lingkungan kerja kolaboratif &amp; profesional</p>
                <p class="text-xs opacity-75 mt-0.5">Proses rekrutmen yang transparan &amp; adil</p>
              </div>
            </div>

            {{-- Langkah-langkah --}}
            <div class="p-6 space-y-5 md:p-10">
              <div>
                <h2 id="rekrutmen-heading" class="text-xl font-bold md:text-2xl" style="color:#1f2937">
                  Tahapan Rekrutmen
                </h2>
                <p class="mt-1 text-sm" style="color:#6b7280">
                  Proses seleksi kami dirancang transparan, cepat, dan profesional.
                </p>
              </div>

              @php
                $steps = [
                    ['Pengajuan Lamaran', 'Kirim CV & data diri melalui sistem kami.', '1-2 hari'],
                    ['Penyaringan CV', 'Tim HR akan meninjau kesesuaian kandidat.', '2-3 hari'],
                    ['Wawancara / Tes', 'Interview HR & user + tes kemampuan (jika ada).', '3-5 hari'],
                    ['Penawaran Kerja', 'Kandidat terpilih menerima offering letter.', '1-2 hari'],
                    ['Onboarding', 'Mulai bekerja & orientasi perusahaan.', 'Hari pertama'],
                ];
              @endphp

              <ol class="space-y-3" aria-label="Langkah-langkah rekrutmen PT Andalan">
                @foreach($steps as $i => [$title, $desc, $time])
                    <li class="flex gap-4 p-3 transition rounded-xl hover:bg-amber-50">
                      <div class="flex items-center justify-center text-xs font-bold text-white rounded-full shadow w-8 h-8 shrink-0 mt-0.5"
                        style="background: #a77d52" aria-hidden="true">{{ $i + 1 }}</div>
                      <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                          <p class="text-sm font-semibold" style="color:#1f2937">{{ $title }}</p>
                          <span class="text-xs shrink-0 whitespace-nowrap px-2 py-0.5 rounded-full"
                            style="background: #ede5dc; color: #6b4f3a">{{ $time }}</span>
                        </div>
                        <p class="mt-0.5 text-xs leading-relaxed" style="color:#6b7280">{{ $desc }}</p>
                      </div>
                    </li>
                @endforeach
              </ol>

              <p class="pt-4 text-xs border-t" style="color:#9ca3af; border-color:#e5e7eb">
                *Durasi dapat berbeda tergantung posisi &amp; jumlah pelamar.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    {{-- ===== LOWONGAN TERBARU ===== --}}
    <section class="px-6 py-10 mx-auto max-w-7xl lg:px-8" aria-labelledby="jobs-heading">
      <div class="overflow-hidden border shadow-sm rounded-2xl"
        style="border-color: #f4f0eb; background: #dfe6da">

        {{-- Header --}}
        <div class="flex items-center justify-between p-6 border-b" style="border-color: #f4f0eb">
          <h2 id="jobs-heading" class="text-base font-bold" style="color: #1f2937">Lowongan Terbaru</h2>
          <a href="{{ route('jobs.index') }}"
            class="inline-flex items-center gap-1 text-sm font-semibold transition hover:opacity-70"
            style="color: #a77d52">
            Lihat semua
            <svg class="w-4 h-4" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          </a>
        </div>

        <div class="p-5">
          @php
            $hasJobs = method_exists($jobs, 'count') ? $jobs->count() > 0 : !$jobs->isEmpty();
          @endphp

          @if(!$hasJobs)
            <div class="py-16 text-center">
              <p class="font-semibold text-zinc-700">Belum ada lowongan saat ini</p>
              <p class="mt-1 text-sm text-zinc-500">Pantau terus halaman ini untuk update terbaru.</p>
            </div>
          @else

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              @foreach ($jobs as $job)
                @php
                    $excerpt = \Illuminate\Support\Str::limit(strip_tags($job->description ?? ''), 120);
                    $site = $job->site ?? null;
                    $siteName = $site?->name ?? null;
                    $siteRegion = $site?->region ?? null;
                    // Perbaikan bug: jangan tampilkan region dua kali jika sama dengan name
                    $showRegion = $siteRegion && $siteRegion !== $siteName;
                    $isNew = $job->created_at && $job->created_at->diffInDays(now()) <= 7;
                @endphp

                <article class="flex flex-col overflow-hidden border rounded-2xl card-hover"
                  style="border-color: #f4f0eb; background: #ede5dc"
                  itemscope itemtype="https://schema.org/JobPosting">
                  <meta itemprop="title" content="{{ $job->title }}">
                  <meta itemprop="datePosted" content="{{ optional($job->created_at)->toDateString() }}">

                  <div class="flex flex-col flex-1 p-5">

                    {{-- Header kartu --}}
                    <div class="flex items-start gap-3">
                      <div class="p-2.5 rounded-xl text-white shrink-0" style="background: #a77d52">
                        <svg class="w-5 h-5" aria-hidden="true"><use href="#i-briefcase"/></svg>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                          <a href="{{ route('jobs.show', $job) }}"
                            class="block text-sm font-bold leading-snug transition hover:opacity-75"
                            style="color: #1f2937"
                            itemprop="url">
                            {{ $job->title }}
                          </a>
                          @if($isNew)
                            <span class="badge badge-new shrink-0">Baru</span>
                          @endif
                        </div>

                        {{-- Lokasi - PERBAIKAN: tidak tampilkan region dua kali --}}
                        <p class="text-[11px] mt-1 leading-relaxed" style="color: #6b4f3a">
                          @if($siteName)
                            <svg class="w-3 h-3 inline-block mr-0.5 -mt-px" aria-hidden="true"><use href="#i-map-pin"/></svg>
                            <span class="font-medium">{{ $siteName }}</span>@if($showRegion)<span class="opacity-60">, {{ $siteRegion }}</span>@endif
                          @else
                            <span class="opacity-50">Lokasi belum tersedia</span>
                          @endif
                          <svg class="w-3 h-3 inline-block mr-0.5 -mt-px" aria-hidden="true"><use href="#i-clock"/></svg>
                          {{ optional($job->created_at)->diffForHumans() }}
                        </p>
                      </div>
                    </div>

                    {{-- Deskripsi --}}
                    @if(!empty($excerpt))
                          <p class="flex-1 mt-3 text-xs leading-relaxed line-clamp-2" style="color: #6b4f3a">
                            {{ $excerpt }}
                          </p>
                    @endif

                    {{-- CTA --}}
                    <div class="flex items-center justify-between pt-4 mt-4 border-t"
                      style="border-color: rgba(167,125,82,.2)">
                      <a href="{{ route('jobs.show', $job) }}"
                        class="inline-flex items-center gap-1 text-xs font-semibold transition hover:opacity-70"
                        style="color: #a77d52">
                        Lihat Detail
                        <svg class="w-3.5 h-3.5" aria-hidden="true"><use href="#i-arrow-right"/></svg>
                      </a>

                      @auth
                        <form action="{{ route('applications.store', $job) }}" method="POST">
                          @csrf
                          <button type="submit"
                            class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-xl text-white hover:opacity-90 active:scale-95 transition shadow-sm"
                            style="background: #a77d52"
                            onclick="return confirm('Yakin ingin melamar posisi ini?')">
                            <svg class="w-3.5 h-3.5" aria-hidden="true"><use href="#i-apply"/></svg>
                            Lamar Sekarang
                          </button>
                        </form>
                      @else
                        <a href="{{ route('login') }}?intended={{ urlencode(route('jobs.show', $job)) }}"
                          class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-xl text-white hover:opacity-90 transition shadow-sm"
                          style="background: #a77d52">
                          <svg class="w-3.5 h-3.5" aria-hidden="true"><use href="#i-user"/></svg>
                          Masuk &amp; Lamar
                        </a>
                      @endauth
                    </div>

                  </div>
                </article>
              @endforeach
            </div>

            {{-- Pagination --}}
            @if(method_exists($jobs, 'withQueryString'))
                  <div class="mt-8">
                    {{ $jobs->withQueryString()->links() }}
                  </div>
            @endif

          @endif
        </div>
      </div>
    </section>

    {{-- ===== FAQ (SEO rich result boost) ===== --}}
    <section class="py-12 bg-white" aria-labelledby="faq-heading">
      <div class="max-w-3xl px-6 mx-auto lg:px-8">
        <h2 id="faq-heading" class="mb-2 text-xl font-bold text-center" style="color:#1f2937">
          Pertanyaan Umum
        </h2>
        <p class="mb-8 text-sm text-center" style="color:#6b7280">
          Butuh informasi lebih? Cek FAQ kami.
        </p>

        @php
            $faqs = [
                [
                    'Bagaimana cara melamar kerja di PT Andalan Artha Primanusa?',
                    'Daftarkan akun Anda di Human Careers, pilih posisi yang sesuai, lalu klik tombol "Lamar Sekarang". Seluruh proses dilakukan secara online dan transparan.'
                ],
                [
                    'Apakah proses rekrutmen PT Andalan gratis?',
                    'Ya, sepenuhnya gratis. PT Andalan Artha Primanusa tidak memungut biaya apapun dari pelamar dalam setiap tahapan rekrutmen.'
                ],
                [
                    'Berapa lama proses seleksi berlangsung?',
                    'Umumnya 7-14 hari kerja, mulai dari pengajuan lamaran hingga penawaran kerja. Durasi dapat berbeda tergantung posisi dan jumlah pelamar.'
                ],
                [
                    'Bagaimana cara memantau status lamaran saya?',
                    'Setelah login, buka menu "Lamaran Saya" untuk melihat status terkini lamaran Anda secara real-time.'
                ],
            ];
        @endphp

        <div class="space-y-3" itemscope itemtype="https://schema.org/FAQPage">
          @foreach($faqs as [$q, $a])
              <details class="overflow-hidden border rounded-2xl"
                style="border-color: #e5e7eb"
                @if($loop->first) open @endif>
                <summary class="flex items-center justify-between gap-4 px-5 py-4 font-semibold transition cursor-pointer text-slate-700 hover:bg-slate-50"
                  aria-label="Buka pertanyaan: {{ $q }}">
                  <span class="text-sm">{{ $q }}</span>
                  <svg class="w-5 h-5 text-slate-400 chevron-icon shrink-0" aria-hidden="true"><use href="#i-chevron"/></svg>
                </summary>
                <div class="px-5 pb-5 text-sm leading-relaxed text-slate-600">
                  {{ $a }}
                </div>
              </details>
          @endforeach
        </div>

        <div class="mt-8 text-center">
          <a href="/faq" class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white rounded-2xl"
            style="background: #a77d52">
            Lihat Semua FAQ
            <svg class="w-4 h-4" aria-hidden="true"><use href="#i-arrow-right"/></svg>
          </a>
        </div>
      </div>
    </section>

  </main>

  {{-- ============================================================
       FOOTER
  ============================================================ --}}
  <footer class="bg-[#1f2937] text-slate-300">
    <div class="px-6 py-12 mx-auto max-w-7xl lg:px-8">
      <div class="grid gap-8 md:grid-cols-4">

        {{-- Brand --}}
        <div class="md:col-span-2">
          <img src="{{ asset('assets/logofix.png') }}" alt="Logo" class="h-10 mb-4">
          <p class="max-w-sm text-sm leading-relaxed text-slate-400">
            Portal karier resmi PT Andalan Artha Primanusa. Kami menghubungkan talenta terbaik dengan peluang karier yang sesuai.
          </p>
          <div class="flex gap-4 mt-6">
            <a href="https://andalan.co.id" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-white transition" aria-label="Website PT Andalan">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
            </a>
            <a href="https://linkedin.com/company/andalan" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-white transition" aria-label="LinkedIn PT Andalan">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
            <a href="https://instagram.com/andalan" target="_blank" rel="noopener noreferrer" class="text-slate-400 hover:text-white transition" aria-label="Instagram PT Andalan">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
          </div>
        </div>

        {{-- Quick Links --}}
        <div>
          <h3 class="mb-4 text-sm font-bold text-white uppercase tracking-wide">Menu</h3>
          <ul class="space-y-2">
            <li><a href="{{ route('jobs.index') }}" class="text-sm transition text-slate-400 hover:text-white">Lowongan Kerja</a></li>
            <li><a href="{{ route('register') }}" class="text-sm transition text-slate-400 hover:text-white">Daftar</a></li>
            <li><a href="{{ route('login') }}" class="text-sm transition text-slate-400 hover:text-white">Masuk</a></li>
            <li><a href="/faq" class="text-sm transition text-slate-400 hover:text-white">FAQ</a></li>
          </ul>
        </div>

        {{-- Contact --}}
        <div>
          <h3 class="mb-4 text-sm font-bold text-white uppercase tracking-wide">Kontak</h3>
          <ul class="space-y-2 text-sm text-slate-400">
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 mt-0.5 shrink-0" aria-hidden="true"><use href="#i-map-pin"/></svg>
              <span>Jl. Plaju No.11, Kebon Melati, Tanah Abang, Jakarta Pusat 10230</span>
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 shrink-0" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="22,6 12,13 2,6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
              <span>hr@andalan.co.id</span>
            </li>
          </ul>
        </div>

      </div>

      <div class="pt-8 mt-12 border-t border-slate-700">
        <p class="text-xs text-center text-slate-500">
          &copy; {{ date('Y') }} PT Andalan Artha Primanusa. All rights reserved.
        </p>
      </div>
    </div>
  </footer>

  <a href="#maincontent" id="toTop" class="p-3 text-white rounded-full shadow-lg" style="background:#a77d52" aria-label="Kembali ke atas">
    <svg class="w-5 h-5"><use href="#i-arrow-up"/></svg>
  </a>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toTop = document.getElementById('toTop');
      window.addEventListener('scroll', function () {
        if (window.scrollY > 300) {
          toTop.classList.add('show');
        } else {
          toTop.classList.remove('show');
        }
      });
    });
  </script>
</body>
</html>