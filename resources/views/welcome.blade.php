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
        "email" => "recruitment@andalanarthaprimanusa.com",
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
        "name" => ["Lowongan", "Masuk", "Daftar", "Lokasi Site"],
        "url" => [url('/jobs'), url('/login'), url('/register'), url('/sites')]
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
    // Mobile menu toggle (Bottom Sheet)
    document.addEventListener('DOMContentLoaded', function () {
      var btnNavToggle   = document.getElementById('btn-nav-toggle');
      var mobileMenu     = document.getElementById('mobile-menu');
      var btnMenuClose   = document.getElementById('btn-menu-close');
      var mobileBackdrop = document.getElementById('mobile-menu-backdrop');

      function openMenu() {
        mobileMenu.classList.remove('pointer-events-none', 'opacity-0');
        mobileMenu.classList.add('pointer-events-auto', 'opacity-100', 'open');
        document.body.style.overflow = 'hidden';
      }
      function closeMenu() {
        mobileMenu.classList.remove('open');
        setTimeout(function () {
          mobileMenu.classList.add('pointer-events-none', 'opacity-0');
          mobileMenu.classList.remove('pointer-events-auto', 'opacity-100');
        }, 350);
        document.body.style.overflow = '';
      }

      if (btnNavToggle) btnNavToggle.addEventListener('click', openMenu);
      if (btnMenuClose) btnMenuClose.addEventListener('click', closeMenu);
      if (mobileBackdrop) mobileBackdrop.addEventListener('click', closeMenu);

      document.addEventListener('keydown', function (e) {
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
      outline: 3px solid #a77d52;
      outline-offset: 3px;
      border-radius: 4px;
    }

    /* Card hover */
    .card-hover {
      transition: transform .25s cubic-bezier(.22,.68,0,1.2), box-shadow .25s ease;
    }
    .card-hover:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 48px rgba(167,125,82,.25);
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
    .badge-open { background: #f4ebe0; color: #7a5530; }
    .badge-new  { background: #f4ebe0; color: #a77d52; }

    .home-section-soft {
      background: #ffffff;
    }
    .home-card {
      border: 1px solid #eadccd;
      background: rgba(255,255,255,.94);
      box-shadow: 0 20px 48px rgba(92,61,30,.08);
    }
    .home-pill {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      border-radius: 999px;
      border: 1px solid #eadccd;
      background: #fff8f0;
      color: #7a5530;
      padding: .35rem .75rem;
      font-size: .72rem;
      font-weight: 800;
    }
    .home-job-card {
      background: #ffffff;
      box-shadow: 0 14px 34px rgba(92,61,30,.07);
      transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
    }
    .home-job-card:hover {
      border-color: rgba(167,125,82,.45);
      box-shadow: 0 22px 52px rgba(92,61,30,.13);
      transform: translateY(-3px);
    }
    .home-primary-link {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .45rem;
      border-radius: 1rem;
      background: #a77d52;
      color: #fff;
      font-weight: 800;
      box-shadow: 0 12px 24px rgba(167,125,82,.24);
      transition: transform .18s ease, opacity .18s ease;
    }
    .home-primary-link:hover { opacity: .94; transform: translateY(-1px); }
    .home-step-card {
      border: 1px solid #eadccd;
      background: #fff;
      box-shadow: 0 14px 34px rgba(92,61,30,.06);
    }
    .home-step-number {
      display: grid;
      width: 2.5rem;
      height: 2.5rem;
      place-items: center;
      border-radius: 1rem;
      background: #a77d52;
      color: #fff;
      font-size: .875rem;
      font-weight: 900;
      box-shadow: 0 10px 20px rgba(167,125,82,.2);
    }

    /* ===== Cara Melamar: APPLICATION JOURNEY ===== */
    .aj-wrap {
      max-width: 1280px;
      margin-inline: auto;
    }

    /* -- Header -- */
    .aj-badge {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .4rem .9rem;
      border-radius: 999px;
      background: #f7efde;
      border: 1px solid rgba(167,125,82,.25);
      color: #7a5530;
      font-size: .8rem;
      font-weight: 800;
      letter-spacing: .02em;
    }

    /* -- Timeline -- */
    .aj-timeline {
      position: relative;
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      align-items: start;
      margin-top: 2.5rem;
      margin-bottom: 2.25rem;
    }
    .aj-timeline-line {
      position: absolute;
      top: 1.25rem;
      left: 8.333%;
      right: 8.333%;
      height: 2px;
      background: rgba(167,125,82,.22);
    }
    .aj-timeline-step {
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .55rem;
    }
    .aj-timeline-dot {
      display: grid;
      width: 2.5rem;
      height: 2.5rem;
      place-items: center;
      border-radius: 999px;
      background: #ffffff;
      border: 2px solid rgba(167,125,82,.4);
      color: #7a5530;
      font-size: .8rem;
      font-weight: 800;
      box-shadow: 0 4px 10px rgba(92,61,30,.06);
    }
    .aj-timeline-step.is-active .aj-timeline-dot {
      background: #a77d52;
      border-color: #a77d52;
      color: #ffffff;
    }
    .aj-timeline-label {
      font-size: .72rem;
      font-weight: 700;
      color: #6b4f3a;
      text-align: center;
      line-height: 1.1;
    }
    .aj-timeline-step.is-active .aj-timeline-label {
      color: #3b2209;
    }
    .aj-timeline-icon {
      display: none;
      place-items: center;
      width: 1rem;
      height: 1rem;
      margin-top: -1rem;
      color: #fff;
    }
    .aj-timeline-step.is-active .aj-timeline-icon {
      display: grid;
    }
    .aj-timeline-step.is-active .aj-timeline-dot {
      color: transparent;
    }

    /* -- Card -- */
    .aj-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.25rem;
    }
    @media (min-width: 1500px) {
      .aj-grid { grid-template-columns: repeat(6, 1fr); gap: 1rem; }
      .aj-grid .aj-card { padding: 1.35rem; }
    }
    .aj-card {
      position: relative;
      display: flex;
      flex-direction: column;
      padding: 1.6rem;
      border-radius: 20px;
      border: 1px solid rgba(167,125,82,.16);
      background: #ffffff;
      box-shadow: 0 8px 24px rgba(92,61,30,.05);
      transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }
    .aj-card:hover {
      transform: translateY(-4px);
      border-color: rgba(167,125,82,.5);
      box-shadow: 0 20px 40px rgba(92,61,30,.12);
    }
    .aj-card-top {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: .75rem;
    }
    .aj-icon {
      display: grid;
      width: 3.25rem;
      height: 3.25rem;
      place-items: center;
      border-radius: 14px;
      background: #f7efde;
      color: #a77d52;
      transition: background .25s ease, color .25s ease, transform .25s ease;
    }
    .aj-icon svg { width: 1.5rem; height: 1.5rem; }
    .aj-card:hover .aj-icon {
      background: #a77d52;
      color: #fff;
      transform: scale(1.04);
    }
    .aj-stepnum {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 1.85rem;
      height: 1.85rem;
      padding-inline: .5rem;
      border-radius: 999px;
      background: #ffffff;
      border: 1px solid rgba(167,125,82,.3);
      color: #7a5530;
      font-size: .72rem;
      font-weight: 800;
    }
    .aj-title {
      margin-top: 1.1rem;
      font-size: 1.05rem;
      font-weight: 800;
      color: #1f2937;
      line-height: 1.25;
      letter-spacing: -.01em;
    }
    .aj-desc {
      margin-top: .4rem;
      font-size: .82rem;
      line-height: 1.45;
      color: #7a5530;
    }
    .aj-points {
      margin-top: .85rem;
      display: flex;
      flex-direction: column;
      gap: .4rem;
    }
    .aj-point {
      position: relative;
      padding-left: 1.1rem;
      font-size: .82rem;
      line-height: 1.35;
      color: #5a4632;
    }
    .aj-point::before {
      content: "";
      position: absolute;
      left: .05rem;
      top: .42em;
      width: .42rem;
      height: .42rem;
      border-radius: 999px;
      background: #a77d52;
    }
    .aj-cta {
      display: inline-flex;
      align-items: center;
      gap: .45rem;
      align-self: flex-start;
      margin-top: auto;
      padding-top: 1rem;
      font-size: .85rem;
      font-weight: 800;
      color: #7a5530;
      transition: color .2s ease, gap .2s ease;
    }
    .aj-cta:hover { color: #a77d52; gap: .7rem; }

    /* -- Tips bar -- */
    .aj-tips {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1.25rem;
      margin-top: 2.25rem;
      padding: 1.4rem 1.6rem;
      border-radius: 20px;
      background: #fff8f0;
      border: 1px solid rgba(167,125,82,.18);
    }
    .aj-tips-left {
      display: flex;
      align-items: center;
      gap: 1rem;
      min-width: 0;
    }
    .aj-tips-icon {
      display: grid;
      width: 3rem;
      height: 3rem;
      flex-shrink: 0;
      place-items: center;
      border-radius: 12px;
      background: #a77d52;
      color: #fff;
    }
    .aj-tips-icon svg { width: 1.4rem; height: 1.4rem; }
    .aj-tips-kicker {
      font-size: .68rem;
      font-weight: 800;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #a77d52;
    }
    .aj-tips-text {
      margin-top: .15rem;
      font-size: .95rem;
      font-weight: 700;
      color: #3b2209;
      line-height: 1.3;
    }
    .aj-benefits {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 1.4rem;
    }
    .aj-benefit {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      font-size: .82rem;
      font-weight: 700;
      color: #3b2209;
    }
    .aj-benefit svg { width: 1.1rem; height: 1.1rem; color: #a77d52; }

    /* -- Mobile carousel -- */
    @media (max-width: 767.5px) {
      .aj-rich-timeline { display: none; }
      .aj-cardcar {
        display: flex;
        gap: 1rem;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
        padding: .25rem .25rem 1rem;
        margin-inline: -.25rem;
        scrollbar-width: none;
      }
      .aj-cardcar::-webkit-scrollbar { display: none; }
      .aj-cardcar .aj-card {
        flex: 0 0 auto;
        width: 84vw;
        scroll-snap-align: start;
      }
      .aj-grid { display: block; }
      .aj-tips { flex-direction: column; align-items: flex-start; }
    }
    .aj-dots { display: none; }
    .aj-dots.is-visible { display: flex; justify-content: center; gap: .5rem; margin-top: .75rem; }
    .aj-dot {
      width: .45rem;
      height: .45rem;
      border-radius: 999px;
      background: rgba(167,125,82,.3);
      border: 0;
      padding: 0;
      cursor: pointer;
      transition: width .25s ease, background .25s ease;
    }
    .aj-dot.is-active { width: 1.4rem; background: #a77d52; }

    @media (prefers-reduced-motion: reduce) {
      .aj-card, .aj-icon, .aj-timeline-dot, .aj-dot, .aj-cta { transition: none; }
      .aj-card:hover { transform: none; }
      .aj-card:hover .aj-icon { transform: none; }
    }
  </style>
</head>

<body class="antialiased bg-white text-zinc-900">

  {{-- Skip nav untuk screen reader --}}
  <a href="#maincontent"
    class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-[#a77d52] focus:text-white focus:rounded-lg focus:px-4 focus:py-2 focus:shadow-lg focus:text-sm focus:font-semibold">
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
    <symbol id="i-file-search" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/><circle cx="16" cy="16" r="3"/><path d="m19 19-1.5-1.5"/></g></symbol>
    <symbol id="i-log-in"      viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></g></symbol>
    <symbol id="i-user-plus"   viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></g></symbol>
    <symbol id="i-clipboard-list" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M9 11h.01"/><path d="M13 11h.01"/><path d="M17 11h.01"/><path d="M9 15h6"/></g></symbol>
    <symbol id="i-file-up"     viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m12 12v6"/><path d="m15 15-3-3-3 3"/></g></symbol>
    <symbol id="i-list-checks" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 17 2 2 4-4"/><path d="m3 7 2 2 4-4"/><path d="M13 6h8"/><path d="M13 12h8"/><path d="M13 18h8"/></g></symbol>
    <symbol id="i-lightbulb"   viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5.76.76 1.23 1.52 1.41 2.5"/></g></symbol>
    <symbol id="i-shield-check" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></g></symbol>
    <symbol id="i-users"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></g></symbol>
    <symbol id="i-check"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 6 9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="9 18 15 12 9 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    <symbol id="i-eye"       viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></g></symbol>
    <symbol id="i-contact"   viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></g></symbol>
    <symbol id="i-file-check" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m9 15 2 2 4-4"/></g></symbol>
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
              <div class="absolute right-0 z-50 p-2 overflow-hidden bg-white border shadow-2xl w-72 rounded-2xl border-slate-200 shadow-slate-900/10 animate-in fade-in slide-in-from-top-2">
                <div class="mb-1 flex items-center gap-3 rounded-xl bg-[#f7efe7] px-3 py-3">
                  <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-[#8b5e3c] text-sm font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                  </div>
                  <div class="min-w-0">
                    <div class="text-sm font-bold truncate text-slate-950">{{ auth()->user()->name }}</div>
                    <div class="text-xs truncate text-slate-500">{{ auth()->user()->email }}</div>
                  </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                  <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-[#f5ede4] group-hover:text-[#8b5e3c]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
                  </span>
                  <span>Profil Saya</span>
                </a>
                <a href="{{ route('applications.mine') }}" class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                  <span class="grid h-9 w-9 place-items-center rounded-lg bg-slate-100 text-slate-500 transition group-hover:bg-[#f5ede4] group-hover:text-[#8b5e3c]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg>
                  </span>
                  <span>Lamaran Saya</span>
                </a>
                <div class="my-2 border-t border-slate-100"></div>
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50">
                    <span class="grid text-red-500 transition rounded-lg h-9 w-9 place-items-center bg-red-50 group-hover:bg-red-100">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-5"/><path d="M14 21h5a2 2 0 0 0 2-2"/></svg>
                    </span>
                    <span>Keluar</span>
                  </button>
                </form>
              </div>
            </details>
          @else
            <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-[#a77d52] transition-colors rounded-xl hover:bg-slate-50">Masuk</a>
            <a href="{{ route('register') }}" class="ml-2 px-5 py-2.5 text-sm font-bold text-white bg-[#a77d52] rounded-2xl shadow-md hover:shadow-lg hover:brightness-110 transition-all">Daftar Gratis</a>
          @endauth
        </nav>
      </div>
    </div>
  </header>

  {{-- ===== MOBILE MENU (Bottom Sheet Premium) ===== --}}
  <style>
    #mobile-menu {
      display: flex;
      align-items: flex-end;
    }
    #mobile-menu-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.45);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
      transition: opacity 0.3s ease;
      opacity: 0;
    }
    #mobile-menu-sheet {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: #ffffff;
      border-radius: 1.5rem 1.5rem 0 0;
      padding-bottom: env(safe-area-inset-bottom, 1rem);
      transform: translateY(100%);
      transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1);
      box-shadow: 0 -8px 40px rgba(167,125,82,0.18);
      max-height: 92vh;
      overflow-y: auto;
    }
    #mobile-menu.open #mobile-menu-backdrop { opacity: 1; }
    #mobile-menu.open #mobile-menu-sheet { transform: translateY(0); }

    .mob-nav-item {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 0.9rem 1.25rem;
      border-radius: 1rem;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.95rem;
      color: #3b2209;
      transition: background 0.18s, color 0.18s;
      border: 1.5px solid transparent;
    }
    .mob-nav-item:hover {
      background: #fff8f2;
      border-color: rgba(167,125,82,0.25);
      color: #a77d52;
    }
    .mob-nav-item .mob-icon {
      width: 2.25rem;
      height: 2.25rem;
      border-radius: 0.625rem;
      background: #fff8f2;
      border: 1.5px solid rgba(167,125,82,0.2);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #a77d52;
      flex-shrink: 0;
      transition: background 0.18s;
    }
    .mob-nav-item:hover .mob-icon {
      background: #a77d52;
      color: #fff;
    }
  </style>

  <div id="mobile-menu" class="fixed inset-0 z-[100] pointer-events-none opacity-0 transition-opacity duration-300">
    {{-- Backdrop --}}
    <div id="mobile-menu-backdrop"></div>

    {{-- Bottom Sheet --}}
    <div id="mobile-menu-sheet">
      {{-- Handle bar --}}
      <div class="flex justify-center pt-3 pb-1">
        <div class="w-10 h-1 rounded-full bg-slate-200"></div>
      </div>

      {{-- Sheet Header --}}
      <div class="flex items-center justify-between px-5 py-3 border-b" style="border-color: rgba(167,125,82,0.15)">
        <img src="{{ asset('assets/logofix.png') }}" alt="Logo PT Andalan" class="w-auto h-8">
        <button id="btn-menu-close"
          class="w-9 h-9 rounded-xl flex items-center justify-center text-[#a77d52] transition hover:bg-[#a77d52]/10"
          aria-label="Tutup menu">
          <svg class="w-5 h-5"><path d="M6 18L18 6M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>

      {{-- Search --}}
      <div class="px-5 pt-4 pb-2">
        <form action="{{ route('jobs.index') }}" method="GET" class="relative">
          <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#a77d52]">
            <svg class="w-4 h-4"><use href="#i-search"/></svg>
          </div>
          <input type="search" name="q" placeholder="Cari posisi atau lokasi..."
            class="w-full py-3 pl-10 pr-4 text-sm transition outline-none rounded-xl"
            style="background:#fff8f2; border:1.5px solid rgba(167,125,82,0.25); color:#3b2209;"
            onfocus="this.style.borderColor='#a77d52'" onblur="this.style.borderColor='rgba(167,125,82,0.25)'">
        </form>
      </div>

      {{-- Nav Items --}}
      <div class="px-4 pt-2 pb-3 space-y-1">
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 px-2 pb-1">Menu</p>

        <a href="{{ route('jobs.index') }}" class="mob-nav-item">
          <span class="mob-icon">
            <svg class="w-4 h-4"><use href="#i-briefcase"/></svg>
          </span>
          Lowongan Kerja
        </a>

        <a href="/sites" class="mob-nav-item">
          <span class="mob-icon">
            <svg class="w-4 h-4"><use href="#i-map-pin"/></svg>
          </span>
          Lokasi Site
        </a>
      </div>

      {{-- Divider --}}
      <div class="mx-5 border-t" style="border-color: rgba(167,125,82,0.12)"></div>

      @auth
        {{-- Logged in user --}}
        <div class="px-4 pt-3 pb-2 space-y-1">
          <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 px-2 pb-1">Akun Saya</p>
          <div class="flex items-center gap-3 px-3 py-3 rounded-xl" style="background:#fff8f2;">
            <div class="flex items-center justify-center flex-shrink-0 w-10 h-10 text-sm font-bold text-white rounded-xl"
              style="background:#a77d52">
              {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
              <div class="text-sm font-bold" style="color:#3b2209">{{ auth()->user()->name }}</div>
              <div class="text-xs capitalize" style="color:#a77d52">{{ auth()->user()->role ?? 'Pelamar' }}</div>
            </div>
          </div>
          <a href="{{ route('profile.edit') }}" class="mob-nav-item">
            <span class="mob-icon"><svg class="w-4 h-4"><use href="#i-user"/></svg></span>
            Profil Saya
          </a>
          <a href="{{ route('applications.mine') }}" class="mob-nav-item">
            <span class="mob-icon"><svg class="w-4 h-4"><use href="#i-briefcase"/></svg></span>
            Lamaran Saya
          </a>
        </div>
        <div class="px-4 pb-5">
          <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit"
              class="flex items-center justify-center w-full gap-2 py-3 text-sm font-semibold transition rounded-xl"
              style="border: 1.5px solid rgba(220,38,38,0.3); color:#dc2626; background:transparent"
              onmouseover="this.style.background='rgba(220,38,38,0.06)'" onmouseout="this.style.background='transparent'">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H6.75A2.25 2.25 0 004.5 5.25v13.5A2.25 2.25 0 006.75 21H13.5a2.25 2.25 0 002.25-2.25V15M9.75 12h10.5m0 0-3-3m3 3-3 3"/>
              </svg>
              Keluar
            </button>
          </form>
        </div>
      @else
        {{-- Guest CTA --}}
        <div class="grid grid-cols-2 gap-3 px-4 pt-4 pb-6">
          <a href="{{ route('login') }}"
            class="flex items-center justify-center gap-2 py-3.5 rounded-xl text-sm font-bold transition"
            style="border:2px solid #a77d52; color:#a77d52; background:#fff"
            onmouseover="this.style.background='#fff8f2'" onmouseout="this.style.background='#fff'">
            <svg class="w-4 h-4"><use href="#i-user"/></svg>
            Masuk
          </a>
          <a href="{{ route('register') }}"
            class="flex items-center justify-center gap-2 py-3.5 rounded-xl text-sm font-bold text-white transition shadow-md"
            style="background:#a77d52"
            onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
            <svg class="w-4 h-4"><use href="#i-apply"/></svg>
            Daftar Gratis
          </a>
        </div>
      @endauth
    </div>
  </div>

  {{-- ============================================================
       MAIN CONTENT
  ============================================================ --}}
  <main id="maincontent">

    {{-- ===== HERO ===== --}}
    <section aria-labelledby="hero-heading" class="home-section-soft">
      <div class="relative overflow-hidden">
        <img src="{{ asset('assets/banner-abn.png') }}"
          alt="Bergabunglah bersama tim profesional PT Andalan Artha Primanusa"
          class="w-full h-[430px] md:h-[500px] object-cover"
          width="1440" height="440"
          fetchpriority="high"
          decoding="async">
        <div class="absolute inset-0 flex items-center"
          style="background: rgba(36,25,16,.55)">
          <div class="w-full px-6 mx-auto max-w-7xl lg:px-8">
            <div class="max-w-2xl text-white">
              <span class="text-white home-pill border-white/20 bg-white/10">
                Portal Karier Andalan
              </span>
              <h1 id="hero-heading" class="mt-5 text-4xl font-black leading-tight tracking-tight md:text-6xl">
                Temukan peluang karier terbaik bersama Andalan
              </h1>
              <p class="max-w-xl mt-5 text-base leading-relaxed text-white/85 md:text-lg">
                Cari lowongan aktif, pilih site yang sesuai, lalu pantau proses lamaran langsung dari akun kamu.
              </p>
              <div class="flex flex-wrap gap-3 mt-7">
                <a href="{{ route('jobs.index') }}"
                  class="inline-flex items-center gap-2 px-5 py-3 text-sm font-extrabold text-[#7a5530] transition bg-white shadow-lg rounded-2xl hover:-translate-y-0.5 hover:bg-[#fff8f0]">
                  <svg class="w-4 h-4" aria-hidden="true"><use href="#i-briefcase"/></svg>
                  Lihat Lowongan
                </a>
                @guest
                  <a href="{{ route('register') }}"
                    class="inline-flex items-center gap-2 px-5 py-3 text-sm font-extrabold text-white transition border border-white/25 bg-white/10 rounded-2xl hover:-translate-y-0.5 hover:bg-white/20">
                    Daftar Sekarang
                  </a>
                @endguest
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    {{-- LEAFLET MAPS SCRIPT & STYLE --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const sitesData = @json($sitesWithCoords ?? []);
        const mapContainer = document.getElementById('sites-map');

        if (!mapContainer || typeof L === 'undefined') return;

        const sitesWithCoords = sitesData.filter(function (site) {
          return Number.isFinite(Number(site.latitude)) && Number.isFinite(Number(site.longitude));
        });

        const avgLat = sitesWithCoords.length
          ? sitesWithCoords.reduce((sum, s) => sum + Number(s.latitude), 0) / sitesWithCoords.length
          : -2.5489;
        const avgLng = sitesWithCoords.length
          ? sitesWithCoords.reduce((sum, s) => sum + Number(s.longitude), 0) / sitesWithCoords.length
          : 118.0149;

        const map = L.map('sites-map').setView([avgLat, avgLng], sitesWithCoords.length ? 5 : 4);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
          attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
          maxZoom: 19,
        }).addTo(map);

        const markers = [];
        sitesWithCoords.forEach(function (site) {
          const marker = L.circleMarker([site.latitude, site.longitude], {
            radius: 10,
            fillColor: site.dot || '#a77d52',
            color: '#fff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.85,
          })
          .bindPopup(
            `<div style="font-size: 13px; font-weight: 700; color: #1f2937;">${site.name}</div>
            <a href="{{ route('jobs.index') }}?site=${site.param}"
              style="display: inline-block; margin-top: 8px; padding: 6px 10px; background: #a77d52; color: white; border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 700;">
              Lihat Lowongan
            </a>`
          )
          .addTo(map);
          markers.push(marker);
        });

        if (markers.length > 0) {
          const group = new L.featureGroup(markers);
          map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }
      });
    </script>

    {{-- ===== APPLICATION JOURNEY ===== --}}
    <section class="px-6 py-14 bg-white lg:px-8" aria-labelledby="apply-flow-heading">
      <div class="aj-wrap">

        @php
          $applySteps = [
            [
              'icon'  => 'i-search',
              'title' => 'Cari Lowongan',
              'desc'  => 'Temukan posisi yang sesuai dengan pengalaman dan minatmu.',
              'items' => [
                'Buka menu Lowongan Kerja',
                'Lihat posisi yang tersedia',
                'Pilih lowongan berstatus open',
              ],
            ],
            [
              'icon'  => 'i-eye',
              'title' => 'Lihat Detail',
              'desc'  => 'Pelajari informasi posisi sebelum mengirim lamaran.',
              'items' => [
                'Baca job description',
                'Lihat lokasi & kualifikasi',
                'Klik "Lamar Sekarang"',
              ],
            ],
            [
              'icon'  => 'i-user-plus',
              'title' => 'Login / Daftar',
              'desc'  => 'Masuk hanya saat kamu sudah siap melamar.',
              'items' => [
                'Login jika sudah punya akun',
                'Daftar gratis jika belum punya akun',
                'Akun otomatis menjadi pelamar',
              ],
              'cta' => true,
            ],
            [
              'icon'  => 'i-contact',
              'title' => 'Lengkapi Biodata',
              'desc'  => 'Lengkapi profil sebagai data utama proses rekrutmen.',
              'items' => [
                'Data diri & kontak',
                'Pendidikan & pengalaman',
                'Alamat KTP & domisili',
                'POH / penempatan',
              ],
            ],
            [
              'icon'  => 'i-file-check',
              'title' => 'Upload CV & Submit',
              'desc'  => 'Pastikan data dan dokumen sudah lengkap.',
              'items' => [
                'Upload CV (wajib)',
                'Lengkapi seluruh field wajib',
                'Submit biodata',
                'Tahap awal: Screening',
              ],
            ],
            [
              'icon'  => 'i-list-checks',
              'title' => 'Pantau Status',
              'desc'  => 'Pantau perkembangan proses rekrutmen secara real-time.',
              'items' => [
                'Buka "Lamaran Saya"',
                'Lihat status terbaru',
                'Screening → Interview',
                'MCU → Offering',
              ],
            ],
          ];
        @endphp

        {{-- ── Header ── --}}
        <div>
          <span class="aj-badge">
            <svg class="w-4 h-4" aria-hidden="true"><use href="#i-clipboard-list"/></svg>
            Cara Melamar
          </span>
          <h2 id="apply-flow-heading" class="mt-5 text-[2.1rem] font-extrabold leading-[1.12] tracking-tight md:text-[2.75rem] md:leading-[1.1]" style="color:#0f1b33">
            Proses melamar yang singkat,<br class="hidden sm:block"> jelas, dan transparan
          </h2>
          <p class="mt-4 text-[15px] leading-relaxed md:text-lg" style="color:#6b4f3a; max-width:700px">
            Pelamar dapat melihat lowongan terlebih dahulu tanpa login.
            Login hanya diperlukan saat kamu siap mengirim lamaran.
            Setelah biodata dan CV lengkap, status lamaran dapat dipantau secara real-time.
          </p>
        </div>

        {{-- ── Timeline desktop ── --}}
        <div class="hidden md:block aj-rich-timeline" aria-hidden="true">
          <div class="aj-timeline">
            <div class="aj-timeline-line"></div>
            @foreach($applySteps as $idx => $s)
              <div class="aj-timeline-step {{ $idx === 0 ? 'is-active' : '' }}">
                <span class="aj-timeline-dot">{{ $idx + 1 }}</span>
                <span class="aj-timeline-label">{{ $s['title'] }}</span>
              </div>
            @endforeach
          </div>
        </div>

        {{-- ── Cards desktop grid ── --}}
        <div class="hidden md:grid aj-grid" aria-label="Langkah proses melamar">
          @foreach($applySteps as $i => $step)
            <article class="aj-card">
              <div class="aj-card-top">
                <span class="aj-icon">
                  <svg aria-hidden="true"><use href="#{{ $step['icon'] }}"/></svg>
                </span>
                <span class="aj-stepnum">{{ $i + 1 }}</span>
              </div>
              <h3 class="aj-title">{{ $step['title'] }}</h3>
              <p class="aj-desc">{{ $step['desc'] }}</p>
              <ul class="aj-points">
                @foreach($step['items'] as $item)
                  <li class="aj-point">{{ $item }}</li>
                @endforeach
              </ul>
              @if(!empty($step['cta']))
                <a href="{{ route('login') }}" class="aj-cta">
                  Masuk &amp; Lamar
                  <svg class="w-3.5 h-3.5" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                </a>
              @endif
            </article>
          @endforeach
        </div>

        {{-- ── Cards mobile carousel ── --}}
        <div id="aj-carousel" class="md:hidden aj-cardcar" aria-label="Langkah proses melamar">
          @foreach($applySteps as $i => $step)
            <article class="aj-card">
              <div class="aj-card-top">
                <span class="aj-icon">
                  <svg aria-hidden="true"><use href="#{{ $step['icon'] }}"/></svg>
                </span>
                <span class="aj-stepnum">{{ $i + 1 }}</span>
              </div>
              <h3 class="aj-title">{{ $step['title'] }}</h3>
              <p class="aj-desc">{{ $step['desc'] }}</p>
              <ul class="aj-points">
                @foreach($step['items'] as $item)
                  <li class="aj-point">{{ $item }}</li>
                @endforeach
              </ul>
              @if(!empty($step['cta']))
                <a href="{{ route('login') }}" class="aj-cta">
                  Masuk &amp; Lamar
                  <svg class="w-3.5 h-3.5" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                </a>
              @endif
            </article>
          @endforeach
        </div>
        <div class="aj-dots md:hidden" id="aj-dots" aria-label="Navigasi langkah"></div>

        {{-- ── Tips bar ── --}}
        <div class="aj-tips">
          <div class="aj-tips-left">
            <span class="aj-tips-icon">
              <svg aria-hidden="true"><use href="#i-lightbulb"/></svg>
            </span>
            <div>
              <span class="aj-tips-kicker">Tips untuk Pelamar</span>
              <p class="aj-tips-text">Pastikan data & dokumen sudah benar sebelum submit.</p>
            </div>
          </div>
          <div class="aj-benefits">
            <span class="aj-benefit">
              <svg aria-hidden="true"><use href="#i-shield-check"/></svg>
              Aman & terpercaya
            </span>
            <span class="aj-benefit">
              <svg aria-hidden="true"><use href="#i-clock"/></svg>
              Proses transparan
            </span>
            <span class="aj-benefit">
              <svg aria-hidden="true"><use href="#i-users"/></svg>
              Kesempatan setara
            </span>
          </div>
        </div>

      </div>
    </section>


    {{-- ===== LOWONGAN TERBARU ===== --}}
    <section class="px-6 py-12 home-section-soft lg:px-8" aria-labelledby="jobs-heading">
      <div class="mx-auto max-w-7xl">
      <div class="home-card overflow-hidden rounded-[1.5rem]">

        {{-- Header --}}
        <div class="flex flex-col gap-4 p-6 border-b md:flex-row md:items-center md:justify-between" style="border-color: #f4f0eb">
          <div>
            <span class="home-pill">Lowongan Aktif</span>
            <h2 id="jobs-heading" class="mt-3 text-2xl font-black tracking-tight md:text-3xl" style="color: #1f2937">Lowongan terbaru untuk kamu</h2>
            <p class="mt-1 text-sm text-slate-500">Pilih posisi yang tersedia dan lihat detail sebelum melamar.</p>
          </div>
          <a href="{{ route('jobs.index') }}" class="px-5 py-3 text-sm home-primary-link">
            Lihat semua lowongan
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
                    $typeRaw  = $job->employment_type ?? '';
                    $typeSlug = \Illuminate\Support\Str::of((string) $typeRaw)->lower()->value();
                    $type     = $typeSlug ? strtoupper($typeRaw) : null;
                    $typeBadge = match ($typeSlug) {
                      'contract', 'kontrak' => 'bg-amber-600',
                      'intern', 'magang'    => 'bg-emerald-600',
                      default               => '#a77d52',
                    };
                    $levelLabel = is_string($job->level ?? null)
                      ? ucwords(str_replace('_', ' ', $job->level))
                      : null;
                @endphp

                <article class="flex flex-col overflow-hidden home-job-card rounded-2xl"
                  itemscope itemtype="https://schema.org/JobPosting">
                  <meta itemprop="title" content="{{ $job->title }}">
                  <meta itemprop="datePosted" content="{{ optional($job->created_at)->toDateString() }}">

                  <div class="flex flex-col flex-1 p-5">

                    {{-- Header kartu --}}
                      <div class="flex items-start gap-3">
                      <div class="p-2.5 rounded-2xl text-white shrink-0 shadow-sm" style="background: #a77d52">
                        <svg class="w-5 h-5" aria-hidden="true"><use href="#i-briefcase"/></svg>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                          <a href="{{ route('jobs.show', $job) }}"
                            class="block text-base font-extrabold leading-snug transition hover:opacity-75"
                            style="color: #1f2937"
                            itemprop="url">
                            {{ $job->title }}
                          </a>
                          <div class="shrink-0 flex flex-col items-end gap-1.5">
                            @if($type)
                              <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold text-white {{ is_string($typeBadge) ? $typeBadge : '' }}" @if(!is_string($typeBadge)) style="background: {{ $typeBadge }}" @endif>
                                {{ $type }}
                              </span>
                            @endif
                            @if($isNew)
                              <span class="badge badge-new">Baru</span>
                            @endif
                          </div>
                        </div>

                        @if($job->code || $levelLabel)
                          <p class="mt-1 text-[11px] font-semibold tracking-wide" style="color: #8b5e3c">
                            @if($job->code)<span class="font-mono">{{ $job->code }}</span>@endif
                            @if($job->code && $levelLabel) <span class="opacity-40">·</span> @endif
                            @if($levelLabel){{ $levelLabel }}@endif
                          </p>
                        @endif

                        {{-- Lokasi - PERBAIKAN: tidak tampilkan region dua kali --}}
                        <p class="mt-2 text-xs leading-relaxed" style="color: #6b4f3a">
                          @if($siteName)
                            <svg class="w-3 h-3 inline-block mr-0.5 -mt-px" aria-hidden="true"><use href="#i-map-pin"/></svg>
                            <span class="font-medium">{{ $siteName }}</span>@if($showRegion)<span class="opacity-60">, {{ $siteRegion }}</span>@endif
                          @else
                            <span class="opacity-50">Lokasi belum tersedia</span>
                          @endif
                          <span class="mx-1 text-[#d0b79e]">•</span>
                          <svg class="w-3 h-3 inline-block mr-0.5 -mt-px" aria-hidden="true"><use href="#i-clock"/></svg>
                          {{ optional($job->created_at)->diffForHumans() }}
                        </p>
                      </div>
                    </div>

                    @if(!empty($job->division))
                      <div class="mt-3 flex items-center gap-1.5">
                        <span class="home-pill">{{ $job->division }}</span>
                      </div>
                    @endif
                    {{-- CTA --}}
                      <div class="flex items-center justify-between gap-3 pt-4 mt-5 border-t"
                      style="border-color: rgba(167,125,82,.2)">
                      <a href="{{ route('jobs.show', $job) }}"
                        class="inline-flex items-center gap-1 text-sm font-bold transition hover:opacity-70"
                        style="color: #a77d52">
                        Lihat Detail
                        <svg class="w-3.5 h-3.5" aria-hidden="true"><use href="#i-arrow-right"/></svg>
                      </a>

                      @auth
                        @if(! auth()->user()->hasVerifiedEmail())
                          <a href="{{ route('verification.notice') }}"
                            class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-xl text-white hover:opacity-90 transition shadow-sm"
                            style="background: #8b5e3c">
                            <svg class="w-3.5 h-3.5" aria-hidden="true"><use href="#i-user"/></svg>
                            Verifikasi Email
                          </a>
                        @else
                        <form action="{{ route('applications.store', $job) }}" method="POST">
                          @csrf
                          <button type="submit"
                            class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-xl text-white hover:opacity-90 active:scale-95 transition shadow-sm"
                            style="background: #a77d52"
                            data-confirm-title="Kirim lamaran?"
                            data-confirm-message="Profil kandidat akan dicek dulu sebelum lamaran dikirim.">
                            <svg class="w-3.5 h-3.5" aria-hidden="true"><use href="#i-apply"/></svg>
                            Lamar Sekarang
                          </button>
                        </form>
                        @endif
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

      </div>
    </section>

  </main>

  {{-- ============================================================
       FOOTER
  ============================================================ --}}
  <footer style="background: #5c3d1e; color: #f9f3ee;">
    <div class="px-6 py-12 mx-auto max-w-7xl lg:px-8">
      <div class="grid gap-8 md:grid-cols-4">

        {{-- Brand --}}
        <div class="md:col-span-2">
          <p class="max-w-sm text-sm leading-relaxed" style="color: #e8d5c4">
            Portal karier resmi PT Andalan Artha Primanusa. Kami menghubungkan talenta terbaik dengan peluang karier yang sesuai.
          </p>
          <div class="flex gap-4 mt-6">
            <a href="https://andalan.co.id" target="_blank" rel="noopener noreferrer" style="color:#e8d5c4" class="transition hover:text-white" aria-label="Website PT Andalan">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
            </a>
            <a href="https://linkedin.com/company/andalan" target="_blank" rel="noopener noreferrer" style="color:#e8d5c4" class="transition hover:text-white" aria-label="LinkedIn PT Andalan">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
            <a href="https://instagram.com/andalan" target="_blank" rel="noopener noreferrer" style="color:#e8d5c4" class="transition hover:text-white" aria-label="Instagram PT Andalan">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
            </a>
          </div>
        </div>

        {{-- Quick Links --}}
        <div>
          <h3 class="mb-4 text-sm font-bold tracking-wide text-white uppercase">Menu</h3>
          <ul class="space-y-2">
            <li><a href="{{ route('jobs.index') }}" class="text-sm transition hover:text-white" style="color:#e8d5c4">Lowongan Kerja</a></li>
            <li><a href="{{ route('register') }}" class="text-sm transition hover:text-white" style="color:#e8d5c4">Daftar</a></li>
            <li><a href="{{ route('login') }}" class="text-sm transition hover:text-white" style="color:#e8d5c4">Masuk</a></li>
            <li><a href="#faq-heading" class="text-sm transition hover:text-white" style="color:#e8d5c4">FAQ</a></li>
          </ul>
        </div>

        {{-- Contact --}}
        <div>
          <h3 class="mb-4 text-sm font-bold tracking-wide text-white uppercase">Kontak</h3>
          <ul class="space-y-2 text-sm" style="color:#e8d5c4">
            <li class="flex items-start gap-2">
              <svg class="w-4 h-4 mt-0.5 shrink-0" aria-hidden="true"><use href="#i-map-pin"/></svg>
              <span>Jl. Plaju No.11, Kebon Melati, Tanah Abang, Jakarta Pusat 10230</span>
            </li>
            <li class="flex items-center gap-2">
              <svg class="w-4 h-4 shrink-0" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" fill="none" stroke="currentColor" stroke-width="2"/><polyline points="22,6 12,13 2,6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
              <a href="mailto:recruitment@andalanarthaprimanusa.com" class="transition hover:text-white">recruitment@andalanarthaprimanusa.com</a>
            </li>
          </ul>
        </div>

      </div>

      <div class="pt-8 mt-12 border-t" style="border-color: rgba(255,255,255,0.2)">
        <p class="text-xs text-center" style="color:#e8d5c4">
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

      // Cara Melamar: mobile carousel dots
      const stepsTrack = document.getElementById('apply-steps');
      const dotsWrap   = document.getElementById('apply-dots');
      if (stepsTrack && dotsWrap && stepsTrack.children.length > 0) {
        const cards = Array.prototype.slice.call(stepsTrack.children);
        const dots  = cards.map(function (_, idx) {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'apply-dot';
          btn.setAttribute('aria-label', 'Langkah ' + (idx + 1));
          btn.addEventListener('click', function () {
            const left = cards[idx].offsetLeft - stepsTrack.clientWidth / 2 + cards[idx].clientWidth / 2;
            stepsTrack.scrollTo({ left: Math.max(left, 0), behavior: 'smooth' });
          });
          dotsWrap.appendChild(btn);
          return btn;
        });

        function syncDots() {
          let active = 0;
          const scrollLeft = stepsTrack.scrollLeft + stepsTrack.clientWidth / 2;
          cards.forEach(function (card, idx) {
            if (scrollLeft >= card.offsetLeft) active = idx;
          });
          dots.forEach(function (dot, idx) {
            dot.classList.toggle('is-active', idx === active);
          });
        }

        if (!window.matchMedia || !window.matchMedia('(min-width: 768px)').matches) {
          syncDots();
        }
        stepsTrack.addEventListener('scroll', syncDots, { passive: true });
        window.addEventListener('resize', syncDots, { passive: true });
      }
    });
  </script>
</body>
</html>
