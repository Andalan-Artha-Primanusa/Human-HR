{{-- resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">

<head>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Human.Careers — Portal Karier Resmi Andalan</title>

  {{-- Performance: DNS prefetch & preconnect --}}
  <link rel="dns-prefetch" href="//fonts.googleapis.com">
  <link rel="dns-prefetch" href="//fonts.gstatic.com">
  <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  {{-- Fonts (preload stylesheet for faster paint) --}}
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
  <noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
  </noscript>

  {{-- Vite Assets (module = non-blocking) --}}
  {{-- === Vite build tanpa @vite (baca manifest.json) === --}}


  {{-- SEO Meta --}}
  <meta name="description" content="Portal karier resmi Andalan. Lowongan terverifikasi, proses seleksi transparan, dan pembaruan status waktu nyata. Satu akun untuk seluruh lokasi kerja Andalan.">
  <meta name="keywords" content="karier, lowongan, pekerjaan, Andalan, rekrutmen, mining, HR, job portal">
  <meta name="author" content="PT Andalan">
  <meta name="theme-color" content="#0a0a0a">
  <meta name="color-scheme" content="light dark">

  {{-- Open Graph / Twitter --}}
  <meta property="og:title" content="Human.Careers — Portal Karier Resmi Andalan">
  <meta property="og:description" content="Lowongan terverifikasi, seleksi transparan, status waktu nyata.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:image" content="{{ asset('storage/media/og-careers.jpg') }}">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Human.Careers — Portal Karier Resmi Andalan">
  <meta name="twitter:description" content="Telusuri lowongan, lamar cepat, dan pantau progres secara real-time.">
  <meta name="twitter:image" content="{{ asset('storage/media/og-careers.jpg') }}">

  {{-- Favicons --}}
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

  {{-- Preload hero image for LCP --}}
  <link rel="preload" as="image" href="{{ asset('assets/hr1.jpg') }}" imagesrcset="{{ asset('assets/hr1.jpg') }} 1x" imagesizes="(min-width: 1024px) 640px, 100vw" fetchpriority="high">

  <style>
    /* ============== Base & tokens ============== */
    :root {
      --blue: #1d4ed8;
      --red: #dc2626;
      --black: #0a0a0a;
      --gray: #e5e7eb
    }

    html,
    body {
      font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial
    }

    .ring-focus:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(29, 78, 216, .35)
    }

    .card-hover {
      transition: transform .25s ease, box-shadow .25s ease
    }

    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, .08)
    }

    details>summary {
      list-style: none;
      cursor: pointer
    }

    details>summary::-webkit-details-marker {
      display: none
    }

    .dropdown[open]>summary svg {
      transform: rotate(180deg)
    }

    .to-top {
      position: fixed;
      right: 1rem;
      bottom: 1rem;
      z-index: 50;
      display: none
    }

    .to-top.show {
      display: block
    }

    .toast {
      position: fixed;
      right: 1rem;
      bottom: 1rem;
      z-index: 60;
      display: none;
      min-width: 240px;
      max-width: 320px
    }

    .toast.show {
      display: block;
      animation: slideUp .25s ease
    }

    @keyframes slideUp {
      from {
        transform: translateY(12px);
        opacity: 0
      }

      to {
        transform: translateY(0);
        opacity: 1
      }
    }

    /* ============== Hero background ============== */
    .hero-wrap {
      position: relative;
      isolation: isolate;
      background: var(--black)
    }

    .hero-wrap::before {
      content: "";
      position: absolute;
      inset: 0;
      z-index: -1;
      background:
        radial-gradient(1200px 400px at -10% -10%, rgba(29, 78, 216, .35), transparent 60%),
        radial-gradient(900px 300px at 120% 120%, rgba(220, 38, 38, .28), transparent 60%);
      pointer-events: none
    }

    /* Stars (planet vibes) */
    .star-sky {
      position: relative;
      isolation: isolate;
    }

    .star-sky .star-layer {
      position: absolute;
      inset: -12% -6% -12% -6%;
      z-index: -1;
      pointer-events: none;
      will-change: transform, opacity;
      background-repeat: no-repeat;
      background-size: cover;
      filter: drop-shadow(0 0 1px rgba(255, 255, 255, .15));
    }

    .star-sky .star-1 {
      opacity: .9;
      animation: sky-drift-1 65s linear infinite;
      background-image:
        radial-gradient(1px 1px at 8% 18%, #fff, transparent 52%),
        radial-gradient(1px 1px at 24% 66%, #fff, transparent 52%),
        radial-gradient(1.4px 1.4px at 72% 28%, #fff, transparent 52%),
        radial-gradient(1px 1px at 64% 12%, #fff, transparent 52%),
        radial-gradient(1px 1px at 9% 78%, #fff, transparent 52%),
        radial-gradient(1.2px 1.2px at 45% 41%, #fff, transparent 52%),
        radial-gradient(1px 1px at 86% 82%, #fff, transparent 52%),
        radial-gradient(1px 1px at 22% 90%, #fff, transparent 52%);
    }

    .star-sky .star-2 {
      opacity: .6;
      animation: sky-drift-2 95s linear infinite reverse;
      background-image:
        radial-gradient(1px 1px at 16% 30%, #fff, transparent 52%),
        radial-gradient(1px 1px at 54% 74%, #fff, transparent 52%),
        radial-gradient(1.4px 1.4px at 68% 52%, #fff, transparent 52%),
        radial-gradient(1px 1px at 90% 22%, #fff, transparent 52%),
        radial-gradient(1px 1px at 12% 56%, #fff, transparent 52%);
      filter: blur(.2px) drop-shadow(0 0 1px rgba(255, 255, 255, .12));
    }

    .star-sky .star-3 {
      opacity: .45;
      animation: sky-drift-3 130s linear infinite;
      background-image:
        radial-gradient(1px 1px at 26% 14%, #fff, transparent 52%),
        radial-gradient(1px 1px at 40% 86%, #fff, transparent 52%),
        radial-gradient(1px 1px at 78% 68%, #fff, transparent 52%),
        radial-gradient(1px 1px at 92% 40%, #fff, transparent 52%),
        radial-gradient(1px 1px at 6% 42%, #fff, transparent 52%);
      filter: blur(.35px) drop-shadow(0 0 1px rgba(255, 255, 255, .1));
    }

    @keyframes sky-drift-1 {
      from {
        transform: translate3d(0, 0, 0)
      }

      to {
        transform: translate3d(-4%, -3%, 0)
      }
    }

    @keyframes sky-drift-2 {
      from {
        transform: translate3d(0, 0, 0)
      }

      to {
        transform: translate3d(-6%, -5%, 0)
      }
    }

    @keyframes sky-drift-3 {
      from {
        transform: translate3d(0, 0, 0)
      }

      to {
        transform: translate3d(-8%, -6%, 0)
      }
    }

    @media (prefers-reduced-motion: reduce) {
      .star-sky .star-layer {
        animation: none
      }
    }

    /* Timeline */
    .tl {
      position: relative
    }

    .tl::after {
      content: "";
      position: absolute;
      right: 12px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: linear-gradient(180deg, rgba(10, 10, 10, .2), rgba(10, 10, 10, .05))
    }

    .tl-step {
      position: relative;
      padding-right: 48px
    }

    .tl-dot {
      --c: var(--blue);
      position: absolute;
      right: 3px;
      top: .4rem;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      box-shadow: inset 0 0 0 3px var(--c), 0 0 0 4px #fff;
      background: transparent
    }

    .tl-dot.green {
      --c: #16a34a
    }

    .tl-dot.blue {
      --c: var(--blue)
    }

    .tl-dot.amber {
      --c: #f59e0b
    }

    .tl-dot.red {
      --c: #dc2626
    }

    /* Marquee */
    .marquee {
      position: relative;
      overflow: hidden
    }

    .marquee__track {
      display: flex;
      gap: .75rem;
      width: max-content;
      animation: marquee-scroll 28s linear infinite;
      will-change: transform
    }

    .marquee:hover .marquee__track {
      animation-play-state: paused
    }

    @keyframes marquee-scroll {
      from {
        transform: translateX(0)
      }

      to {
        transform: translateX(-50%)
      }
    }

    @media (prefers-reduced-motion:reduce) {
      .marquee__track {
        animation: none
      }
    }

    .chip {
      transition: transform .2s ease
    }

    .chip:hover {
      transform: translateY(-2px)
    }

    .chips {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem
    }

    .chip-pill {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      padding: .375rem .625rem;
      border-radius: 9999px;
      background: #fff;
      border: 1px solid var(--chip-bd, #e5e7eb);
      transition: transform .2s ease, background .2s ease
    }

    .chip-pill:hover {
      transform: translateY(-2px);
      background: #fafafa
    }

    .ico-sm {
      display: inline-grid;
      place-items: center;
      width: 28px;
      height: 28px;
      border-radius: 9999px;
      background: rgba(29, 78, 216, .08);
      color: #1d4ed8;
      border: 1px solid rgba(29, 78, 216, .25)
    }

    .count-badge {
      font-size: 11px;
      line-height: 1;
      padding: .2rem .45rem;
      border-radius: 9999px;
      background: #f4f4f5;
      color: #111827
    }
  </style>
</head>

<body class="bg-white text-zinc-900 antialiased">
  @php
  /** Server-side defaults (avoid undefined warnings) */
  $jobs = $jobs ?? collect();
  $myApps = $myApps ?? collect();
  $myAppsSummary = $myAppsSummary ?? ['total' => ($myApps->count() ?? 0), 'byStatus' => collect()];
  $myAppsProgress = $myAppsProgress ?? collect();
  $sitesSimple = $sitesSimple ?? collect();

  $jobsCollection = ($jobs instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $jobs->getCollection() : collect($jobs);
  $filteredJobs = $jobsCollection->when(
  ($jobsCollection->first()?->getAttributes() ?? null) && array_key_exists('status', $jobsCollection->first()->getAttributes()),
  fn($c)=>$c->where('status','open'),
  fn($c)=>$c
  );

  $byDivision = isset($byDivision) && $byDivision instanceof \Illuminate\Support\Collection
  ? $byDivision
  : $filteredJobs->groupBy('division')->map->count()->sortDesc()
  ->mapWithKeys(fn($v,$k)=>[ $k ?: 'Tanpa Divisi' => (int)$v ]);

  $brandBlue = '#1d4ed8'; $brandRed = '#dc2626'; $brandBlack = '#0a0a0a'; $brandGray = '#e5e7eb';
  @endphp

  {{-- Skip link --}}
  <a href="#maincontent" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:bg-blue-600 focus:text-white focus:rounded px-3 py-2">Lewati ke konten utama</a>

  {{-- SVG Sprite (inline = no additional request) --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true">
    <symbol id="i-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <g stroke-width="2" stroke-linecap="round">
        <path d="M4 6h16M4 12h16M4 18h16" />
      </g>
    </symbol>
    <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="7" />
        <path d="M21 21l-4.3-4.3" />
      </g>
    </symbol>
    <symbol id="i-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <polyline points="6 9 12 15 18 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 21a8 8 0 1 0-16 0" />
        <circle cx="12" cy="7" r="4" />
      </g>
    </symbol>
    <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z" />
        <path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" />
      </g>
    </symbol>
    <symbol id="i-arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14" />
        <path d="m12 5 7 7-7 7" />
      </g>
    </symbol>
    <symbol id="i-rocket" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 15s3-7 10-10c5-2 6 4 3 6-4 3-10 10-10 10S3 22 5 15Z" />
        <path d="M14 5l5 5" />
        <path d="M6 14l7 7" />
      </g>
    </symbol>
    <symbol id="i-apply" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <g stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 5v14" />
        <path d="M5 12h14" />
      </g>
    </symbol>
    <symbol id="i-check" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M20 6L9 17l-5-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="i-bolt" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M13 2L3 14h7l-1 8 11-12h-7l1-8z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="i-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M12 3l7 4v5a9 9 0 1 1-14 0V7l7-4z" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
    </symbol>
    <symbol id="i-bell" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" stroke-width="1.8" />
      <path d="M9 17a3 3 0 0 0 6 0" stroke-width="1.8" />
    </symbol>
    <symbol id="i-mail" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M4 6h16v12H4z" stroke-width="2" />
      <path d="M22 6l-10 7L2 6" stroke-width="2" />
    </symbol>
    <symbol id="i-external" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M14 3h7v7" stroke-width="2" />
      <path d="M10 14L21 3" stroke-width="2" />
      <path d="M5 12v7h7" stroke-width="2" />
    </symbol>
    <symbol id="i-instagram" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2" />
      <circle cx="12" cy="12" r="4" stroke-width="2" />
      <circle cx="17" cy="7" r="1.2" stroke-width="2" />
    </symbol>
    <symbol id="i-linkedin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2" />
      <path d="M7 17v-7M7 7h.01M12 17v-4a3 3 0 1 1 6 0v4" stroke-width="2" stroke-linecap="round" />
    </symbol>
  </svg>

  {{-- HEADER --}}
  <header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b" style="border-color: {{ $brandGray }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button id="btn-nav" class="md:hidden p-2 text-zinc-700 hover:text-black ring-focus" aria-label="Menu" aria-expanded="false" aria-controls="nav-mobile">
          <svg class="w-5 h-5">
            <use href="#i-menu" />
          </svg>
        </button>
        <a href="{{ route('welcome') }}" class="flex items-center gap-2 font-extrabold tracking-tight" style="color: {{ $brandBlack }}" aria-label="Beranda Human.Careers">
          <!-- FOTO / LOGO -->
          <img src="{{ asset('assets/AndalanLogo.png') }}"
  alt="Logo Human Careers"
  class="h-20 md:h-20 w-auto object-contain">
        </a>
      </div>

      {{-- Search (desktop) --}}
      <form action="{{ route('jobs.index') }}" method="GET" class="hidden md:flex items-center flex-1 max-w-lg mx-6" role="search" autocomplete="off">
        <div class="relative w-full">
          <input name="q" type="search" placeholder="Cari posisi, divisi, atau kata kunci…" class="w-full rounded-xl bg-white border text-zinc-800 placeholder-zinc-500 focus:ring-2 px-10 py-2 outline-none"
            style="border-color: {{ $brandGray }}; --tw-ring-color: {{ $brandBlue }};" aria-label="Pencarian lowongan" inputmode="search">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" aria-hidden="true"><svg class="w-5 h-5">
              <use href="#i-search" />
            </svg></span>
        </div>
      </form>

      {{-- Right nav --}}
      <nav class="hidden md:flex items-center gap-6 text-sm" aria-label="Navigasi utama">
        <a href="{{ route('jobs.index') }}" class="hover:opacity-80" style="color: {{ $brandBlack }}">Lowongan</a>
        @auth
        <a href="{{ route('applications.mine') }}" class="hover:opacity-80" style="color: {{ $brandBlack }}">Lamaran</a>
        <details class="dropdown relative">
          <summary class="flex items-center gap-2 cursor-pointer select-none hover:opacity-80" style="color: {{ $brandBlack }}" aria-haspopup="menu" aria-expanded="false">
            @php $uname = auth()->user()->name ?? auth()->user()->email ?? 'Pengguna'; $ini = strtoupper(mb_substr($uname,0,1)); @endphp
            <span class="inline-grid place-items-center w-9 h-9 rounded-full border text-xs font-bold" style="background: rgba(29,78,216,.08); color: {{ $brandBlue }}; border-color: rgba(29,78,216,.3);">{{ $ini }}</span>
            <svg class="w-4 h-4 transition">
              <use href="#i-chevron" />
            </svg>
          </summary>
          <div class="absolute right-0 mt-2 w-60 rounded-xl border bg-white shadow-2xl p-2" style="border-color: {{ $brandGray }}" role="menu">
            <div class="px-2 py-1.5 text-[11px] text-zinc-500">Masuk sebagai</div>
            <div class="px-2 pb-2 text-sm text-zinc-800 truncate">{{ $uname }}</div>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-zinc-100" role="menuitem">Profil</a>
            <a href="{{ route('applications.mine') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-zinc-100" role="menuitem">Lamaran Saya</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1" role="none">
              @csrf
              <button class="w-full text-left px-3 py-2 rounded-lg text-sm" style="color: {{ $brandRed }};" role="menuitem">Keluar</button>
            </form>
          </div>
        </details>
        @else
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 hover:opacity-80" style="color: {{ $brandBlack }}"><svg class="w-4 h-4">
            <use href="#i-user" />
          </svg> Masuk</a>
        <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-xl text-white font-semibold hover:opacity-90" style="background: {{ $brandBlue }};">Daftar</a>
        @endauth
      </nav>
    </div>

    {{-- Mobile menu --}}
    <div id="nav-mobile" class="md:hidden hidden border-t bg-white" style="border-color: {{ $brandGray }}">
      <div class="px-4 py-3 space-y-3">
        <form action="{{ route('jobs.index') }}" method="GET" class="flex" role="search" autocomplete="off">
          <div class="relative w-full">
            <input name="q" type="search" placeholder="Cari posisi, divisi, atau kata kunci…" class="w-full rounded-lg bg-white border text-zinc-800 placeholder-zinc-500 focus:ring-2 pl-10 pr-3 py-2 outline-none"
              style="border-color: {{ $brandGray }}; --tw-ring-color: {{ $brandBlue }};" aria-label="Pencarian lowongan" inputmode="search">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500" aria-hidden="true"><svg class="w-5 h-5">
                <use href="#i-search" />
              </svg></span>
          </div>
        </form>
        <a href="{{ route('jobs.index') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Lowongan</a>
        @auth
        <a href="{{ route('applications.mine') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Lamaran</a>
        <div class="border-t" style="border-color: {{ $brandGray }}"></div>
        <a href="{{ route('profile.edit') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Profil</a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button class="w-full text-left px-2 py-2 rounded-lg" style="color: {{ $brandRed }};">Keluar</button>
        </form>
        @else
        <div class="flex items-center gap-3">
          <a href="{{ route('login') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border hover:bg-zinc-100" style="border-color: {{ $brandGray }};">
            <svg class="w-4 h-4">
              <use href="#i-user" />
            </svg> Masuk
          </a>
          <a href="{{ route('register') }}" class="px-3 py-2 rounded-lg text-white font-semibold hover:opacity-90" style="background: {{ $brandRed }};">Daftar</a>
        </div>
        @endauth
      </div>
    </div>

    {{-- Breadcrumb --}}
    <div class="border-t bg-white" style="border-color: {{ $brandGray }}">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center text-xs text-zinc-500" aria-label="Breadcrumb">
        <ol class="inline-flex items-center gap-2">
          <li><a href="{{ route('welcome') }}" class="hover:text-zinc-800">Beranda</a></li>
          <li aria-hidden="true">/</li>
          <li class="text-zinc-800" aria-current="page">Karier</li>
        </ol>
      </nav>
    </div>
  </header>

  <section class="hero-wrap relative overflow-hidden">

    {{-- FULL WIDTH BANNER --}}
    <div class="w-screen relative left-1/2 right-1/2 -ml-[50vw] -mr-[50vw]">
      <img
        src="{{ asset('assets/banner-abn.png') }}"
        alt="Build Your Career With Andalan"
        class="w-full h-auto block object-cover"
        fetchpriority="high"
        decoding="async">
    </div>

  </section>




  {{-- SITES & DIVISIONS --}}
  <section class="bg-white border-b" style="border-color: {{ $brandGray }}" aria-label="Lokasi & Divisi">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
      <div class="grid md:grid-cols-2 gap-6">

        {{-- Sites marquee --}}
        <div>
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold" style="color: {{ $brandBlack }}">Lokasi Site</h2>
          </div>

          @php
          $sitesCol = ($sitesSimple instanceof \Illuminate\Support\Collection) ? $sitesSimple : collect($sitesSimple ?? []);
          $sitesNorm = $sitesCol
          ->filter(fn($s) => !empty($s['name']))
          ->map(function($s){
          $name = (string)($s['name'] ?? '—');
          $dot = preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', (string)($s['dot'] ?? '')) ? $s['dot'] : '#1d4ed8';
          $param = $s['code'] ?? $s['id'] ?? $name;
          return ['name'=>$name,'dot'=>$dot,'param'=>$param];
          })->values();
          $sitesDup = $sitesNorm->concat($sitesNorm);
          @endphp

          @if($sitesNorm->isNotEmpty())
          <div class="marquee" role="region" aria-label="Daftar lokasi site berjalan">
            <div class="marquee__track">
              @foreach($sitesDup as $s)
              <div class="shrink-0">
                <a href="{{ route('jobs.index', ['site' => $s['param']]) }}"
                  class="chip inline-flex items-center gap-2 px-3 py-2 rounded-xl border bg-white"
                  style="border-color: {{ $brandGray }}"
                  aria-label="Lihat lowongan site {{ $s['name'] }}">
                  <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $s['dot'] }}"></span>
                  <span class="text-sm text-zinc-700 whitespace-nowrap">{{ $s['name'] }}</span>
                </a>
              </div>
              @endforeach
            </div>
          </div>
          <noscript>
            <ul class="chips mt-3" role="list">
              @foreach($sitesNorm as $s)
              <li>
                <a href="{{ route('jobs.index', ['site' => $s['param']]) }}" class="chip-pill" aria-label="Lihat lowongan site {{ $s['name'] }}">
                  <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $s['dot'] }}"></span>
                  <span class="text-sm text-zinc-700">{{ $s['name'] }}</span>
                </a>
              </li>
              @endforeach
            </ul>
          </noscript>
          @else
          <p class="text-sm text-zinc-600">Belum ada data site.</p>
          @endif
        </div>
      </div>
    </div>
  </section>

  {{-- TIMELINE & PHOTO --}}
  <section class="bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 pb-2">
      <div class="rounded-2xl border overflow-hidden" style="border-color: {{ $brandGray }}">
        <div class="grid md:grid-cols-2">
          <div class="relative">
            <img src="{{ asset('assets/foto1.png') }}" alt="Tim Andalan"
              class="w-full h-full object-cover min-h-[260px]" width="1200" height="800" loading="lazy" decoding="async">
            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/55 to-transparent p-4">
              <p class="text-white text-sm">Lingkungan kerja kolaboratif dengan budaya keselamatan dan pembelajaran berkelanjutan.</p>
            </div>
          </div>
          <div class="p-6 md:p-8">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold" style="color: {{ $brandBlack }}">Tahapan Rekrutmen</h3>
              <span class="inline-flex items-center gap-1 text-[11px] px-2 py-1 rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200">
                <svg class="w-3.5 h-3.5" aria-hidden="true">
                  <use href="#i-bolt" />
                </svg> Pembaruan Waktu Nyata
              </span>
            </div>
            <div class="tl space-y-4" aria-label="Tahapan rekrutmen">
              <div class="tl-step"><span class="tl-dot blue" aria-hidden="true"></span>
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-sm font-medium" style="color: {{ $brandBlack }}">1) Pengajuan Lamaran</p>
                    <p class="text-xs text-zinc-500">Buat akun, lengkapi profil, dan ajukan lamaran pada posisi yang relevan.</p>
                  </div>
                  <span class="text-[11px] px-2 py-1 rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200">Mulai</span>
                </div>
              </div>
              <div class="tl-step"><span class="tl-dot amber" aria-hidden="true"></span>
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-sm font-medium" style="color: {{ $brandBlack }}">2) Penyaringan CV</p>
                    <p class="text-xs text-zinc-500">Penilaian kesesuaian awal berdasarkan kebutuhan jabatan dan lokasi kerja.</p>
                  </div>
                  <span class="text-[11px] px-2 py-1 rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200">Proses</span>
                </div>
              </div>
              <div class="tl-step"><span class="tl-dot amber" aria-hidden="true"></span>
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-sm font-medium" style="color: {{ $brandBlack }}">3) Wawancara/Asesmen</p>
                    <p class="text-xs text-zinc-500">Wawancara HR/User; untuk jabatan tertentu disertai psikotes atau studi kasus.</p>
                  </div>
                  <span class="text-[11px] px-2 py-1 rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200">Proses</span>
                </div>
              </div>
              <div class="tl-step"><span class="tl-dot green" aria-hidden="true"></span>
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-sm font-medium" style="color: {{ $brandBlack }}">4) Penawaran Kerja</p>
                    <p class="text-xs text-zinc-500">Kandidat terpilih menerima surat penawaran kerja resmi dari Andalan.</p>
                  </div>
                  <span class="text-[11px] px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">Lulus</span>
                </div>
              </div>
              <div class="tl-step"><span class="tl-dot green" aria-hidden="true"></span>
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-sm font-medium" style="color: {{ $brandBlack }}">5) Onboarding</p>
                    <p class="text-xs text-zinc-500">Pengurusan dokumen, orientasi, dan penugasan awal secara terstruktur.</p>
                  </div>
                  <span class="text-[11px] px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">Selesai</span>
                </div>
              </div>
            </div>
            <div class="mt-5 text-xs text-zinc-500 flex items-center justify-between">
              <span>Status diperbarui otomatis melalui portal. Anda fokus persiapan—kami urus prosesnya.</span>
              <span class="inline-flex items-center gap-1 text-zinc-600"><svg class="w-3.5 h-3.5" aria-hidden="true">
                  <use href="#i-shield" />
                </svg> Data pribadi terlindungi.</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- MY APPLICATIONS --}}
  @auth
  <section class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <div class="rounded-2xl bg-white border" style="border-color: {{ $brandGray }}">
      <div class="p-6 border-b flex items-center justify-between" style="border-color: {{ $brandGray }}">
        <h2 class="font-semibold" style="color: {{ $brandBlack }}">Lamaran Saya</h2>
        <a href="{{ route('applications.mine') }}" class="text-sm hover:opacity-80" style="color: {{ $brandBlue }}">Lihat semua</a>
      </div>

      @if($myApps->isEmpty())
      <div class="p-6 text-zinc-600">Belum ada lamaran. Telusuri lowongan di bawah, lalu ajukan lamaran.</div>
      @else
      <div class="p-4 sm:p-6">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          @foreach($myApps as $app)
          @php
          $prog = $myAppsProgress[$app->id] ?? null;
          $statusKey = strtoupper($app->overall_status ?? $app->current_stage ?? ($prog['current_stage'] ?? 'SUBMITTED'));
          $badge = match($statusKey){
          'SUBMITTED','SCREENING','INTERVIEW' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',
          'OFFERED','HIRED' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
          'not_qualified' => 'bg-zinc-100 text-zinc-700 ring-1 ring-inset ring-zinc-200',
          default => 'bg-zinc-50 text-zinc-700 ring-1 ring-inset ring-zinc-200',
          };
          $pct = (int)($prog['progress_percent'] ?? 0);
          $curr = $prog['current_label'] ?? ucfirst(strtolower($statusKey));
          $next = $prog['next_stage_label'] ?? null;
          $hint = $prog['hint'] ?? null;
          $isFinal = (bool)($prog['is_final'] ?? in_array($statusKey,['HIRED','not_qualified'],true));
          @endphp
          <a href="{{ route('jobs.show', $app->job_id) }}" class="group rounded-xl p-4 border hover:shadow-sm transition bg-white card-hover" style="border-color: {{ $brandGray }}">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-xs text-zinc-500">Posisi</p>
                <h3 class="font-semibold truncate" style="color: {{ $brandBlack }}">
                  {{ $app->job->title ?? '—' }}
                  @if($app->job?->site?->code)
                  <span class="ml-1 text-xs text-zinc-400">• {{ $app->job->site->code }}</span>
                  @endif
                </h3>
              </div>
              <span class="text-[11px] px-2 py-1 rounded-full {{ $badge }}">{{ $statusKey }}</span>
            </div>

            <div class="mt-3">
              <div class="flex justify-between text-xs text-zinc-500">
                <span>Saat ini: <span class="font-medium text-zinc-700">{{ $curr }}</span></span>
                <span>{{ $pct }}%</span>
              </div>
              <div class="mt-1 h-2 w-full rounded-full bg-zinc-100 overflow-hidden">
                <div class="h-full rounded-full" style="width: {{ $pct }}%; background: linear-gradient(90deg, {{ $brandBlue }}, {{ $brandRed }} );"></div>
              </div>
              <div class="mt-1.5 text-xs text-zinc-500">
                @if($isFinal)
                <span class="font-medium text-zinc-600">Status akhir.</span>
                @elseif($next)
                Langkah berikut: <span class="font-medium text-zinc-700">{{ $next }}</span>
                @endif
                @if($hint)
                <span class="ml-1 text-zinc-400">• {{ $hint }}</span>
                @endif
              </div>
            </div>

            <div class="mt-3 flex items-center justify-between text-xs text-zinc-500">
              <span>Diajukan: {{ optional($app->created_at)->format('d M Y') }}</span>
              <span class="inline-flex items-center gap-1 text-zinc-700 group-hover:gap-2 transition">Detail <svg class="w-3.5 h-3.5" aria-hidden="true">
                  <use href="#i-arrow-right" />
                </svg></span>
            </div>
          </a>
          @endforeach
        </div>
      </div>
      @endif
    </div>
  </section>
  @endauth

  {{-- LATEST JOBS --}}
  <section class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <div class="rounded-2xl bg-white border" style="border-color: {{ $brandGray }}">
      <div class="p-6 border-b flex items-center justify-between" style="border-color: {{ $brandGray }}">
        <h2 class="font-semibold" style="color: {{ $brandBlack }}">Lowongan Terbaru</h2>
        <a href="{{ route('jobs.index') }}" class="text-sm font-medium hover:opacity-80" style="color: {{ $brandRed }}">Lihat semua</a>
      </div>
      <div class="p-5">
        @if(method_exists($jobs,'count') ? $jobs->count() === 0 : ($jobs->isEmpty() ?? true))
        <p class="text-zinc-600">Belum ada lowongan saat ini.</p>
        @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          @foreach ($jobs as $job)
          @php $excerpt = \Illuminate\Support\Str::limit(strip_tags($job->description ?? ''), 120); @endphp
          <div class="rounded-xl border bg-white card-hover" style="border-color: {{ $brandGray }};">
            <div class="p-5">
              <div class="flex items-start gap-3">
                <div class="p-2.5 rounded-lg text-white" style="background: {{ $brandBlack }}"><svg class="w-5 h-5" aria-hidden="true">
                    <use href="#i-briefcase" />
                  </svg></div>
                <div class="min-w-0">
                  <a href="{{ route('jobs.show', $job) }}" class="block font-semibold hover:opacity-80" style="color: {{ $brandBlack }}">{{ $job->title }}</a>
                  <p class="text-[11px] text-zinc-500 mt-0.5">{{ $job->site?->code ?? $job->site?->name ?? '—' }} • Diposting {{ optional($job->created_at)->diffForHumans() }}</p>
                </div>
              </div>
              @if(!empty($excerpt))
              <p class="text-sm text-zinc-600 mt-3 line-clamp-2">{{ $excerpt }}</p>
              @endif
              <div class="mt-4 flex items-center justify-between">
                <a href="{{ route('jobs.show', $job) }}" class="inline-flex items-center gap-1.5 text-sm font-medium hover:opacity-80" style="color: {{ $brandBlue }}">Detail <svg class="w-4 h-4" aria-hidden="true">
                    <use href="#i-arrow-right" />
                  </svg></a>
                @auth
                <form action="{{ route('applications.store', $job) }}" method="POST" onsubmit="return confirm('Lamar posisi ini?')">
                  @csrf
                  <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-lg text-white hover:opacity-90" style="background: {{ $brandRed }};">
                    <svg class="w-4 h-4" aria-hidden="true">
                      <use href="#i-apply" />
                    </svg> Lamar
                  </button>
                </form>
                @else
                <a href="{{ route('login') }}?intended={{ urlencode(route('jobs.show',$job)) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-lg text-white hover:opacity-90" style="background: {{ $brandBlack }};">
                  <svg class="w-4 h-4" aria-hidden="true">
                    <use href="#i-user" />
                  </svg> Masuk untuk Melamar
                </a>
                @endauth
              </div>
            </div>
          </div>
          @endforeach
        </div>

        @if(method_exists($jobs,'withQueryString'))
        <div class="mt-6">{{ $jobs->withQueryString()->links() }}</div>
        @endif
        @endif
      </div>
    </div>
  </section>

  {{-- FOOTER --}}
  <footer style="background: {{ $brandBlack }};">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-14 text-white">

      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10">

        {{-- BRAND --}}
        <div>
          <img src="{{ asset('assets/AndalanLogo.png') }}"
            alt="Logo Human Careers"
            class="h-32 md:h-40 w-auto object-contain">

          <p class="text-sm text-zinc-300 leading-relaxed">
            Portal karier resmi PT Andalan Artha Primanusa.
            Transparan, profesional, dan terpercaya untuk seluruh pencari kerja.
          </p>

        </div>

        {{-- MENU --}}
        <div>
          <h4 class="font-semibold mb-3">Navigasi</h4>
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
        </div>

        {{-- COMPANY --}}
        <div>
          <h4 class="font-semibold mb-3">Perusahaan</h4>
          <ul class="space-y-2 text-sm text-zinc-300">
            <li><a href="#" class="hover:text-white">Tentang Kami</a></li>
            <li><a href="#" class="hover:text-white">Kebijakan Privasi</a></li>
            <li><a href="#" class="hover:text-white">Syarat & Ketentuan</a></li>
            <li><a href="#" class="hover:text-white">Etika Rekrutmen</a></li>
          </ul>
        </div>

        {{-- CONTACT --}}
        <div>
          <h4 class="font-semibold mb-3">Kontak</h4>

          <p class="text-sm text-zinc-300 mb-2">
            Email: <a href="mailto:hr@andalan.co.id" class="underline hover:text-white">hr@andalan.co.id</a>
          </p>

          <p class="text-sm text-zinc-300 leading-relaxed">
            PT Andalan Artha Primanusa - Tanah Andalan<br>
            Jl. Plaju No.11, Kebon Melati,<br>
            Tanah Abang, Jakarta Pusat 10230,<br>
            DKI Jakarta, Indonesia
          </p>

          {{-- SOCIAL --}}
          <div class="mt-4 flex gap-3">

            <a href="#" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
              <svg class="w-5 h-5">
                <use href="#i-instagram" />
              </svg>
            </a>

            <a href="#" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
              <svg class="w-5 h-5">
                <use href="#i-linkedin" />
              </svg>
            </a>

            <a href="#" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition">
              <svg class="w-5 h-5">
                <use href="#i-youtube" />
              </svg>
            </a>

          </div>
        </div>

      </div>

      {{-- COPYRIGHT --}}
      <div class="mt-12 pt-6 text-sm flex flex-col md:flex-row items-center justify-between gap-4 border-t border-white/20">

        <div class="text-zinc-400 text-center md:text-left">
          © 2026 PT Andalan Artha Primanusa Tbk. Seluruh Hak Dilindungi
        </div>

        <div class="text-zinc-500 text-xs">
          Powered by Human Careers System Andalan
        </div>

      </div>

    </div>
  </footer>


  {{-- JSON-LD (inline small; server-side renders once) --}}
  @php
  $orgLd = [
  '@context'=>'https://schema.org','@type'=>'Organization','@id'=>url('/').'#org',
  'name'=>'PT Andalan','url'=>url('/'),'logo'=>asset('storage/media/og-careers.jpg'),
  'sameAs'=>['https://www.linkedin.com/','https://www.instagram.com/'],
  ];
  $siteLd = [
  '@context'=>'https://schema.org','@type'=>'WebSite','@id'=>url('/').'#website',
  'name'=>'Human.Careers','url'=>url('/'),
  'potentialAction'=>['@type'=>'SearchAction','target'=>route('jobs.index').'?q={search_term_string}','query-input'=>'required name=search_term_string'],
  ];
  $breadcrumbLd = [
  '@context'=>'https://schema.org','@type'=>'BreadcrumbList','@id'=>url('/').'#breadcrumb',
  'itemListElement'=>[
  ['@type'=>'ListItem','position'=>1,'name'=>'Beranda','item'=>route('welcome')],
  ['@type'=>'ListItem','position'=>2,'name'=>'Karier','item'=>url()->current()],
  ],
  ];
  @endphp
  <script type="application/ld+json">
    {
      !!json_encode($orgLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!
    }
  </script>
  <script type="application/ld+json">
    {
      !!json_encode($siteLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!
    }
  </script>
  <script type="application/ld+json">
    {
      !!json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!
    }
  </script>

  {{-- Minimal inline JS (no frameworks; tiny & fast) --}}
  <script>
    (function() {
      const b = document.getElementById('btn-nav'),
        m = document.getElementById('nav-mobile');
      if (b && m) {
        b.addEventListener('click', () => {
          m.classList.toggle('hidden');
          const exp = b.getAttribute('aria-expanded') === 'true';
          b.setAttribute('aria-expanded', String(!exp));
        });
      }
      const toTop = document.getElementById('toTop');
      const onScroll = () => {
        if (!toTop) return;
        window.scrollY > 320 ? toTop.classList.add('show') : toTop.classList.remove('show');
      };
      window.addEventListener('scroll', onScroll, {
        passive: true
      });
      toTop?.addEventListener('click', () => window.scrollTo({
        top: 0,
        behavior: 'smooth'
      }));
      window.showToast = function() {
        const t = document.getElementById('toast');
        t?.classList.add('show');
        setTimeout(() => t?.classList.remove('show'), 3500);
      };
    })();
  </script>

  {{-- Back-to-top --}}
  <button id="toTop" class="to-top rounded-full p-3 border bg-white shadow hover:shadow-md ring-focus" style="border-color: {{ $brandGray }}" aria-label="Kembali ke atas">
    <svg class="w-5 h-5 text-zinc-700" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M12 5l-7 7m7-7 7 7M12 5v14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </button>
</body>

</html>