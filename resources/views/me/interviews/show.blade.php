{{-- resources/views/me/interviews/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Interview • karir-andalan')

@section('content')
    @php
        use Illuminate\Support\Str;

        $tz = config('app.timezone', 'Asia/Jakarta');
        $start = optional($iv->start_at)?->timezone($tz);
        $end   = optional($iv->end_at)?->timezone($tz);
        $dur   = $start && $end ? $start->diffInMinutes($end) : null;
        $panel = is_array($iv->panel) ? $iv->panel : (empty($iv->panel) ? [] : (json_decode($iv->panel, true) ?: []));
        $responseStatus = $iv->candidate_response_status ?: 'pending';

        $responseBadge = match ($responseStatus) {
            'confirmed'            => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'declined'             => 'bg-rose-50 text-rose-700 ring-rose-200',
            'reschedule_requested' => 'bg-purple-50 text-purple-700 ring-purple-200',
            default                => 'bg-[#fff7ed] text-amber-700 ring-amber-200',
        };
        $responseLabel = match ($responseStatus) {
            'confirmed'            => 'Dikonfirmasi',
            'declined'             => 'Ditolak',
            'reschedule_requested' => 'Minta Reschedule',
            default                => 'Menunggu Konfirmasi',
        };
    @endphp

    <div class="mx-auto px-4 py-6 max-w-4xl sm:px-6 lg:px-8">

      {{-- HEADER — shared component --}}
      <x-admin.page-header eyebrow="Manajemen Interview" title="Detail Interview"
        description="Tinjau jadwal interview kamu, berikan konfirmasi kehadiran, atau ajukan penjadwalan ulang.">
        <a href="{{ route('me.interviews.index') }}" class="ph-action">
          ← Kembali
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
      </x-admin.page-header>

      {{-- FLASH --}}
      @if(session('ok'))
        <div class="mt-6 px-4 py-3 rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200">
          {{ session('ok') }}
        </div>
      @endif

      <div class="mt-6 overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color:#e7ded6">
        {{-- STRIP --}}
        <div class="h-1.5 rounded-t-2xl" style="background:#a77d52"></div>

        <div class="p-6">

          {{-- HEADER INFORMASI --}}
          <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
            <div class="min-w-0">
              <div class="flex items-center gap-2 text-sm text-slate-500">
                <span>{{ $start?->format('l, d M Y · H:i') }}</span>
                @if($end)<span class="text-slate-300">–</span><span>{{ $end->format('H:i') }}</span>@endif
              </div>
              <h2 class="mt-1 text-2xl font-semibold break-words text-slate-950">
                {{ $iv->title ?: $iv->application->job->title }}
              </h2>
            </div>

            <div class="shrink-0 flex flex-col items-start gap-2 md:items-end">
              <span class="rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $responseBadge }}">
                {{ $responseLabel }}
              </span>
              <a href="{{ route('me.interviews.ics', $iv->id) }}" class="abtn abtn-neutral abtn-sm">
                Tambah ke Kalender (ICS)
              </a>
            </div>
          </div>

          {{-- RINCIAN JADWAL --}}
          <div class="mt-6 grid gap-3 sm:grid-cols-2">

            <div class="flex items-start gap-3 rounded-xl border p-3.5" style="border-color:#e7ded6;background:#fffaf5">
              <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg" style="background:#a77d5220;color:#a77d52">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
              </div>
              <div class="min-w-0">
                <div class="text-xs text-slate-500">Posisi</div>
                <div class="text-sm font-medium break-words" style="color:#6b4f3a">{{ $iv->application->job->title }}</div>
                @if($iv->application->job->site?->name)
                  <div class="text-xs text-slate-500 mt-0.5">{{ $iv->application->job->site->name }}</div>
                @endif
              </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl border p-3.5" style="border-color:#e7ded6;background:#fffaf5">
              <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg" style="background:#a77d5220;color:#a77d52">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <div class="min-w-0">
                <div class="text-xs text-slate-500">Mode</div>
                <div class="text-sm font-medium" style="color:#6b4f3a">{{ strtoupper($iv->mode) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">Durasi @if($dur)± {{ $dur }} menit @else (TBD) @endif</div>
              </div>
            </div>

            <div class="flex items-start gap-3 rounded-xl border p-3.5 sm:col-span-2"
                 style="border-color:#e7ded6;background:#fffaf5">
              <div class="grid h-9 w-9 shrink-0 place-items-center rounded-lg" style="background:#a77d5220;color:#a77d52">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><circle cx="12" cy="11" r="3"/></svg>
              </div>
              <div class="min-w-0">
                <div class="text-xs text-slate-500">Lokasi / Link</div>
                <div class="text-sm font-medium break-words" style="color:#6b4f3a">
                  @if($iv->mode === 'online')
                    @if($iv->meeting_link)
                      <a class="inline-flex items-center gap-1 text-[#a77d52] hover:underline" href="{{ $iv->meeting_link }}" target="_blank" rel="noopener">
                        {{ Str::limit($iv->meeting_link, 50) }}
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                      </a>
                    @else
                      Online
                    @endif
                  @else
                    {{ $iv->location ?: 'TBD' }}
                  @endif
                </div>
              </div>
            </div>
          </div>

          {{-- PANEL --}}
          @if(!empty($panel))
            <div class="mt-6">
              <div class="text-sm font-semibold text-slate-700">Panel Interviewer</div>
              <ul class="mt-2 space-y-1.5 pl-1 text-sm" style="color:#6b4f3a">
                @foreach($panel as $p)
                  <li class="flex items-center gap-2">
                    <span class="h-1.5 w-1.5 rounded-full" style="background:#a77d52"></span>
                    {{ $p }}
                  </li>
                @endforeach
              </ul>
            </div>
          @endif

          {{-- CATATAN --}}
          @if($iv->notes)
            <div class="mt-6">
              <div class="text-sm font-semibold text-slate-700">Catatan Interview</div>
              <div class="mt-2 rounded-xl border p-3.5 text-sm whitespace-pre-line" style="border-color:#e7ded6;background:#fffaf5;color:#6b4f3a">{{ $iv->notes }}</div>
            </div>
          @endif

          {{-- RESPONS KAMU --}}
          @if($iv->candidate_response_note)
            <div class="mt-6">
              <div class="text-sm font-semibold text-slate-700">Respons Kamu</div>
              <div class="mt-2 rounded-xl border p-3.5 text-sm whitespace-pre-line" style="border-color:#e7ded6;background:#fffaf5;color:#6b4f3a">{{ $iv->candidate_response_note }}</div>
              @if($iv->candidate_reschedule_time)
                <div class="mt-1.5 text-xs text-slate-500">
                  Usulan waktu: {{ $iv->candidate_reschedule_time->timezone($tz)->format('d M Y H:i') }}
                </div>
              @endif
            </div>
          @endif

          {{-- AKSI --}}
          <div class="mt-6 border-t pt-5 flex flex-wrap items-center gap-3" style="border-color:#e7ded6">
            @if($responseStatus !== 'confirmed')
              <form action="{{ route('me.interviews.confirm', $iv->id) }}" method="POST">
                @csrf
                <button class="abtn" style="background:#a77d52">Konfirmasi Hadir</button>
              </form>
            @endif

            @if($responseStatus !== 'declined')
              <form action="{{ route('me.interviews.decline', $iv->id) }}" method="POST" class="flex flex-wrap items-center gap-2">
                @csrf
                <input type="text" name="reason" required maxlength="1000" placeholder="Alasan tolak"
                       class="w-44 rounded-lg border border-[#dfc9b0] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#a77d52]/30">
                <button class="abtn abtn-danger">Tolak</button>
              </form>
            @endif

            <form action="{{ route('me.interviews.request_reschedule', $iv->id) }}" method="POST" class="flex flex-wrap items-center gap-2">
              @csrf
              <input type="datetime-local" name="proposed_time"
                     class="rounded-lg border border-[#dfc9b0] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#a77d52]/30">
              <input type="text" name="reason" required maxlength="1000" placeholder="Alasan reschedule"
                     class="w-52 rounded-lg border border-[#dfc9b0] px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#a77d52]/30">
              <button class="abtn abtn-warning">Minta Reschedule</button>
            </form>
          </div>

        </div>
      </div>

    </div>
@endsection