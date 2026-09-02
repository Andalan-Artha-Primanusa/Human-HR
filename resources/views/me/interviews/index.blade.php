{{-- resources/views/me/interviews/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Interview Saya • karir-andalan')

@section('content')
    @php
        use Illuminate\Support\Str;

        $tz = config('app.timezone', 'Asia/Jakarta');

        $responseBadge = function ($status) {
            return match ((string) $status) {
                'confirmed'          => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'declined'           => 'bg-rose-50 text-rose-700 ring-rose-200',
                'reschedule_requested' => 'bg-purple-50 text-purple-700 ring-purple-200',
                default              => 'bg-[#fff7ed] text-amber-700 ring-amber-200',
            };
        };

        $responseLabel = function ($status) {
            return match ((string) $status) {
                'confirmed'          => 'Dikonfirmasi',
                'declined'           => 'Ditolak',
                'reschedule_requested' => 'Minta Reschedule',
                default              => 'Menunggu Konfirmasi',
            };
        };
    @endphp

    <div class="mx-auto px-4 py-6 max-w-7xl sm:px-6 lg:px-8">

      {{-- HEADER — shared component --}}
      <x-admin.page-header eyebrow="Manajemen Interview" title="Interview Saya"
        description="Semua jadwal interview dari lowongan yang kamu lamar. Konfirmasi kehadiran dan unduh kalender dari halaman detail.">
        <a href="{{ route('applications.mine') }}" class="ph-action">
          Lihat Lamaran Saya
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
      </x-admin.page-header>

      {{-- FLASH --}}
      @if(session('ok'))
        <div class="mt-6 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
          {{ session('ok') }}
        </div>
      @endif

      {{-- EMPTY STATE --}}
      @if($interviews->isEmpty())
        <div class="mt-6 overflow-hidden bg-white border rounded-2xl text-center shadow-sm" style="border-color:#e7ded6">
          <div class="p-8" style="background:#fffaf5">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-[#a77d52] ring-1 ring-[#ead8c5]">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 8h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <p class="mt-4 text-lg font-semibold" style="color:#6b4f3a">Belum ada jadwal interview</p>
            <p class="max-w-xl mx-auto mt-2 text-sm text-slate-600">
              Jadwal interview akan muncul di halaman ini begitu HR menjadwalkan tahap seleksi untuk lamaran kamu.
            </p>
            <div class="flex flex-wrap justify-center gap-2 mt-4">
              <a href="{{ route('jobs.index') }}" class="abtn abtn-primary">
                Cari Lowongan
              </a>
              <a href="{{ route('applications.mine') }}" class="abtn abtn-neutral">
                Lihat Lamaran
              </a>
            </div>
          </div>
        </div>
      @else
        {{-- GRID KARTU --}}
        <section class="grid gap-5 mt-6 md:grid-cols-2 xl:grid-cols-3">
          @foreach($interviews as $iv)
            @php
                $start = optional($iv->start_at)?->timezone($tz);
                $end   = optional($iv->end_at)?->timezone($tz);
                $dur   = $start && $end ? $start->diffInMinutes($end) : null;
                $resp  = $iv->candidate_response_status ?: 'pending';
                $job   = $iv->application->job;
            @endphp

            <article class="overflow-hidden bg-white border shadow-sm rounded-2xl hover:-translate-y-0.5 hover:shadow-md transition" style="border-color:#e7ded6">
              {{-- STRIP --}}
              <div class="h-1.5 rounded-t-2xl" style="background:#a77d52"></div>

              <div class="p-5">

                {{-- HEADER KARTU --}}
                <div class="flex items-start justify-between gap-3">
                  <div class="flex min-w-0 items-start gap-3">
                    <div class="grid h-11 w-11 shrink-0 place-items-center rounded-xl" style="background:#a77d5220;color:#a77d52">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 8h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                      <h3 class="font-semibold leading-tight break-words text-slate-950">
                        {{ $iv->title ?: $job->title }}
                      </h3>
                      <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                        <span>{{ strtoupper($iv->mode) }}</span>
                        <span class="text-slate-300">•</span>
                        <span>{{ $start?->format('d M Y') }}</span>
                      </div>
                    </div>
                  </div>

                  {{-- STATUS --}}
                  <span class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $responseBadge($resp) }}">
                    {{ $responseLabel($resp) }}
                  </span>
                </div>

                {{-- INFO JADWAL --}}
                <div class="mt-4 space-y-2 text-sm" style="color:#6b4f3a">
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $start?->format('D, d M Y · H:i') }}{{ $end ? '–' . $end->format('H:i') : '' }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
                    <span class="truncate">
                      @if($iv->mode === 'online')
                        {{ $iv->meeting_link ? Str::limit($iv->meeting_link, 40) : 'Online' }}
                      @else
                        {{ $iv->location ?: 'TBD' }}
                      @endif
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="truncate">
                      {{ $job->title }}@if($job->site?->name) · {{ $job->site->name }}@endif
                    </span>
                  </div>
                  @if($dur)
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                      <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      <span>Durasi ± {{ $dur }} menit</span>
                    </div>
                  @endif
                </div>

                {{-- AKSI --}}
                <div class="mt-5 border-t pt-4 flex items-center gap-2" style="border-color:#e7ded6">
                  <a href="{{ route('me.interviews.show', $iv->id) }}" class="abtn abtn-primary abtn-sm">
                    Lihat Detail
                  </a>
                  <a href="{{ route('me.interviews.ics', $iv->id) }}" class="abtn abtn-neutral abtn-sm">
                    Download ICS
                  </a>
                </div>

              </div>
            </article>
          @endforeach
        </section>

        {{-- PAGINATION --}}
        @if($interviews->hasPages())
          <div class="mt-8">
            {{ $interviews->links() }}
          </div>
        @endif
      @endif

    </div>
@endsection