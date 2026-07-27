@extends('layouts.app')
@section('title', 'Detail Lamaran - Karir Andalan')

@section('content')
  @php
    $user = $application->user;
    $job = $application->job;
    $tz = config('app.timezone', 'Asia/Jakarta');
  @endphp

  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-slate-900">Detail Lamaran</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $job?->title ?? '-' }} · {{ $user?->name ?? '-' }}</p>
      </div>
      <a href="{{ url()->previous() }}" class="px-4 py-2 text-sm border rounded-lg border-slate-200 hover:bg-slate-50">Kembali</a>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
      <section class="p-5 bg-white border rounded-xl border-slate-200 lg:col-span-2">
        <h2 class="font-semibold text-slate-900">Ringkasan</h2>
        <dl class="grid gap-3 mt-4 text-sm sm:grid-cols-2">
          <div><dt class="text-slate-500">Kandidat</dt><dd class="font-medium text-slate-900">{{ $user?->name ?? '-' }}</dd></div>
          <div><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900">{{ $user?->email ?? '-' }}</dd></div>
          <div><dt class="text-slate-500">Lowongan</dt><dd class="font-medium text-slate-900">{{ $job?->title ?? '-' }}</dd></div>
          <div><dt class="text-slate-500">Site</dt><dd class="font-medium text-slate-900">{{ $job?->site?->name ?? $job?->site?->code ?? '-' }}</dd></div>
          <div><dt class="text-slate-500">Stage Saat Ini</dt><dd class="font-medium text-slate-900">{{ ucfirst(str_replace('_', ' ', $application->current_stage ?? '-')) }}</dd></div>
          <div><dt class="text-slate-500">Status</dt><dd class="font-medium text-slate-900">{{ ucfirst($application->overall_status ?? '-') }}</dd></div>
        </dl>
      </section>

      <section class="p-5 bg-white border rounded-xl border-slate-200">
        <h2 class="font-semibold text-slate-900">Offer</h2>
        @if($application->offer)
          <p class="mt-3 text-sm text-slate-600">Status: <span class="font-semibold">{{ ucfirst($application->offer->status ?? '-') }}</span></p>
          @if(Route::has('admin.offers.pdf'))
            <a href="{{ route('admin.offers.pdf', $application->offer) }}" class="inline-flex mt-3 px-3 py-2 text-sm border rounded-lg border-slate-200 hover:bg-slate-50">Lihat PDF</a>
          @endif
        @else
          <p class="mt-3 text-sm text-slate-500">Belum ada offer.</p>
        @endif
      </section>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
      <section class="p-5 bg-white border rounded-xl border-slate-200">
        <h2 class="font-semibold text-slate-900">Riwayat Stage</h2>
        <div class="mt-4 space-y-3">
          @forelse($application->stages as $stage)
            <div class="pb-3 border-b last:border-0 border-slate-100">
              <div class="text-sm font-medium text-slate-900">{{ ucfirst(str_replace('_', ' ', $stage->stage_key ?? '-')) }}</div>
              <div class="text-xs text-slate-500">
                {{ optional($stage->created_at)->timezone($tz)->format('Y-m-d H:i') }}
                @if($stage->actor) · oleh {{ $stage->actor->name }} @endif
              </div>
            </div>
          @empty
            <p class="text-sm text-slate-500">Belum ada riwayat stage.</p>
          @endforelse
        </div>
      </section>

      <section class="p-5 bg-white border rounded-xl border-slate-200">
        <h2 class="font-semibold text-slate-900">Interview</h2>
        <div class="mt-4 space-y-3">
          @forelse($application->interviews as $iv)
            <div class="pb-3 border-b last:border-0 border-slate-100">
              <div class="text-sm font-medium text-slate-900">{{ $iv->title ?: 'Interview' }}</div>
              <div class="text-xs text-slate-500">
                {{ optional($iv->start_at)->timezone($tz)->format('Y-m-d H:i') }} · {{ strtoupper($iv->mode ?? '-') }}
              </div>
            </div>
          @empty
            <p class="text-sm text-slate-500">Belum ada jadwal interview.</p>
          @endforelse
        </div>
      </section>
    </div>

    <section class="p-5 bg-white border rounded-xl border-slate-200">
      <h2 class="font-semibold text-slate-900">Feedback</h2>
      <div class="mt-4 space-y-3">
        @forelse($application->feedbacks as $feedback)
          <div class="pb-3 border-b last:border-0 border-slate-100">
            <div class="text-sm whitespace-pre-line text-slate-700">{{ $feedback->notes ?? $feedback->feedback ?? '-' }}</div>
            <div class="mt-1 text-xs text-slate-500">
              {{ optional($feedback->created_at)->timezone($tz)->format('Y-m-d H:i') }}
              @if($feedback->user) · {{ $feedback->user->name }} @endif
            </div>
          </div>
        @empty
          <p class="text-sm text-slate-500">Belum ada feedback.</p>
        @endforelse
      </div>
    </section>
  </div>
@endsection
