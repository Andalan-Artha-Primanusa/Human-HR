@extends('layouts.app')
@section('title', 'Interview Saya')

@section('content')
@php
  use Illuminate\Support\Str;
  use Carbon\Carbon;

  $tz = config('app.timezone', 'Asia/Jakarta');
@endphp

<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Interview Saya</h1>
    <a href="{{ route('applications.mine') }}" class="text-sm text-blue-700 hover:underline">Lihat Lamaran Saya</a>
  </div>

  @if(session('ok'))
    <div class="p-3 rounded-md bg-green-50 text-green-700 border border-green-200">{{ session('ok') }}</div>
  @endif

  @if($interviews->isEmpty())
    <div class="p-6 rounded-xl border border-slate-200 bg-white text-slate-600">
      Belum ada jadwal interview.
    </div>
  @else
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      @foreach($interviews as $iv)
        @php
          $start = optional($iv->start_at)?->timezone($tz);
          $end   = optional($iv->end_at)?->timezone($tz);
          $dur   = $start && $end ? $start->diffInMinutes($end) : null;
        @endphp
        <div class="rounded-xl border bg-white p-4 hover:shadow-sm transition">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-sm text-slate-500">
                {{ $start?->format('D, d M Y · H:i') }}
                @if($end)–{{ $end->format('H:i') }}@endif
              </div>
              <div class="mt-0.5 font-semibold truncate">{{ $iv->title ?: $iv->application->job->title }}</div>
              <div class="text-xs text-slate-600 mt-0.5 truncate">
                {{ strtoupper($iv->mode) }}
                •
                @if($iv->mode === 'online')
                  {{ $iv->meeting_link ? Str::limit($iv->meeting_link, 40) : 'Online' }}
                @else
                  {{ $iv->location ?: 'TBD' }}
                @endif
              </div>
              <div class="text-xs text-slate-500 mt-0.5 truncate">
                Job: {{ $iv->application->job->title }}
                @if($iv->application->job->site?->name) · {{ $iv->application->job->site->name }} @endif
              </div>
              @if($dur)
                <div class="text-[11px] text-slate-500 mt-0.5">Durasi ± {{ $dur }} menit</div>
              @endif
            </div>
          </div>

          <div class="mt-4 flex items-center gap-2">
            <a href="{{ route('me.interviews.show', $iv->id) }}"
               class="px-3 py-1.5 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm">
              Lihat Detail
            </a>
            <a href="{{ route('me.interviews.ics', $iv->id) }}"
               class="px-3 py-1.5 rounded-md border border-blue-300 text-blue-700 hover:bg-blue-50 text-sm">
              Download ICS
            </a>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
