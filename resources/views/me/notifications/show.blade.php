@extends('layouts.app')
@section('title', 'Detail Interview')

@section('content')
@php
  $localTime = optional($iv->scheduled_at)->timezone(config('app.timezone'));
@endphp
<div class="max-w-3xl mx-auto space-y-6">
  @if(session('ok'))
    <div class="p-3 rounded-md bg-green-50 text-green-700 border border-green-200">{{ session('ok') }}</div>
  @endif

  <div class="rounded-xl border p-5 bg-white">
    <div class="flex items-start justify-between gap-4">
      <div>
        <div class="text-sm text-slate-500">{{ $localTime?->format('l, d M Y · H:i') }}</div>
        <h1 class="mt-1 text-2xl font-semibold">{{ $iv->application->job->title }}</h1>
        <div class="text-sm text-slate-600 mt-1">
          {{ strtoupper($iv->method) }} • {{ $iv->location ?: 'TBD' }}
        </div>
      </div>
      <div class="text-xs px-2 py-1 rounded-full h-fit
        @class([
          'bg-amber-50 text-amber-700 border border-amber-200' => $iv->status === 'pending',
          'bg-blue-50 text-blue-700 border border-blue-200'     => $iv->status === 'confirmed',
          'bg-red-50 text-red-700 border border-red-200'        => $iv->status === 'declined',
          'bg-purple-50 text-purple-700 border border-purple-200' => $iv->status === 'reschedule_requested',
        ])">
        {{ Str::of($iv->status)->headline() }}
      </div>
    </div>

    @if($iv->notes)
      <div class="mt-4 text-sm text-slate-700 whitespace-pre-line">
        {{ $iv->notes }}
      </div>
    @endif

    <div class="mt-5 flex flex-wrap items-center gap-3">
      <a href="{{ route('me.interviews.ics', $iv->id) }}" class="px-3 py-2 rounded-md border text-blue-700 border-blue-300 hover:bg-blue-100">Tambah ke Kalender (ICS)</a>

      @if($iv->status !== 'confirmed')
        <form action="{{ route('me.interviews.confirm', $iv->id) }}" method="POST">
          @csrf
          <input type="hidden" name="note" value="">
          <button class="px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Konfirmasi Hadir</button>
        </form>
      @endif

      @if($iv->status !== 'declined')
        <button x-data @click="$refs.dform.showModal()" class="px-3 py-2 rounded-md border text-red-700 border-red-300 hover:bg-red-50">Tolak</button>
      @endif

      <button x-data @click="$refs.rform.showModal()" class="px-3 py-2 rounded-md border text-purple-700 border-purple-300 hover:bg-purple-50">Minta Reschedule</button>
    </div>
  </div>

  {{-- Dialog Decline --}}
  <dialog x-ref="dform" class="modal">
    <form method="POST" action="{{ route('me.interviews.decline', $iv->id) }}" class="modal-box space-y-4">
      @csrf
      <h3 class="font-semibold text-lg">Tolak Interview</h3>
      <textarea name="reason" required class="w-full rounded-md border p-2" rows="4" placeholder="Sampaikan alasan singkat..."></textarea>
      <div class="flex justify-end gap-2">
        <button type="button" class="px-3 py-2 rounded-md border" @click="$refs.dform.close()">Batal</button>
        <button class="px-3 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Kirim Penolakan</button>
      </div>
    </form>
  </dialog>

  {{-- Dialog Reschedule --}}
  <dialog x-ref="rform" class="modal">
    <form method="POST" action="{{ route('me.interviews.request_reschedule', $iv->id) }}" class="modal-box space-y-4">
      @csrf
      <h3 class="font-semibold text-lg">Minta Reschedule</h3>
      <label class="block text-sm">Waktu usulan (opsional)</label>
      <input type="datetime-local" name="proposed_time" class="w-full rounded-md border p-2">
      <label class="block text-sm mt-2">Alasan (wajib)</label>
      <textarea name="reason" required class="w-full rounded-md border p-2" rows="4" placeholder="Jelaskan alasannya..."></textarea>
      <div class="flex justify-end gap-2">
        <button type="button" class="px-3 py-2 rounded-md border" @click="$refs.rform.close()">Batal</button>
        <button class="px-3 py-2 rounded-md bg-purple-600 text-white hover:bg-purple-700">Kirim Permintaan</button>
      </div>
    </form>
  </dialog>
</div>
@endsection
