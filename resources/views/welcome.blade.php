{{-- resources/views/welcome.blade.php (Corporate • B/W/B/R • Video + Sticky Charts + Right Timeline • FIXED) --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Andalan Careers — Portal Karier Resmi</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Tambah di <head> sekali saja -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
  /* ===== Global ===== */
  html, body {
    font-family: 'Poppins', ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial;
  }

  /* ===== Dropdown ===== */
  .dropdown[open] > summary svg { transform: rotate(180deg) }
  details > summary { list-style: none; }
  details > summary::-webkit-details-marker { display: none; }

  /* ===== Right-aligned timeline ===== */
  .tl { position: relative; }
  .tl::after{
    content:""; position:absolute; right:12px; top:0; bottom:0; width:2px;
    background: linear-gradient(180deg, rgba(10,10,10,.2), rgba(10,10,10,.05));
  }
  .tl-step { position: relative; padding-right:48px; }
  .tl-dot{
    --c:#1d4ed8; position:absolute; right:3px; top:.4rem; width:18px; height:18px;
    border-radius:50%;
    box-shadow: inset 0 0 0 3px var(--c), 0 0 0 4px #fff;
    background: transparent;
  }
  .tl-dot.dot-green{ --c:#16a34a; }  /* completed */
  .tl-dot.dot-blue{  --c:#1d4ed8; }  /* current   */
  .tl-dot.dot-amber{ --c:#f59e0b; }  /* upcoming  */

  @media (min-width: 1024px){
    .sticky-right{ position: sticky; top: 6rem; }
  }

  /* ===== Heading accent biru untuk hero ===== */
  .heading-accent {
    position: relative;
    display: inline-block;
    line-height: 1.15;
  }
  .heading-accent::before{
    content:"";
    position:absolute;
    z-index:-1;
    inset: 14% -8%;                    /* tebal highlight */
    background: linear-gradient(90deg, #e0f2fe, #bfdbfe, #93c5fd); /* biru lembut */
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(29,78,216,.12);
  }
  @media (max-width: 640px){
    .heading-accent::before{ inset: 18% -6%; border-radius: 12px; }
  }
</style>
</head>
<body class="bg-white text-zinc-900 antialiased">
@php
  $jobs           = $jobs           ?? collect();
  $myApps         = $myApps         ?? collect();
  $myAppsSummary  = $myAppsSummary  ?? ['total'=>($myApps->count() ?? 0),'byStatus'=>collect(['SUBMITTED'=>0,'SCREENING'=>0,'INTERVIEW'=>0,'OFFERED'=>0,'HIRED'=>0,'REJECTED'=>0])];
  $myAppsProgress = $myAppsProgress ?? collect();

  $byDivision = ($jobs instanceof \Illuminate\Pagination\LengthAwarePaginator ? $jobs->getCollection() : $jobs)
    ->groupBy('division')->map->count()->sortDesc();

  $byStatus = collect(['SUBMITTED','SCREENING','INTERVIEW','OFFERED','HIRED','REJECTED'])
    ->mapWithKeys(fn($k)=>[$k=>(int)($myAppsSummary['byStatus'][$k] ?? 0)]);

  $brandBlue  = '#1d4ed8'; $brandRed = '#dc2626'; $brandBlack = '#0a0a0a'; $brandGray = '#e5e7eb';

  // ===== Timeline data (SAFE) =====
  $latestApp = (auth()->check() && $myApps->isNotEmpty()) ? $myApps->first() : null;

  $stageLabels = [
    'SUBMITTED'  => 'Pengajuan Berkas',
    'SCREENING'  => 'Screening CV',
    'INTERVIEW'  => 'Wawancara',
    'OFFERED'    => 'Offering',
    'HIRED'      => 'Diterima'
  ];
  $order = array_keys($stageLabels);

  // Default jika tidak ada lamaran
  $currKey   = 'SUBMITTED';
  $rejected  = false;
  $currIndex = 0;

  if ($latestApp) {
      $currKey = strtoupper(
          $latestApp->overall_status
          ?? $latestApp->current_stage
          ?? (($myAppsProgress[$latestApp->id]['current_stage'] ?? 'SUBMITTED'))
      );
      $rejected  = ($currKey === 'REJECTED');
      $currIndex = $rejected ? -1 : (array_search($currKey, $order, true) !== false ? array_search($currKey, $order, true) : 0);
  }
@endphp

{{-- ===== Icons ===== --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></g></symbol>
  <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></g></symbol>
  <symbol id="i-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="6 9 12 15 18 9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
  <symbol id="i-user" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 1 0-16 0"/><circle cx="12" cy="7" r="4"/></g></symbol>
  <symbol id="i-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1"/></g></symbol>
  <symbol id="i-arrow-right" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></g></symbol>
  <symbol id="i-rocket" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 15s3-7 10-10c5-2 6 4 3 6-4 3-10 10-10 10S3 22 5 15Z"/><path d="M14 5l5 5"/><path d="M6 14l7 7"/></g></symbol>
  <symbol id="i-apply" viewBox="0 0 24 24" fill="none" stroke="currentColor"><g stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></g></symbol>
</svg>

{{-- ===== NAVBAR ===== --}}
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b" style="border-color: {{ $brandGray }}">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <button id="btn-nav" class="md:hidden p-2 text-zinc-700 hover:text-black" aria-label="Menu">
        <svg class="w-5 h-5"><use href="#i-menu"/></svg>
      </button>
      <a href="{{ route('welcome') }}" class="font-extrabold tracking-tight" style="color: {{ $brandBlack }}">
        HUMAN<span style="color: {{ $brandBlue }}">.</span><span style="color: {{ $brandRed }}">Careers</span>
      </a>
    </div>

    <form action="{{ route('jobs.index') }}" method="GET" class="hidden md:flex items-center flex-1 max-w-lg mx-6">
      <div class="relative w-full">
        <input name="q" type="search" placeholder="Cari posisi, divisi, atau kata kunci…"
               class="w-full rounded-xl bg-white border text-zinc-800 placeholder-zinc-500 focus:ring-2 px-10 py-2 outline-none"
               style="border-color: {{ $brandGray }}; --tw-ring-color: {{ $brandBlue }};">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500"><svg class="w-5 h-5"><use href="#i-search"/></svg></span>
      </div>
    </form>

    <nav class="hidden md:flex items-center gap-6 text-sm">
      <a href="{{ route('jobs.index') }}" class="hover:opacity-80" style="color: {{ $brandBlack }}">Lowongan</a>
      @auth
        <a href="{{ route('applications.mine') }}" class="hover:opacity-80" style="color: {{ $brandBlack }}">Lamaran</a>
        <details class="dropdown relative">
          <summary class="flex items-center gap-2 cursor-pointer select-none hover:opacity-80" style="color: {{ $brandBlack }}">
            @php $uname = auth()->user()->name ?? auth()->user()->email ?? 'User'; $ini = strtoupper(mb_substr($uname,0,1)); @endphp
            <span class="inline-grid place-items-center w-9 h-9 rounded-full border text-xs font-bold"
                  style="background: rgba(29,78,216,.08); color: {{ $brandBlue }}; border-color: rgba(29,78,216,.3);">{{ $ini }}</span>
            <svg class="w-4 h-4 transition"><use href="#i-chevron"/></svg>
          </summary>
          <div class="absolute right-0 mt-2 w-60 rounded-xl border bg-white shadow-2xl p-2" style="border-color: {{ $brandGray }}">
            <div class="px-2 py-1.5 text-[11px] text-zinc-500">Masuk sebagai</div>
            <div class="px-2 pb-2 text-sm text-zinc-800 truncate">{{ $uname }}</div>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-zinc-100">Profil</a>
            <a href="{{ route('applications.mine') }}" class="block px-3 py-2 rounded-lg text-sm hover:bg-zinc-100">Lamaran Saya</a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">@csrf
              <button class="w-full text-left px-3 py-2 rounded-lg text-sm" style="color: {{ $brandRed }};">Keluar</button>
            </form>
          </div>
        </details>
      @else
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 hover:opacity-80" style="color: {{ $brandBlack }}">
          <svg class="w-4 h-4"><use href="#i-user"/></svg> Masuk
        </a>
        <a href="{{ route('register') }}" class="px-3 py-1.5 rounded-xl text-white font-semibold hover:opacity-90" style="background: {{ $brandBlue }};">Daftar</a>
      @endauth
    </nav>
  </div>

  {{-- Mobile menu --}}
  <div id="nav-mobile" class="md:hidden hidden border-t bg-white" style="border-color: {{ $brandGray }}">
    <div class="px-4 py-3 space-y-3">
      <form action="{{ route('jobs.index') }}" method="GET" class="flex">
        <div class="relative w-full">
          <input name="q" type="search" placeholder="Cari posisi, divisi, atau kata kunci…" class="w-full rounded-lg bg-white border text-zinc-800 placeholder-zinc-500 focus:ring-2 pl-10 pr-3 py-2 outline-none" style="border-color: {{ $brandGray }}; --tw-ring-color: {{ $brandBlue }};">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-zinc-500"><svg class="w-5 h-5"><use href="#i-search"/></svg></span>
        </div>
      </form>
      <a href="{{ route('jobs.index') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Lowongan</a>
      @auth
        <a href="{{ route('applications.mine') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Lamaran</a>
        <div class="border-t" style="border-color: {{ $brandGray }}"></div>
        <a href="{{ route('profile.edit') }}" class="block px-2 py-2 rounded-lg hover:bg-zinc-100">Profil</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button class="w-full text-left px-2 py-2 rounded-lg" style="color: {{ $brandRed }};">Keluar</button>
        </form>
      @else
        <div class="flex items-center gap-3">
          <a href="{{ route('login') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border hover:bg-zinc-100" style="border-color: {{ $brandGray }};">
            <svg class="w-4 h-4"><use href="#i-user"/></svg> Masuk
          </a>
          <a href="{{ route('register') }}" class="px-3 py-2 rounded-lg text-white font-semibold hover:opacity-90" style="background: {{ $brandRed }};">Daftar</a>
        </div>
      @endauth
    </div>
  </div>

  {{-- Breadcrumb --}}
  <div class="border-t bg-white" style="border-color: {{ $brandGray }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-10 flex items-center text-xs text-zinc-500">
      <a href="{{ route('welcome') }}" class="hover:text-zinc-800">Beranda</a>
      <span class="mx-2">/</span><span class="text-zinc-800">Karier</span>
    </div>
  </div>
</header>

{{-- ===== HERO ===== --}}
<section class="bg-white border-b" style="border-color: {{ $brandGray }}">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">
    <div class="grid lg:grid-cols-2 gap-8 items-center">
      <div>
        <h1 class="text-3xl md:text-4xl font-extrabold" style="color: {{ $brandBlack }}">
          <span class="heading-accent">Bangun Karier Bersama Andalan</span>
        </h1>

        <p class="mt-3 max-w-xl text-zinc-600">
          Temukan peluang terbaik, ajukan lamaran dengan mudah, dan pantau progres seleksi secara transparan.
        </p>

        <div class="mt-6 flex flex-wrap items-center gap-3">
          <a href="{{ route('jobs.index') }}"
             class="inline-flex items-center gap-2 rounded-xl text-white font-semibold px-4 py-2 hover:opacity-90"
             style="background: {{ $brandBlue }};">
            <svg class="w-4 h-4"><use href="#i-briefcase"/></svg> Lihat Lowongan
          </a>
          @guest
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 hover:bg-zinc-50"
               style="border-color: {{ $brandGray }}; color: {{ $brandBlack }};">
              Masuk untuk Melamar <svg class="w-4 h-4"><use href="#i-arrow-right"/></svg>
            </a>
          @endguest
        </div>

        <div class="mt-6 grid grid-cols-3 divide-x rounded-xl border bg-white" style="border-color: {{ $brandGray }}">
          <div class="p-4 text-center">
            <div class="text-2xl font-extrabold" style="color: {{ $brandBlack }}">
              {{ method_exists($jobs,'total') ? $jobs->total() : ($jobs->count() ?? 0) }}
            </div>
            <div class="text-[11px] text-zinc-500">Total Lowongan</div>
          </div>
          <div class="p-4 text-center">
            <div class="text-2xl font-extrabold" style="color: {{ $brandBlack }}">
              @auth {{ $myAppsSummary['total'] ?? $myApps->count() }} @else 0 @endauth
            </div>
            <div class="text-[11px] text-zinc-500">Lamaran Saya</div>
          </div>
          <div class="p-4 text-center">
            <div class="text-2xl font-extrabold" style="color: {{ $brandBlack }}">{{ now()->format('M') }}</div>
            <div class="text-[11px] text-zinc-500">Periode</div>
          </div>
        </div>
      </div>

      <div class="lg:justify-self-end">
        <div class="rounded-2xl bg-white border p-6 shadow-sm" style="border-color: {{ $brandGray }}">
          <div class="flex items-center gap-3">
            <div class="p-3 rounded-xl text-white" style="background: {{ $brandRed }};">
              <svg class="w-6 h-6"><use href="#i-rocket"/></svg>
            </div>
            <div>
              <p class="text-xs text-zinc-500">Proses seleksi transparan</p>
              <p class="font-semibold" style="color: {{ $brandBlack }}">Pemantauan status real-time</p>
            </div>
          </div>
          @auth
          <div class="mt-4 flex flex-wrap gap-2 text-[11px]">
            @php $by = $myAppsSummary['byStatus'] ?? collect(); @endphp
            @foreach(['SUBMITTED','SCREENING','INTERVIEW','OFFERED','HIRED','REJECTED'] as $s)
              <span class="px-2 py-1 rounded-full bg-zinc-100 text-zinc-800">
                {{ ucfirst(strtolower($s)) }}: {{ $by[$s] ?? 0 }}
              </span>
            @endforeach
          </div>
          @endauth
        </div>
      </div>
    </div>
  </div>
</section>


{{-- ===== COMPANY OVERVIEW: Video + Sticky Charts ===== --}}
<section class="bg-white">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 py-8">
    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <div class="rounded-2xl overflow-hidden border" style="border-color: {{ $brandGray }}">
          <video class="w-full aspect-video bg-black" controls poster="/storage/media/company-poster.jpg">
            <source src="/storage/media/company.mp4" type="video/mp4">
            Browser Anda tidak mendukung pemutaran video.
          </video>
        </div>
        <div class="mt-3 text-sm text-zinc-600">Profil perusahaan: budaya, nilai inti, dan lingkungan kerja kolaboratif di Andalan.</div>
      </div>

      <div class="lg:col-span-1">
        <div class="sticky-right space-y-6">
          <div class="rounded-2xl bg-white border p-5" style="border-color: {{ $brandGray }}">
            <h3 class="font-semibold mb-3" style="color: {{ $brandBlack }}">Distribusi Lowongan per Divisi</h3>
            <div style="height:260px"><canvas id="chartDivision"></canvas></div>
          </div>
          <div class="rounded-2xl bg-white border p-5" style="border-color: {{ $brandGray }}">
            <h3 class="font-semibold mb-3" style="color: {{ $brandBlack }}">Ringkasan Status Lamaran</h3>
            <div style="height:260px"><canvas id="chartApplications"></canvas></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ===== APPLICATION STAGES (Right Timeline + Photo) ===== --}}
<section class="bg-white">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 pb-2">
    <div class="rounded-2xl border overflow-hidden" style="border-color: {{ $brandGray }}">
      <div class="grid md:grid-cols-2">
        <div class="relative">
          <img src="/storage/media/office-team.jpg" alt="Tim Andalan" class="w-full h-full object-cover min-h-[260px]">
          <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/50 to-transparent p-4">
            <p class="text-white text-sm">Lingkungan kerja yang dinamis dan kolaboratif.</p>
          </div>
        </div>

        <div class="p-6 md:p-8">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold" style="color: {{ $brandBlack }}">Tahapan Rekrutmen Anda</h3>
            @auth
              @if($latestApp)
                <span class="text-xs text-zinc-500">Posisi: <span class="font-medium text-zinc-700">{{ $latestApp->job->title ?? '—' }}</span></span>
              @endif
            @endauth
          </div>

          @auth
            @if($latestApp)
              <div class="tl space-y-4">
                @foreach($stageLabels as $key => $label)
                  @php
                    $idx = array_search($key, $order, true);
                    $class = 'dot-amber';
                    if($rejected){
                      $class = ($key === 'SUBMITTED') ? 'dot-blue' : 'dot-amber';
                    } else {
                      if($idx < $currIndex) $class = 'dot-green';
                      elseif($idx === $currIndex) $class = 'dot-blue';
                      else $class = 'dot-amber';
                    }
                  @endphp
                  <div class="tl-step">
                    <span class="tl-dot {{ $class }}"></span>
                    <div class="flex items-start justify-between gap-4">
                      <div>
                        <p class="text-sm font-medium" style="color: {{ $brandBlack }}">{{ $label }}</p>
                        <p class="text-xs text-zinc-500">
                          @switch($class)
                            @case('dot-green') Selesai @break
                            @case('dot-blue') Sedang diproses @break
                            @default Menunggu giliran
                          @endswitch
                        </p>
                      </div>
                      @if($class==='dot-blue')
                        <span class="text-[11px] px-2 py-1 rounded-full bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200">Aktif</span>
                      @elseif($class==='dot-green')
                        <span class="text-[11px] px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">Selesai</span>
                      @else
                        <span class="text-[11px] px-2 py-1 rounded-full bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200">Berikutnya</span>
                      @endif
                    </div>
                  </div>
                @endforeach

                @if($rejected)
                  <div class="tl-step">
                    <span class="tl-dot" style="--c:#dc2626"></span>
                    <div class="flex items-start justify-between gap-4">
                      <div>
                        <p class="text-sm font-medium" style="color: {{ $brandBlack }}">Keputusan</p>
                        <p class="text-xs text-zinc-500">Lamaran tidak melanjutkan proses.</p>
                      </div>
                      <span class="text-[11px] px-2 py-1 rounded-full bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200">Ditutup</span>
                    </div>
                  </div>
                @endif
              </div>
              <div class="mt-5 text-xs text-zinc-500">Status diperbarui otomatis berdasarkan progres seleksi.</div>
            @else
              <p class="text-zinc-600">Belum ada data lamaran. Silakan pilih posisi yang tersedia.</p>
            @endif
          @else
            <p class="text-zinc-600">Masuk untuk melihat tahapan rekrutmen pribadi Anda.</p>
            <div class="mt-3">
              <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 hover:bg-zinc-50" style="border-color: {{ $brandGray }}; color: {{ $brandBlack }};">
                Masuk
              </a>
            </div>
          @endauth
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ===== MAIN CONTENT (My Applications + Jobs) ===== --}}
<div class="max-w-7xl mx-auto px-6 lg:px-8 py-10 space-y-10">
  @auth
  <section class="rounded-2xl bg-white border" style="border-color: {{ $brandGray }}">
    <div class="p-6 border-b" style="border-color: {{ $brandGray }}">
      <h2 class="font-semibold" style="color: {{ $brandBlack }}">Lamaran Saya</h2>
    </div>
    @if($myApps->isEmpty())
      <div class="p-6 text-zinc-600">Belum ada lamaran. Silakan telusuri daftar lowongan berikut.</div>
    @else
      <div class="p-4 sm:p-6">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
          @foreach($myApps as $app)
            @php
              $prog = $myAppsProgress[$app->id] ?? null;
              $statusKey = strtoupper($app->overall_status ?? $app->current_stage ?? ($prog['current_stage'] ?? 'SUBMITTED'));
              $badge = match($statusKey) {
                'SUBMITTED','SCREENING','INTERVIEW' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',
                'OFFERED','HIRED'                   => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
                'REJECTED'                          => 'bg-zinc-100 text-zinc-700 ring-1 ring-inset ring-zinc-200',
                default                             => 'bg-zinc-50 text-zinc-700 ring-1 ring-inset ring-zinc-200',
              };
              $pct    = (int)($prog['progress_percent'] ?? 0);
              $curr   = $prog['current_label']    ?? ucfirst(strtolower($statusKey));
              $next   = $prog['next_stage_label'] ?? null;
              $hint   = $prog['hint']             ?? null;
              $isFinal= (bool)($prog['is_final'] ?? in_array($statusKey, ['HIRED','REJECTED'], true));
            @endphp

            <a href="{{ route('jobs.show', $app->job_id) }}" class="group rounded-xl p-4 border hover:shadow-sm transition bg-white" style="border-color: {{ $brandGray }}">
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
                    Tahap berikutnya: <span class="font-medium text-zinc-700">{{ $next }}</span>
                  @endif
                  @if($hint)
                    <span class="ml-1 text-zinc-400">• {{ $hint }}</span>
                  @endif
                </div>
              </div>

              <div class="mt-3 flex items-center justify-between text-xs text-zinc-500">
                <span>Diajukan: {{ optional($app->created_at)->format('d M Y') }}</span>
                <span class="inline-flex items-center gap-1 text-zinc-700 group-hover:gap-2 transition">
                  Detail <svg class="w-3.5 h-3.5"><use href="#i-arrow-right"/></svg>
                </span>
              </div>
            </a>
          @endforeach
        </div>
      </div>
    @endif
  </section>
  @endauth

  {{-- Lowongan --}}
  <section class="rounded-2xl bg-white border" style="border-color: {{ $brandGray }}">
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
            <div class="rounded-xl border bg-white hover:shadow-sm transition" style="border-color: {{ $brandGray }}">
              <div class="p-5">
                <div class="flex items-start gap-3">
                  <div class="p-2.5 rounded-lg text-white" style="background: {{ $brandBlack }}"><svg class="w-5 h-5"><use href="#i-briefcase"/></svg></div>
                  <div class="min-w-0">
                    <a href="{{ route('jobs.show', $job) }}" class="block font-semibold hover:opacity-80" style="color: {{ $brandBlack }}">{{ $job->title }}</a>
                    <p class="text-[11px] text-zinc-500 mt-0.5">
                      {{ $job->site?->code ?? $job->site?->name ?? '—' }} • Diposting {{ optional($job->created_at)->diffForHumans() }}
                    </p>
                  </div>
                </div>

                @if(!empty($excerpt))
                  <p class="text-sm text-zinc-600 mt-3 line-clamp-2">{{ $excerpt }}</p>
                @endif

                <div class="mt-4 flex items-center justify-between">
                  <a href="{{ route('jobs.show', $job) }}" class="inline-flex items-center gap-1.5 text-sm font-medium hover:opacity-80" style="color: {{ $brandBlue }}">
                    Detail <svg class="w-4 h-4"><use href="#i-arrow-right"/></svg>
                  </a>

                  @auth
                    <form action="{{ route('applications.store', $job) }}" method="POST" onsubmit="return confirm('Lamar posisi ini?')">
                      @csrf
                      <button type="submit" class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-lg text-white hover:opacity-90" style="background: {{ $brandRed }};">
                        <svg class="w-4 h-4"><use href="#i-apply"/></svg> Lamar
                      </button>
                    </form>
                  @else
                    <a href="{{ route('login') }}?intended={{ urlencode(route('jobs.show',$job)) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold px-3 py-1.5 rounded-lg text-white hover:opacity-90" style="background: {{ $brandBlack }};">
                      <svg class="w-4 h-4"><use href="#i-user"/></svg> Masuk untuk Melamar
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
  </section>

  {{-- CTA --}}
  <section class="rounded-2xl border p-6 bg-white" style="border-color: {{ $brandGray }}">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="text-zinc-700">
        <p class="font-semibold" style="color: {{ $brandBlack }}">Tingkatkan Profil Anda</p>
        <p class="text-sm text-zinc-600">Perbarui informasi terbaru untuk peluang yang lebih relevan.</p>
      </div>
      <div class="flex items-center gap-3">
        @auth
          <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-2 rounded-xl text-white font-semibold px-4 py-2 hover:opacity-90" style="background: {{ $brandBlack }};">Edit Profil</a>
        @else
          <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl text-white font-semibold px-4 py-2 hover:opacity-90" style="background: {{ $brandBlue }};">Daftar</a>
          <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 hover:bg-zinc-50" style="border-color: {{ $brandGray }}; color: {{ $brandBlack }};">Masuk</a>
        @endauth
      </div>
    </div>
  </section>
</div>

{{-- ===== FOOTER ===== --}}
<footer style="background: {{ $brandBlack }};">
  <div class="max-w-7xl mx-auto px-6 lg:px-8 py-12 text-white">
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
      <div>
        <div class="font-extrabold mb-3">HUMAN<span style="color: {{ $brandBlue }}">.</span><span style="color: {{ $brandRed }}">Careers</span></div>
        <p class="text-sm text-zinc-300">Portal karier resmi Andalan. Kami berkomitmen menghadirkan proses rekrutmen yang transparan, objektif, dan berkelas dunia.</p>
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
          <li><a href="#" class="hover:underline">Syarat & Ketentuan</a></li>
        </ul>
      </div>
      <div>
        <h4 class="font-semibold mb-3">Hubungi Kami</h4>
        <p class="text-sm text-zinc-300">Kantor Pusat<br>Jl. Contoh No. 123, Jakarta</p>
        <p class="text-sm text-zinc-300 mt-2">Email: careers@andalan.co.id</p>
        <form action="#" method="POST" class="mt-4 flex">
          <input type="email" placeholder="Alamat email" class="w-full rounded-l-lg bg-white text-zinc-800 px-3 py-2 focus:outline-none">
          <button class="rounded-r-lg text-white px-4" style="background: {{ $brandRed }};">Berlangganan</button>
        </form>
      </div>
    </div>
    <div class="mt-10 border-top pt-6 text-sm flex flex-col md:flex-row items-center justify-between gap-3" style="border-top: 1px solid rgba(255,255,255,.15);">
      <div class="text-zinc-400">© {{ date('Y') }} PT Andalan. Seluruh hak cipta dilindungi.</div>
      <div class="flex items-center gap-3">
        <span class="w-4 h-4 rounded-full" style="background: {{ $brandBlue }};"></span>
        <span class="w-4 h-4 rounded-full" style="background: {{ $brandRed }};"></span>
        <span class="w-4 h-4 rounded-full bg-white"></span>
      </div>
    </div>
  </div>
</footer>

{{-- ===== JS: Mobile nav + Charts ===== --}}
<script>
  (function(){const b=document.getElementById('btn-nav'),m=document.getElementById('nav-mobile'); if(b&&m){b.addEventListener('click',()=>m.classList.toggle('hidden'));}})();

  const divisionLabels = {!! json_encode($byDivision->keys()->values()) !!};
  const divisionValues = {!! json_encode($byDivision->values()->map(fn($v)=>(int)$v)) !!};
  const statusLabels   = {!! json_encode($byStatus->keys()->values()) !!};
  const statusValues   = {!! json_encode($byStatus->values()) !!};

  const brandBlue = "{{ $brandBlue }}", brandRed = "{{ $brandRed }}";

  const ctxDiv = document.getElementById('chartDivision');
  if (ctxDiv && window.Chart) {
    new Chart(ctxDiv, {
      type: 'bar',
      data: {
        labels: divisionLabels,
        datasets: [{ label: 'Lowongan', data: divisionValues,
          backgroundColor: divisionLabels.map((_,i)=> i%2===0 ? brandBlue : brandRed),
          borderColor: divisionLabels.map((_,i)=> i%2===0 ? brandBlue : brandRed),
          borderWidth: 1, borderRadius: 6 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, grid: { color: '#e5e7eb' } }, x: { grid: { display: false } } },
        plugins: { legend: { display:false }, tooltip: { enabled:true } }
      }
    });
  }

  const ctxApp = document.getElementById('chartApplications');
  if (ctxApp && window.Chart) {
    new Chart(ctxApp, {
      type: 'doughnut',
      data: {
        labels: statusLabels,
        datasets: [{ data: statusValues,
          backgroundColor: [brandBlue,'#3b82f6','#1e40af',brandRed,'#ef4444','#d1d5db'],
          borderColor: '#ffffff', borderWidth: 2 }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '60%',
        plugins: { legend:{ position:'bottom', labels:{ boxWidth:12 }}, tooltip:{ enabled:true } }
      }
    });
  }
</script>
</body>
</html>
