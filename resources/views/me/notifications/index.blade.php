@extends('layouts.app')
@section('title','Notifikasi')

@section('content')
@php
  $tab = $tab ?? request('tab','all');
@endphp

<div class="max-w-4xl mx-auto space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Notifikasi</h1>
    <form action="{{ route('me.notifications.read_all') }}" method="POST">
      @csrf
      <button class="px-3 py-1.5 rounded-md border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm">
        Tandai semua dibaca
      </button>
    </form>
  </div>

  @if(session('ok'))
    <div class="p-3 rounded-md bg-green-50 text-green-700 border border-green-200">{{ session('ok') }}</div>
  @endif
  @if(session('warn'))
    <div class="p-3 rounded-md bg-yellow-50 text-yellow-800 border border-yellow-200">{{ session('warn') }}</div>
  @endif

  <div class="flex items-center gap-2 text-sm">
    <a href="{{ route('me.notifications.index', ['tab'=>'all']) }}"
       class="px-3 py-1.5 rounded-md border {{ $tab==='all' ? 'border-blue-300 text-blue-700 bg-blue-50' : 'border-slate-300 text-slate-700 hover:bg-slate-50' }}">
      Semua
    </a>
    <a href="{{ route('me.notifications.index', ['tab'=>'unread']) }}"
       class="px-3 py-1.5 rounded-md border {{ $tab==='unread' ? 'border-blue-300 text-blue-700 bg-blue-50' : 'border-slate-300 text-slate-700 hover:bg-slate-50' }}">
      Belum dibaca
      @isset($unreadCount)
        @if($unreadCount > 0)
          <span class="ml-2 text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-200">{{ $unreadCount }}</span>
        @endif
      @endisset
    </a>
  </div>

  @if(empty($notifications) || $notifications->isEmpty())
    <div class="rounded-xl border bg-white p-6 text-slate-600">
      Tidak ada notifikasi.
    </div>
  @else
    <div class="rounded-xl border bg-white divide-y">
      @foreach($notifications as $n)
        @php
          $data = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?: []);
          $title = $data['title'] ?? ($data['type'] ?? 'Notification');
          $desc  = $data['message'] ?? ($data['job_title'] ?? null);
          $cta   = $data['cta_url'] ?? null;
        @endphp
        <div class="p-4 {{ is_null($n->read_at) ? 'bg-blue-50/40' : '' }}">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-sm font-medium text-slate-800">{{ $title }}</div>
              @if($desc)
                <div class="text-xs text-slate-600 mt-0.5">{{ $desc }}</div>
              @endif
              <div class="text-[11px] text-slate-500 mt-0.5">{{ $n->created_at->diffForHumans() }}</div>
              @if($cta)
                <a href="{{ $cta }}" target="_blank" rel="noopener"
                   class="inline-block mt-2 text-xs text-blue-700 hover:underline">Buka tautan</a>
              @endif
            </div>
            <form action="{{ route('me.notifications.read_one', $n->id) }}" method="POST" class="shrink-0">
              @csrf
              <button class="px-2.5 py-1 rounded-md border text-xs {{ is_null($n->read_at) ? 'border-blue-300 text-blue-700 hover:bg-blue-50' : 'border-slate-300 text-slate-700 hover:bg-slate-50' }}">
                {{ is_null($n->read_at) ? 'Tandai dibaca' : 'Sudah dibaca' }}
              </button>
            </form>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-4">
      {{ $notifications->links() }}
    </div>
  @endif
</div>
@endsection
