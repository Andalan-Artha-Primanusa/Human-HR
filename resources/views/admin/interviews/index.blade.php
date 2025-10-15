@extends('layouts.app', [ 'title' => 'Admin · Interviews' ])

@section('content')
  {{-- ===== Header Panel ala bar biru-merah ===== --}}
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
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Interviews</h1>
          <p class="text-sm text-slate-600">Jadwal interview yang sudah dibuat & teririm.</p>
        </div>

        {{-- Quick Search --}}
        <form method="GET" class="flex items-center gap-2">
          <input
            name="q"
            value="{{ $q ?? request('q') }}"
            class="input"
            placeholder="Cari kandidat / job…"
            autocomplete="off"
          >
          <button class="btn btn-primary">Cari</button>
          @if(!empty($q ?? request('q')))
            <a href="{{ route('admin.interviews.index') }}" class="btn btn-ghost">Reset</a>
          @endif
        </form>
      </div>
    </div>
  </div>

  {{-- ===== Tabel ===== --}}
  <div class="card">
    <div class="card-body overflow-x-auto">
      @if(($interviews->count() ?? 0) > 0)
        <table class="table min-w-[900px]">
          <thead>
            <tr>
              <th class="th w-56">Tanggal</th>
              <th class="th w-60">Kandidat</th>
              <th class="th">Posisi</th>
              <th class="th w-32 text-center">Mode</th>
              <th class="th w-[22rem]">Lokasi / Link</th>
              <th class="th w-52">PIC / Email</th>
            </tr>
          </thead>
          <tbody>
            @foreach($interviews as $iv)
              @php
                $start     = \Carbon\Carbon::parse($iv->start_at);
                $end       = \Carbon\Carbon::parse($iv->end_at);
                $candidate = $iv->application?->user?->name ?? '—';
                $jobTitle  = $iv->application?->job?->title ?? '—';
                $siteCode  = $iv->application?->job?->site?->code ?? null;
                $picName   = auth()->user()->name  ?? '—';
                $picEmail  = auth()->user()->email ?? '—';
              @endphp
              <tr class="align-top">
                {{-- Tanggal --}}
                <td class="td">
                  <div class="font-medium text-slate-900">
                    {{ $start->format('d M Y, H:i') }} — {{ $end->format('H:i') }}
                  </div>
                  <div class="text-xs text-slate-500">{{ $start->diffForHumans() }}</div>
                </td>

                {{-- Kandidat --}}
                <td class="td">
                  <div class="font-medium text-slate-900">{{ $candidate }}</div>
                  <div class="text-xs text-slate-500">#{{ $iv->application_id }}</div>
                </td>

                {{-- Posisi --}}
                <td class="td">
                  <div class="text-slate-900">{{ $jobTitle }}</div>
                  @if($siteCode)
                    <div class="text-xs text-slate-500">Site: {{ $siteCode }}</div>
                  @endif
                </td>

                {{-- Mode --}}
                <td class="td text-center">
                  <span class="badge {{ $iv->mode === 'onsite' ? 'badge-amber' : 'badge-blue' }}">
                    {{ strtoupper($iv->mode ?? 'online') }}
                  </span>
                </td>

                {{-- Lokasi / Link --}}
                <td class="td">
                  @if(($iv->mode ?? 'online') === 'onsite')
                    {{ $iv->location ?? '—' }}
                  @else
                    @if($iv->meeting_link)
                      <a class="text-blue-600 hover:underline" href="{{ $iv->meeting_link }}" target="_blank" rel="noopener">
                        Join link
                      </a>
                    @else
                      —
                    @endif
                  @endif
                </td>

                {{-- PIC --}}
                <td class="td">
                  <div class="text-sm">{{ $picName }}</div>
                  <div class="text-xs text-slate-500">{{ $picEmail }}</div>
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
              <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V5m8 2V5M5 11h14M7 21h10a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H7A2 2 0 0 0 5 8v11a2 2 0 0 0 2 2Z"/>
            </svg>
          </div>
          <div class="text-slate-600">Belum ada jadwal interview.</div>
          <div class="text-slate-500 text-sm">Buat dari tombol <span class="font-medium">Schedule</span> di Kanban.</div>
        </div>
      @endif
    </div>
  </div>

  {{-- Pagination --}}
  <div class="mt-6">
    {{ method_exists($interviews, 'links') ? $interviews->withQueryString()->links() : '' }}
  </div>
@endsection
