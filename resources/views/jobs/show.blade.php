{{-- resources/views/jobs/show.blade.php --}}
@extends('layouts.app', ['title' => e($job->title)])

@php
  use Illuminate\Support\Carbon;

  // Palet warna utama
  $BLUE  = '#1d4ed8'; // Tailwind blue-700
  $RED   = '#dc2626'; // Tailwind red-600

  // ==== SANITIZER (whitelist aman untuk konten dari editor) ====
  // Disarankan pakai HTML Purifier jika tersedia untuk kontrol atribut (mis. <a href>).
  $sanitize = function ($v) {
      if (!is_string($v) || trim($v) === '') return null;
      $decoded = html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $allowed = '<p><br><ul><ol><li><strong><em><b><i><u><h2><h3><h4><blockquote><code><pre><a>';
      return strip_tags($decoded, $allowed);
  };

  /** @var \App\Models\JobApplication|null $myApp */
  $myApp = auth()->check()
      ? $job->applications()->where('user_id', auth()->id())->with(['stages','stages.actor','stages.user'])->latest()->first()
      : null;

  /** @var \App\Models\CandidateProfile|null $meProfile */
  $meProfile = auth()->check()
      ? \App\Models\CandidateProfile::where('user_id', auth()->id())->first()
      : null;

  // Tahapan & label
  $stageOrder = ['applied','psychotest','hr_iv','user_iv','final','offer','hired'];
  $pretty = [
    'applied'    => 'Pengajuan Berkas',
    'psychotest' => 'Psikotes',
    'hr_iv'      => 'HR Interview',
    'user_iv'    => 'User Interview',
    'final'      => 'Final',
    'offer'      => 'Offering',
    'hired'      => 'Diterima',
    'rejected'   => 'Ditolak',
  ];

  // Status keseluruhan & stage saat ini (aman saat $myApp null)
  $overall = strtolower($myApp?->overall_status ?? 'in_progress');
  $currRaw = strtolower($myApp?->current_stage ?? 'applied');
  $currKey = in_array($currRaw, $stageOrder, true) ? $currRaw : 'applied';

  // Koleksi stage
  $stagesColl = collect($myApp?->stages ?? []);
  $stageMap = $stagesColl->mapWithKeys(fn($s) => ($k = strtolower($s->stage_key ?? '')) ? [$k => $s] : []);
  $visited = $stagesColl->pluck('stage_key')->map(fn($v)=>strtolower($v))
            ->filter(fn($v)=>in_array($v,$stageOrder,true))->unique()
            ->push($currKey)->unique()->values()->all();

  $idxNow  = array_search($currKey, $stageOrder, true); $idxNow = $idxNow===false?0:$idxNow;
  $prevKey = $idxNow>0 ? $stageOrder[$idxNow-1] : null;
  $nextKey = $idxNow<count($stageOrder)-1 ? $stageOrder[$idxNow+1] : null;

  $progressPct = function() use ($myApp,$stageOrder,$overall,$currKey){
    if(!$myApp) return 0;
    $idx = array_search($currKey,$stageOrder,true); if($idx===false) $idx=0;
    $max = max(count($stageOrder)-1,1);
    if($overall==='rejected'){
      return min(100, max(40,(int)round($idx/$max*100)));
    }
    return (int)round($idx/$max*100);
  };

  // Role admin (Spatie / non-Spatie)
  $isAdmin = auth()->check() && (
    (method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['hr','superadmin'])) ||
    in_array(auth()->user()->role ?? null, ['hr','superadmin'], true)
  );

  $employmentPretty = [
    'fulltime' => 'Fulltime',
    'contract' => 'Contract',
    'intern'   => 'Intern',
    'parttime' => 'Part-time',
    'freelance'=> 'Freelance',
  ];

  // ===== Extra Pretty Maps =====
  $levelLabels = [
    'bod'        => 'BOD',
    'manager'    => 'Manager',
    'supervisor' => 'Supervisor',
    'spv'        => 'SPV',
    'staff'      => 'Staff',
    'non_staff'  => 'Non Staff',
  ];

  // Format uang
  $fmtMoney = function($n, $cur = 'IDR') {
    if(!is_numeric($n)) return null;
    return ($cur ?: 'IDR').' '.number_format((float)$n, 0, ',', '.');
  };

  // Timezone prioritas
  $siteTz = optional($job->site)->timezone ?: data_get($job->site, 'meta.timezone');
  $TZ     = $siteTz ?: config('app.timezone', 'Asia/Jakarta');

  // Formatter tanggal lokal ringkas
  $formatTs = function ($ts) use ($TZ) {
    if (!$ts) return null;
    try {
      $c = $ts instanceof Carbon ? $ts->copy() : Carbon::parse($ts);
      $c = $c->setTimezone($TZ);
      $namaHari = ['Sun'=>'Minggu','Mon'=>'Senin','Tue'=>'Selasa','Wed'=>'Rabu','Thu'=>'Kamis','Fri'=>'Jumat','Sat'=>'Sabtu'][$c->format('D')] ?? $c->format('D');
      $namaBln  = ['Jan'=>'Jan','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Apr','May'=>'Mei','Jun'=>'Jun','Jul'=>'Jul','Aug'=>'Agu','Sep'=>'Sep','Oct'=>'Okt','Nov'=>'Des'][$c->format('M')] ?? $c->format('M');
      $abbr = str_contains($TZ,'Jakarta') ? 'WIB' : (str_contains($TZ,'Makassar') ? 'WITA' : (str_contains($TZ,'Jayapura') ? 'WIT' : $c->format('T')));
      return sprintf('%s, %02d %s %04d, %s %s', $namaHari,(int)$c->format('d'),$namaBln,(int)$c->format('Y'),$c->format('H:i'),$abbr);
    } catch (\Throwable $e) {
      return (string)$ts;
    }
  };

  // Penentu nama actor perubahan stage
  $actorName = function ($stage) {
    $name = $stage->actor->name ?? $stage->user->name ?? null;
    if ($name) return $name;
    $uid = $stage->acted_by ?? $stage->changed_by ?? $stage->updated_by ?? $stage->user_id ?? null;
    if ($uid) {
      $u = \App\Models\User::query()->select('name')->find($uid);
      if ($u && $u->name) return $u->name;
    }
    return 'Sistem/Unknown';
  };

  // Terakhir diubah
  $latestStage   = $stagesColl->sortByDesc(fn($s) => $s->updated_at ?? $s->created_at ?? null)->first();
  $lastChangedAt = $latestStage?->updated_at ?? $myApp?->updated_at;
  $lastChangedBy = $latestStage ? $actorName($latestStage)
                  : (($myApp && method_exists($myApp,'updatedBy')) ? ($myApp->updatedBy->name ?? null) : null);

  $closingAt = $job->closing_at ?? null;

  // CreatedBy / UpdatedBy (nama user dari id job)
  $createdByName = null;
  $updatedByName = null;
  try {
    if (!empty($job->created_by)) {
      $u = \App\Models\User::query()->select('name')->find($job->created_by);
      $createdByName = $u?->name;
    }
    if (!empty($job->updated_by)) {
      $u2 = \App\Models\User::query()->select('name')->find($job->updated_by);
      $updatedByName = $u2?->name;
    }
  } catch (\Throwable $e) {}

  // Normalisasi keywords & skills (bisa string CSV / array)
  $keywords = collect(
      is_array($job->keywords ?? null) ? $job->keywords
        : (is_string($job->keywords ?? null) ? preg_split('/\s*,\s*/', (string)$job->keywords, -1, PREG_SPLIT_NO_EMPTY) : [])
    )->filter()->unique()->values();

  $skills = collect(
      is_array($job->skills ?? null) ? $job->skills
        : (is_string($job->skills ?? null) ? preg_split('/\s*,\s*/', (string)$job->skills, -1, PREG_SPLIT_NO_EMPTY) : [])
    )->filter()->unique()->values();

  // Hitung sisa hari/jam menuju closing (countdown ringkas)
  $closingAtCarbon = $closingAt ? Carbon::parse($closingAt)->timezone($TZ) : null;
  $countdownText = null;
  if ($closingAtCarbon) {
    $now = Carbon::now($TZ);
    if ($closingAtCarbon->isPast()) {
      $countdownText = 'Ditutup';
    } else {
      $diffDays = $now->diffInDays($closingAtCarbon);
      $diffHours = $now->copy()->addDays($diffDays)->diffInHours($closingAtCarbon);
      $countdownText = $diffDays > 0
        ? $diffDays.' hari lagi'
        : ($diffHours > 0 ? $diffHours.' jam lagi' : 'Kurang dari 1 jam');
    }
  }

  // ====== Breadcrumb JSON-LD (SEO) ======
  $breadcrumbLd = [
    '@context' => 'https://schema.org',
    '@type'    => 'BreadcrumbList',
    'itemListElement' => [
      [
        '@type'    => 'ListItem',
        'position' => 1,
        'name'     => 'Dashboard',
        'item'     => route('dashboard'),
      ],
      [
        '@type'    => 'ListItem',
        'position' => 2,
        'name'     => 'Jobs',
        'item'     => route('jobs.index'),
      ],
      [
        '@type'    => 'ListItem',
        'position' => 3,
        'name'     => (string) $job->title,
        'item'     => request()->fullUrl(),
      ],
    ],
  ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8 py-6">

  {{-- BREADCRUMB (UI kece, aksesibel, responsif) --}}
  <nav class="mb-4" aria-label="Breadcrumb">
    <div class="relative rounded-xl border border-slate-200 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60 shadow-sm">
      {{-- garis aksen tipis merah-biru --}}
      <div class="h-1 w-full overflow-hidden rounded-t-xl">
        <div class="h-full w-full" style="background: linear-gradient(90deg, {{ $RED }} 0%, {{ $BLUE }} 100%);"></div>
      </div>

      {{-- isi breadcrumb (scroll-x jika panjang) --}}
      <ol class="flex items-center gap-2 px-3 py-2 text-sm text-slate-600 overflow-x-auto scrollbar-thin scrollbar-track-transparent scrollbar-thumb-slate-200"
          itemscope itemtype="https://schema.org/BreadcrumbList">
        {{-- Dashboard --}}
        <li class="flex items-center gap-2 shrink-0" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
          <a href="{{ route('dashboard') }}" itemprop="item" class="group inline-flex items-center gap-1 rounded-lg px-2 py-1 hover:bg-slate-50">
            <svg class="h-4 w-4 text-slate-500 group-hover:text-slate-700" aria-hidden="true" viewBox="0 0 24 24" fill="none">
              <path d="M3 10.5l9-7 9 7V20a2 2 0 0 1-2 2h-4.5a.5.5 0 0 1-.5-.5V15a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v6.5a.5.5 0 0 1-.5.5H5a2 2 0 0 1-2-2v-9.5z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
            </svg>
            <span class="font-medium text-slate-700 group-hover:text-slate-900" itemprop="name">Dashboard</span>
          </a>
          <meta itemprop="position" content="1"/>
          <span class="text-slate-300">/</span>
        </li>

        {{-- Jobs --}}
        <li class="flex items-center gap-2 shrink-0" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
          <a href="{{ route('jobs.index') }}" itemprop="item" class="group inline-flex items-center gap-1 rounded-lg px-2 py-1 hover:bg-slate-50">
            <svg class="h-4 w-4 text-slate-500 group-hover:text-slate-700" aria-hidden="true" viewBox="0 0 24 24" fill="none">
              <path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v9a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V8z" stroke="currentColor" stroke-width="1.5"/>
              <path d="M9 6a3 3 0 0 1 6 0" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <span class="font-medium text-slate-700 group-hover:text-slate-900" itemprop="name">Jobs</span>
          </a>
          <meta itemprop="position" content="2"/>
          <span class="text-slate-300">/</span>
        </li>

        {{-- Current page --}}
        <li class="flex items-center gap-2 min-w-0" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
          <div class="inline-flex items-center gap-1 rounded-lg px-2 py-1 bg-slate-50 ring-1 ring-slate-200">
            <svg class="h-4 w-4 text-slate-500" aria-hidden="true" viewBox="0 0 24 24" fill="none">
              <path d="M12 12m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" stroke="currentColor" stroke-width="1.5"/>
              <path d="M12 8v4l3 2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span class="truncate font-semibold text-slate-900" itemprop="name">{{ e($job->title) }}</span>
          </div>
          <meta itemprop="item" content="{{ request()->fullUrl() }}"/>
          <meta itemprop="position" content="3"/>
        </li>

        {{-- Tambahan trail opsional (mis. Recruiter) --}}
        @isset($trail)
          <span class="text-slate-300">/</span>
          <li class="flex items-center gap-2 shrink-0" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
            <div class="inline-flex items-center gap-1 rounded-lg px-2 py-1 bg-slate-50 ring-1 ring-slate-200">
              <span class="font-semibold text-slate-900" itemprop="name">{{ e($trail) }}</span>
            </div>
            <meta itemprop="position" content="4"/>
          </li>
        @endisset
      </ol>

      {{-- tombol kecil back (UX) --}}
      <div class="absolute right-2 top-2">
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-600 hover:bg-slate-50">
          ← Kembali
        </a>
      </div>

      {{-- (Opsional) Salin tautan cepat --}}
      <div class="absolute right-2 top-11 md:top-2 md:right-24">
        <button type="button"
                onclick="navigator.clipboard?.writeText('{{ e(request()->fullUrl()) }}'); this.innerText='Tautan Disalin'; setTimeout(()=>this.innerText='Salin Tautan',1500)"
                class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-xs text-slate-600 hover:bg-slate-50">
          Salin Tautan
        </button>
      </div>
    </div>
  </nav>

  {{-- HEADER --}}
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    {{-- Strip warna merah–biru --}}
    <div class="flex h-2 w-full">
      <div class="flex-1" style="background: {{ $BLUE }}"></div>
      <div class="w-32" style="background: {{ $RED }}"></div>
    </div>

    <div class="p-5 md:p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div class="min-w-0">
          <h1 class="truncate text-3xl font-semibold text-slate-900">{{ e($job->title) ?? '—' }}</h1>

          {{-- Baris info utama (divisi / site / tutup) --}}
          <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-600">
            <span class="inline-flex items-center gap-1">
              <svg class="h-4 w-4 text-slate-500" aria-hidden="true"><use href="#i-brief"/></svg>
              {{ e($job->division ?: '—') }}
            </span>
            <span class="text-slate-300">•</span>
            <span class="inline-flex items-center gap-1">
              <svg class="h-4 w-4 text-slate-500" aria-hidden="true"><use href="#i-pin"/></svg>
              {{ e($job->site?->code ? ($job->site->code . ' — ' . ($job->site->name ?? '')) : '—') }}
            </span>
            @if($closingAt)
              <span class="text-slate-300">•</span>
              <span class="inline-flex items-center gap-1">
                <svg class="h-4 w-4 text-slate-500" aria-hidden="true"><use href="#i-clock"/></svg>
                Tutup: {{ e(Carbon::parse($closingAt)->timezone($TZ)->format('d M Y, H:i')) }}
                {{ str_contains($TZ,'Jakarta') ? 'WIB' : (str_contains($TZ,'Makassar') ? 'WITA' : (str_contains($TZ,'Jayapura') ? 'WIT' : '')) }}
              </span>
            @endif
          </div>

          {{-- META DIPINDAH KE HEADER --}}
          <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
            <span class="rounded-full bg-blue-50 px-2.5 py-1 font-medium text-blue-700 ring-1 ring-inset ring-blue-200">
              Diposting: {{ e(optional($job->created_at)->timezone($TZ)->format('d M Y, H:i') ?? '—') }}
            </span>
            <span class="rounded-full bg-red-50 px-2.5 py-1 font-medium text-red-700 ring-1 ring-inset ring-red-200">
              Diubah: {{ e(optional($job->updated_at)->timezone($TZ)->format('d M Y, H:i') ?? '—') }}
            </span>
            @if(isset($job->applications_count))
              <span class="rounded-full bg-slate-100 px-2.5 py-1 font-medium text-slate-700 ring-1 ring-inset ring-slate-200">
                Jumlah Pelamar: {{ (int) $job->applications_count }}
              </span>
            @endif
            @if($countdownText)
              <span class="rounded-full bg-amber-50 px-2.5 py-1 font-medium text-amber-700 ring-1 ring-inset ring-amber-200">
                Tutup: {{ e($countdownText) }}
              </span>
            @endif
          </div>
        </div>

        {{-- Kanan: status + aksi --}}
        <div class="flex flex-wrap items-center gap-2">
          <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset
            {{ $job->status==='open' ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
            STATUS: {{ strtoupper(e($job->status ?? 'draft')) }}
          </span>

          {{-- Admin quick actions --}}
          @if($isAdmin)
            @if(Route::has('admin.jobs.edit'))
              <a href="{{ route('admin.jobs.edit', $job) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                Edit
              </a>
            @endif
            @if(Route::has('admin.applications.index'))
              <a href="{{ route('admin.applications.index', ['job' => $job->id]) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                Kandidat
              </a>
            @endif
            @if(Route::has('admin.jobs.toggle'))
              <form method="POST" action="{{ route('admin.jobs.toggle', $job) }}"
                    onsubmit="return confirm('Ubah status lowongan?');">
                @csrf @method('PATCH')
                <button type="submit" class="rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                  {{ ($job->status === 'open') ? 'Tutup' : 'Buka' }}
                </button>
              </form>
            @endif
          @endif

          {{-- Aksi kandidat: Edit/Lengkapi Profil --}}
          @auth
            @if(Route::has('candidate.profiles.edit'))
              <a href="{{ route('candidate.profiles.edit', $job) }}"
                 class="rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                {{ $meProfile ? 'Perbarui Profil' : 'Lengkapi Profil' }}
              </a>
            @endif
          @endauth

          {{-- CTA pelamar --}}
          @auth
            @if(($job->status ?? 'draft') === 'open' && !$myApp)
              <form method="POST" action="{{ route('applications.store',$job) }}">@csrf
                <button class="rounded-lg px-4 py-2 text-sm font-semibold text-white"
                        style="background: linear-gradient(90deg, {{ $BLUE }} 0%, {{ $RED }} 100%);">
                  Lamar Sekarang
                </button>
              </form>
            @elseif($myApp)
              <a href="{{ route('applications.mine') }}"
                 class="rounded-lg px-4 py-2 text-sm font-semibold text-white"
                 style="background: {{ $RED }}">
                Lihat Progres
              </a>
            @else
              <button disabled class="rounded-lg px-4 py-2 text-sm font-semibold text-white opacity-60" style="background: {{ $RED }}">Tutup</button>
            @endif
          @else
            <a class="rounded-lg px-4 py-2 text-sm font-semibold text-white"
               style="background: linear-gradient(90deg, {{ $BLUE }} 0%, {{ $RED }} 100%);"
               href="{{ route('login') }}">
              Login untuk Melamar
            </a>
          @endauth
        </div>
      </div>
    </div>
  </div>

  {{-- GRID UTAMA --}}
  <div class="mt-6 grid gap-6 lg:grid-cols-3">
    {{-- LEFT --}}
    <div class="lg:col-span-2 space-y-6">
      {{-- Ringkasan --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <div class="grid gap-4 sm:grid-cols-3">
          <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">Tipe</div>
            <div class="mt-1 inline-flex items-center rounded bg-blue-700 px-2 py-1 text-[11px] font-semibold text-white">
              {{ e($employmentPretty[$job->employment_type] ?? strtoupper($job->employment_type ?? '—')) }}
            </div>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">Openings</div>
            <div class="mt-1 text-xl font-semibold text-slate-900">{{ (int) ($job->openings ?? 1) }}</div>
          </div>
          <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">Lokasi</div>
            <div class="mt-1 inline-flex items-center gap-1 text-slate-800">
              <svg class="h-4 w-4 text-slate-500" aria-hidden="true"><use href="#i-pin"/></svg>
              {{ e($job->site?->name ?? $job->site?->code ?? '—') }}
            </div>
          </div>
        </div>

        {{-- Gaji (opsional) --}}
        @if($job->salary_min || $job->salary_max || $job->currency)
          <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3">
            <div class="text-xs text-slate-500">Perkiraan Gaji</div>
            <div class="mt-1 text-slate-900">
              @php
                $cur = $job->currency ?: 'IDR';
                $min = $fmtMoney($job->salary_min, $cur);
                $max = $fmtMoney($job->salary_max, $cur);
              @endphp
              @if($min && $max)
                {{ $min }} – {{ $max }}
              @elseif($min)
                ≥ {{ $min }}
              @elseif($max)
                ≤ {{ $max }}
              @else
                {{ e($cur) }}
              @endif
              @if(!empty($job->salary_period))
                <span class="text-slate-500 text-sm">/ {{ e($job->salary_period) }}</span>
              @endif
            </div>
          </div>
        @endif
      </div>

      {{-- Informasi Lengkap --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Informasi Lengkap</h2>

        <dl class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Kode Lowongan</dt>
            <dd class="col-span-2 text-slate-800">{{ e($job->code ?? '—') }}</dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Perusahaan</dt>
            <dd class="col-span-2 text-slate-800">
              @if($job->company)
                {{ e(($job->company->code ?? '')) }}{{ $job->company->code?' — ':'' }}{{ e(($job->company->name ?? '')) }}
              @else
                —
              @endif
            </dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Level</dt>
            <dd class="col-span-2 text-slate-800">{{ e($levelLabels[strtolower((string)$job->level)] ?? (ucwords(str_replace('_',' ',(string)$job->level)) ?: '—')) }}</dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Status</dt>
            <dd class="col-span-2">
              <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset
                {{ $job->status==='open' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                {{ strtoupper(e($job->status ?? 'draft')) }}
              </span>
            </dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Lokasi (Site)</dt>
            <dd class="col-span-2 text-slate-800">
              @if($job->site)
                {{ e($job->site->code ?? '—') }}{{ ($job->site->code && $job->site->name)?' — ':'' }}{{ e($job->site->name ?? '') }}
              @else
                —
              @endif
            </dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Tipe Pekerjaan</dt>
            <dd class="col-span-2 text-slate-800">
              {{ e($employmentPretty[$job->employment_type] ?? strtoupper($job->employment_type ?? '—')) }}
            </dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Openings</dt>
            <dd class="col-span-2 text-slate-800">{{ (int) ($job->openings ?? 1) }}</dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Diposting</dt>
            <dd class="col-span-2 text-slate-800">
              {{ e(optional($job->created_at)->timezone($TZ)->format('d M Y, H:i') ?? '—') }}
              @if($createdByName) · oleh <span class="font-medium">{{ e($createdByName) }}</span>@endif
            </dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Diubah</dt>
            <dd class="col-span-2 text-slate-800">
              {{ e(optional($job->updated_at)->timezone($TZ)->format('d M Y, H:i') ?? '—') }}
              @if($updatedByName) · oleh <span class="font-medium">{{ e($updatedByName) }}</span>@endif
            </dd>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <dt class="text-slate-500">Tutup</dt>
            <dd class="col-span-2 text-slate-800">
              @if($closingAt)
                {{ e(Carbon::parse($closingAt)->timezone($TZ)->format('d M Y, H:i')) }}
                {{ str_contains($TZ,'Jakarta') ? 'WIB' : (str_contains($TZ,'Makassar') ? 'WITA' : (str_contains($TZ,'Jayapura') ? 'WIT' : '')) }}
                @if($countdownText) <span class="ml-2 rounded bg-amber-50 px-1.5 py-0.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-200">{{ e($countdownText) }}</span>@endif
              @else
                —
              @endif
            </dd>
          </div>
        </dl>
      </div>

      {{-- Deskripsi (aman) --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Deskripsi Pekerjaan</h2>
        <div class="prose max-w-none text-slate-800">
          <style>
            .prose ul{list-style:disc;padding-left:1.25rem}
            .prose ol{list-style:decimal;padding-left:1.25rem}
            .prose li{margin:.25rem 0}
          </style>
          @if(filled($job->description))
            {!! $sanitize($job->description) !!}
          @else
            <p class="text-slate-500">Belum ada deskripsi yang dituliskan.</p>
          @endif
        </div>
      </div>

      {{-- Tanggung Jawab --}}
      @if(filled($job->responsibilities))
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Tanggung Jawab</h2>
        <div class="prose max-w-none text-slate-800">
          {!! $sanitize($job->responsibilities) !!}
        </div>
      </div>
      @endif

      {{-- Kualifikasi --}}
      @if(filled($job->qualifications))
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Kualifikasi</h2>
        <div class="prose max-w-none text-slate-800">
          {!! $sanitize($job->qualifications) !!}
        </div>
      </div>
      @endif

      {{-- Benefit --}}
      @if(filled($job->benefits))
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Benefit</h2>
        <div class="prose max-w-none text-slate-800">
          {!! $sanitize($job->benefits) !!}
        </div>
      </div>
      @endif

      {{-- Kata Kunci & Keahlian --}}
      @php
        $tags = collect($job->tags ?? [])
          ->when(is_string($job->tags ?? null), fn($c) => collect(preg_split('/\s*,\s*/', $job->tags, -1, PREG_SPLIT_NO_EMPTY)))
          ->filter()->unique()->values();
      @endphp
      @if($keywords->count() || $skills->count() || $tags->count())
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <h2 class="text-lg font-semibold text-slate-900">Kata Kunci & Keahlian</h2>

        @if($keywords->count())
          <div class="mt-2">
            <div class="text-xs text-slate-500 mb-1">Keywords</div>
            <div class="flex flex-wrap gap-2">
              @foreach($keywords as $kw)
                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">{{ e($kw) }}</span>
              @endforeach
            </div>
          </div>
        @endif

        @if($skills->count())
          <div class="mt-3">
            <div class="text-xs text-slate-500 mb-1">Skills</div>
            <div class="flex flex-wrap gap-2">
              @foreach($skills as $sk)
                <span class="rounded-full bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">{{ e($sk) }}</span>
              @endforeach
            </div>
          </div>
        @endif

        @if($tags->count())
          <div class="mt-3">
            <div class="text-xs text-slate-500 mb-1">Tags</div>
            <div class="flex flex-wrap gap-2">
              @foreach($tags as $t)
                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">{{ e($t) }}</span>
              @endforeach
            </div>
          </div>
        @endif
      </div>
      @endif
    </div>

    {{-- RIGHT: Progress / Site / Profil Kandidat --}}
    <aside class="space-y-6">
      {{-- Progres Lamaran --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-5 md:p-6">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900">Progres Lamaran Kamu</h3>

            {{-- ADMIN stage controls --}}
            @if($myApp && $isAdmin && Route::has('admin.applications.move') && ($overall !== 'rejected'))
              @php $canPrev = filled($prevKey); $canNext = filled($nextKey); @endphp
              <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('admin.applications.move', $myApp) }}"
                      onsubmit="return {{ $canPrev ? 'confirm' : '(function(){return false;})' }}('Kembalikan tahap ke: {{ $pretty[$prevKey] ?? '—' }} ?')">
                  @csrf
                  <input type="hidden" name="to" value="{{ $canPrev ? e($prevKey) : '' }}">
                  <button type="submit" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-slate-900 hover:bg-slate-50 disabled:opacity-40"
                          {{ $canPrev ? '' : 'disabled' }}
                          title="{{ $canPrev ? 'Kembali ke: '.($pretty[$prevKey] ?? '—') : 'Tidak bisa mundur' }}">
                    <svg class="h-4 w-4" aria-hidden="true"><use href="#i-chevron-left"/></svg>
                  </button>
                </form>

                <form method="POST" action="{{ route('admin.applications.move', $myApp) }}"
                      onsubmit="return {{ $canNext ? 'confirm' : '(function(){return false;})' }}('Lanjutkan tahap ke: {{ $pretty[$nextKey] ?? '—' }} ?')">
                  @csrf
                  <input type="hidden" name="to" value="{{ $canNext ? e($nextKey) : '' }}">
                  <button type="submit" class="rounded-lg border border-slate-200 px-2.5 py-1.5 text-white disabled:opacity-40"
                          style="background: {{ $BLUE }}"
                          {{ $canNext ? '' : 'disabled' }}
                          title="{{ $canNext ? 'Lanjut ke: '.($pretty[$nextKey] ?? '—') : 'Sudah tahap terakhir' }}">
                    <svg class="h-4 w-4" aria-hidden="true"><use href="#i-chevron-right"/></svg>
                  </button>
                </form>
              </div>
            @endif
          </div>

          @guest
            <p class="mt-2 text-sm text-slate-600">Masuk untuk melihat timeline lamaran pribadi.</p>
            <a href="{{ route('login') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-900 hover:bg-slate-50">
              Login
            </a>
          @else
            @if(!$myApp)
              <div class="mt-3 rounded-xl border border-slate-200 px-4 py-3 text-sm">
                Belum ada lamaran untuk posisi ini.
                @if(($job->status ?? 'draft')==='open')
                <form method="POST" action="{{ route('applications.store',$job) }}" class="mt-3">@csrf
                  <button class="w-full rounded-lg px-3 py-2 text-sm font-semibold text-white"
                          style="background: linear-gradient(90deg, {{ $BLUE }} 0%, {{ $RED }} 100%);">
                    Lamar Sekarang
                  </button>
                </form>
                @endif
              </div>
            @else
              {{-- Progress bar --}}
              @php $pct = $progressPct(); @endphp
              <div class="mt-3">
                <div class="flex items-center justify-between text-xs text-slate-600">
                  <span>Progress</span><span>{{ $pct }}%</span>
                </div>
                <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                  <div class="h-full rounded-full"
                       style="width: {{ $pct }}%; background: {{ ($overall==='rejected') ? $RED : $BLUE }}"></div>
                </div>
              </div>

              {{-- Timeline --}}
              <div class="mt-5 relative">
                <div class="absolute right-3 top-0 bottom-0 w-0.5 bg-slate-200"></div>
                <div class="space-y-3">
                  @foreach($stageOrder as $key)
                    @php
                      $isNow  = ($key === $currKey) && ($overall!=='rejected');
                      $done   = in_array($key,$visited,true) && !$isNow;
                      $muted  = !$done && !$isNow;
                      $dotBg  = $done ? '#16a34a' : ($isNow ? $BLUE : '#f59e0b');

                      $st = $stageMap[$key] ?? null;
                      $ts = $done ? ($st->updated_at ?? $st->created_at ?? null)
                           : ($isNow ? ($st->created_at ?? null) : null);

                      $waktuTampil = $ts ? $formatTs($ts) : null;
                      $who = $st ? $actorName($st) : null;
                    @endphp
                    <div class="relative pr-12">
                      <span class="absolute right-0 top-1 grid h-4 w-4 place-items-center rounded-full ring-4 ring-white" style="background: {{ $dotBg }}"></span>
                      <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                          <div class="text-sm font-medium {{ $muted ? 'text-slate-700' : 'text-slate-900' }}">{{ e($pretty[$key]) }}</div>
                          <div class="text-xs text-slate-500">
                            @if($done) Selesai
                            @elseif($isNow) Sedang diproses
                            @else Menunggu giliran
                            @endif
                          </div>

                          @if($waktuTampil || $who)
                            <div class="mt-1 text-[11px] text-slate-500">
                              @if($waktuTampil)
                                <div class="inline-flex items-center gap-1">
                                  <svg class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><use href="#i-clock"/></svg>
                                  <span>Waktu: {{ e($waktuTampil) }}</span>
                                </div>
                              @endif
                              @if($who)
                                <div>Diubah oleh: <span class="font-medium text-slate-700">{{ e($who) }}</span></div>
                              @endif
                              @if(!empty($st?->notes))
                                <div class="mt-0.5">Catatan: <span class="text-slate-700">{{ e($st->notes) }}</span></div>
                              @endif
                            </div>
                          @endif
                        </div>
                        @if($isNow)
                          <span class="shrink-0 rounded-full bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">Aktif</span>
                        @elseif($done)
                          <span class="shrink-0 rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Selesai</span>
                        @else
                          <span class="shrink-0 rounded-full bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Berikutnya</span>
                        @endif
                      </div>
                    </div>
                  @endforeach

                  @if($overall==='rejected')
                    @php
                      $rejectTime = $latestStage ? $formatTs($latestStage->updated_at ?? $latestStage->created_at) : ($myApp ? $formatTs($myApp->updated_at ?? $myApp->created_at) : null);
                      $rejectBy   = $latestStage ? $actorName($latestStage) : ($lastChangedBy ?? null);
                    @endphp
                    <div class="relative pr-12">
                      <span class="absolute right-0 top-1 grid h-4 w-4 place-items-center rounded-full ring-4 ring-white" style="background: {{ $RED }}"></span>
                      <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                          <div class="text-sm font-medium text-slate-900">Keputusan</div>
                          <div class="text-xs text-slate-500">Lamaran tidak melanjutkan proses.</div>
                          @if($rejectTime || $rejectBy)
                            <div class="mt-1 text-[11px] text-slate-500">
                              @if($rejectTime)
                                <div class="inline-flex items-center gap-1">
                                  <svg class="h-3.5 w-3.5 text-slate-400" aria-hidden="true"><use href="#i-clock"/></svg>
                                  <span>Waktu: {{ e($rejectTime) }}</span>
                                </div>
                              @endif
                              @if($rejectBy)
                                <div>Diubah oleh: <span class="font-medium text-slate-700">{{ e($rejectBy) }}</span></div>
                              @endif
                            </div>
                          @endif
                        </div>
                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">Ditutup</span>
                      </div>
                    </div>
                  @endif
                </div>
              </div>

              {{-- Meta lamaran --}}
              <div class="mt-5 grid gap-2 text-xs text-slate-600">
                <div class="inline-flex items-center gap-2">
                  <svg class="h-4 w-4 text-slate-500" aria-hidden="true"><use href="#i-clock"/></svg>
                  Diajukan: {{ $myApp?->created_at ? e($formatTs($myApp->created_at)) : '—' }}
                </div>
                <div class="inline-flex items-center gap-2">
                  <svg class="h-4 w-4 text-slate-500" aria-hidden="true"><use href="#i-brief"/></svg>
                  Status keseluruhan:
                  @php
                    $overallText = strtoupper($overall ?? 'IN_PROGRESS');
                    $overallClass =
                      $overall==='hired' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' :
                      ($overall==='rejected' ? 'bg-slate-100 text-slate-700 ring-slate-200' :
                      'bg-blue-50 text-blue-700 ring-blue-200');
                  @endphp
                  <span class="ml-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $overallClass }}">
                    {{ e($overallText) }}
                  </span>
                </div>

                @if($lastChangedAt)
                  <div class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 text-slate-500" aria-hidden="true"><use href="#i-clock"/></svg>
                    Terakhir diubah: {{ e($formatTs($lastChangedAt)) }}
                    @if($lastChangedBy)
                      <span>• oleh <span class="font-medium text-slate-700">{{ e($lastChangedBy) }}</span></span>
                    @endif
                  </div>
                @endif
              </div>
            @endif
          @endguest
        </div>
      </div>

      {{-- Tentang Site --}}
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <h3 class="text-base font-semibold text-slate-900">Tentang Site</h3>
        @if($job->site)
          @php
            $s = $job->site;
            $tz = $s->timezone ?: data_get($s->meta, 'timezone');
            $addr = $s->address ?: data_get($s->meta, 'address');
          @endphp
          <dl class="mt-3 grid grid-cols-3 gap-y-2 text-sm">
            <dt class="text-slate-500">Kode</dt><dd class="col-span-2 text-slate-800">{{ e($s->code) }}</dd>
            <dt class="text-slate-500">Nama</dt><dd class="col-span-2 text-slate-800">{{ e($s->name) }}</dd>
            <dt class="text-slate-500">Region</dt><dd class="col-span-2 text-slate-800">{{ e($s->region ?: '—') }}</dd>
            <dt class="text-slate-500">Timezone</dt><dd class="col-span-2 text-slate-800">{{ e($tz ?: '—') }}</dd>
            @if($addr)
              <dt class="text-slate-500">Alamat</dt><dd class="col-span-2 text-slate-800">{{ e($addr) }}</dd>
            @endif
          </dl>
          <div class="mt-3 flex items-center gap-3">
            <a href="{{ route('sites.show', $s) }}" class="text-sm text-blue-700 hover:underline">Lihat detail site</a>
            @if($isAdmin && Route::has('admin.sites.show'))
              <span class="text-slate-300">•</span>
              <a href="{{ route('admin.sites.show', $s) }}" class="text-sm text-slate-700 hover:underline">Admin view</a>
            @endif
          </div>
        @else
          <p class="mt-2 text-sm text-slate-600">Site belum ditautkan.</p>
        @endif
      </div>

      {{-- PROFIL KANDIDAT (di bawah Site) --}}
      @auth
      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
        <div class="flex items-center justify-between">
          <h3 class="text-base font-semibold text-slate-900">Profil Kandidat Kamu</h3>
          @if(Route::has('candidate.profiles.edit'))
            <a href="{{ route('candidate.profiles.edit', $job) }}" class="text-sm text-blue-700 hover:underline">Ubah</a>
          @endif
        </div>

        @if($meProfile)
          <dl class="mt-3 grid grid-cols-3 gap-y-2 text-sm">
            <dt class="text-slate-500">Nama</dt>
            <dd class="col-span-2 text-slate-800">{{ e($meProfile->full_name ?? auth()->user()->name) }}</dd>

            <dt class="text-slate-500">Pendidikan</dt>
            <dd class="col-span-2 text-slate-800">
              {{ e($meProfile->last_education ?? '—') }}
              @if($meProfile->education_major) · {{ e($meProfile->education_major) }} @endif
            </dd>

            <dt class="text-slate-500">Kontak</dt>
            <dd class="col-span-2 text-slate-800">
              {{ e($meProfile->phone ?? '—') }} @if($meProfile->email) · {{ e($meProfile->email) }} @endif
            </dd>
          </dl>

          @if($meProfile->cv_path)
            <div class="mt-3 text-sm">
              <a class="text-blue-700 hover:underline" href="{{ Storage::disk('public')->url($meProfile->cv_path) }}" target="_blank" rel="noopener">Lihat CV</a>
            </div>
          @endif

          <div class="mt-4">
            @if(Route::has('candidate.profiles.edit'))
              <a href="{{ route('candidate.profiles.edit', $job) }}" class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50">
                Perbarui Profil
              </a>
            @endif
          </div>
        @else
          <p class="mt-2 text-sm text-slate-600">Profil kandidat belum diisi.</p>
          @if(Route::has('candidate.profiles.edit'))
            <a href="{{ route('candidate.profiles.edit', $job) }}" class="mt-3 inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white">
              Lengkapi Sekarang
            </a>
          @endif
        @endif
      </div>
      @endauth

      {{-- (Opsional) Jobs serupa --}}
      @isset($relatedJobs)
        @if($relatedJobs->count())
          <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-5 md:p-6">
            <h3 class="text-base font-semibold text-slate-900">Lowongan Serupa</h3>
            <ul class="mt-3 space-y-2">
              @foreach($relatedJobs as $r)
                <li class="flex items-center justify-between gap-3">
                  <div class="min-w-0">
                    <a href="{{ route('jobs.show', $r) }}" class="font-medium text-slate-900 hover:underline truncate">{{ e($r->title) }}</a>
                    <div class="text-xs text-slate-500">{{ e($r->division ?: '—') }} · {{ e($r->site?->code ?: '—') }}</div>
                  </div>
                  <a href="{{ route('jobs.show', $r) }}" class="text-sm text-blue-700 hover:underline shrink-0">Lihat</a>
                </li>
              @endforeach
            </ul>
          </div>
        @endif
      @endisset
    </aside>
  </div>
</div>

{{-- ICON SPRITE --}}
@once
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-10a7 7 0 10-14 0c0 5.65 7 10 7 10z"/>
    <circle cx="12" cy="11" r="2" stroke-width="2"/>
  </symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <circle cx="12" cy="12" r="9" stroke-width="2"/>
    <path stroke-width="2" stroke-linecap="round" d="M12 7v5l3 2"/>
  </symbol>
  <symbol id="i-brief" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="2" d="M3 8a2 2 0 012-2h14a2 2 0 012 2v9a3 3 0 01-3 3H6a3 3 0 01-3-3V8z"/>
    <path stroke-width="2" d="M9 6a3 3 0 013-3h0a3 3 0 013 3v0"/>
  </symbol>
  <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
  </symbol>
  <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
  </symbol>
</svg>
@endonce

{{-- JSON-LD Breadcrumbs (SEO) --}}
<script type="application/ld+json">{!! json_encode($breadcrumbLd, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>

{{-- JSON-LD JobPosting (aman & minimal) --}}
@php
  $jobLd = [
    '@context' => 'https://schema.org',
    '@type' => 'JobPosting',
    'title' => (string) $job->title,
    'description' => strip_tags($sanitize($job->description ?? '') ?? ''),
    'datePosted' => optional($job->created_at)->toIso8601String(),
    'validThrough' => $closingAt ? Carbon::parse($closingAt)->toIso8601String() : null,
    'employmentType' => strtoupper($job->employment_type ?? 'FULL_TIME'),
    'hiringOrganization' => [
      '@type' => 'Organization',
      'name' => (string) config('app.name'),
    ],
    'jobLocation' => [
      '@type' => 'Place',
      'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => optional($job->site)->address,
        'addressRegion' => optional($job->site)->region,
        'addressCountry' => 'ID',
      ],
    ],
  ];
  if($job->salary_min || $job->salary_max) {
    $jobLd['baseSalary'] = [
      '@type' => 'MonetaryAmount',
      'currency' => $job->currency ?: 'IDR',
      'value' => [
        '@type' => 'QuantitativeValue',
        'minValue' => $job->salary_min ?: null,
        'maxValue' => $job->salary_max ?: null,
        'unitText' => $job->salary_period ?: 'MONTH',
      ],
    ];
  }
@endphp
<script type="application/ld+json">{!! json_encode(array_filter($jobLd, fn($v) => $v !== null), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
@endsection
