{{-- resources/views/sites/show.blade.php --}}
@extends('layouts.app')
@section('title', $site->name . ' - Site Karir Andalan')

@php
    $PRIMARY = '#a77d52';
    $PRIMARY_DARK = '#8b5e3c';
    $BORD = '#eadfd4';
    $tz = $site->timezone ?: data_get($site->meta, 'timezone');
    $addr = $site->address ?: data_get($site->meta, 'address');
    $lat = data_get($site->meta, 'lat')
        ?? data_get($site->meta, 'latitude')
        ?? data_get($site->meta, 'location.lat')
        ?? data_get($site->meta, 'location.latitude');
    $lng = data_get($site->meta, 'lng')
        ?? data_get($site->meta, 'lon')
        ?? data_get($site->meta, 'long')
        ?? data_get($site->meta, 'longitude')
        ?? data_get($site->meta, 'location.lng')
        ?? data_get($site->meta, 'location.lon')
        ?? data_get($site->meta, 'location.long')
        ?? data_get($site->meta, 'location.longitude');
    $hasCoords = is_numeric($lat ?? null) && is_numeric($lng ?? null);
    $gmQuery = trim(collect([$site->name, $addr, $site->region])->filter()->implode(' '));
    $gmUrl = $hasCoords
        ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($lat . ',' . $lng)
        : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($gmQuery ?: $site->code);
    $openJobs = $site->jobs ?? collect();
    $employmentPretty = [
        'fulltime' => 'Full-time',
        'contract' => 'Contract',
        'intern' => 'Intern',
        'parttime' => 'Part-time',
        'freelance' => 'Freelance',
    ];
@endphp

@section('content')
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden">
    <symbol id="site-pin" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-10a7 7 0 1 0-14 0c0 5.65 7 10 7 10Z"/><circle cx="12" cy="11" r="2.5" stroke-width="2"/>
    </symbol>
    <symbol id="site-briefcase" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 6V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v1M4 8h16v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8Z"/><path stroke-width="2" stroke-linecap="round" d="M4 12h16"/>
    </symbol>
    <symbol id="site-clock" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <circle cx="12" cy="12" r="9" stroke-width="2"/><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
    </symbol>
    <symbol id="site-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/>
    </symbol>
  </svg>

  <div class="mx-auto w-full max-w-[1400px] px-4 py-6 sm:px-6 lg:px-8">
    {{-- HEADER (shared solid-brown page-header) --}}
    <section class="page-header" aria-labelledby="site-detail-title">
      <div class="page-header__inner">
        <div class="page-header__copy">
          <p class="page-header__eyebrow">Profil Site</p>
          <div class="flex flex-wrap items-center gap-3">
            <h1 id="site-detail-title" class="page-header__title">{{ $site->name }}</h1>
            <span class="inline-flex items-center gap-2 rounded-full px-3 py-0.5 text-[11px] font-bold uppercase tracking-[.12em]" style="background:rgba(255,255,255,.16);color:#fff;box-shadow:inset 0 0 0 1px rgba(255,255,255,.32)">
              <span class="h-2 w-2 rounded-full {{ $site->is_active ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
              {{ $site->is_active ? 'Site Aktif' : 'Site Nonaktif' }}
            </span>
          </div>
          <p class="page-header__desc">
            <span>{{ $site->code }}</span>
            @if($site->region)<span class="mx-1.5 opacity-50">•</span><span>{{ $site->region }}</span>@endif
            @if($tz)<span class="mx-1.5 opacity-50">•</span><span>{{ $tz }}</span>@endif
          </p>
        </div>

        <div class="page-header__actions">
          <a href="{{ route('jobs.index', ['site' => $site->code]) }}" class="ph-action">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Lowongan
          </a>
          <a href="{{ $gmUrl }}" target="_blank" rel="noopener" class="ph-action ph-action--brand">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 0 1 3 16.382V5.618a1 1 0 0 1 1.447-.894L9 7m0 13 6-3m-6 3V7m6 10 5.447 2.724A1 1 0 0 0 21 18.382V7.618a1 1 0 0 0-1.447-.894L15 9m0 10V9"/></svg>
            Buka Maps
          </a>
        </div>
      </div>
    </section>

    {{-- Kartu hero: ringkasan + peta --}}
    <section class="overflow-hidden rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
      <div class="grid lg:grid-cols-[1.05fr_.95fr]">
        <div class="p-6 sm:p-8 lg:p-10">

          <div class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl border bg-[#fffaf5] p-4" style="border-color: {{ $BORD }}">
              <p class="text-xs font-medium text-slate-500">Lowongan Aktif</p>
              <p class="mt-2 text-2xl font-bold text-slate-950">{{ (int) ($site->open_jobs_count ?? $openJobs->count()) }}</p>
            </div>
            <div class="rounded-xl border bg-white p-4" style="border-color: {{ $BORD }}">
              <p class="text-xs font-medium text-slate-500">Region</p>
              <p class="mt-2 truncate text-base font-bold text-slate-950">{{ $site->region ?: '-' }}</p>
            </div>
            <div class="rounded-xl border bg-white p-4" style="border-color: {{ $BORD }}">
              <p class="text-xs font-medium text-slate-500">Timezone</p>
              <p class="mt-2 truncate text-base font-bold text-slate-950">{{ $tz ?: '-' }}</p>
            </div>
          </div>

          @if($addr)
            <div class="mt-4 flex gap-3 rounded-xl border bg-[#fffaf5] p-4" style="border-color: {{ $BORD }}">
              <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#a77d52]"><use href="#site-pin"/></svg>
              <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-[#8b5e3c]">Alamat</p>
                <p class="mt-1 leading-relaxed text-slate-800">{{ $addr }}</p>
              </div>
            </div>
          @endif

          @if($site->notes)
            <div class="mt-4 rounded-xl border bg-[#fffaf5] p-4 text-sm leading-relaxed text-slate-700" style="border-color: {{ $BORD }}">
              {{ $site->notes }}
            </div>
          @endif
        </div>

        <div class="min-h-[360px] border-t bg-slate-100 lg:border-l lg:border-t-0" style="border-color: {{ $BORD }}">
          @if($hasCoords)
            @once
              <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
              <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            @endonce
            <div id="map-site-{{ $site->id }}" class="h-full min-h-[360px] w-full"></div>
            <script>
              (function(){
                var lat = {{ json_encode((float) $lat) }};
                var lng = {{ json_encode((float) $lng) }};
                var name = {!! json_encode($site->name) !!};
                var code = {!! json_encode($site->code) !!};
                var map = L.map('map-site-{{ $site->id }}', { scrollWheelZoom: false }).setView([lat, lng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                  maxZoom: 19,
                  attribution: '&copy; OpenStreetMap'
                }).addTo(map);
                L.marker([lat, lng]).addTo(map).bindPopup('<strong>' + (name || '-') + '</strong><br><span>' + (code || '-') + '</span>').openPopup();
              })();
            </script>
          @else
            <iframe
              src="https://www.google.com/maps?q={{ urlencode($gmQuery ?: $site->code) }}&output=embed"
              width="100%" height="100%" class="min-h-[360px]" style="border:0;" allowfullscreen="" loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
          @endif
        </div>
      </div>
    </section>

    <section class="mt-6 rounded-2xl border bg-white p-5 shadow-sm sm:p-6" style="border-color: {{ $BORD }}">
      <div class="flex flex-wrap items-center justify-between gap-3">
        <x-section-title title="Posisi yang sedang dibuka" />
        <a href="{{ route('jobs.index', ['site' => $site->code]) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-[#8b5e3c] hover:underline">
          Lihat semua
          <svg class="h-4 w-4"><use href="#site-arrow"/></svg>
        </a>
      </div>

      @if($openJobs->count())
        <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          @foreach($openJobs as $job)
            <article class="flex min-h-[220px] flex-col rounded-xl border bg-white p-4 transition hover:-translate-y-0.5 hover:border-[#a77d52] hover:shadow-md"
                     style="border-color: {{ $BORD }}">
              <div class="flex items-start justify-between gap-3">
                <div class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#fffaf5] text-[#8b5e3c] ring-1 ring-inset ring-[#ead8c5]">
                  <svg class="h-5 w-5"><use href="#site-briefcase"/></svg>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">OPEN</span>
              </div>

              <a href="{{ route('jobs.show', $job) }}" class="mt-4 line-clamp-2 text-base font-bold leading-snug text-slate-950 hover:text-[#8b5e3c]">
                {{ $job->title }}
              </a>

              <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold text-slate-600">
                @if($job->division)
                  <span class="rounded-full bg-slate-50 px-2 py-1 ring-1 ring-inset ring-slate-200">{{ $job->division }}</span>
                @endif
                @if($job->employment_type)
                  <span class="rounded-full bg-slate-50 px-2 py-1 ring-1 ring-inset ring-slate-200">{{ $employmentPretty[$job->employment_type] ?? ucfirst($job->employment_type) }}</span>
                @endif
                <span class="rounded-full bg-slate-50 px-2 py-1 ring-1 ring-inset ring-slate-200">{{ (int) ($job->openings ?? 1) }} opening</span>
              </div>

              @php
                $closingLabel = null;
                if (!empty($job->closing_at)) {
                    try {
                        $closingLabel = $job->closing_at instanceof \Illuminate\Support\Carbon
                            ? $job->closing_at->format('d M Y')
                            : \Illuminate\Support\Carbon::parse($job->closing_at)->format('d M Y');
                    } catch (\Throwable $e) {
                        $closingLabel = null;
                    }
                }
              @endphp
              @if($job->code || $job->level || $closingLabel)
                <div class="mt-4 space-y-1.5 text-xs text-slate-600">
                  @if($job->code)
                    <p class="truncate"><span class="text-slate-400">Kode:</span> {{ $job->code }}</p>
                  @endif
                  @if($job->level)
                    <p><span class="text-slate-400">Level:</span> {{ $job->level }}</p>
                  @endif
                  @if($closingLabel)
                    <p><span class="text-slate-400">Tutup:</span> {{ $closingLabel }}</p>
                  @endif
                </div>
              @endif

              <div class="mt-auto flex items-center justify-between border-t pt-4 text-xs text-slate-500" style="border-color: {{ $BORD }}">
                <span class="inline-flex items-center gap-1.5">
                  <svg class="h-3.5 w-3.5"><use href="#site-clock"/></svg>
                  {{ optional($job->created_at)->format('d M Y') ?? '-' }}
                </span>
                <a href="{{ route('jobs.show', $job) }}" class="font-bold text-[#8b5e3c] hover:underline">Detail</a>
              </div>
            </article>
          @endforeach
        </div>
      @else
        <div class="mt-5 rounded-xl border bg-[#fffaf5] p-8 text-center" style="border-color: {{ $BORD }}">
          <div class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-white text-[#8b5e3c] ring-1 ring-inset ring-[#ead8c5]">
            <svg class="h-6 w-6"><use href="#site-briefcase"/></svg>
          </div>
          <p class="mt-4 font-bold text-slate-950">Belum ada lowongan aktif</p>
          <p class="mt-1 text-sm text-slate-600">Cek lagi nanti atau lihat lowongan dari site lain.</p>
        </div>
      @endif
    </section>

    <p class="mt-5 text-xs text-slate-500">
      Diperbarui terakhir {{ optional($site->updated_at)->format('d M Y H:i') ?? '-' }}
    </p>
  </div>
@endsection
