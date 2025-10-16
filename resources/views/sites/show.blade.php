{{-- resources/views/sites/show.blade.php --}}
@extends('layouts.app')
@section('title', 'Site · '.$site->code)

@php
  $BORD = '#e5e7eb';
  // fallback ringan dari meta jika kolom kosong
  $tz   = $site->timezone ?: data_get($site->meta, 'timezone');
  $addr = $site->address  ?: data_get($site->meta, 'address');

  // helper money
  $fmtMoney = function($n, $cur = 'IDR') {
    if(!is_numeric($n)) return null;
    return ($cur ?: 'IDR').' '.number_format((float)$n, 0, ',', '.');
  };
@endphp

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  {{-- HEADER: bar biru–merah --}}
  <div class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>

    <div class="p-6 md:p-7">
      <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">
            {{ $site->name }} <span class="text-slate-400">({{ $site->code }})</span>
          </h1>
          <div class="mt-1 text-sm text-slate-600">
            {{ $site->region ?: '—' }} @if($tz) · TZ: {{ $tz }} @endif
          </div>
        </div>
        {{-- ⬇️ baliknya ke halaman lamar (jobs) dengan filter site --}}
        <a href="{{ route('jobs.index', ['site' => $site->code]) }}" class="btn btn-ghost">Kembali ke Lowongan</a>
      </div>
    </div>
  </div>

  {{-- INFO UTAMA --}}
  <section class="rounded-2xl border bg-white shadow-sm p-6" style="border-color: {{ $BORD }}">
    <div class="grid gap-4 sm:grid-cols-2">
      <div>
        <div class="text-slate-500 text-sm">Kode</div>
        <div class="mt-1 text-lg font-medium text-slate-800">{{ $site->code }}</div>
      </div>

      <div>
        <div class="text-slate-500 text-sm">Status</div>
        <div class="mt-1">
          <span class="badge {{ $site->is_active ? 'badge-green' : 'badge-amber' }}">
            {{ $site->is_active ? 'ACTIVE' : 'INACTIVE' }}
          </span>
        </div>
      </div>

      <div>
        <div class="text-slate-500 text-sm">Region</div>
        <div class="mt-1 text-slate-800">{{ $site->region ?: '—' }}</div>
      </div>

      <div>
        <div class="text-slate-500 text-sm">Timezone</div>
        <div class="mt-1 text-slate-800">{{ $tz ?: '—' }}</div>
      </div>

      @if($addr)
        <div class="sm:col-span-2">
          <div class="text-slate-500 text-sm">Alamat</div>
          <div class="mt-1 text-slate-800 leading-relaxed">{{ $addr }}</div>
        </div>
      @endif

      @if($site->notes)
        <div class="sm:col-span-2">
          <div class="text-slate-500 text-sm">Catatan</div>
          <div class="mt-1 text-slate-800 whitespace-pre-line">{{ $site->notes }}</div>
        </div>
      @endif

      @if(!empty($site->meta))
        <div class="sm:col-span-2">
          <div class="text-slate-500 text-sm">Meta</div>
          <pre class="mt-1 text-xs bg-slate-50 rounded-xl p-4 border overflow-auto" style="border-color: {{ $BORD }}">
{{ json_encode(is_array($site->meta) ? $site->meta : (json_decode($site->meta ?? '[]', true) ?: []), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}
          </pre>
        </div>
      @endif
    </div>
  </section>

  {{-- LOKASI / MAPS --}}
  @php
    // Ambil koordinat dari meta (beberapa kemungkinan key)
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

    // Query Google Maps untuk fallback / tombol "Buka di Google Maps"
    $gmQuery = trim(collect([
        $site->name,
        $addr ?: null,
        $site->region ?: null,
        $tz ? "TZ: ".$tz : null,
    ])->filter()->implode(' '));
    $gmUrl = $hasCoords
        ? 'https://www.google.com/maps/search/?api=1&query='.urlencode($lat.','.$lng)
        : 'https://www.google.com/maps/search/?api=1&query='.urlencode($gmQuery ?: $site->code);
  @endphp

  <section class="rounded-2xl border bg-white shadow-sm p-6" style="border-color: {{ $BORD }}">
    <div class="flex items-center justify-between">
      <h2 class="text-base font-semibold text-slate-900">Lokasi Peta</h2>
      <a href="{{ $gmUrl }}" target="_blank" rel="noopener"
         class="text-sm text-blue-700 hover:underline">Buka di Google Maps</a>
    </div>

    <div class="mt-4">
      @if($hasCoords)
        {{-- Leaflet Map (tanpa API key) --}}
        @once
          <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
          <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                  integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        @endonce

        <div id="map-site-{{ $site->id }}" class="rounded-xl border" style="height: 320px; border-color: {{ $BORD }}"></div>

        <script>
          (function(){
            var lat = {{ json_encode((float)$lat) }};
            var lng = {{ json_encode((float)$lng) }};
            var name = {!! json_encode($site->name) !!};
            var addr = {!! json_encode($addr) !!};
            var code = {!! json_encode($site->code) !!};

            var map = L.map('map-site-{{ $site->id }}', { scrollWheelZoom: false }).setView([lat, lng], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
              maxZoom: 19,
              attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            var popupHtml = '<div style="min-width:180px"><div style="font-weight:600;color:#0f172a;">'
                            + (name || '-') + ' <span style="color:#94a3b8">(' + (code || '-') + ')</span></div>'
                            + (addr ? '<div style="margin-top:4px;color:#475569;">'+ addr +'</div>' : '')
                            + '</div>';

            L.marker([lat, lng]).addTo(map).bindPopup(popupHtml).openPopup();
          })();
        </script>
      @else
        {{-- Fallback: Google Maps iframe pakai query alamat --}}
        <div class="rounded-xl overflow-hidden border" style="border-color: {{ $BORD }}">
          <iframe
            src="https://www.google.com/maps?q={{ urlencode($gmQuery ?: $site->code) }}&output=embed"
            width="100%" height="320" style="border:0;" allowfullscreen="" loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <p class="mt-2 text-xs text-slate-500">
          * Peta berdasarkan pencarian alamat. Untuk akurasi tinggi, simpan koordinat <code>lat</code> & <code>lng</code> di <strong>meta</strong>.
        </p>
      @endif
    </div>
  </section>

  {{-- LOWONGAN TERBARU (kartu-kartu refined) --}}
  @if(isset($site->jobs) && $site->jobs->count())
    @php
      // pretty label utk employment_type
      $employmentPretty = [
        'fulltime' => 'Full-time',
        'contract' => 'Contract',
        'intern'   => 'Intern',
        'parttime' => 'Part-time',
        'freelance'=> 'Freelance',
      ];
    @endphp

    <section class="rounded-2xl border bg-white shadow-sm p-6" style="border-color: {{ $BORD }}">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-900">Lowongan Terbaru di Site Ini</h2>
        <span class="text-xs text-slate-500">
          Total lowongan: {{ $site->jobs_count ?? $site->jobs()->count() }}
        </span>
      </div>

      <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($site->jobs as $job)
          <article class="group relative overflow-hidden rounded-2xl border bg-white shadow-sm transition hover:shadow-md"
                   style="border-color: {{ $BORD }}">
            {{-- top accent bar --}}
            <div class="h-1.5 w-full flex">
              <div class="flex-1 bg-blue-600"></div>
              <div class="w-10 bg-red-500"></div>
            </div>

            <div class="p-4 flex h-full flex-col">
              {{-- header: title + status --}}
              <div class="flex items-start justify-between gap-3">
                <a href="{{ route('jobs.show', $job) }}"
                   class="line-clamp-2 font-semibold text-slate-900 hover:underline">
                  {{ $job->title }}
                </a>

                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1 ring-inset
                  {{ ($job->status ?? 'draft') === 'open'
                      ? 'bg-blue-50 text-blue-700 ring-blue-200'
                      : 'bg-slate-100 text-slate-700 ring-slate-200' }}">
                  {{ strtoupper($job->status ?? 'draft') }}
                </span>
              </div>

              {{-- quick meta chips --}}
              <div class="mt-2 flex flex-wrap items-center gap-2">
                @if(!empty($job->division))
                  <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">
                    <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" d="M3 8a2 2 0 012-2h14a2 2 0 012 2v9a3 3 0 01-3 3H6a3 3 0 01-3-3V8z"/><path stroke-width="2" d="M9 6a3 3 0 013-3a3 3 0 013 3"/></svg>
                    {{ $job->division }}
                  </span>
                @endif

                @if(!empty($job->employment_type))
                  <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">
                    <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" d="M12 22v-4M6 12l6-8l6 8v6a4 4 0 0 1-8 0"/></svg>
                    {{ $employmentPretty[$job->employment_type] ?? ucfirst($job->employment_type) }}
                  </span>
                @endif

                <span class="inline-flex items-center gap-1 rounded-full bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">
                  <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-width="2" d="M8 7h8M8 12h8M8 17h5"/></svg>
                  {{ (int)($job->openings ?? 1) }} openings
                </span>
              </div>

              {{-- lokasi row --}}
              @php $s = $job->site; @endphp
              @if($s)
                <div class="mt-3 flex items-start gap-2 text-xs text-slate-600">
                  <svg class="mt-0.5 h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-4.35 7-10a7 7 0 10-14 0c0 5.65 7 10 7 10z"/><circle cx="12" cy="11" r="2"/>
                  </svg>
                  <div class="min-w-0">
                    <div class="truncate font-medium text-slate-700">
                      {{ $s->name ?? $s->code ?? '—' }}
                    </div>
                    <div class="truncate">
                      @if(!empty($s->region)) {{ $s->region }} @endif
                      @if(!empty($s->timezone)) <span class="text-slate-400">·</span> TZ: {{ $s->timezone }} @endif
                    </div>
                  </div>
                </div>
              @endif

              {{-- description --}}
              @php
                $desc = trim(strip_tags($job->description ?? ''));
                $short = \Illuminate\Support\Str::limit($desc, 140);
              @endphp
              @if($short)
                <p class="mt-3 line-clamp-3 text-sm leading-relaxed text-slate-700">{{ $short }}</p>
              @endif

              {{-- salary + closing --}}
              @if($job->salary_min || $job->salary_max || $job->currency || $job->closing_at)
                <div class="mt-3 rounded-lg border bg-slate-50 px-3 py-2 text-xs text-slate-700"
                     style="border-color: {{ $BORD }}">
                  @php
                    $cur = $job->currency ?: 'IDR';
                    $min = $fmtMoney($job->salary_min, $cur);
                    $max = $fmtMoney($job->salary_max, $cur);
                    $salaryText = null;
                    if($min && $max)       $salaryText = $min.' – '.$max;
                    elseif($min)           $salaryText = '≥ '.$min;
                    elseif($max)           $salaryText = '≤ '.$max;
                    elseif($job->currency) $salaryText = $cur;
                  @endphp

                  <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                    @if($salaryText)
                      <span>Gaji: <span class="font-medium">{{ $salaryText }}</span>
                        @if(!empty($job->salary_period)) <span class="text-slate-500">/ {{ $job->salary_period }}</span>@endif
                      </span>
                    @endif

                    @if(!empty($job->closing_at))
                      @if($salaryText)<span class="text-slate-300">•</span>@endif
                      <span>Tutup: <span class="font-medium">{{ \Illuminate\Support\Carbon::parse($job->closing_at)->format('d M Y') }}</span></span>
                    @endif
                  </div>
                </div>
              @endif

              {{-- footer --}}
              <div class="mt-4 flex items-center justify-between">
                <div class="text-xs text-slate-500">
                  Diposting: {{ optional($job->created_at)->format('d M Y') ?? '—' }}
                </div>
                <a href="{{ route('jobs.show', $job) }}"
                   class="btn btn-outline btn-sm group-hover:translate-x-0.5 transition">
                  Lihat
                </a>
              </div>
            </div>
          </article>
        @endforeach
      </div>
    </section>
  @else
    <section class="rounded-2xl border bg-white shadow-sm p-6" style="border-color: {{ $BORD }}">
      <div class="text-sm text-slate-600">Belum ada lowongan aktif di site ini.</div>
    </section>
  @endif

  {{-- FOOTER TIMESTAMP --}}
  <div class="text-xs text-slate-500">
    Dibuat: {{ optional($site->created_at)->format('d M Y H:i') ?? '-' }} ·
    Diperbarui: {{ optional($site->updated_at)->format('d M Y H:i') ?? '-' }}
  </div>
</div>
@endsection
