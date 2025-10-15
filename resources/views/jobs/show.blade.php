{{-- resources/views/jobs/show.blade.php --}}
@extends('layouts.app', ['title' => $job->title])

@php
  $BLUE  = '#1d4ed8';   // biru
  $RED   = '#dc2626';   // merah
  $BORD  = '#e5e7eb';   // gray-200

  /** @var \App\Models\JobApplication|null $myApp */
  $myApp = auth()->check()
      ? $job->applications()->where('user_id', auth()->id())->with('stages')->latest()->first()
      : null;

  // urutan & label tahapan
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

  $overall = $myApp?->overall_status ?? null;
  $currKey = strtolower($myApp?->current_stage ?? 'applied');
  $visited = collect($myApp?->stages ?? [])->pluck('stage_key')->map(fn($v)=>strtolower($v))->all();

  $idxNow = array_search($currKey, $stageOrder, true);
  if ($idxNow === false) $idxNow = 0;
  $prevKey = $idxNow > 0 ? $stageOrder[$idxNow-1] : null;
  $nextKey = $idxNow < count($stageOrder)-1 ? $stageOrder[$idxNow+1] : null;

  $progressPct = function() use ($myApp,$stageOrder){
    if(!$myApp) return 0;
    $key = strtolower($myApp->current_stage ?? 'applied');
    $idx = array_search($key,$stageOrder,true); if($idx===false) $idx=0;
    $max = max(count($stageOrder)-1,1);
    if(($myApp->overall_status ?? '')==='rejected'){
      return min(100, max(40,(int)round($idx/$max*100)));
    }
    return (int)round($idx/$max*100);
  };

  // cek role admin secara fleksibel (pakai kolom role biasa atau spatie)
  $isAdmin = auth()->check() && (
    (method_exists(auth()->user(), 'hasAnyRole') && auth()->user()->hasAnyRole(['hr','superadmin']))
    || in_array(auth()->user()->role ?? null, ['hr','superadmin'], true)
  );
@endphp

@section('content')

{{-- ===== Icons (inline sekali) ===== --}}
<svg xmlns="http://www.w3.org/2000/svg" class="hidden">
  <symbol id="i-brief" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" d="M3 7h18v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7Z"/><path d="M8 7V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1" stroke-width="1.8"/>
  </symbol>
  <symbol id="i-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <circle cx="12" cy="12" r="9" stroke-width="2"/><path d="M12 7v5l3 2" stroke-width="2" stroke-linecap="round"/>
  </symbol>
  <symbol id="i-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <path d="M12 21s7-4.35 7-10a7 7 0 1 0-14 0c0 5.65 7 10 7 10Z" stroke-width="2"/><circle cx="12" cy="11" r="2.5" stroke-width="2"/>
  </symbol>
  <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <polyline points="15 18 9 12 15 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
  <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
    <polyline points="9 18 15 12 9 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </symbol>
</svg>

<div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 lg:px-8 py-6">

  {{-- Header bar --}}
  <div class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="flex h-2 w-full">
      <div class="flex-1" style="background: {{ $BLUE }}"></div>
      <div class="w-32" style="background: {{ $RED }}"></div>
    </div>

    <div class="p-5 md:p-6">
      <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div class="min-w-0">
          <h1 class="truncate text-3xl font-semibold text-slate-900">{{ $job->title }}</h1>
          <div class="mt-1 text-sm text-slate-600">
            {{ $job->division ?? '—' }} · {{ $job->site?->code ?? $job->site_code ?? '—' }}
          </div>
        </div>

        {{-- CTA Apply / State --}}
        <div class="flex items-center gap-2">
          <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset
            {{ $job->status==='open' ? 'bg-blue-50 text-blue-700 ring-blue-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
            STATUS: {{ strtoupper($job->status) }}
          </span>

          @auth
            @if($job->status==='open' && !$myApp)
              <form method="POST" action="{{ route('applications.store',$job) }}">
                @csrf
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

  {{-- Main grid --}}
  <div class="mt-6 grid gap-6 lg:grid-cols-3">
    {{-- Left: deskripsi --}}
    <div class="lg:col-span-2 rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
      <div class="p-5 md:p-6">
        <div class="grid gap-4 sm:grid-cols-3">
          <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
            <div class="text-xs text-slate-500">Tipe</div>
            <div class="mt-1 inline-flex items-center rounded bg-blue-700 px-2 py-1 text-[11px] font-semibold text-white">
              {{ strtoupper($job->employment_type) }}
            </div>
          </div>
          <div class="rounded-xl border bg-white px-4 py-3" style="border-color: {{ $BORD }}">
            <div class="text-xs text-slate-500">Openings</div>
            <div class="mt-1 text-xl font-semibold text-slate-900">{{ (int) $job->openings }}</div>
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
          {!! nl2br(e($job->description)) !!}
        </div>
      </div>
    </div>

    {{-- Right: Timeline / Ringkasan Lamaran --}}
    <aside>
      <div class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
        <div class="p-5 md:p-6">
          <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900">Progres Lamaran Kamu</h3>

            {{-- ADMIN CONTROLS: next/prev stage (POST-only) --}}
            @if($myApp && $isAdmin && Route::has('admin.applications.move') && ($overall !== 'rejected'))
              <div class="flex items-center gap-2">
                {{-- Prev --}}
                <form method="POST"
                      action="{{ route('admin.applications.move', $myApp) }}"
                      onsubmit="return confirm('Kembalikan tahap ke: {{ $pretty[$prevKey] ?? '—' }} ?')">
                  @csrf
                  <input type="hidden" name="to" value="{{ $prevKey }}">
                  <button type="submit" class="rounded-lg border px-2.5 py-1.5 text-slate-900 hover:bg-slate-50 disabled:opacity-40"
                          style="border-color: {{ $BORD }}" {{ $prevKey ? '' : 'disabled' }}
                          title="{{ $prevKey ? 'Kembali ke: '.$pretty[$prevKey] : 'Tidak bisa mundur' }}">
                    <svg class="h-4 w-4"><use href="#i-chevron-left"/></svg>
                  </button>
                </form>
                {{-- Next --}}
                <form method="POST"
                      action="{{ route('admin.applications.move', $myApp) }}"
                      onsubmit="return confirm('Lanjutkan tahap ke: {{ $pretty[$nextKey] ?? '—' }} ?')">
                  @csrf
                  <input type="hidden" name="to" value="{{ $nextKey }}">
                  <button type="submit" class="rounded-lg border px-2.5 py-1.5 text-white disabled:opacity-40"
                          style="border-color: {{ $BORD }}; background: {{ $BLUE }}"
                          {{ $nextKey ? '' : 'disabled' }}
                          title="{{ $nextKey ? 'Lanjut ke: '.$pretty[$nextKey] : 'Sudah tahap terakhir' }}">
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
                @if($job->status==='open')
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

              {{-- Timeline vertical kanan --}}
              <div class="mt-5 relative">
                <div class="absolute right-3 top-0 bottom-0 w-0.5" style="background:#e6e6e6"></div>
                <div class="space-y-3">
                  @foreach($stageOrder as $key)
                    @php
                      $isNow  = $key===$currKey && $overall!=='rejected';
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

                  @if(($overall ?? '')==='rejected')
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

              {{-- Meta --}}
              <div class="mt-5 grid gap-2 text-xs text-slate-600">
                <div class="inline-flex items-center gap-2">
                  <svg class="h-4 w-4 text-slate-500"><use href="#i-clock"/></svg>
                  Diajukan: {{ optional($myApp->created_at)->format('d M Y') }}
                </div>
                <div class="inline-flex items-center gap-2">
                  <svg class="h-4 w-4 text-slate-500"><use href="#i-brief"/></svg>
                  Status keseluruhan:
                  <span class="ml-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset
                    {{ $overall==='hired' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
                      : ($overall==='rejected' ? 'bg-slate-100 text-slate-700 ring-slate-200'
                      : 'bg-blue-50 text-blue-700 ring-blue-200') }}">
                    {{ strtoupper($overall) }}
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
