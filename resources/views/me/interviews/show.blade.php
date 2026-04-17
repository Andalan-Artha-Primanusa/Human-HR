@extends('layouts.app')
@section('title', 'Detail Interview')

@section('content')
    @php
        use Illuminate\Support\Str;
        $tz = config('app.timezone', 'Asia/Jakarta');
        $start = optional($iv->start_at)?->timezone($tz);
        $end = optional($iv->end_at)?->timezone($tz);
        $dur = $start && $end ? $start->diffInMinutes($end) : null;
        $panel = is_array($iv->panel) ? $iv->panel : (empty($iv->panel) ? [] : (json_decode($iv->panel, true) ?: []));
    @endphp

    <div class="max-w-4xl mx-auto space-y-6">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Detail Interview</h1>
        <a href="{{ route('me.interviews.index') }}" class="text-sm text-slate-600 hover:underline">← Kembali ke daftar</a>
      </div>

      @if(session('ok'))
        <div class="p-3 text-green-700 border border-green-200 rounded-md bg-green-50">{{ session('ok') }}</div>
      @endif

      <div class="p-5 bg-white border rounded-xl">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <div class="text-sm text-slate-500">
              {{ $start?->format('l, d M Y · H:i') }}
              @if($end)–{{ $end->format('H:i') }}@endif
            </div>
            <div class="mt-1 text-2xl font-semibold break-words">{{ $iv->title ?: $iv->application->job->title }}</div>

            <div class="mt-2 text-sm text-slate-700">
              <div><span class="font-medium">Job:</span> {{ $iv->application->job->title }}
                @if($iv->application->job->site?->name) • {{ $iv->application->job->site->name }} @endif
              </div>
              <div class="mt-1"><span class="font-medium">Mode:</span> {{ strtoupper($iv->mode) }}</div>
              <div class="mt-1">
                <span class="font-medium">Lokasi/Link:</span>
                @if($iv->mode === 'online')
                      @if($iv->meeting_link)
                        <a class="text-blue-700 break-all hover:underline" href="{{ $iv->meeting_link }}" target="_blank" rel="noopener">{{ $iv->meeting_link }}</a>
                      @else
                        Online
                      @endif
                @else
                      {{ $iv->location ?: 'TBD' }}
                @endif
              </div>
              @if($dur)
                <div class="mt-1"><span class="font-medium">Durasi:</span> ± {{ $dur }} menit</div>
              @endif
            </div>
          </div>

          <div class="flex flex-col gap-2 shrink-0">
            <a href="{{ route('me.interviews.ics', $iv->id) }}"
               class="px-3 py-2 text-sm text-center text-blue-700 border border-blue-300 rounded-md hover:bg-blue-50">
              Tambah ke Kalender (ICS)
            </a>
          </div>
        </div>

        @if(!empty($panel))
              <div class="mt-5">
                <div class="text-sm font-medium text-slate-700">Panel Interviewer</div>
                <ul class="pl-6 mt-2 text-sm list-disc text-slate-700">
                  @foreach($panel as $p)
                    <li>{{ $p }}</li>
                  @endforeach
                </ul>
              </div>
        @endif

        @if($iv->notes)
              <div class="mt-5">
                <div class="text-sm font-medium text-slate-700">Catatan</div>
                <div class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $iv->notes }}</div>
              </div>
        @endif
      </div>
    </div>
@endsection
