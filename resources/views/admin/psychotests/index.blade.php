@extends('layouts.app', [ 'title' => 'Admin · Psychotests' ])

@section('content')
  {{-- ===== Header ala bar biru–merah ===== --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm mb-5">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>
    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Psychotests</h1>
          <p class="text-sm text-slate-600">Daftar attempt psikotes kandidat.</p>
        </div>

        {{-- Filter Ringkas --}}
        @php
          $q      = $q      ?? request('q');
          $status = $status ?? request('status');
          $opts   = ['' => 'Semua', 'active' => 'Active', 'finished' => 'Finished'];
        @endphp
        <form method="GET" class="rounded-xl p-3 glass shadow-sm grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3">
          <input name="q" value="{{ $q }}" class="input col-span-2 md:col-span-1" placeholder="Cari kandidat / job…">
          <select name="status" class="input">
            @foreach($opts as $k => $v)
              <option value="{{ $k }}" @selected($status===$k)>{{ $v }}</option>
            @endforeach
          </select>
          <div class="flex gap-2">
            <button class="btn btn-primary w-full">Filter</button>
            @if(request()->filled('q') || request()->filled('status'))
              <a href="{{ route('admin.psychotests.index') }}" class="btn btn-ghost w-full md:w-auto">Reset</a>
            @endif
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- ===== Tabel ===== --}}
  <div class="card">
    <div class="card-body overflow-x-auto">
      @if(($attempts->count() ?? 0) > 0)
        <table class="table min-w-[980px]">
          <thead>
            <tr>
              <th class="th w-48">Tanggal</th>
              <th class="th w-56">Kandidat</th>
              <th class="th">Posisi</th>
              <th class="th w-56">Nama Tes</th>
              <th class="th w-24 text-center">Skor</th>
              <th class="th w-28 text-center">Status</th>
              <th class="th w-24"></th>
            </tr>
          </thead>
          <tbody>
            @foreach($attempts as $at)
              @php
                $start     = optional(\Illuminate\Support\Carbon::parse($at->started_at ?? $at->created_at));
                $finished  = $at->finished_at ? \Illuminate\Support\Carbon::parse($at->finished_at) : null;

                $application = $at->application ?? null;
                $candidate   = $application?->user?->name ?? '—';
                $jobTitle    = $application?->job?->title ?? '—';
                $siteCode    = $application?->job?->site?->code ?? null;

                // nama kolom test fleksibel (title/name/label/slug)
                $testObj   = $at->test ?? null;
                $testName  = $testObj->name ?? $testObj->title ?? $testObj->label ?? $testObj->slug ?? '—';

                $isActive  = (bool)($at->is_active ?? false);
                $badge     = $isActive ? 'badge-blue' : 'badge-green';
              @endphp
              <tr>
                <td class="td align-top">
                  <div class="font-medium text-slate-900">
                    {{ $start?->format('d M Y, H:i') ?? '—' }}
                    @if($finished)
                      <span class="text-slate-400">→ {{ $finished->format('H:i') }}</span>
                    @endif
                  </div>
                  <div class="text-xs text-slate-500">
                    {{ $finished ? $finished->diffForHumans() : ($start?->diffForHumans() ?? '—') }}
                  </div>
                </td>

                <td class="td align-top">
                  <div class="font-medium text-slate-900">{{ $candidate }}</div>
                  <div class="text-xs text-slate-500">#App {{ $at->application_id }}</div>
                </td>

                <td class="td align-top">
                  <div class="text-slate-900">{{ $jobTitle }}</div>
                  @if($siteCode)
                    <div class="text-xs text-slate-500">Site: {{ $siteCode }}</div>
                  @endif
                </td>

                <td class="td align-top">
                  {{ $testName }}
                </td>

                <td class="td align-top text-center">
                  {{ is_numeric($at->score ?? null) ? number_format((float)$at->score, 2) : '—' }}
                </td>

                <td class="td align-top text-center">
                  <span class="badge {{ $badge }}">{{ $isActive ? 'ACTIVE' : 'FINISHED' }}</span>
                </td>

                <td class="td align-top text-right">
                  {{-- tombol opsional (detail / export) kalau ada route-nya --}}
                  @if(isset($application) && \Illuminate\Support\Facades\Route::has('admin.applications.index'))
                    <a class="btn btn-outline btn-sm" href="{{ route('admin.applications.index', ['q' => '#App '.$at->application_id]) }}">Lihat</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        {{-- Empty state --}}
        <div class="py-16 grid place-content-center text-center">
          <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z"/>
            </svg>
          </div>
          <div class="text-slate-600">Belum ada attempt psychotest.</div>
        </div>
      @endif
    </div>
  </div>

  {{-- Pagination --}}
  <div class="mt-6">
    {{ method_exists($attempts, 'withQueryString') ? $attempts->withQueryString()->links() : (method_exists($attempts, 'links') ? $attempts->links() : '') }}
  </div>
@endsection
