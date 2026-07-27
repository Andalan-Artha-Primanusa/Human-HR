@extends('layouts.app')
@section('title', 'Detail Interview • karir-andalan')

@section('content')
    @php
        use Illuminate\Support\Str;
        $tz = config('app.timezone', 'Asia/Jakarta');
        $start = optional($iv->start_at)?->timezone($tz);
        $end = optional($iv->end_at)?->timezone($tz);
        $dur = $start && $end ? $start->diffInMinutes($end) : null;
        $panel = is_array($iv->panel) ? $iv->panel : (empty($iv->panel) ? [] : (json_decode($iv->panel, true) ?: []));
        $responseStatus = $iv->candidate_response_status ?: 'pending';
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
            <div class="rounded-full border px-3 py-1 text-center text-xs font-semibold
              @class([
                'border-amber-200 bg-amber-50 text-amber-700' => $responseStatus === 'pending',
                'border-emerald-200 bg-emerald-50 text-emerald-700' => $responseStatus === 'confirmed',
                'border-red-200 bg-red-50 text-red-700' => $responseStatus === 'declined',
                'border-purple-200 bg-purple-50 text-purple-700' => $responseStatus === 'reschedule_requested',
              ])">
              {{ Str::of($responseStatus)->replace('_', ' ')->headline() }}
            </div>
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

        @if($iv->candidate_response_note)
              <div class="mt-5">
                <div class="text-sm font-medium text-slate-700">Respons Kamu</div>
                <div class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $iv->candidate_response_note }}</div>
                @if($iv->candidate_reschedule_time)
                  <div class="mt-1 text-xs text-slate-500">
                    Usulan waktu: {{ $iv->candidate_reschedule_time->timezone($tz)->format('d M Y H:i') }}
                  </div>
                @endif
              </div>
        @endif

        <div class="flex flex-wrap items-center gap-3 mt-5">
          @if($responseStatus !== 'confirmed')
            <form action="{{ route('me.interviews.confirm', $iv->id) }}" method="POST">
              @csrf
              <button class="px-3 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-md hover:bg-emerald-700">
                Konfirmasi Hadir
              </button>
            </form>
          @endif

          @if($responseStatus !== 'declined')
            <form action="{{ route('me.interviews.decline', $iv->id) }}" method="POST" class="flex items-center gap-2">
              @csrf
              <input type="text" name="reason" required maxlength="1000" placeholder="Alasan tolak"
                     class="w-44 rounded-md border border-slate-300 px-3 py-2 text-sm">
              <button class="px-3 py-2 text-sm font-semibold border border-red-300 rounded-md text-red-700 hover:bg-red-50">
                Tolak
              </button>
            </form>
          @endif

          <form action="{{ route('me.interviews.request_reschedule', $iv->id) }}" method="POST" class="flex flex-wrap items-center gap-2">
            @csrf
            <input type="datetime-local" name="proposed_time"
                   class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <input type="text" name="reason" required maxlength="1000" placeholder="Alasan reschedule"
                   class="w-52 rounded-md border border-slate-300 px-3 py-2 text-sm">
            <button class="px-3 py-2 text-sm font-semibold border border-purple-300 rounded-md text-purple-700 hover:bg-purple-50">
              Minta Reschedule
            </button>
          </form>
        </div>
      </div>
    </div>
@endsection
