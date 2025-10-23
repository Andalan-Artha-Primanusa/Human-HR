@extends('layouts.app')
@section('title','Notifikasi')

@section('content')
@php
  use Illuminate\Pagination\LengthAwarePaginator;
  use Illuminate\Support\Collection;

  // tab aktif
  $tab = request('tab','all');

  // koleksi dari controller (fallback aman)
  /** @var \Illuminate\Support\Collection $unread */
  /** @var \Illuminate\Support\Collection $read */
  $unread      = isset($unread) && $unread instanceof Collection ? $unread : collect();
  $read        = isset($read)   && $read instanceof Collection   ? $read   : collect();
  $unreadCount = $unread->count();

  // gabungkan & sort desc by created_at untuk tab "all"
  $base = $tab === 'unread'
        ? $unread
        : $unread->concat($read)->sortByDesc(fn($n) => $n->created_at);

  // ===== Manual pagination di view (tanpa ubah controller) =====
  $perPage = (int) request('per_page', 15);
  $page    = (int) request('page', 1);
  $total   = $base->count();
  $items   = $base->slice(($page - 1) * $perPage, $perPage)->values();

  /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $notifications */
  $notifications = new LengthAwarePaginator(
      $items,
      $total,
      $perPage,
      $page,
      ['path' => request()->url(), 'query' => request()->query()]
  );
@endphp

<div class="max-w-4xl mx-auto space-y-4">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold text-slate-900">Notifikasi</h1>
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

  {{-- Tabs --}}
  <div class="flex items-center gap-2 text-sm">
    <a href="{{ route('me.notifications.index', array_merge(request()->except('page'), ['tab'=>'all'])) }}"
       class="px-3 py-1.5 rounded-md border {{ $tab==='all' ? 'border-blue-300 text-blue-700 bg-blue-50' : 'border-slate-300 text-slate-700 hover:bg-slate-50' }}">
      Semua
    </a>
    <a href="{{ route('me.notifications.index', array_merge(request()->except('page'), ['tab'=>'unread'])) }}"
       class="px-3 py-1.5 rounded-md border {{ $tab==='unread' ? 'border-blue-300 text-blue-700 bg-blue-50' : 'border-slate-300 text-slate-700 hover:bg-slate-50' }}">
      Belum dibaca
      @if($unreadCount > 0)
        <span class="ml-2 text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-700 border border-red-200">{{ $unreadCount }}</span>
      @endif
    </a>
  </div>

  @if($notifications->isEmpty())
    <div class="rounded-xl border border-slate-200 bg-white p-6 text-slate-600">
      Tidak ada notifikasi.
    </div>
  @else
    <div class="rounded-xl border border-slate-200 bg-white divide-y">
      @foreach($notifications as $n)
        @php
          $data = is_array($n->data) ? $n->data : (json_decode($n->data, true) ?: []);
          $title = $data['title'] ?? ($data['type'] ?? class_basename($n->type) ?? 'Notifikasi');
          $desc  = $data['message'] ?? ($data['excerpt'] ?? ($data['job_title'] ?? null));
          $cta   = $data['url'] ?? ($data['cta_url'] ?? null);
          $isUnread = is_null($n->read_at);
        @endphp
        <div class="p-4 {{ $isUnread ? 'bg-blue-50/40' : '' }}">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-sm font-medium text-slate-900">{{ $title }}</div>
              @if($desc)
                <div class="text-xs text-slate-600 mt-0.5">{{ $desc }}</div>
              @endif
              <div class="text-[11px] text-slate-500 mt-0.5">{{ optional($n->created_at)->diffForHumans() }}</div>
              @if($cta)
                <a href="{{ $cta }}" target="_blank" rel="noopener"
                   class="inline-block mt-2 text-xs text-blue-700 hover:underline">Buka tautan</a>
              @endif
            </div>

            <div class="shrink-0 flex items-center gap-2">
              <form action="{{ route('me.notifications.read', $n->id) }}" method="POST">
                @csrf
                <button class="px-2.5 py-1 rounded-md border text-xs {{ $isUnread ? 'border-blue-300 text-blue-700 hover:bg-blue-50' : 'border-slate-300 text-slate-700 hover:bg-slate-50' }}">
                  {{ $isUnread ? 'Tandai dibaca' : 'Sudah dibaca' }}
                </button>
              </form>
              <form action="{{ route('me.notifications.destroy', $n->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi ini?')">
                @csrf @method('DELETE')
                <button class="px-2.5 py-1 rounded-md border border-red-200 text-xs text-red-700 hover:bg-red-50">
                  Hapus
                </button>
              </form>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="mt-4">
      {{-- bawa juga tab & per_page saat pindah halaman --}}
      {{ $notifications->appends(['tab'=>$tab,'per_page'=>$perPage])->links() }}
    </div>
  @endif
</div>
@endsection
