{{-- resources/views/applications/mine.blade.php --}}
@extends('layouts.app')

@section('title', 'Lamaran Saya • karir-andalan')

@php
    // === THEME ===
    $PRIMARY = '#a77d52';
    $PRIMARY_DARK = '#8b5e3c';
    $SOFT = '#f5efe8';
    $CARD = '#fffaf5';
    $TEXT = '#6b4f3a';
    $BORD = '#e7ded6';

    // === STAGES ===
    $stageOrder = ['screening', 'psychological_test', 'hr_iv', 'post_test', 'user_iv', 'offer', 'mcu', 'mobilisasi', 'skill_test', 'finish'];
    $pretty = [
        'applied' => 'Screening',
        'screening' => 'Screening',
        'psychotest' => 'Psychological Test',
        'psychological_test' => 'Psychological Test',
        'hr_iv' => 'HR Interview',
        'post_test' => 'Post Test',
        'user_iv' => 'User Interview',
        'user_trainer_iv' => 'User Interview',
        'offer' => 'Offering Letter (OL)',
        'mcu' => 'Medical Check Up',
        'mobilisasi' => 'Mobilisasi (Travel)',
        'ground_test' => 'Skill Test',
        'skill_test' => 'Skill Test',
        'onsite' => 'Finish',
        'hired' => 'Finish',
        'not_qualified' => 'Finish',
        'rejected' => 'Finish',
        'finish' => 'Finish',
    ];
    $stageAlias = [
        'applied' => 'screening',
        'psychotest' => 'psychological_test',
        'user_trainer_iv' => 'user_iv',
        'ground_test' => 'skill_test',
        'onsite' => 'finish',
        'hired' => 'finish',
        'not_qualified' => 'finish',
        'rejected' => 'finish',
    ];

    $col = $apps->getCollection();
    $summary = [
        'total' => $apps->total(),
        'active' => $col->where('overall_status', 'active')->count(),
        'hired' => $col->where('overall_status', 'hired')->count(),
        'rejected' => $col->where('overall_status', 'rejected')->count(),
    ];

    $progressOf = function ($app) use ($stageOrder, $stageAlias) {
        $key = strtolower($app->current_stage ?? 'screening');
        $key = $stageAlias[$key] ?? $key;
        $idx = array_search($key, $stageOrder, true);
        if ($idx === false)
            $idx = 0;
        $max = max(count($stageOrder) - 1, 1);
        return (int) round($idx / $max * 100);
    };

    $badge = function ($overall) {
        return match (strtolower((string) $overall)) {
        'hired' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'active' => 'bg-[#fffaf5] text-[#8b5e3c] ring-[#ead8c5]',
        default => 'bg-slate-50 text-slate-700 ring-slate-200',
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
      <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-4.4 7-11a7 7 0 1 0-14 0c0 6.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></symbol>
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

    <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">

      {{-- HEADER — shared component --}}
      <x-admin.page-header eyebrow="Dashboard Pelamar" title="Lamaran Saya"
        description="Pantau progres seleksi, jadwal interview, dan offering dari satu halaman.">
        <a href="{{ route('jobs.index') }}" class="ph-action">
          Cari Lowongan
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M13 5l7 7-7 7"/></svg>
        </a>
      </x-admin.page-header>

      {{-- STATS --}}
      <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
           style="border-color: {{ $BORD }}">

        @php
          $stats = [
              ['Total', $summary['total'], $TEXT, 'users'],
              ['Aktif', $summary['active'], $PRIMARY, 'clock'],
              ['Hired', $summary['hired'], '#16a34a', 'check'],
              ['Rejected', $summary['rejected'], '#dc2626', 'x'],
          ];
        @endphp

        @foreach($stats as [$label, $val, $color, $icon])
            <div class="flex items-center gap-4 px-4 py-4 transition border rounded-xl bg-white shadow-sm hover:shadow-md"
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

      {{-- GRID --}}
      <section class="grid gap-5 mt-6 lg:grid-cols-2 xl:grid-cols-3">
        @foreach($apps as $app)
                @php
                    $job = $app->job;
                    $pct = $progressOf($app);
                    $siteLabel = $job?->site?->name ?? $job?->site?->code ?? '-';
                @endphp

                <article class="overflow-hidden transition bg-white border shadow-sm rounded-2xl hover:-translate-y-0.5 hover:shadow-lg"
                         style="border-color: {{ $BORD }}">

                  <div class="p-5 bg-gradient-to-br from-[#fffaf5] via-white to-white border-b" style="border-color: {{ $BORD }}">

              {{-- HEADER --}}
              <div class="flex items-start justify-between gap-3">
                <div class="flex min-w-0 items-start gap-3">

                  {{-- ICON JOB --}}
                  <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl shadow-sm"
                       style="background: {{ $PRIMARY }}; color: white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 7V6a2 2 0 012-2h8a2 2 0 012 2v1M6 7h12M6 7v11a2 2 0 002 2h8a2 2 0 002-2V7"/>
                    </svg>
                  </div>

                  <div class="min-w-0">
                    <h3 class="font-semibold leading-tight text-slate-950">
                      {{ $job->title ?? '-' }}
                    </h3>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                      <span>{{ $job->division ?? '-' }}</span>
                      <span class="text-slate-300">•</span>
                      <span>{{ $siteLabel }}</span>
                    </div>
                  </div>
                </div>

                {{-- STATUS --}}
                <div class="flex shrink-0 flex-col items-end gap-2">
                  <span class="rounded-full px-3 py-1.5 text-[11px] font-bold ring-1 ring-inset {{ $badge($app->overall_status) }}">
                    {{ $statusLabel($app->overall_status) }}
                  </span>
                  @if(data_get($app->minepro_progress ?? [], 'current_process'))
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                      Sinkron MinePro
                    </span>
                  @endif
                </div>
              </div>
                  </div>

            <div class="p-5">

              {{-- PROGRESS --}}
              <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                <div class="flex justify-between mb-1 text-xs"
                     style="color: {{ $TEXT }}">
                  <span class="flex items-center gap-1 font-bold">
                    {{-- ICON STEP --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5l7 7-7 7"/>
                    </svg>
                    {{ $pretty[$stageAlias[strtolower((string) $app->current_stage)] ?? $app->current_stage] ?? '-' }}
                  </span>

                  <span class="font-semibold">{{ $pct }}%</span>
                </div>

                {{-- BAR --}}
                <div class="h-2.5 overflow-hidden bg-white rounded-full ring-1 ring-slate-200">
                  <div class="h-full transition-all duration-500 rounded-full"
                       style="width: {{ $pct }}%; background: {{ $PRIMARY }};">
                  </div>
                </div>
              </div>

              <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                  <p class="text-[11px] font-medium text-slate-500">Tanggal Lamar</p>
                  <p class="mt-1 font-semibold text-slate-900">{{ optional($app->created_at)->format('d M Y') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                  <p class="text-[11px] font-medium text-slate-500">Lokasi</p>
                  <p class="mt-1 truncate font-semibold text-slate-900">{{ $siteLabel }}</p>
                </div>
              </div>

              {{-- STAGES --}}
              <div class="mt-5 space-y-2">
                @foreach($stageOrder as $key)
                    @php $currentStageKey = $stageAlias[strtolower((string) $app->current_stage)] ?? strtolower((string) $app->current_stage); $isCurrentStage = $currentStageKey === $key; @endphp
                    <div class="flex items-center gap-3 text-xs {{ $isCurrentStage ? 'font-bold text-[#8b5e3c]' : 'text-slate-500' }}">
                      <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full ring-1 ring-inset {{ $isCurrentStage ? 'bg-[#fffaf5] ring-[#ead8c5]' : 'bg-slate-50 ring-slate-200' }}">
                        @if($isCurrentStage)
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                          </svg>
                        @else
                          <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                        @endif
                      </span>
                      <span class="truncate">{{ $pretty[$key] }}</span>
                      @if($isCurrentStage)
                        <span class="ml-auto rounded-full bg-[#fffaf5] px-2 py-0.5 text-[10px] font-bold text-[#8b5e3c] ring-1 ring-inset ring-[#ead8c5]">Aktif</span>
                      @endif
                    </div>
                @endforeach
              </div>

              {{-- FOOTER --}}
              <div class="mt-5 border-t pt-4 text-sm" style="border-color: {{ $BORD }}">
                <div class="flex flex-col gap-3">
                  <div class="flex flex-wrap items-center gap-2">
                    @if($app->interviews && $app->interviews->count())
                      <a href="{{ route('me.interviews.show', $app->interviews->first()) }}"
                         class="inline-flex items-center gap-1 rounded-lg border border-[#a77d52] px-3 py-2 text-xs font-semibold text-[#a77d52] transition hover:bg-[#a77d52] hover:text-white"
                         title="Lihat Jadwal Interview">
                        Interview
                      </a>
                    @endif
                      @php
                        $olStatus = $app->relationLoaded('offer') && $app->offer ? strtolower($app->offer->status) : null;
                        $currentStageKey = $stageAlias[strtolower((string) $app->current_stage)] ?? strtolower((string) $app->current_stage);
                        $canAcceptOl = (in_array($currentStageKey, ['finish', 'offer'], true) || $olStatus === 'sent')
                          && strtolower((string) $app->overall_status) !== 'hired'
                          && strtolower((string) $app->overall_status) !== 'rejected';
                      @endphp
                      @if($canAcceptOl)
                        <div class="flex items-center gap-2">
                          <form method="POST" action="{{ route('applications.accept-offer', $app) }}" class="inline-flex">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
                                    style="background: {{ $PRIMARY }}">
                              @if($olStatus === 'sent') ⏳ @endif Terima OL
                            </button>
                          </form>
                          <button type="button"
                                  class="inline-flex items-center gap-1 rounded-lg border border-red-500 px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
                                  style="background: rgba(220,38,38,0.8)"
                                  onclick="openRejectOlModal('{{ $app->id }}', '{{ $app->user->name }}')">
                            Tolak OL
                          </button>
                        </div>
                      @elseif($olStatus === 'accepted')
                        <span class="rounded-lg px-3 py-1.5 text-xs font-semibold"
                              style="background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7;">
                          ✔ Sudah Terima OL
                        </span>
                      @elseif($olStatus === 'rejected')
                        <span class="rounded-lg px-3 py-1.5 text-xs font-semibold"
                              style="background: #ffebee; color: #c62828; border: 1px solid #ef9a9a;">
                          ✕ OL Ditolak
                        </span>
                      @endif
                    <a href="{{ route('jobs.show', $app->job_id) }}"
                       class="ml-auto inline-flex items-center gap-1 rounded-lg px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
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
                  <div class="mt-3 flex items-center gap-1 text-xs text-slate-600">
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
          <div class="mt-6 overflow-hidden border bg-white text-center shadow-sm rounded-2xl"
               style="border-color: {{ $BORD }}">
            <div class="p-8" style="background: {{ $CARD }};">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-white text-[#a77d52] ring-1 ring-[#ead8c5]">
              <svg class="h-7 w-7" aria-hidden="true"><use href="#i-brief"/></svg>
            </div>
            <p class="mt-4 text-lg font-semibold" style="color: {{ $TEXT }}">Belum ada lamaran yang tersimpan</p>
            <p class="max-w-xl mx-auto mt-2 text-sm text-slate-600">
              Lamaran baru akan masuk ke halaman ini setelah profil kandidat lengkap dan kamu menekan tombol Lamar pada lowongan.
            </p>
            <div class="flex flex-wrap justify-center gap-2 mt-4">
              <a href="{{ route('jobs.index') }}"
                 class="abtn abtn-primary">
                Cari Lowongan
              </a>
              @if(Route::has('candidate.profiles.edit'))
                @if(!empty($latestOpenJob))
                  <a href="{{ route('candidate.profiles.edit', ['job' => $latestOpenJob->id]) }}"
                     class="abtn abtn-neutral">
                    Lengkapi Profil
                  </a>
                @endif
              @endif
            </div>
            </div>
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
          window.KarirFeedback?.open({
            type: 'success',
            title: 'Penolakan berhasil dikirim',
            message: 'HR akan menghubungi kamu untuk proses berikutnya.',
          });
          closeRejectOlModal();
          setTimeout(() => location.reload(), 500);
        } else {
          window.KarirFeedback?.open({
            type: 'error',
            title: 'Penolakan gagal dikirim',
            message: window.KarirApiError?.sanitize(data.message || data.error, 'Data tidak dapat diproses. Silakan coba kembali.') || 'Data tidak dapat diproses. Silakan coba kembali.',
          });
          btn.disabled = false;
          btn.textContent = 'Tolak OL';
        }
      })
      .catch(err => {
        window.KarirFeedback?.open({
          type: 'error',
          title: 'Tidak dapat terhubung',
          message: window.KarirApiError?.sanitize(err.message, 'Tidak dapat terhubung ke server. Periksa koneksi Anda lalu coba kembali.') || 'Tidak dapat terhubung ke server. Periksa koneksi Anda lalu coba kembali.',
        });
        btn.disabled = false;
        btn.textContent = 'Tolak OL';
      });
    }
    </script>

@endsection
