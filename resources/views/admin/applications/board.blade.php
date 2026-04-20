{{-- resources/views/admin/applications/kanban.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Kanban Kandidat'])

@section('content')
<style>
/* ===== BASE ===== */
:root {
  --br-dark: #6b3f1f;
  --br-mid: #8b5e3c;
  --br-light: #a77d52;
  --br-pale: #c9a882;
  --br-bg: #f7f3ef;
  --br-surface: #f0ebe4;
}

/* ===== PAGE HEADER ===== */
.kn-header {
  background: linear-gradient(135deg, #8b5e3c 0%, #a77d52 60%, #c9a882 100%);
  padding: 1.5rem 2rem 1.25rem;
  border-radius: 0 0 1.25rem 1.25rem;
  color: #fff;
  position: relative;
  overflow: hidden;
  margin-bottom: 1.25rem;
}
.kn-header::after {
  content: '';
  position: absolute;
  right: -40px; top: -40px;
  width: 200px; height: 200px;
  border-radius: 50%;
  background: rgba(255,255,255,.08);
}
.kn-header h1 { font-size: 1.6rem; font-weight: 700; letter-spacing: -.4px; }
.kn-header p  { font-size: .82rem; opacity: .85; margin-top: .3rem; }

/* ===== FILTER BAR ===== */
.kn-filter {
  display: flex; flex-wrap: wrap; gap: .6rem; align-items: center;
  background: #fff; border-bottom: 1px solid #e2d9cf;
  padding: .75rem 1.5rem;
}
.kn-filter input,
.kn-filter select {
  border: 1.5px solid #d4c4b0; border-radius: .55rem;
  padding: .42rem .85rem; font-size: .82rem;
  background: #faf7f4; color: #5a3e28; outline: none;
  min-width: 140px;
  transition: border-color .15s, box-shadow .15s;
}
.kn-filter input:focus,
.kn-filter select:focus {
  border-color: var(--br-light);
  box-shadow: 0 0 0 3px rgba(167,125,82,.18);
}
.kn-filter .btn-filter {
  background: linear-gradient(135deg, #a77d52, #8b5e3c);
  color: #fff; border: none; border-radius: .55rem;
  padding: .44rem 1.1rem; font-size: .82rem; font-weight: 700;
  cursor: pointer; letter-spacing: .2px;
  transition: opacity .15s;
}
.kn-filter .btn-filter:hover { opacity: .9; }

/* ===== BOARD ===== */
.kn-board-wrap { overflow-x: auto; padding: 1rem 1.25rem 3rem; -webkit-overflow-scrolling: touch; }
.kn-board { display: flex; gap: .9rem; align-items: flex-start; min-width: max-content; }

/* ===== COLUMN ===== */
.kn-col {
  width: 276px;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid #e2d9cf;
  display: flex; flex-direction: column;
  max-height: 80vh;
  box-shadow: 0 2px 14px rgba(107,63,31,.06);
  transition: outline .1s;
}
.kn-col.dragover .kn-col-body {
  background: rgba(167,125,82,.07);
  outline: 2px dashed #c9a882;
  border-radius: .5rem;
}
.kn-col-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: .7rem 1rem;
  background: linear-gradient(90deg, #f5f0ea, #ede6dc);
  border-bottom: 1px solid #ddd3c4;
  border-radius: 1rem 1rem 0 0;
  position: sticky; top: 0; z-index: 2;
}
.kn-col-title {
  font-size: .68rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .7px;
  color: #7a4f2a;
}
.kn-col-badge {
  background: #8b5e3c; color: #fff;
  font-size: .65rem; font-weight: 700;
  border-radius: 999px; padding: .12rem .5rem;
  min-width: 22px; text-align: center;
}
.kn-col-body {
  padding: .7rem .6rem;
  overflow-y: auto; flex: 1;
  display: flex; flex-direction: column; gap: .6rem;
}

/* ===== CARD ===== */
.kn-card {
  background: #fff; border: 1px solid #e8dfd4;
  border-radius: .875rem; padding: .875rem;
  cursor: grab; position: relative;
  transition: transform .15s, box-shadow .15s, border-color .2s, opacity .2s;
}
.kn-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107,63,31,.13);
  border-color: #c9a882;
}
.kn-card:active { cursor: grabbing; }
.kn-card.dragging { opacity: .45; transform: scale(.97); }
.kn-card.card-locked { opacity: .55; cursor: not-allowed; }
.kn-card.card-locked .kn-card-name { color: #8a7060; }

.kn-card-name  { font-weight: 700; font-size: .87rem; color: #3d1f08; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kn-card-job   { font-size: .72rem; color: #9a7558; margin-top: .1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kn-card-email { font-size: .7rem; color: #b89070; margin-top: .3rem; }
.kn-card-role  { font-size: .68rem; color: #c0a080; margin-top: .1rem; }

.kn-pill { display: inline-flex; align-items: center; gap: .25rem; font-size: .65rem; font-weight: 700; padding: .18rem .55rem; border-radius: 999px; letter-spacing: .2px; margin-top: .45rem; }
.pill-active   { background: #fef3e2; color: #92580b; }
.pill-hired    { background: #e6f4ea; color: #256629; }
.pill-nq       { background: #fdeaea; color: #9b2525; }

.kn-lock-note {
  font-size: .65rem; color: #b05020;
  background: #fff3ed; border: 1px dashed #e8b090;
  border-radius: .4rem; padding: .28rem .6rem;
  margin-top: .5rem; display: flex; align-items: center; gap: .3rem;
}

.kn-card-actions {
  display: flex; flex-wrap: wrap; gap: .4rem;
  margin-top: .65rem; padding-top: .6rem;
  border-top: 1px solid #f0e9df;
}

/* ===== BUTTONS ===== */
.btn-xs {
  font-size: .67rem; font-weight: 700; padding: .26rem .62rem;
  border-radius: .45rem; border: 1.5px solid; cursor: pointer;
  transition: all .12s; white-space: nowrap; line-height: 1.4;
  font-family: inherit;
}
.btn-outline  { background: #fff; border-color: #c9a882; color: #7a4f2a; }
.btn-outline:hover { background: #f7f0e8; border-color: #a77d52; }
.btn-primary  { background: linear-gradient(135deg,#a77d52,#7a4f2a); border-color: transparent; color: #fff; }
.btn-primary:hover { opacity: .9; }
.btn-sched    { background: linear-gradient(135deg,#8b5e3c,#6b3f1f); border-color: transparent; color: #fff; }
.btn-sched:hover { opacity: .9; }
.btn-move     { background: linear-gradient(135deg,#1a6b35,#0f4d26); border-color: transparent; color: #fff; }
.btn-move:hover { opacity: .9; }

/* ===== MODAL OVERLAY ===== */
.kn-overlay {
  position: fixed; inset: 0; z-index: 1000;
  background: rgba(40,18,5,.5);
  backdrop-filter: blur(3px);
  display: flex; align-items: center; justify-content: center; padding: 1rem;
}
.kn-overlay.hidden { display: none; }
.kn-modal {
  background: #fff; border-radius: 1.25rem;
  width: 100%; max-width: 520px;
  box-shadow: 0 24px 64px rgba(40,18,5,.35);
  overflow: hidden;
  animation: modalIn .18s ease;
}
.kn-modal.wide { max-width: 580px; }
@keyframes modalIn { from { opacity:0; transform:scale(.95) translateY(10px); } }
.kn-modal-head {
  padding: 1.1rem 1.4rem .9rem;
  border-bottom: 1px solid #ede6dc;
  background: linear-gradient(90deg,#faf6f1,#f4ece1);
  display: flex; align-items: flex-start; justify-content: space-between;
}
.kn-modal-title { font-size: .97rem; font-weight: 700; color: #4a2b0e; }
.kn-modal-sub   { font-size: .75rem; color: #9a7558; margin-top: .15rem; }
.kn-modal-close {
  background: none; border: none; cursor: pointer;
  font-size: 1.2rem; color: #9a7558; padding: .1rem .3rem;
  border-radius: .3rem; line-height: 1;
}
.kn-modal-close:hover { background: #f0e8df; color: #6b3f1f; }
.kn-modal-body   { padding: 1.25rem 1.4rem; }
.kn-modal-footer {
  padding: .9rem 1.4rem;
  border-top: 1px solid #ede6dc;
  background: #faf6f1;
  display: flex; justify-content: flex-end; gap: .6rem;
}

/* ===== FORM ===== */
.fm-grid    { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
.fm-full    { grid-column: 1/-1; }
.fm-group   { display: flex; flex-direction: column; gap: .3rem; }
.fm-label   { font-size: .7rem; font-weight: 700; color: #7a4f2a; text-transform: uppercase; letter-spacing: .4px; }
.fm-ctrl    { border: 1.5px solid #ddd3c4; border-radius: .6rem; padding: .5rem .75rem; font-size: .82rem; color: #3d1f08; background: #faf7f4; outline: none; width: 100%; font-family: inherit; transition: border-color .15s, box-shadow .15s; }
.fm-ctrl:focus { border-color: var(--br-light); box-shadow: 0 0 0 3px rgba(167,125,82,.18); }
textarea.fm-ctrl { resize: vertical; min-height: 76px; }

/* ===== FEEDBACK CHIP ===== */
.fb-chip { display: flex; align-items: flex-start; gap: .65rem; padding: .8rem; background: #faf6f1; border: 1px solid #e8dfd4; border-radius: .75rem; margin-bottom: .6rem; }
.fb-chip:last-child { margin-bottom: 0; }
.fb-icon { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; flex-shrink: 0; }
.fb-hr    { background: #e8f0fe; color: #1a56db; }
.fb-user  { background: #e6f4ea; color: #256629; }
.fb-tr    { background: #fef3e2; color: #92580b; }
.fb-label { font-size: .68rem; font-weight: 700; color: #9a7558; text-transform: uppercase; letter-spacing: .3px; }
.fb-note  { font-size: .8rem; color: #3d1f08; margin-top: .2rem; line-height: 1.5; }
.fb-approve { display: inline-flex; align-items: center; gap: .3rem; font-size: .68rem; font-weight: 700; margin-top: .35rem; padding: .18rem .5rem; border-radius: 999px; }
.fb-yes   { background: #e6f4ea; color: #256629; }
.fb-no    { background: #fdeaea; color: #9b2525; }

/* ===== STAGE NEXT BANNER ===== */
.stage-banner {
  background: linear-gradient(90deg,#e8f4fd,#d6eaf8);
  border: 1px solid #b0d0ef; border-radius: .65rem;
  padding: .6rem .9rem; font-size: .78rem; color: #185fa5;
  display: flex; align-items: center; gap: .5rem;
  margin-bottom: .9rem;
}
.stage-banner b { font-weight: 800; }

/* FREE MOVE SELECT */
.free-move-wrap { margin-top: .6rem; }
.free-move-wrap label { font-size: .68rem; font-weight: 700; color: #7a4f2a; text-transform: uppercase; letter-spacing: .4px; display: block; margin-bottom: .25rem; }
.free-move-wrap select { border: 1.5px solid #c9a882; border-radius: .5rem; padding: .35rem .6rem; font-size: .75rem; color: #5a3e28; background: #faf7f4; outline: none; width: 100%; }
.free-move-wrap select:focus { border-color: var(--br-light); box-shadow: 0 0 0 3px rgba(167,125,82,.18); }

/* ===== TOAST ===== */
.kn-toast {
  position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 2000;
  background: #3d1f08; color: #fff;
  padding: .65rem 1.2rem; border-radius: .75rem;
  font-size: .82rem; font-weight: 600;
  box-shadow: 0 8px 24px rgba(40,18,5,.35);
  transition: all .3s; min-width: 200px;
}
.kn-toast.hidden { opacity: 0; transform: translateY(12px); pointer-events: none; }
.kn-toast.ok  { background: #1f6b35; }
.kn-toast.err { background: #9b2525; }
</style>

@php
  $stages = [
    'applied'         => 'Applied',
    'screening'       => 'Screening CV/Berkas',
    'psychotest'      => 'Psikotest',
    'hr_iv'           => 'HR Interview',
    'user_iv'         => 'User Interview',
    'user_trainer_iv' => 'User/Trainer IV',
    'offer'           => 'OL (Offering Letter)',
    'mcu'             => 'MCU',
    'mobilisasi'      => 'Mobilisasi',
    'ground_test'     => 'Ground Test',
    'hired'           => 'Hired',
    'not_qualified'   => 'Not Lolos',
  ];

  /**
   * Stage yang BEBAS dipindah oleh hr/admin/superadmin
   * tanpa perlu feedback tambahan (setelah user_trainer_iv selesai)
   */
  $freeAfter = ['offer','mcu','mobilisasi','ground_test','hired','not_qualified'];

  $authRole  = auth()->user()->role ?? 'guest';
  $isSuperHR = in_array($authRole, ['admin','hr','superadmin']);
@endphp

<div class="kn-wrap">

  {{-- HEADER --}}
  <div class="kn-header">
    <h1>Kanban Kandidat</h1>
    <p>Drag &amp; drop kartu antar stage · Klik <strong>Schedule</strong> untuk kirim undangan interview · Klik <strong>View Feedback</strong> untuk melihat penilaian</p>
  </div>

  {{-- FILTER --}}
  <form method="GET" class="kn-filter">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama / posisi..." />
    <select name="only">
      <option value="">Semua Stage</option>
      @foreach($stages as $k => $v)
        <option value="{{ $k }}" @selected(request('only') === $k)>{{ $v }}</option>
      @endforeach
    </select>
    <button type="submit" class="btn-filter">Filter</button>
  </form>

  {{-- BOARD --}}
  <div class="kn-board-wrap">
    <div class="kn-board" id="kn-board">

      @foreach($stages as $stageKey => $stageLabel)
        @php $items = $grouped[$stageKey] ?? collect(); @endphp

        <div class="kn-col" data-stage="{{ $stageKey }}" id="kncol-{{ $stageKey }}">
          <div class="kn-col-head">
            <span class="kn-col-title">{{ $stageLabel }}</span>
            <span class="kn-col-badge" id="cnt-{{ $stageKey }}">{{ $items->count() }}</span>
          </div>

          <div class="kn-col-body" id="col-{{ $stageKey }}">
            @foreach($items as $a)
              @php
                $fbHR      = $a->feedbacks->where('role','hr')->sortByDesc('created_at')->first();
                $fbUser    = $a->feedbacks->whereIn('role',['karyawan','pelamar'])->sortByDesc('created_at')->first();
                $fbTrainer = $a->feedbacks->where('role','trainer')->sortByDesc('created_at')->first();

                /**
                 * Kartu LOCKED di hr_iv jika feedback HR belum ada
                 * (hanya berlaku di kolom hr_iv)
                 */
                $isHrLocked = ($stageKey === 'hr_iv' && !$fbHR && $isSuperHR);

                /**
                 * Setelah user_trainer_iv, admin/hr/superadmin bebas drag ke stage mana saja
                 */
                $stageOrder = array_keys($stages);
                $curIdx     = array_search($stageKey, $stageOrder);
                $trainerIdx = array_search('user_trainer_iv', $stageOrder);
                $isFreeMove = ($isSuperHR && $curIdx > $trainerIdx);

                $draggable  = !$isHrLocked;
              @endphp

              <div
                class="kn-card {{ $isHrLocked ? 'card-locked' : '' }}"
                id="card-{{ $a->id }}"
                draggable="{{ $draggable ? 'true' : 'false' }}"
                data-id="{{ $a->id }}"
                data-stage="{{ $stageKey }}"
                data-move-url="{{ route('admin.applications.board.move') }}"
                data-csrf="{{ csrf_token() }}"
                data-candidate="{{ $a->user->name }}"
                data-job="{{ $a->job->title }}"
                data-fb-hr="{{ $fbHR ? json_encode(['notes'=>$fbHR->feedback,'approve'=>$fbHR->approve]) : 'null' }}"
                data-fb-user="{{ $fbUser ? json_encode(['notes'=>$fbUser->feedback,'approve'=>$fbUser->approve]) : 'null' }}"
                data-fb-trainer="{{ $fbTrainer ? json_encode(['notes'=>$fbTrainer->feedback,'approve'=>$fbTrainer->approve]) : 'null' }}"
                data-schedule-url="{{ route('admin.interviews.store', $a) }}"
              >
                <div class="kn-card-name">{{ $a->user->name }}</div>
                <div class="kn-card-job">{{ $a->job->title }}</div>
                <div class="kn-card-email">{{ $a->user->email }}</div>
                <div class="kn-card-role">{{ ucfirst($a->user->role) }}</div>
                <span class="kn-pill
                  @if($a->overall_status==='hired') pill-hired
                  @elseif($a->overall_status==='not_qualified') pill-nq
                  @else pill-active
                  @endif">
                  {{ $a->overall_status === 'hired' ? '✓ Hired' : ($a->overall_status === 'not_qualified' ? '✕ Not Lolos' : '● Active') }}
                </span>

                @if($isHrLocked)
                  <div class="kn-lock-note">⚠ Isi feedback HR sebelum dapat dilanjutkan</div>
                @endif

                <div class="kn-card-actions">
                  {{-- VIEW FEEDBACK BUTTONS --}}
                  @if($fbHR)
                    <button type="button" class="btn-xs btn-outline" onclick="openFbModal(this,'hr')">View Feedback HR</button>
                  @endif
                  @if($fbUser)
                    <button type="button" class="btn-xs btn-outline" onclick="openFbModal(this,'user')">View Feedback Karyawan</button>
                  @endif
                  @if($fbTrainer)
                    <button type="button" class="btn-xs btn-outline" onclick="openFbModal(this,'trainer')">View Feedback Trainer</button>
                  @endif

                  {{-- FEEDBACK FORM BUTTON (hanya di hr_iv & belum ada feedback) --}}
                  @if($stageKey === 'hr_iv' && !$fbHR && $isSuperHR)
                    <button type="button" class="btn-xs btn-primary"
                      onclick="openFbForm(this, '{{ $a->id }}', '{{ $a->user->name }}', 'hr_iv')">
                      + Isi Feedback HR
                    </button>
                  @endif

                  {{-- SCHEDULE BUTTON (hr_iv, user_iv, user_trainer_iv) --}}
                  @if(in_array($stageKey, ['hr_iv','user_iv','user_trainer_iv']))
                    @php
                      $toStageMap = [
                        'hr_iv'           => 'user_iv',
                        'user_iv'         => 'user_trainer_iv',
                        'user_trainer_iv' => 'offer',
                      ];
                      $toStageNext = $toStageMap[$stageKey] ?? null;
                    @endphp
                    <button type="button" class="btn-xs btn-sched"
                      onclick="openSchedModal(this, '{{ $toStageNext }}')">
                      Schedule
                    </button>
                  @endif

                  {{-- FREE MOVE DROPDOWN (admin/hr/superadmin, setelah user_trainer_iv) --}}
                  @if($isFreeMove && $isSuperHR)
                    <div class="free-move-wrap" style="width:100%">
                      <label>Pindahkan ke Stage</label>
                      <select onchange="freeMoveCard(this, '{{ $a->id }}', '{{ $stageKey }}', '{{ csrf_token() }}', '{{ route('admin.applications.board.move') }}')">
                        <option value="">— Pilih Stage —</option>
                        @foreach($stages as $sk => $sl)
                          @if($sk !== $stageKey)
                            <option value="{{ $sk }}">{{ $sl }}</option>
                          @endif
                        @endforeach
                      </select>
                    </div>
                  @endif

                  <a class="btn-xs btn-outline" href="{{ route('jobs.show', $a->job) }}" target="_blank">Job</a>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach

    </div>
  </div>

</div>{{-- /kn-wrap --}}


{{-- ============================= MODAL: VIEW FEEDBACK ============================= --}}
<div class="hidden kn-overlay" id="overlay-fb">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title" id="fb-modal-title">Feedback</div>
        <div class="kn-modal-sub" id="fb-modal-sub">Detail penilaian interviewer</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-fb')">✕</button>
    </div>
    <div class="kn-modal-body" id="fb-modal-body"></div>
    <div class="kn-modal-footer">
      <button class="btn-xs btn-outline" onclick="closeModal('overlay-fb')">Tutup</button>
    </div>
  </div>
</div>


{{-- ============================= MODAL: ISI FEEDBACK FORM ============================= --}}
<div class="hidden kn-overlay" id="overlay-fbform">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title" id="fbform-title">Isi Feedback HR</div>
        <div class="kn-modal-sub">Wajib diisi sebelum kandidat dapat dilanjutkan ke stage berikutnya</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-fbform')">✕</button>
    </div>
    <div class="kn-modal-body">
      <input type="hidden" id="fbform-app-id" value="">
      <input type="hidden" id="fbform-stage" value="">
      <div style="display:flex;flex-direction:column;gap:.8rem">
        <div class="fm-group">
          <label class="fm-label">Catatan / Feedback</label>
          <textarea class="fm-ctrl" id="fbform-notes" rows="3" placeholder="Tulis penilaian kandidat secara detail..."></textarea>
        </div>
        <div class="fm-group">
          <label class="fm-label">Setuju Lanjut ke Stage Berikutnya?</label>
          <select class="fm-ctrl" id="fbform-approve">
            <option value="">— Pilih —</option>
            <option value="yes">✓ Setuju</option>
            <option value="no">✕ Tidak Setuju</option>
          </select>
        </div>
      </div>
    </div>
    <div class="kn-modal-footer">
      <button class="btn-xs btn-outline" onclick="closeModal('overlay-fbform')">Batal</button>
      <button class="btn-xs btn-primary" onclick="submitFbForm()">Simpan Feedback</button>
    </div>
  </div>
</div>


{{-- ============================= MODAL: SCHEDULE INTERVIEW ============================= --}}
<div class="hidden kn-overlay" id="overlay-sched">
  <div class="kn-modal wide">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title">Schedule Interview</div>
        <div class="kn-modal-sub" id="sched-sub">Undangan ICS akan dikirim ke kandidat</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-sched')">✕</button>
    </div>
    <div class="kn-modal-body">
      <div class="stage-banner" id="sched-banner">
        ➜ Setelah submit, kandidat akan otomatis dipindah ke stage berikutnya
      </div>
      <form id="sched-form" method="POST">
        @csrf
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" id="sched-to-stage" name="to_stage" value="">
        <div class="fm-grid">
          <div class="fm-group fm-full">
            <label class="fm-label">Judul Interview</label>
            <input class="fm-ctrl" type="text" name="title" id="sched-title" required>
          </div>
          <div class="fm-group">
            <label class="fm-label">Mode</label>
            <select class="fm-ctrl" name="mode" id="sched-mode" onchange="toggleSchedMode()">
              <option value="online">Online</option>
              <option value="onsite">Onsite</option>
            </select>
          </div>
          <div class="fm-group" id="sched-link-group">
            <label class="fm-label">Meeting Link</label>
            <input class="fm-ctrl" type="text" name="meeting_link" id="sched-link" placeholder="https://meet.google.com/...">
          </div>
          <div class="fm-group" id="sched-loc-group" style="display:none">
            <label class="fm-label">Lokasi</label>
            <input class="fm-ctrl" type="text" name="location" id="sched-loc" placeholder="R. Interview / Alamat kantor">
          </div>
          <div class="fm-group">
            <label class="fm-label">Mulai</label>
            <input class="fm-ctrl" type="datetime-local" name="start_at" id="sched-start" required>
          </div>
          <div class="fm-group">
            <label class="fm-label">Selesai</label>
            <input class="fm-ctrl" type="datetime-local" name="end_at" id="sched-end" required>
          </div>
          <div class="fm-group fm-full">
            <label class="fm-label">Catatan untuk Kandidat</label>
            <textarea class="fm-ctrl" name="notes" id="sched-notes" placeholder="Bawa portfolio / hadir 10 menit lebih awal..."></textarea>
          </div>
        </div>
      </form>
    </div>
    <div class="kn-modal-footer">
      <button class="btn-xs btn-outline" onclick="closeModal('overlay-sched')">Batal</button>
      <button class="btn-xs btn-primary" onclick="submitSchedule()">Kirim Undangan &amp; Pindah Stage</button>
    </div>
  </div>
</div>


{{-- TOAST --}}
<div class="hidden kn-toast" id="kn-toast"></div>


<script>
/* ============================= UTILS ============================= */
const stageLabels = @json($stages);

function openModal(id)  { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

function showToast(msg, type = 'ok') {
  const t = document.getElementById('kn-toast');
  t.textContent = msg;
  t.className = 'kn-toast ' + type;
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.add('hidden'), 2800);
}

function roleLabel(r) { return r === 'hr' ? 'HR' : r === 'user' ? 'Karyawan' : 'Trainer'; }
function roleIcon(r)  { return r === 'hr' ? 'HR' : r === 'user' ? 'US' : 'TR'; }
function roleCls(r)   { return r === 'hr' ? 'fb-hr' : r === 'user' ? 'fb-user' : 'fb-tr'; }

function getCard(el) { return el.closest('.kn-card'); }
function getCardData(card, key) {
  const raw = card.dataset[key];
  try { return raw && raw !== 'null' ? JSON.parse(raw) : null; } catch { return null; }
}

function updateCount(stage, delta) {
  const el = document.getElementById('cnt-' + stage);
  if (el) el.textContent = Math.max(0, (parseInt(el.textContent) || 0) + delta);
}

/* ============================= VIEW FEEDBACK MODAL ============================= */
function openFbModal(btn, role) {
  const card = getCard(btn);
  const fb   = getCardData(card, 'fb' + role.charAt(0).toUpperCase() + role.slice(1));

  document.getElementById('fb-modal-title').textContent = 'Feedback ' + roleLabel(role);
  document.getElementById('fb-modal-sub').textContent   = 'Diisi oleh ' + roleLabel(role) + ' saat sesi interview berlangsung';

  const approveHtml = fb && fb.approve
    ? (fb.approve === 'yes'
        ? '<span class="fb-approve fb-yes">✓ Setuju Lanjut</span>'
        : '<span class="fb-approve fb-no">✕ Tidak Setuju</span>')
    : '<span class="fb-approve" style="background:#f5f5f5;color:#aaa">— Belum diisi —</span>';

  document.getElementById('fb-modal-body').innerHTML = `
    <div class="fb-chip">
      <div class="fb-icon ${roleCls(role)}">${roleIcon(role)}</div>
      <div style="flex:1">
        <div class="fb-label">${roleLabel(role)}</div>
        <div class="fb-note">${fb && fb.notes ? fb.notes : '<span style="color:#aaa">Belum ada catatan</span>'}</div>
        ${approveHtml}
      </div>
    </div>`;
  openModal('overlay-fb');
}

/* ============================= ISI FEEDBACK FORM ============================= */
let fbFormCardEl = null;

function openFbForm(btn, appId, name, stage) {
  fbFormCardEl = getCard(btn);
  document.getElementById('fbform-title').textContent = 'Isi Feedback HR — ' + name;
  document.getElementById('fbform-app-id').value = appId;
  document.getElementById('fbform-stage').value  = stage;
  document.getElementById('fbform-notes').value  = '';
  document.getElementById('fbform-approve').value = '';
  openModal('overlay-fbform');
}

function submitFbForm() {
  const notes   = document.getElementById('fbform-notes').value.trim();
  const approve = document.getElementById('fbform-approve').value;
  const appId   = document.getElementById('fbform-app-id').value;
  const stage   = document.getElementById('fbform-stage').value;
  const csrf    = document.querySelector('meta[name="csrf-token"]')?.content
               || (fbFormCardEl && fbFormCardEl.dataset.csrf)
               || '';

  if (!notes)   { showToast('Catatan feedback wajib diisi', 'err'); return; }
  if (!approve) { showToast('Pilih setuju/tidak setuju', 'err');    return; }

  fetch(`/admin/applications/feedback`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrf,
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ application_id: appId, stage_key: stage, role: 'hr', feedback: notes, approve }),
  })
  .then(async res => {
    const contentType = res.headers.get('content-type') || '';
    if (!res.ok) {
      if (contentType.includes('application/json')) {
        const j = await res.json().catch(()=>({}));
        throw new Error(j.message || 'Gagal simpan');
      } else {
        const text = await res.text();
        throw new Error('Unexpected response: ' + text.slice(0, 100));
      }
    }
    return contentType.includes('application/json') ? res.json() : res.text();
  })
  .then(() => {
    // Update card UI
    if (fbFormCardEl) {
      fbFormCardEl.dataset.fbHr = JSON.stringify({ notes, approve });
      fbFormCardEl.removeAttribute('draggable'); // reset
      fbFormCardEl.setAttribute('draggable', 'true');
      fbFormCardEl.classList.remove('card-locked');
      const lockNote = fbFormCardEl.querySelector('.kn-lock-note');
      if (lockNote) lockNote.remove();
      // Ganti tombol "Isi Feedback" jadi "View Feedback HR"
      const fillBtn = fbFormCardEl.querySelector('[onclick*="openFbForm"]');
      if (fillBtn) {
        const viewBtn = document.createElement('button');
        viewBtn.type = 'button';
        viewBtn.className = 'btn-xs btn-outline';
        viewBtn.setAttribute('onclick', "openFbModal(this,'hr')");
        viewBtn.textContent = 'View Feedback HR';
        fillBtn.replaceWith(viewBtn);
      }
      initDrag(fbFormCardEl);
    }
    closeModal('overlay-fbform');
    showToast('Feedback HR tersimpan. Kartu siap di-drag!', 'ok');
  })
  .catch(err => {
    showToast(err.message || 'Gagal menyimpan feedback', 'err');
  });
}

/* ============================= SCHEDULE MODAL ============================= */
let schedCardEl  = null;
let schedToStage = null;

function openSchedModal(btn, toStage) {
  schedCardEl  = getCard(btn);
  schedToStage = toStage;

  const candidate = schedCardEl.dataset.candidate || 'Kandidat';
  const job       = schedCardEl.dataset.job       || '';
  const url       = schedCardEl.dataset.scheduleUrl || '#';
  const fromStage = schedCardEl.dataset.stage;

  document.getElementById('sched-sub').textContent    = candidate + ' — ' + job;
  document.getElementById('sched-title').value        = 'Interview – ' + candidate;
  document.getElementById('sched-to-stage').value     = toStage || '';
  document.getElementById('sched-banner').innerHTML   =
    `➜ Setelah submit, <b>${candidate}</b> akan dipindah ke <b>${stageLabels[toStage] || toStage}</b>`;

  document.getElementById('sched-form').action = url;
  document.getElementById('sched-notes').value  = '';
  document.getElementById('sched-start').value  = '';
  document.getElementById('sched-end').value    = '';
  document.getElementById('sched-mode').value   = 'online';
  toggleSchedMode();
  openModal('overlay-sched');
}

function toggleSchedMode() {
  const m = document.getElementById('sched-mode').value;
  document.getElementById('sched-link-group').style.display = m === 'online' ? '' : 'none';
  document.getElementById('sched-loc-group').style.display  = m === 'onsite' ? '' : 'none';
}

function submitSchedule() {
  const title = document.getElementById('sched-title').value.trim();
  const start = document.getElementById('sched-start').value;
  const end   = document.getElementById('sched-end').value;
  if (!title)       { showToast('Judul interview wajib diisi', 'err');   return; }
  if (!start || !end) { showToast('Waktu mulai & selesai wajib diisi', 'err'); return; }
  if (start >= end) { showToast('Waktu selesai harus setelah waktu mulai', 'err'); return; }

  const form   = document.getElementById('sched-form');
  const csrf   = form.querySelector('[name="_token"]').value;
  const url    = form.action;
  const data   = new FormData(form);

  fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: data })
  .then(async res => {
    const contentType = res.headers.get('content-type') || '';
    if (!res.ok) {
      if (contentType.includes('application/json')) {
        const j = await res.json().catch(()=>({}));
        throw new Error(j.message || 'Gagal kirim');
      } else {
        const text = await res.text();
        throw new Error('Unexpected response: ' + text.slice(0, 100));
      }
    }
    return contentType.includes('application/json') ? res.json() : res.text();
  })
  .then(() => {
    closeModal('overlay-sched');
    // Pindahkan kartu secara optimistic
    if (schedCardEl && schedToStage) {
      const fromStage = schedCardEl.dataset.stage;
      const toCol     = document.getElementById('col-' + schedToStage);
      if (toCol) {
        toCol.insertBefore(schedCardEl, toCol.firstChild);
        schedCardEl.dataset.stage = schedToStage;
        updateCount(fromStage,  -1);
        updateCount(schedToStage, 1);
      }
    }
    showToast('Undangan dikirim! Kandidat dipindah ke ' + (stageLabels[schedToStage] || schedToStage), 'ok');
    schedCardEl  = null;
    schedToStage = null;
  })
  .catch(err => {
    closeModal('overlay-sched');
    showToast(err.message || 'Gagal mengirim undangan', 'err');
  });
}

/* ============================= FREE MOVE (after user_trainer_iv) ============================= */
function freeMoveCard(selectEl, appId, fromStage, csrf, baseUrl) {
  const toStage = selectEl.value;
  if (!toStage) return;

  const card = selectEl.closest('.kn-card');
  selectEl.value = ''; // reset dropdown

  fetch(baseUrl + '?id=' + appId, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrf,
      'Accept': 'application/json',
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: new URLSearchParams({ to_stage: toStage }),
  })
  .then(async res => {
    const contentType = res.headers.get('content-type') || '';
    if (!res.ok) {
      if (contentType.includes('application/json')) {
        const j = await res.json().catch(()=>({}));
        throw new Error(j.message || 'Gagal pindah');
      } else {
        const text = await res.text();
        throw new Error('Unexpected response: ' + text.slice(0, 100));
      }
    }
    return contentType.includes('application/json') ? res.json() : res.text();
  })
  .then(() => {
    const toCol = document.getElementById('col-' + toStage);
    if (toCol && card) {
      toCol.insertBefore(card, toCol.firstChild);
      card.dataset.stage = toStage;
      updateCount(fromStage, -1);
      updateCount(toStage,    1);
    }
    showToast('Dipindah ke ' + (stageLabels[toStage] || toStage), 'ok');
  })
  .catch(err => {
    showToast(err.message || 'Gagal memindahkan kartu', 'err');
  });
}

/* ============================= DRAG & DROP ============================= */
let dragCard = null, dragFrom = null;

function initDrag(card) {
  card.removeEventListener('dragstart', _onDragStart);
  card.removeEventListener('dragend',   _onDragEnd);
  card.addEventListener('dragstart', _onDragStart);
  card.addEventListener('dragend',   _onDragEnd);
}
function _onDragStart(e) {
  const card = e.currentTarget;
  if (card.classList.contains('card-locked')) { e.preventDefault(); return; }
  dragCard = card;
  dragFrom = card.dataset.stage;
  setTimeout(() => card.classList.add('dragging'), 0);
}
function _onDragEnd(e) { e.currentTarget.classList.remove('dragging'); }

document.querySelectorAll('.kn-card[draggable="true"]').forEach(initDrag);

document.querySelectorAll('.kn-col').forEach(col => {
  const body = col.querySelector('.kn-col-body');
  col.addEventListener('dragover',  e => { e.preventDefault(); col.classList.add('dragover'); });
  col.addEventListener('dragleave', e => { if (!col.contains(e.relatedTarget)) col.classList.remove('dragover'); });
  col.addEventListener('drop', e => {
    e.preventDefault();
    col.classList.remove('dragover');
    if (!dragCard) return;

    const toStage = col.dataset.stage;
    if (toStage === dragFrom) { dragCard = null; dragFrom = null; return; }

    const appId  = dragCard.dataset.id;
    const csrf   = dragCard.dataset.csrf;
    const url    = dragCard.dataset.moveUrl;
    const from   = dragFrom;

    // Optimistic move
    body.insertBefore(dragCard, body.firstChild);
    dragCard.dataset.stage = toStage;
    updateCount(from,    -1);
    updateCount(toStage,  1);

    const movedCard = dragCard;
    dragCard = null; dragFrom = null;

    fetch(url + '?id=' + appId, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrf,
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({ to_stage: toStage }),
    })
    .then(async res => {
      const contentType = res.headers.get('content-type') || '';
      if (!res.ok) {
        if (contentType.includes('application/json')) {
          const j = await res.json().catch(()=>({}));
          throw new Error(j.message || 'Move failed');
        } else {
          const text = await res.text();
          throw new Error('Unexpected response: ' + text.slice(0, 100));
        }
      }
      return contentType.includes('application/json') ? res.json() : res.text();
    })
    .then(() => {
      showToast('Dipindah ke ' + (stageLabels[toStage] || toStage), 'ok');
    })
    .catch(err => {
      // Rollback
      const fromCol = document.getElementById('col-' + from);
      if (fromCol) fromCol.insertBefore(movedCard, fromCol.firstChild);
      movedCard.dataset.stage = from;
      updateCount(toStage, -1);
      updateCount(from,     1);
      showToast(err.message || 'Gagal memindahkan kartu', 'err');
    });
  });
});
</script>
@endsection