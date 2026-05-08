{{-- resources/views/applications/mine.blade.php --}}
@extends('layouts.app')

@section('title', 'Lamaran Saya • karir-andalan')

@php
    // === THEME (GANTI TOTAL KE COKLAT) ===
    $PRIMARY = '#a77d52';
    $SOFT = '#f5efe8';
    $CARD = '#ede5dc';
    $TEXT = '#6b4f3a';
    $BORD = '#e7ded6';

    // === STAGES ===
    $stageOrder = ['applied', 'psychotest', 'hr_iv', 'user_iv', 'final', 'offer', 'onsite', 'hired'];
    $pretty = [
        'applied' => 'Pengajuan Berkas',
        'psychotest' => 'Psikotes',
        'hr_iv' => 'HR Interview',
        'user_iv' => 'User Interview',
        'final' => 'Final',
        'offer' => 'Offering',
        'onsite' => 'Onsite',
        'hired' => 'Diterima',
        'rejected' => 'Ditolak'
    ];

    $col = $apps->getCollection();
    $summary = [
        'total' => $apps->total(),
        'active' => $col->where('overall_status', 'active')->count(),
        'hired' => $col->where('overall_status', 'hired')->count(),
        'rejected' => $col->where('overall_status', 'rejected')->count(),
    ];

    $progressOf = function ($app) use ($stageOrder) {
        $key = strtolower($app->current_stage ?? 'applied');
        $idx = array_search($key, $stageOrder, true);
        if ($idx === false)
            $idx = 0;
        $max = max(count($stageOrder) - 1, 1);
        return (int) round($idx / $max * 100);
    };

    $badge = function ($overall) {
        return match (strtolower((string) $overall)) {
            'hired' => 'bg-[#ede5dc] text-[#6b4f3a]',
            'rejected' => 'bg-[#f5efe8] text-[#6b4f3a]',
            'active' => 'bg-[#ede5dc] text-[#a77d52]',
            default => 'bg-[#f5efe8] text-[#6b4f3a]',
        };
    };

    $statusLabel = function ($overall) {
      return match (strtolower((string) $overall)) {
        'hired' => 'Sudah Keterima',
        'rejected' => 'Ditolak',
        'active' => 'Masih Berjalan',
        default => strtoupper((string) $overall),
      };
    };
@endphp

@section('content')

    {{-- ICON (TETAP ADA) --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="hidden">
      <symbol id="i-brief" viewBox="0 0 24 24"><rect x="3" y="7" width="18" height="12" rx="2"/></symbol>
      <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/></symbol>
      <symbol id="i-check" viewBox="0 0 24 24"><path d="M4 12l5 5 11-11"/></symbol>
      <symbol id="i-x" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6l-12 12"/></symbol>
      <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 5l7 7-7 7"/></symbol>
    </svg>


    {{-- ALERT FLOATING TENGAH --}}
    @if(session('success') || session('info'))
      <div id="flash-alert" style="position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);z-index:1000;min-width:320px;max-width:90vw;" class="flex items-center justify-center">
        <div class="px-6 py-4 text-lg font-semibold text-center bg-white border shadow-lg rounded-xl border-emerald-300 animate-fadein">
          @if(session('success'))
            <span class="text-emerald-700">{{ session('success') }}</span>
          @endif
          @if(session('info'))
            <span class="text-blue-700">{{ session('info') }}</span>
          @endif
          <button onclick="document.getElementById('flash-alert').remove()" class="ml-4 text-slate-400 hover:text-slate-700">&times;</button>
        </div>
      </div>
      <style>@keyframes fadein{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:scale(1)}}</style>
      <script>setTimeout(()=>{const e=document.getElementById('flash-alert');if(e)e.remove()},5000)</script>
    @endif

    <div class="px-6 py-8 mx-auto max-w-7xl">

      {{-- HEADER --}}
      <section class="overflow-hidden border shadow-sm rounded-2xl"
               style="border-color: {{ $BORD }}">

        <div class="flex items-center justify-between p-6"
             style="background: {{ $PRIMARY }}">
          <div>
            <h1 class="text-2xl font-semibold text-white">Lamaran Saya</h1>
            <p class="text-sm text-white/80">Pantau progres seleksi kamu secara ringkas</p>
          </div>

          <a href="{{ route('jobs.index') }}"
             class="px-4 py-2 text-sm font-semibold bg-white rounded-lg"
             style="color: {{ $PRIMARY }}">
             Cari Lowongan
          </a>
        </div>

    {{-- STATS --}}
    <div class="grid gap-4 p-5 sm:grid-cols-2 xl:grid-cols-4"
         style="background: #ffffff">

      @php
        $stats = [
            ['Total', $summary['total'], $TEXT, 'users'],
            ['Aktif', $summary['active'], $PRIMARY, 'clock'],
            ['Hired', $summary['hired'], '#16a34a', 'check'],
            ['Rejected', $summary['rejected'], '#dc2626', 'x'],
        ];
      @endphp

      @foreach($stats as [$label, $val, $color, $icon])
          <div class="flex items-center gap-4 px-4 py-4 transition border rounded-xl hover:shadow-md"
               style="border-color: {{ $BORD }}">

            {{-- ICON --}}
            <div class="p-2 rounded-lg"
                 style="background: {{ $color }}20; color: {{ $color }}">

              @if($icon === 'users')
                <!-- Users -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-1a4 4 0 00-5-3.87M9 20H4v-1a4 4 0 015-3.87m0 0a4 4 0 110-8 4 4 0 010 8zm8 0a4 4 0 10-8 0"/>
                </svg>
              @elseif($icon === 'clock')
                <!-- Clock -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              @elseif($icon === 'check')
                <!-- Check -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7"/>
                </svg>
              @elseif($icon === 'x')
                <!-- X -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
              @endif

            </div>

            {{-- TEXT --}}
            <div>
              <div class="text-xs text-slate-500">{{ $label }}</div>
              <div class="text-2xl font-semibold" style="color: {{ $color }}">
                {{ $val }}
              </div>
            </div>

          </div>
      @endforeach
    </div>
      </section>

      {{-- GRID --}}
      <section class="grid gap-4 mt-6 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($apps as $app)
                @php
                    $job = $app->job;
                    $pct = $progressOf($app);
                @endphp

                <article class="transition border shadow-sm rounded-2xl hover:shadow-md"
                         style="border-color: {{ $BORD }}; background: {{ $CARD }}">

                  {{-- STRIP --}}
                  <div class="h-1.5 rounded-t-2xl"
                       style="background: {{ $PRIMARY }}"></div>

            <div class="p-5 transition bg-white border rounded-xl hover:shadow-md"
                 style="border-color: {{ $BORD }}">

              {{-- HEADER --}}
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-2">

                  {{-- ICON JOB --}}
                  <div class="p-2 rounded-lg"
                       style="background: {{ $PRIMARY }}20; color: {{ $PRIMARY }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7V6a2 2 0 012-2h8a2 2 0 012 2v1M6 7h12M6 7v11a2 2 0 002 2h8a2 2 0 002-2V7"/>
                    </svg>
                  </div>

                  <h3 class="font-semibold leading-tight">
                    {{ $job->title ?? '-' }}
                  </h3>
                </div>

                {{-- STATUS --}}
                <span class="text-[11px] px-2.5 py-1 rounded-full font-medium {{ $badge($app->overall_status) }}">
                  {{ $statusLabel($app->overall_status) }}
                </span>
              </div>

              {{-- PROGRESS --}}
              <div class="mt-4">
                <div class="flex justify-between mb-1 text-xs"
                     style="color: {{ $TEXT }}">
                  <span class="flex items-center gap-1">
                    {{-- ICON STEP --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"/>
                    </svg>
                    {{ $pretty[$app->current_stage] ?? '-' }}
                  </span>

                  <span class="font-medium">{{ $pct }}%</span>
                </div>

                {{-- BAR --}}
                <div class="h-2 overflow-hidden bg-gray-200 rounded-full">
                  <div class="h-full transition-all duration-500 rounded-full"
                       style="width: {{ $pct }}%; background: linear-gradient(to right, {{ $PRIMARY }}, #6366f1)">
                  </div>
                </div>
              </div>

              {{-- STAGES --}}
              <div class="mt-4 flex flex-wrap gap-1.5">
                @foreach($stageOrder as $key)
                    <span class="text-[11px] px-2 py-1 rounded-full flex items-center gap-1"
                          style="background: {{ $SOFT }}; color: {{ $TEXT }}">

                      {{-- DOT --}}
                      <span class="w-1.5 h-1.5 rounded-full"
                            style="background: {{ $PRIMARY }}"></span>

                      {{ $pretty[$key] }}
                    </span>
                @endforeach
              </div>

              {{-- FOOTER --}}
              <div class="flex flex-col gap-1 mt-5 text-sm">
                <div class="flex items-center justify-between">
                  <span class="flex items-center gap-1.5" style="color: {{ $TEXT }}">
                    {{-- ICON DATE --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10m-11 8h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ optional($app->created_at)->format('d M Y') }}
                  </span>
                  <div class="flex items-center gap-2">
                    @if($app->interviews && $app->interviews->count())
                      <a href="{{ route('me.interviews.show', $app->interviews->first()) }}"
                         class="px-3 py-1.5 rounded-lg text-[#a77d52] border border-[#a77d52] text-xs font-medium flex items-center gap-1 hover:bg-[#a77d52] hover:text-white transition"
                         title="Lihat Jadwal Interview">
                        Interview
                      </a>
                    @endif
                      @php
                        $olStatus = $app->relationLoaded('offer') && $app->offer ? strtolower($app->offer->status) : null;
                        $canAcceptOl = (in_array(strtolower((string) $app->current_stage), ['final', 'offer'], true) || $olStatus === 'sent')
                          && strtolower((string) $app->overall_status) !== 'hired'
                          && strtolower((string) $app->overall_status) !== 'rejected';
                      @endphp
                      @if($canAcceptOl)
                        <div class="flex items-center gap-2">
                          <form method="POST" action="{{ route('applications.accept-offer', $app) }}" class="inline-flex">
                            @csrf
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-lg text-white text-xs font-medium flex items-center gap-1 hover:opacity-90 transition"
                                    style="background: {{ $PRIMARY }}">
                              @if($olStatus === 'sent') ⏳ @endif Terima OL
                            </button>
                          </form>
                          <button type="button"
                                  class="px-3 py-1.5 rounded-lg text-white text-xs font-medium flex items-center gap-1 hover:opacity-90 transition border border-red-500"
                                  style="background: rgba(220,38,38,0.8)"
                                  onclick="openRejectOlModal('{{ $app->id }}', '{{ $app->user->name }}')">
                            Tolak OL
                          </button>
                        </div>
                      @elseif($olStatus === 'accepted')
                        <span class="px-3 py-1.5 rounded-lg text-xs font-medium"
                              style="background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7;">
                          ✔ Sudah Terima OL
                        </span>
                      @elseif($olStatus === 'rejected')
                        <span class="px-3 py-1.5 rounded-lg text-xs font-medium"
                              style="background: #ffebee; color: #c62828; border: 1px solid #ef9a9a;">
                          ✕ OL Ditolak
                        </span>
                      @endif
                    <a href="{{ route('jobs.show', $app->job_id) }}"
                       class="px-3 py-1.5 rounded-lg text-white text-xs font-medium flex items-center gap-1 hover:opacity-90 transition"
                       style="background: {{ $PRIMARY }}">
                      Detail
                      {{-- ICON ARROW --}}
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                           fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5l7 7-7 7"/>
                      </svg>
                    </a>
                  </div>
                </div>
                @if($app->poh)
                  <div class="flex items-center gap-1 mt-1 text-xs text-slate-600">
                    <svg class="w-4 h-4 text-slate-400" aria-hidden="true"><use href="#i-pin"/></svg>
                    <span>POH: {{ $app->poh->name }}</span>
                  </div>
                @endif
              </div>

            </div>
                </article>
        @endforeach
      </section>

      {{-- EMPTY --}}
      @if(!$apps->count())
          <div class="p-6 mt-6 text-center border rounded-xl"
               style="border-color: {{ $BORD }}; background: {{ $CARD }}">
            <p style="color: {{ $TEXT }}">Belum ada lamaran</p>
          </div>
      @endif

    </div>

    {{-- MODAL: REJECT OL --}}
    <div id="modal-reject-ol" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50" onclick="if(event.target === this) closeRejectOlModal()">
      <div class="bg-white rounded-xl shadow-lg max-w-md w-full mx-4" onclick="event.stopPropagation()">
        <div class="p-6 border-b" style="border-color: {{ $BORD }}">
          <h3 class="text-lg font-semibold" style="color: {{ $TEXT }}">Tolak Offering Letter</h3>
          <p class="text-sm mt-1" style="color: #9a7558">Berikan alasan penolakan (opsional). HR akan menghubungi Anda.</p>
        </div>
        <div class="p-6">
          <textarea id="reject-ol-reason"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2"
                    style="border-color: {{ $BORD }}; --tw-ring-color: {{ $PRIMARY }}; min-height: 100px"
                    placeholder="Jelaskan alasan Anda menolak offering letter ini..."></textarea>
          <p class="text-xs mt-2" style="color: #c4a882">Catatan: Informasi ini akan dikirim ke tim HR.</p>
        </div>
        <div class="p-6 border-t flex gap-2 justify-end" style="border-color: {{ $BORD }}">
          <button type="button"
                  class="px-4 py-2 rounded-lg border text-sm font-medium transition"
                  style="border-color: {{ $BORD }}; color: {{ $TEXT }}"
                  onclick="closeRejectOlModal()">
            Batal
          </button>
          <button type="button"
                  class="px-4 py-2 rounded-lg text-white text-sm font-medium transition hover:opacity-90"
                  style="background: rgba(220,38,38,0.8)"
                  id="btn-submit-reject-ol"
                  onclick="submitRejectOl()">
            Tolak OL
          </button>
        </div>
      </div>
    </div>

    <script>
    let _rejectOlAppId = null;
    
    function openRejectOlModal(appId, userName) {
      _rejectOlAppId = appId;
      document.getElementById('reject-ol-reason').value = '';
      document.getElementById('modal-reject-ol').classList.remove('hidden');
    }
    
    function closeRejectOlModal() {
      document.getElementById('modal-reject-ol').classList.add('hidden');
      _rejectOlAppId = null;
    }
    
    function submitRejectOl() {
      const reason = document.getElementById('reject-ol-reason').value.trim();
      if (!_rejectOlAppId) return;
      
      const btn = document.getElementById('btn-submit-reject-ol');
      btn.disabled = true;
      btn.textContent = 'Mengirim...';
      
      fetch(`{{ url('/me/applications') }}/${_rejectOlAppId}/reject-offer`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ rejection_reason: reason || 'Menolak penawaran (tanpa alasan)' })
      })
      .then(res => res.json())
      .then(data => {
        if (data.ok || data.message?.includes('berhasil')) {
          alert('Penolakan berhasil dikirim. HR akan menghubungi Anda.');
          closeRejectOlModal();
          setTimeout(() => location.reload(), 500);
        } else {
          alert('Error: ' + (data.message || data.error || 'Gagal menolak OL'));
          btn.disabled = false;
          btn.textContent = 'Tolak OL';
        }
      })
      .catch(err => {
        alert('Error: ' + err.message);
        btn.disabled = false;
        btn.textContent = 'Tolak OL';
      });
    }
    </script>

@endsection