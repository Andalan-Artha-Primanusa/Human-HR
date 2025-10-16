{{-- resources/views/jobs/show.blade.php --}}
@extends('layouts.app', ['title' => $job->title])

@php
  $BLUE  = '#1d4ed8';
  $RED   = '#dc2626';
  $BORD  = '#e5e7eb';

  /** @var \App\Models\JobApplication|null $myApp */
  $myApp = auth()->check()
      ? $job->applications()->where('user_id', auth()->id())->with('stages')->latest()->first()
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

  // cek role admin (fleksibel, dukung spatie/tanpa spatie)
  $isAdmin = auth()->check() && (
    (method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['hr','superadmin']))
    || in_array(auth()->user()->role ?? null, ['hr','superadmin'], true)
  );

  // helper kecil
  $employmentPretty = [
    'fulltime' => 'Fulltime',
    'contract' => 'Contract',
    'intern'   => 'Intern',
  ];
@endphp

@section('content')
<div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8 py-6">

  {{-- HEADER: bar biru–merah + judul & CTA --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="flex h-2 w-full">
      <div class="flex-1" style="background: {{ $BLUE }}"></div>
      <div class="w-32" style="background: {{ $RED }}"></div>
    </div>

    <div class="p-5 md:p-6">
      <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div class="min-w-0">
          <h1 class="truncate text-3xl font-semibold text-slate-900">
            {{ $job->title ?? '—' }}
          </h1>
          <div class="mt-1 text-sm text-slate-600">
            {{ $job->division ?: '—' }} ·
            {{-- ambil dari relasi site --}}
            {{ $job->site?->code ? ($job->site->code . ' — ' . ($job->site->name ?? '')) : '—' }}
          </div>
        </div>

        <div class="flex items-center gap-2">
          {{-- status badge --}}
          <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset
            {{ $job->status==='open' ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
            STATUS: {{ strtoupper($job->status ?? 'draft') }}
          </span>

          {{-- Admin quick actions (opsional) --}}
          @if($isAdmin)
            @if(Route::has('admin.jobs.edit'))
              <a href="{{ route('admin.jobs.edit', $job) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50"
                 style="border-color: {{ $BORD }}">Edit</a>
            @endif
            @if(Route::has('admin.applications.index'))
              <a href="{{ route('admin.applications.index', ['job' => $job->id]) }}" class="rounded-lg border px-3 py-2 text-sm hover:bg-slate-50"
                 style="border-color: {{ $BORD }}">Kandidat</a>
            @endif
          @endif

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

  {{-- MAIN GRID --}}
  <div class="mt-6 grid gap-6 lg:grid-cols-3">
    {{-- LEFT: detail & deskripsi --}}
    <div class="lg:col-span-2 rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
      <div class="p-5 md:p-6">
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

        <hr class="my-5 border-t" style="border-color: {{ $BORD }}">

        <h2 class="text-lg font-semibold text-slate-900">Deskripsi Pekerjaan</h2>
        <div class="prose max-w-none prose-p:my-2 prose-li:my-1 text-slate-800">
          @if(filled($job->description))
            {!! nl2br(e($job->description)) !!}
          @else
            <p class="text-slate-500">Belum ada deskripsi yang dituliskan.</p>
          @endif
        </div>
      </div>
    </div>

    {{-- RIGHT: timeline / ringkasan lamaran --}}
    <aside>
      <div class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
        <div class="p-5 md:p-6">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900">Progres Lamaran Kamu</h3>

            {{-- ADMIN stage controls (POST-only, hanya jika ada $myApp dan belum rejected) --}}
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
    </aside>
  </div>
</div>
@endsection
