{{-- resources/views/jobs/show.blade.php --}}
@extends('layouts.app', ['title' => $job->title])

@php
  $BLUE  = '#1d4ed8';
  $RED   = '#dc2626';
  $BORD  = '#e5e7eb';

  // ==== DECODE HELPER (untuk konten yang tersimpan sebagai HTML entities) ====
  $decode = function ($v) {
      return is_string($v) ? html_entity_decode($v, ENT_QUOTES | ENT_HTML5, 'UTF-8') : $v;
  };

  /** @var \App\Models\JobApplication|null $myApp */
  $myApp = auth()->check()
      ? $job->applications()->where('user_id', auth()->id())->with('stages')->latest()->first()
      : null;

  /** @var \App\Models\CandidateProfile|null $meProfile */
  $meProfile = auth()->check()
      ? \App\Models\CandidateProfile::where('user_id', auth()->id())->first()
      : null;

  // urutan & label tahapan (sinkron dg controller)
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

  $overallRaw = $myApp?->overall_status;
  $overall    = $overallRaw ? strtolower($overallRaw) : 'in_progress';

  $currRaw = strtolower($myApp?->current_stage ?? 'applied');
  $currKey = in_array($currRaw, $stageOrder, true) ? $currRaw : 'applied';

  $visited = collect($myApp?->stages ?? [])
      ->pluck('stage_key')->map(fn($v) => strtolower($v))
      ->filter(fn($v) => in_array($v, $stageOrder, true))
      ->unique()->push($currKey)->unique()->values()->all();

  $idxNow  = array_search($currKey, $stageOrder, true);
  $idxNow  = ($idxNow === false) ? 0 : $idxNow;
  $prevKey = $idxNow > 0 ? $stageOrder[$idxNow-1] : null;
  $nextKey = $idxNow < count($stageOrder)-1 ? $stageOrder[$idxNow+1] : null;

  $progressPct = function() use ($myApp,$stageOrder,$overall,$currKey){
    if(!$myApp) return 0;
    $idx = array_search($currKey,$stageOrder,true); if($idx===false) $idx=0;
    $max = max(count($stageOrder)-1,1);
    if($overall==='rejected'){
      return min(100, max(40,(int)round($idx/$max*100)));
    }
    return (int)round($idx/$max*100);
  };

  // role admin (spatie/tanpa spatie)
  $isAdmin = auth()->check() && (
    (method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['hr','superadmin']))
    || in_array(auth()->user()->role ?? null, ['hr','superadmin'], true)
  );

  $employmentPretty = [
    'fulltime' => 'Fulltime',
    'contract' => 'Contract',
    'intern'   => 'Intern',
    'parttime' => 'Part-time',
    'freelance'=> 'Freelance',
  ];

  // helper tampilan gaji
  $fmtMoney = function($n, $cur = 'IDR') {
    if(!is_numeric($n)) return null;
    $num = number_format((float)$n, 0, ',', '.');
    return ($cur ?: 'IDR').' '.$num;
  };

  // tanggal penutupan lowongan (opsional)
  $closingAt = $job->closing_at ?? null;
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8 py-6">

  {{-- BREADCRUMB --}}
  <nav class="mb-3 text-sm text-slate-500">
    <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Dashboard</a>
    <span class="mx-1 text-slate-300">/</span>
    <a href="{{ route('jobs.index') }}" class="hover:text-slate-700">Jobs</a>
    <span class="mx-1 text-slate-300">/</span>
    <span class="text-slate-700">{{ $job->title }}</span>
  </nav>

  {{-- HEADER --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="flex h-2 w-full">
      <div class="flex-1" style="background: {{ $BLUE }}"></div>
      <div class="w-32" style="background: {{ $RED }}"></div>
    </div>

    <div class="p-5 md:p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
        <div class="min-w-0">
          <h1 class="truncate text-3xl font-semibold text-slate-900">{{ $job->title ?? '—' }}</h1>
          <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-slate-600">
            <span class="inline-flex items-center gap-1">
              <svg class="h-4 w-4 text-slate-500"><use href="#i-brief"/></svg>
              {{ $job->division ?: '—' }}
            </span>
            <span class="text-slate-300">•</span>
            <span class="inline-flex items-center gap-1">
              <svg class="h-4 w-4 text-slate-500"><use href="#i-pin"/></svg>
              {{ $job->site?->code ? ($job->site->code . ' — ' . ($job->site->name ?? '')) : '—' }}
            </span>
            @if($closingAt)
              <span class="text-slate-300">•</span>
              <span class="inline-flex items-center gap-1">
                <svg class="h-4 w-4 text-slate-500"><use href="#i-clock"/></svg>
                Tutup: {{ \Illuminate\Support\Carbon::parse($closingAt)->format('d M Y') }}
              </span>
            @endif
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset
            {{ $job->status==='open' ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
            STATUS: {{ strtoupper($job->status ?? 'draft') }}
          </span>

          {{-- Admin quick actions --}}
          @if($isAdmin)
            @if(Route::has('admin.jobs.edit'))
              <a href="{{ route('admin.jobs.edit', $job) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50"
                 style="border-color: {{ $BORD }}">Edit</a>
            @endif
            @if(Route::has('admin.applications.index'))
              <a href="{{ route('admin.applications.index', ['job' => $job->id]) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50"
                 style="border-color: {{ $BORD }}">Kandidat</a>
            @endif
            @if(Route::has('admin.jobs.toggle'))
              <form method="POST" action="{{ route('admin.jobs.toggle', $job) }}"
                    onsubmit="return confirm('Ubah status lowongan?');">
                @csrf @method('PATCH')
                <button type="submit" class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50"
                        style="border-color: {{ $BORD }}">
                  {{ ($job->status === 'open') ? 'Tutup' : 'Buka' }}
                </button>
              </form>
            @endif
          @endif

          {{-- Aksi kandidat: Edit/Lengkapi Profil --}}
          @auth
            @if(Route::has('candidate.profiles.edit'))
              <a href="{{ route('candidate.profiles.edit', $job) }}"
                 class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50"
                 style="border-color: {{ $BORD }}">
                {{ $meProfile ? 'Perbarui Profil' : 'Lengkapi Profil' }}
              </a>
            @endif
          @endauth

          {{-- CTA pelamar --}}
          @auth
            @if(($job->status ?? 'draft') === 'open' && !$myApp)
              <form method="POST" action="{{ route('applications.store',$job) }}">@csrf
                <button class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background: {{ $BLUE }}">Lamar Sekarang</button>
              </form>
            @elseif($myApp)
              <a href="{{ route('applications.mine') }}"
                 class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background: {{ $RED }}">
                Lihat Progres
              </a>
            @else
              <button disabled class="rounded-lg px-4 py-2 text-sm font-semibold text-white opacity-60" style="background: {{ $RED }}">Tutup</button>
            @endif
          @else
            <a class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background: {{ $BLUE }}"
               href="{{ route('login') }}">Login untuk Melamar</a>
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
      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <div class="grid gap-4 sm:grid-cols-3">
          <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
            <div class="text-xs text-slate-500">Tipe</div>
            <div class="mt-1 inline-flex items-center rounded bg-blue-700 px-2 py-1 text-[11px] font-semibold text-white">
              {{ $employmentPretty[$job->employment_type] ?? strtoupper($job->employment_type ?? '—') }}
            </div>
          </div>
          <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
            <div class="text-xs text-slate-500">Openings</div>
            <div class="mt-1 text-xl font-semibold text-slate-900">{{ (int) ($job->openings ?? 1) }}</div>
          </div>
          <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
            <div class="text-xs text-slate-500">Lokasi</div>
            <div class="mt-1 inline-flex items-center gap-1 text-slate-800">
              <svg class="h-4 w-4 text-slate-500"><use href="#i-pin"/></svg>
              {{ $job->site?->name ?? $job->site?->code ?? '—' }}
            </div>
          </div>
        </div>

        {{-- Gaji (opsional) --}}
        @if($job->salary_min || $job->salary_max || $job->currency)
          <div class="mt-4 rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
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
                {{ $cur }}
              @endif
              @if(!empty($job->salary_period))
                <span class="text-slate-500 text-sm">/ {{ $job->salary_period }}</span>
              @endif
            </div>
          </div>
        @endif
      </div>

      {{-- Deskripsi (render HTML dari editor) --}}
      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h2 class="text-lg font-semibold text-slate-900">Deskripsi Pekerjaan</h2>
        <div class="prose max-w-none text-slate-800">
          <style>
            .prose ul{list-style:disc;padding-left:1.25rem}
            .prose ol{list-style:decimal;padding-left:1.25rem}
            .prose li{margin:.25rem 0}
          </style>
          @if(filled($job->description))
            {!! $decode($job->description) !!}
          @else
            <p class="text-slate-500">Belum ada deskripsi yang dituliskan.</p>
          @endif
        </div>
      </div>

      {{-- Tanggung Jawab --}}
      @if(filled($job->responsibilities))
      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h2 class="text-lg font-semibold text-slate-900">Tanggung Jawab</h2>
        <div class="prose max-w-none text-slate-800">
          {!! $decode($job->responsibilities) !!}
        </div>
      </div>
      @endif

      {{-- Kualifikasi --}}
      @if(filled($job->qualifications))
      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h2 class="text-lg font-semibold text-slate-900">Kualifikasi</h2>
        <div class="prose max-w-none text-slate-800">
          {!! $decode($job->qualifications) !!}
        </div>
      </div>
      @endif

      {{-- Benefit --}}
      @if(filled($job->benefits))
      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h2 class="text-lg font-semibold text-slate-900">Benefit</h2>
        <div class="prose max-w-none text-slate-800">
          {!! $decode($job->benefits) !!}
        </div>
      </div>
      @endif

      {{-- Skill / Tags --}}
      @php
        $tags = collect($job->tags ?? [])
          ->when(is_string($job->tags ?? null), fn($c) => collect(preg_split('/\s*,\s*/', $job->tags, -1, PREG_SPLIT_NO_EMPTY)))
          ->filter()->unique()->values();
      @endphp
      @if($tags->count())
        <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
          <h2 class="text-lg font-semibold text-slate-900">Keahlian</h2>
          <div class="mt-2 flex flex-wrap gap-2">
            @foreach($tags as $t)
              <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">{{ $t }}</span>
            @endforeach
          </div>
        </div>
      @endif
    </div>

    {{-- RIGHT: timeline / ringkasan & site --}}
    <aside class="space-y-6">
      {{-- Progres Lamaran --}}
      <div class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
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
                  <input type="hidden" name="to" value="{{ $canPrev ? $prevKey : '' }}">
                  <button type="submit" class="rounded-lg border px-2.5 py-1.5 text-slate-900 hover:bg-slate-50 disabled:opacity-40"
                          style="border-color: {{ $BORD }}" {{ $canPrev ? '' : 'disabled' }}
                          title="{{ $canPrev ? 'Kembali ke: '.$pretty[$prevKey] : 'Tidak bisa mundur' }}">
                    <svg class="h-4 w-4"><use href="#i-chevron-left"/></svg>
                  </button>
                </form>

                <form method="POST" action="{{ route('admin.applications.move', $myApp) }}"
                      onsubmit="return {{ $canNext ? 'confirm' : '(function(){return false;})' }}('Lanjutkan tahap ke: {{ $pretty[$nextKey] ?? '—' }} ?')">
                  @csrf
                  <input type="hidden" name="to" value="{{ $canNext ? $nextKey : '' }}">
                  <button type="submit" class="rounded-lg border px-2.5 py-1.5 text-white disabled:opacity-40"
                          style="border-color: {{ $BORD }}; background: {{ $BLUE }}"
                          {{ $canNext ? '' : 'disabled' }}
                          title="{{ $canNext ? 'Lanjut ke: '.$pretty[$nextKey] : 'Sudah tahap terakhir' }}">
                    <svg class="h-4 w-4"><use href="#i-chevron-right"/></svg>
                  </button>
                </form>
              </div>
            @endif
          </div>

          @guest
            <p class="mt-2 text-sm text-slate-600">Masuk untuk melihat timeline lamaran pribadi.</p>
            <a href="{{ route('login') }}" class="mt-3 inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm text-slate-900 hover:bg-slate-50" style="border-color: {{ $BORD }}">
              Login
            </a>
          @else
            @if(!$myApp)
              <div class="mt-3 rounded-xl border px-4 py-3 text-sm" style="border-color: {{ $BORD }}">
                Belum ada lamaran untuk posisi ini.
                @if(($job->status ?? 'draft')==='open')
                <form method="POST" action="{{ route('applications.store',$job) }}" class="mt-3">@csrf
                  <button class="w-full rounded-lg px-3 py-2 text-sm font-semibold text-white" style="background: {{ $BLUE }}">Lamar Sekarang</button>
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
                <div class="absolute right-3 top-0 bottom-0 w-0.5" style="background:#e6e6e6"></div>
                <div class="space-y-3">
                  @foreach($stageOrder as $key)
                    @php
                      $isNow  = ($key === $currKey) && ($overall!=='rejected');
                      $done   = in_array($key,$visited,true) && !$isNow;
                      $muted  = !$done && !$isNow;
                      $dotBg  = $done ? '#16a34a' : ($isNow ? $BLUE : '#f59e0b');
                    @endphp
                    <div class="relative pr-12">
                      <span class="absolute right-0 top-1 grid h-4 w-4 place-items-center rounded-full ring-4 ring-white" style="background: {{ $dotBg }}"></span>
                      <div class="flex items-start justify-between gap-3">
                        <div>
                          <div class="text-sm font-medium {{ $muted ? 'text-slate-700' : 'text-slate-900' }}">{{ $pretty[$key] }}</div>
                          <div class="text-xs text-slate-500">
                            @if($done) Selesai
                            @elseif($isNow) Sedang diproses
                            @else Menunggu giliran
                            @endif
                          </div>
                        </div>
                        @if($isNow)
                          <span class="rounded-full bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700 ring-1 ring-inset ring-blue-200">Aktif</span>
                        @elseif($done)
                          <span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Selesai</span>
                        @else
                          <span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Berikutnya</span>
                        @endif
                      </div>
                    </div>
                  @endforeach

                  @if($overall==='rejected')
                    <div class="relative pr-12">
                      <span class="absolute right-0 top-1 grid h-4 w-4 place-items-center rounded-full ring-4 ring-white" style="background: {{ $RED }}"></span>
                      <div class="flex items-start justify-between gap-3">
                        <div>
                          <div class="text-sm font-medium text-slate-900">Keputusan</div>
                          <div class="text-xs text-slate-500">Lamaran tidak melanjutkan proses.</div>
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
                  <svg class="h-4 w-4 text-slate-500"><use href="#i-clock"/></svg>
                  Diajukan: {{ optional($myApp->created_at)->format('d M Y') ?? '—' }}
                </div>
                <div class="inline-flex items-center gap-2">
                  <svg class="h-4 w-4 text-slate-500"><use href="#i-brief"/></svg>
                  Status keseluruhan:
                  @php
                    $overallText = strtoupper($overall ?? 'IN_PROGRESS');
                    $overallClass =
                      $overall==='hired' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' :
                      ($overall==='rejected' ? 'bg-slate-100 text-slate-700 ring-slate-200' :
                      'bg-blue-50 text-blue-700 ring-blue-200');
                  @endphp
                  <span class="ml-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset {{ $overallClass }}">
                    {{ $overallText }}
                  </span>
                </div>
              </div>
            @endif
          @endguest
        </div>
      </div>

      {{-- Tentang Site (link publik) --}}
      <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
        <h3 class="text-base font-semibold text-slate-900">Tentang Site</h3>
        @if($job->site)
          @php
            $s = $job->site;
            $tz = $s->timezone ?: data_get($s->meta, 'timezone');
            $addr = $s->address ?: data_get($s->meta, 'address');
          @endphp
          <dl class="mt-3 grid grid-cols-3 gap-y-2 text-sm">
            <dt class="text-slate-500">Kode</dt><dd class="col-span-2 text-slate-800">{{ $s->code }}</dd>
            <dt class="text-slate-500">Nama</dt><dd class="col-span-2 text-slate-800">{{ $s->name }}</dd>
            <dt class="text-slate-500">Region</dt><dd class="col-span-2 text-slate-800">{{ $s->region ?: '—' }}</dd>
            <dt class="text-slate-500">Timezone</dt><dd class="col-span-2 text-slate-800">{{ $tz ?: '—' }}</dd>
            @if($addr)
              <dt class="text-slate-500">Alamat</dt><dd class="col-span-2 text-slate-800">{{ $addr }}</dd>
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

      {{-- (Opsional) Jobs serupa --}}
      @isset($relatedJobs)
        @if($relatedJobs->count())
          <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
            <h3 class="text-base font-semibold text-slate-900">Lowongan Serupa</h3>
            <ul class="mt-3 space-y-2">
              @foreach($relatedJobs as $r)
                <li class="flex items-center justify-between gap-3">
                  <div class="min-w-0">
                    <a href="{{ route('jobs.show', $r) }}" class="font-medium text-slate-900 hover:underline truncate">{{ $r->title }}</a>
                    <div class="text-xs text-slate-500">{{ $r->division ?: '—' }} · {{ $r->site?->code ?: '—' }}</div>
                  </div>
                  <a href="{{ route('jobs.show', $r) }}" class="text-sm text-blue-700 hover:underline shrink-0">Lihat</a>
                </li>
              @endforeach
            </ul>
          </div>
        @endif
      @endisset
    </aside>

    {{-- === NEW: Panel ringkasan profil kandidat (di kolom kanan) === --}}
    @auth
      <aside class="lg:col-span-1 space-y-6">
        <div class="rounded-2xl border bg-white shadow-sm p-5 md:p-6" style="border-color: {{ $BORD }}">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900">Profil Kandidat Kamu</h3>
            @if(Route::has('candidate.profiles.edit'))
              <a href="{{ route('candidate.profiles.edit', $job) }}" class="text-sm text-blue-700 hover:underline">Ubah</a>
            @endif
          </div>

          @if($meProfile)
            <dl class="mt-3 grid grid-cols-3 gap-y-2 text-sm">
              <dt class="text-slate-500">Nama</dt>
              <dd class="col-span-2 text-slate-800">{{ $meProfile->full_name ?? auth()->user()->name }}</dd>

              <dt class="text-slate-500">Pendidikan</dt>
              <dd class="col-span-2 text-slate-800">
                {{ $meProfile->last_education ?? '—' }}
                @if($meProfile->education_major) · {{ $meProfile->education_major }} @endif
              </dd>

              <dt class="text-slate-500">Kontak</dt>
              <dd class="col-span-2 text-slate-800">{{ $meProfile->phone ?? '—' }} @if($meProfile->email) · {{ $meProfile->email }} @endif</dd>
            </dl>

            @if($meProfile->cv_path)
              <div class="mt-3 text-sm">
                <a class="text-blue-700 hover:underline" href="{{ Storage::disk('public')->url($meProfile->cv_path) }}" target="_blank">Lihat CV</a>
              </div>
            @endif

            <div class="mt-4">
              @if(Route::has('candidate.profiles.edit'))
                <a href="{{ route('candidate.profiles.edit', $job) }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-slate-50" style="border-color: {{ $BORD }}">
                  Perbarui Profil
                </a>
              @endif
            </div>
          @else
            <p class="mt-2 text-sm text-slate-600">Profil kandidat belum diisi.</p>
            @if(Route::has('candidate.profiles.edit'))
              <a href="{{ route('candidate.profiles.edit', $job) }}" class="mt-3 inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white">Lengkapi Sekarang</a>
            @endif
          @endif
        </div>
      </aside>
    @endauth
  </div>

  {{-- FOOTER meta --}}
  <div class="mt-6 flex flex-wrap items-center gap-3 text-xs text-slate-500">
    <span>Diposting: {{ optional($job->created_at)->format('d M Y') ?? '—' }}</span>
    <span>•</span>
    <span>Diubah: {{ optional($job->updated_at)->format('d M Y') ?? '—' }}</span>
    @if(isset($job->applications_count))
      <span>•</span>
      <span>Jumlah Pelamar: {{ $job->applications_count }}</span>
    @endif
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

{{-- JSON-LD JobPosting --}}
@php
  $jobLd = [
    '@context' => 'https://schema.org',
    '@type' => 'JobPosting',
    'title' => $job->title,
    'description' => strip_tags($decode($job->description ?? '')),
    'datePosted' => optional($job->created_at)->toIso8601String(),
    'validThrough' => $closingAt ? \Illuminate\Support\Carbon::parse($closingAt)->toIso8601String() : null,
    'employmentType' => strtoupper($job->employment_type ?? 'FULL_TIME'),
    'hiringOrganization' => [
      '@type' => 'Organization',
      'name' => config('app.name'),
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
