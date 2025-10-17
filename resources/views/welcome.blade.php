{{-- resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Human.Careers — Portal Karier Resmi Andalan</title>

  {{-- Vite Assets --}}
  @vite(['resources/css/app.css','resources/js/app.js'])

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  {{-- SEO Meta --}}
  <meta name="description" content="Portal karier resmi Andalan. Lowongan terverifikasi, proses seleksi transparan, dan pembaruan status waktu nyata. Satu akun untuk seluruh lokasi kerja Andalan.">
  <meta name="keywords" content="karier, lowongan, pekerjaan, Andalan, rekrutmen, mining, HR, job portal">
  <meta name="author" content="PT Andalan">
  <meta name="theme-color" content="#0a0a0a">

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

  {{-- Favicon --}}
  <link rel="icon" href="{{ asset('favicon.ico') }}">
  <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

  <style>
    html,
    body {
      font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial
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

    :root {
      --blue: #1d4ed8;
      --red: #dc2626;
      --black: #0a0a0a;
      --gray: #e5e7eb
    }

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

    .card-hover {
      transition: transform .25s ease, box-shadow .25s ease
    }

    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, .08)
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

    .ring-focus:focus {
      outline: none;
      box-shadow: 0 0 0 3px rgba(29, 78, 216, .35)
    }
  </style>
</head>

<body class="bg-white text-zinc-900 antialiased">
  @php
  /** Server-side computed / fallback */
  $jobs = $jobs ?? collect();
  $myApps = $myApps ?? collect();
  $myAppsSummary = $myAppsSummary ?? ['total' => ($myApps->count() ?? 0), 'byStatus' => collect()];
  $myAppsProgress = $myAppsProgress ?? collect();
  $sitesSimple = $sitesSimple ?? collect(); // [{name, dot}]

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

  {{-- SVG Sprite --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden">
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
        <a href="{{ route('welcome') }}" class="font-extrabold tracking-tight" style="color: {{ $brandBlack }}" aria-label="Beranda Human.Careers">
          HUMAN<span style="color: {{ $brandBlue }}">.</span><span style="color: {{ $brandRed }}">Careers</span>
        </a>
      </div>

      {{-- Search (desktop) --}}
      <form action="{{ route('jobs.index') }}" method="GET" class="hidden md:flex items-center flex-1 max-w-lg mx-6" role="search">
        <div class="relative w-full">
          <input name="q" type="search" placeholder="Cari posisi, divisi, atau kata kunci…" class="w-full rounded-xl bg-white border text-zinc-800 placeholder-zinc-500 focus:ring-2 px-10 py-2 outline-none" style="border-color: {{ $brandGray }}; --tw-ring-color: {{ $brandBlue }};" aria-label="Pencarian lowongan">
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
        <form action="{{ route('jobs.index') }}" method="GET" class="flex" role="search">
          <div class="relative w-full">
            <input name="q" type="search" placeholder="Cari posisi, divisi, atau kata kunci…" class="w-full rounded-lg bg-white border text-zinc-800 placeholder-zinc-500 focus:ring-2 pl-10 pr-3 py-2 outline-none" style="border-color: {{ $brandGray }}; --tw-ring-color: {{ $brandBlue }};" aria-label="Pencarian lowongan">
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

  {{-- HERO --}}
  <section class="hero-wrap" aria-labelledby="hero-title">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 md:py-16">
      <div class="grid lg:grid-cols-12 gap-8 items-center">
        {{-- Left --}}
        <div class="lg:col-span-6" id="maincontent">
          <div class="inline-flex items-center gap-2 text-[11px] text-blue-200 bg-white/5 ring-1 ring-white/10 px-2.5 py-1 rounded-full">
            <svg class="w-3.5 h-3.5 text-blue-300" aria-hidden="true">
              <use href="#i-bolt" />
            </svg>
            Proses seleksi transparan, notifikasi waktu nyata
          </div>
          <h1 id="hero-title" class="mt-3 text-3xl md:text-4xl font-extrabold leading-tight text-white">
            Bangun Karier Bersama Andalan <span aria-hidden="true">🚀</span>
          </h1>
          <p class="mt-3 text-zinc-300 max-w-xl">Portal karier resmi Andalan. Lowongan terverifikasi, alur seleksi jelas, dan pembaruan status berlangsung waktu nyata. Satu akun untuk seluruh lokasi kerja Andalan.</p>
          <div class="mt-6 flex flex-wrap items-center gap-3">
            <a href="{{ route('jobs.index') }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 font-semibold text-white hover:opacity-90 ring-focus" style="background: {{ $brandBlue }};">
              <svg class="w-4 h-4" aria-hidden="true">
                <use href="#i-briefcase" />
              </svg> Telusuri Lowongan
            </a>
            @guest
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl px-4 py-2 font-semibold text-black hover:opacity-90 ring-focus" style="background:#fff;">
              <svg class="w-4 h-4" aria-hidden="true">
                <use href="#i-rocket" />
              </svg> Buat Akun
            </a>
            @endguest
          </div>

          <div class="mt-6 grid grid-cols-3 divide-x divide-white/10 rounded-xl border border-white/10 bg-white/5 text-white/90" role="group" aria-label="Statistik ringkas">
            <div class="p-4 text-center">
              <div class="text-2xl font-extrabold text-white">{{ method_exists($jobs,'total') ? $jobs->total() : ($jobs->count() ?? 0) }}</div>
              <div class="text-[11px] text-zinc-300">Lowongan Aktif</div>
            </div>
            <div class="p-4 text-center">
              <div class="text-2xl font-extrabold text-white">
                @auth {{ $myAppsSummary['total'] ?? $myApps->count() }} @else 0 @endauth
              </div>
              <div class="text-[11px] text-zinc-300">Lamaran Saya</div>
            </div>
            <div class="p-4 text-center">
              <div class="text-2xl font-extrabold text-white">{{ now()->format('M') }}</div>
              <div class="text-[11px] text-zinc-300">Periode</div>
            </div>
          </div>
        </div>

        {{-- Right --}}
        <div class="lg:col-span-6">
          <div class="relative">
            <img src="{{ asset('assets/hr1.jpg') }}" alt="Kegiatan tim Andalan" class="w-full aspect-[4/3] object-cover rounded-2xl ring-1 ring-white/10 shadow-[0_20px_60px_rgba(0,0,0,.45)]">
            <div class="absolute bottom-3 right-3 backdrop-blur bg-black/40 text-white text-xs px-3 py-1.5 rounded-full ring-1 ring-white/10 flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-300" aria-hidden="true">
                <use href="#i-check" />
              </svg>
              Program Onboarding & Pelatihan Internal
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- LOKASI SITE --}}
  <section class="bg-white border-b" style="border-color: {{ $brandGray }}" aria-label="Lokasi kerja Andalan">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
      <div class="flex items-center justify-between mb-3">
        <h2 class="font-semibold" style="color: {{ $brandBlack }}">Lokasi Site</h2>
        <span class="text-xs text-zinc-500">Ikon & nama saja</span>
      </div>

      @if($sitesSimple instanceof \Illuminate\Support\Collection && $sitesSimple->isNotEmpty())
      <ul class="flex gap-3 overflow-x-auto pb-1" role="list" style="scrollbar-width:thin;">
        @foreach($sitesSimple as $s)
        <li class="shrink-0">
          <div class="flex items-center gap-2 px-3 py-2 rounded-xl border bg-white card-hover" style="border-color: {{ $brandGray }}">
            <span class="inline-block w-2.5 h-2.5 rounded-full" style="background: {{ $s['dot'] ?? '#1d4ed8' }}"></span>
            <span class="text-sm text-zinc-700 whitespace-nowrap">{{ $s['name'] ?? '—' }}</span>
          </div>
        </li>
        @endforeach
      </ul>
      @else
      <p class="text-sm text-zinc-600">Belum ada data site.</p>
      @endif
    </div>
  </section>

  {{-- RINGKASAN DIVISI (Open) + Filter Cepat --}}
  <section class="bg-white border-b" style="border-color: {{ $brandGray }}">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
      <div class="rounded-2xl bg-white border p-6 shadow-sm" style="border-color: {{ $brandGray }}">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-3">
            <div class="p-3 rounded-xl text-white" style="background: {{ $brandRed }}"><svg class="w-6 h-6" aria-hidden="true">
                <use href="#i-rocket" />
              </svg></div>
            <div>
              <p class="text-xs text-zinc-500">Lowongan terbuka</p>
              <h2 class="font-semibold" style="color: {{ $brandBlack }}">Per Divisi (Open)</h2>
            </div>
          </div>
          <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('jobs.index') }}" class="px-2.5 py-1 rounded-lg border hover:bg-zinc-50" style="border-color: {{ $brandGray }}">Semua</a>
            @foreach($byDivision->take(6) as $div => $total)
            <a href="{{ route('jobs.index', ['division'=>$div]) }}" class="px-2.5 py-1 rounded-lg border hover:bg-zinc-50" style="border-color: {{ $brandGray }}">{{ $div }}</a>
            @endforeach
          </div>
        </div>

        <div class="mt-4">
          @if($byDivision->isNotEmpty())
          <ul class="divide-y divide-zinc-100">
            @foreach($byDivision as $div => $total)
            <li class="py-2.5 flex items-center justify-between">
              <span class="text-sm text-zinc-700">{{ $div }}</span>
              <span class="text-[11px] px-2.5 py-1 rounded-full bg-zinc-100 text-zinc-800">{{ (int)$total }}</span>
            </li>
            @endforeach
          </ul>
          @else
          <div class="text-sm text-zinc-600">Belum ada data divisi.</div>
          @endif
        </div>
      </div>
    </div>
  </section>

  {{-- TIMELINE & Foto --}}
  <section class="bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 pb-2">
      <div class="rounded-2xl border overflow-hidden" style="border-color: {{ $brandGray }}">
        <div class="grid md:grid-cols-2">
          <div class="relative">
            <img src="{{ asset('assets/foto1.png') }}" alt="Tim Andalan" class="w-full h-full object-cover min-h-[260px]">
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
                    <p class="text-sm font-medium" style="color: {{ $brandBlack }}">2) Penyaringan Kurikulum Vitae</p>
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

  {{-- STAT KARTU --}}
  <section class="bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 grid sm:grid-cols-3 gap-4">
      <div class="rounded-2xl border p-5 card-hover" style="border-color: {{ $brandGray }}">
        <div class="flex items-center gap-3">
          <span class="p-2.5 rounded-lg text-white" style="background: {{ $brandBlue }}"><svg class="w-5 h-5" aria-hidden="true">
              <use href="#i-bolt" />
            </svg></span>
          <div>
            <p class="text-sm font-semibold" style="color: {{ $brandBlack }}">Transparansi Progres</p>
            <p class="text-xs text-zinc-500">Pantau status lamaran dari layar Anda setiap saat.</p>
          </div>
        </div>
      </div>
      <div class="rounded-2xl border p-5 card-hover" style="border-color: {{ $brandGray }}">
        <div class="flex items-center gap-3">
          <span class="p-2.5 rounded-lg text-white" style="background: {{ $brandRed }}"><svg class="w-5 h-5" aria-hidden="true">
              <use href="#i-rocket" />
            </svg></span>
          <div>
            <p class="text-sm font-semibold" style="color: {{ $brandBlack }}">Proses Efisien</p>
            <p class="text-xs text-zinc-500">Alur seleksi ringkas dan komunikatif.</p>
          </div>
        </div>
      </div>
      <div class="rounded-2xl border p-5 card-hover" style="border-color: {{ $brandGray }}">
        <div class="flex items-center gap-3">
          <span class="p-2.5 rounded-lg text-white bg-emerald-600"><svg class="w-5 h-5" aria-hidden="true">
              <use href="#i-shield" />
            </svg></span>
          <div>
            <p class="text-sm font-semibold" style="color: {{ $brandBlack }}">Keamanan Data</p>
            <p class="text-xs text-zinc-500">Informasi pelamar kami lindungi secara menyeluruh.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- LAMARAN SAYA (auth) --}}
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

  {{-- LOWONGAN TERBARU --}}
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

  {{-- TESTIMONI --}}
  <section class="bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
      <div class="rounded-2xl border p-6 md:p-8" style="border-color: {{ $brandGray }}">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <h2 class="font-semibold" style="color: {{ $brandBlack }}">Cerita Singkat Karyawan</h2>
          <span class="text-xs text-zinc-500">Testimoni internal (contoh)</span>
        </div>
        <div class="mt-5 grid md:grid-cols-3 gap-4">
          <figure class="rounded-xl border p-5 bg-white card-hover" style="border-color: {{ $brandGray }}">
            <blockquote class="text-sm text-zinc-700">“Proses seleksi jelas dan komunikatif. Setelah bergabung, program onboarding memudahkan saya memahami budaya kerja.”</blockquote>
            <figcaption class="mt-3 text-xs text-zinc-500">— Anisa, HR Generalist</figcaption>
          </figure>
          <figure class="rounded-xl border p-5 bg-white card-hover" style="border-color: {{ $brandGray }}">
            <blockquote class="text-sm text-zinc-700">“Tim yang suportif dan kesempatan belajar yang luas membuat saya berkembang pesat.”</blockquote>
            <figcaption class="mt-3 text-xs text-zinc-500">— Dimas, IT Support</figcaption>
          </figure>
          <figure class="rounded-xl border p-5 bg-white card-hover" style="border-color: {{ $brandGray }}">
            <blockquote class="text-sm text-zinc-700">“Standar keselamatan yang tinggi dan perencanaan kerja yang rapi.”</blockquote>
            <figcaption class="mt-3 text-xs text-zinc-500">— Sari, Supervisor Operasi</figcaption>
          </figure>
        </div>
      </div>
    </div>
  </section>

  {{-- FAQ --}}
  <section class="bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
      <div class="rounded-2xl border p-6 md:p-8" style="border-color: {{ $brandGray }}">
        <div class="flex items-center justify-between gap-3 flex-wrap">
          <h2 class="font-semibold" style="color: {{ $brandBlack }}">Pertanyaan yang Sering Diajukan</h2>
          <span class="text-xs text-zinc-500">Kebijakan & proses rekrutmen</span>
        </div>
        <div class="mt-5 grid md:grid-cols-2 gap-4">
          <details class="rounded-xl border p-4 bg-white" style="border-color: {{ $brandGray }}">
            <summary class="flex items-center justify-between cursor-pointer select-none"><span class="text-sm font-medium" style="color: {{ $brandBlack }}">Apakah semua lowongan di sini resmi?</span><svg class="w-4 h-4 text-zinc-500 transition" aria-hidden="true">
                <use href="#i-chevron" />
              </svg></summary>
            <p class="mt-2 text-sm text-zinc-600">Ya. Seluruh lowongan pada portal ini merupakan lowongan resmi Andalan. Kami tidak memungut biaya dalam proses rekrutmen.</p>
          </details>
          <details class="rounded-xl border p-4 bg-white" style="border-color: {{ $brandGray }}">
            <summary class="flex items-center justify-between cursor-pointer select-none"><span class="text-sm font-medium" style="color: {{ $brandBlack }}">Bagaimana cara memantau progres lamaran?</span><svg class="w-4 h-4 text-zinc-500 transition" aria-hidden="true">
                <use href="#i-chevron" />
              </svg></summary>
            <p class="mt-2 text-sm text-zinc-600">Masuk ke akun Anda lalu buka menu “Lamaran Saya”. Status akan diperbarui otomatis dan notifikasi akan dikirim saat ada perubahan penting.</p>
          </details>
          <details class="rounded-xl border p-4 bg-white" style="border-color: {{ $brandGray }}">
            <summary class="flex items-center justify-between cursor-pointer select-none"><span class="text-sm font-medium" style="color: {{ $brandBlack }}">Apakah data saya aman?</span><svg class="w-4 h-4 text-zinc-500 transition" aria-hidden="true">
                <use href="#i-chevron" />
              </svg></summary>
            <p class="mt-2 text-sm text-zinc-600">Kami menerapkan perlindungan data berlapis. Informasi Anda digunakan hanya untuk kepentingan rekrutmen sesuai kebijakan privasi.</p>
          </details>
          <details class="rounded-xl border p-4 bg-white" style="border-color: {{ $brandGray }}">
            <summary class="flex items-center justify-between cursor-pointer select-none"><span class="text-sm font-medium" style="color: {{ $brandBlack }}">Apakah tersedia program magang atau fresh graduate?</span><svg class="w-4 h-4 text-zinc-500 transition" aria-hidden="true">
                <use href="#i-chevron" />
              </svg></summary>
            <p class="mt-2 text-sm text-zinc-600">Tersedia sesuai kebutuhan periode berjalan. Silakan cek lowongan dan filter kategori terkait.</p>
          </details>
        </div>
      </div>
    </div>
  </section>

  {{-- CTA Sekunder --}}
  <section class="bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 grid md:grid-cols-2 gap-4">
      <div class="rounded-2xl border p-6 bg-white card-hover" style="border-color: {{ $brandGray }}">
        <div class="flex items-center gap-3">
          <span class="p-2.5 rounded-lg text-white" style="background: {{ $brandBlue }}"><svg class="w-5 h-5" aria-hidden="true">
              <use href="#i-rocket" />
            </svg></span>
          <div>
            <h3 class="font-semibold" style="color: {{ $brandBlack }}">Program Campus Hiring</h3>
            <p class="text-sm text-zinc-600">Peluang bagi lulusan baru untuk memulai karier dengan pendampingan intensif.</p>
          </div>
        </div>
        <div class="mt-4"><a href="{{ route('jobs.index', ['type' => 'internship']) }}" class="inline-flex items-center gap-1.5 text-sm font-medium hover:opacity-80" style="color: {{ $brandBlue }}">Lihat peluang <svg class="w-4 h-4" aria-hidden="true">
              <use href="#i-arrow-right" />
            </svg></a></div>
      </div>
      <div class="rounded-2xl border p-6 bg-white card-hover" style="border-color: {{ $brandGray }}">
        <div class="flex items-center gap-3">
          <span class="p-2.5 rounded-lg text-white" style="background: {{ $brandRed }}"><svg class="w-5 h-5" aria-hidden="true">
              <use href="#i-briefcase" />
            </svg></span>
          <div>
            <h3 class="font-semibold" style="color: {{ $brandBlack }}">Experienced Hire</h3>
            <p class="text-sm text-zinc-600">Percepat proses Anda untuk posisi yang memerlukan pengalaman profesional.</p>
          </div>
        </div>
        <div class="mt-4"><a href="{{ route('jobs.index', ['type' => 'experienced']) }}" class="inline-flex items-center gap-1.5 text-sm font-medium hover:opacity-80" style="color: {{ $brandRed }}">Jelajahi posisi <svg class="w-4 h-4" aria-hidden="true">
              <use href="#i-arrow-right" />
            </svg></a></div>
      </div>
    </div>
  </section>

  {{-- CTA Penutup --}}
  <section class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <div class="rounded-2xl border p-6 bg-white" style="border-color: {{ $brandGray }}">
      <div class="flex flex-col md:flex-row items-center justify-between gap-5">
        <div class="space-y-1">
          <div class="inline-flex items-center gap-2 text-xs text-blue-700 bg-blue-50 ring-1 ring-blue-200 px-2.5 py-1 rounded-full"><svg class="w-3.5 h-3.5" aria-hidden="true">
              <use href="#i-bolt" />
            </svg> Jalur Penerimaan Cepat</div>
          <h3 class="text-xl font-extrabold" style="color: {{ $brandBlack }}">Siap Bergabung dengan Andalan?</h3>
          <p class="text-sm text-zinc-600">Lengkapi profil, aktifkan pemberitahuan, dan jadilah pelamar pertama yang ditinjau.</p>
          <ul class="mt-2 text-sm text-zinc-700 space-y-1">
            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" aria-hidden="true">
                <use href="#i-check" />
              </svg> Tanda terima lamaran otomatis</li>
            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" aria-hidden="true">
                <use href="#i-check" />
              </svg> Pelacakan progres waktu nyata</li>
            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-600" aria-hidden="true">
                <use href="#i-bell" />
              </svg> Notifikasi jadwal wawancara</li>
          </ul>
        </div>
        <div class="flex items-center gap-3">
          @auth
          <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl text-white font-semibold px-4 py-2 hover:opacity-90" style="background: {{ $brandBlack }};"><svg class="w-4 h-4" aria-hidden="true">
              <use href="#i-user" />
            </svg> Perbarui Profil</a>
          <a href="{{ route('applications.mine') }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 hover:bg-zinc-50" style="border-color: {{ $brandGray }}; color: {{ $brandBlack }};"><svg class="w-4 h-4" aria-hidden="true">
              <use href="#i-bell" />
            </svg> Lihat Progres</a>
          @else
          <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl text-white font-semibold px-4 py-2 hover:opacity-90" style="background: {{ $brandBlue }};"><svg class="w-4 h-4" aria-hidden="true">
              <use href="#i-rocket" />
            </svg> Daftar Sekarang</a>
          <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 hover:bg-zinc-50" style="border-color: {{ $brandGray }}; color: {{ $brandBlack }};"><svg class="w-4 h-4" aria-hidden="true">
              <use href="#i-user" />
            </svg> Masuk</a>
          @endauth
        </div>
      </div>
    </div>
  </section>

  {{-- FOOTER --}}
  <footer style="background: {{ $brandBlack }};">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 text-white">
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div>
          <div class="font-extrabold mb-3">HUMAN<span style="color: {{ $brandBlue }}">.</span><span style="color: {{ $brandRed }}">Careers</span></div>
          <p class="text-sm text-zinc-300">Portal karier resmi Andalan. Transparan, objektif, dan ramah bagi pelamar.</p>
          <div class="mt-4 flex items-center gap-2">
            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-1 rounded-full" style="background: rgba(255,255,255,.08);"><svg class="w-3.5 h-3.5" aria-hidden="true">
                <use href="#i-shield" />
              </svg> Pemberi kerja tepercaya</span>
            <span class="inline-flex items-center gap-1 text-[11px] px-2 py-1 rounded-full" style="background: rgba(255,255,255,.08);"><svg class="w-3.5 h-3.5" aria-hidden="true">
                <use href="#i-bolt" />
              </svg> Status real-time</span>
          </div>
        </div>
        <div>
          <h4 class="font-semibold mb-3">Tautan Utama</h4>
          <ul class="space-y-2 text-sm text-zinc-300">
            <li><a href="{{ route('jobs.index') }}" class="hover:underline">Lowongan</a></li>
            @auth
            <li><a href="{{ route('applications.mine') }}" class="hover:underline">Lamaran Saya</a></li>
            <li><a href="{{ route('profile.edit') }}" class="hover:underline">Profil</a></li>
            @else
            <li><a href="{{ route('login') }}" class="hover:underline">Masuk</a></li>
            <li><a href="{{ route('register') }}" class="hover:underline">Daftar</a></li>
            @endauth
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-3">Informasi Perusahaan</h4>
          <ul class="space-y-2 text-sm text-zinc-300">
            <li><a href="#" class="hover:underline">Tentang Kami</a></li>
            <li><a href="#" class="hover:underline">Kebijakan Privasi</a></li>
            <li><a href="#" class="hover:underline">Syarat dan Ketentuan</a></li>
            <li><a href="#" class="hover:underline">Etika Rekrutmen</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-semibold mb-3">Kontak</h4>
          <p class="text-sm text-zinc-300">Email: <a href="mailto:hr@andalan.co.id" class="underline">hr@andalan.co.id</a></p>
          <p class="text-sm text-zinc-300">Alamat: Jl. Contoh Raya No. 1, Jakarta</p>
          <div class="mt-3 flex items-center gap-2">
            <a href="#" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border hover:bg-white/10" style="border-color: rgba(255,255,255,.2)"><svg class="w-4 h-4" aria-hidden="true">
                <use href="#i-linkedin" />
              </svg> LinkedIn</a>
            <a href="#" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border hover:bg-white/10" style="border-color: rgba(255,255,255,.2)"><svg class="w-4 h-4" aria-hidden="true">
                <use href="#i-instagram" />
              </svg> Instagram</a>
          </div>
        </div>
      </div>
      <div class="mt-10 pt-6 text-sm flex flex-col md:flex-row items-center justify-between gap-3" style="border-top: 1px solid rgba(255,255,255,.15);">
        <div class="text-zinc-400">© {{ date('Y') }} PT Andalan. Seluruh hak cipta dilindungi.</div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center gap-1 text-[11px] px-2 py-1 rounded-full" style="background: rgba(255,255,255,.08);"><svg class="w-3.5 h-3.5" aria-hidden="true">
              <use href="#i-external" />
            </svg> Karier Anda, Prioritas Kami.</span>
          <span class="w-4 h-4 rounded-full" style="background: {{ $brandBlue }}"></span>
          <span class="w-4 h-4 rounded-full" style="background: {{ $brandRed }}"></span>
          <span class="w-4 h-4 rounded-full bg-white"></span>
        </div>
      </div>
    </div>
  </footer>

  {{-- Toast --}}
  <div id="toast" class="toast rounded-xl border bg-white p-3 shadow-xl" style="border-color: {{ $brandGray }}" role="status" aria-live="polite">
    <div class="flex items-start gap-2">
      <div class="mt-0.5 p-1 rounded bg-emerald-100 text-emerald-700"><svg class="w-4 h-4" aria-hidden="true">
          <use href="#i-check" />
        </svg></div>
      <div class="text-sm">
        <p class="font-medium text-zinc-800">Berlangganan berhasil.</p>
        <p class="text-[12px] text-zinc-500">Kami akan mengirimkan kabar terbaru setiap minggu.</p>
      </div>
    </div>
  </div>

  {{-- JSON-LD (single source of truth) --}}
  @php
  $orgLd = [
  '@context' => 'https://schema.org',
  '@type' => 'Organization',
  '@id' => url('/').'#org',
  'name' => 'PT Andalan',
  'url' => url('/'),
  'logo' => asset('storage/media/og-careers.jpg'),
  'sameAs' => [
  'https://www.linkedin.com/',
  'https://www.instagram.com/',
  ],
  ];

  $siteLd = [
  '@context' => 'https://schema.org',
  '@type' => 'WebSite',
  '@id' => url('/').'#website',
  'name' => 'Human.Careers',
  'url' => url('/'),
  'potentialAction' => [
  '@type' => 'SearchAction',
  'target' => route('jobs.index').'?q={search_term_string}',
  'query-input' => 'required name=search_term_string',
  ],
  ];

  $breadcrumbLd = [
  '@context' => 'https://schema.org',
  '@type' => 'BreadcrumbList',
  '@id' => url('/').'#breadcrumb',
  'itemListElement' => [
  [
  '@type' => 'ListItem',
  'position' => 1,
  'name' => 'Beranda',
  'item' => route('welcome'),
  ],
  [
  '@type' => 'ListItem',
  'position' => 2,
  'name' => 'Karier',
  'item' => url()->current(),
  ],
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

  {{-- JS --}}
  <script>
    (function() {
      const b = document.getElementById('btn-nav');
      const m = document.getElementById('nav-mobile');
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

  {{-- Floating Back-to-top --}}
  <button id="toTop" class="to-top rounded-full p-3 border bg-white shadow hover:shadow-md ring-focus" style="border-color: {{ $brandGray }}" aria-label="Kembali ke atas">
    <svg class="w-5 h-5 text-zinc-700" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M12 5l-7 7m7-7 7 7M12 5v14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
  </button>
</body>

</html>