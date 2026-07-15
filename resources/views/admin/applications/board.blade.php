{{-- resources/views/admin/applications/kanban.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Kanban Kandidat'])

@section('content')
{{-- FLASH MESSAGE --}}
@if(session('ok') || session('error') || session('warn'))
  <div class="max-w-[1320px] mx-auto px-4 mt-4">
    <div class="px-4 py-3 rounded-xl text-sm font-medium border shadow-sm
      {{ session('ok') ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200' }}">
      <span>{{ session('ok') ?? session('error') ?? session('warn') }}</span>
      <button onclick="this.parentElement.remove()" class="float-right font-bold">&times;</button>
    </div>
  </div>
  <script>setTimeout(()=>{const e=document.querySelector('[class*="bg-emerald-50"],[class*="bg-red-50"]');if(e&&e.parentElement)e.parentElement.remove()},5000)</script>
@endif
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
  background: #a77d52;
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
  position: sticky;
  top: 0;
  z-index: 20;
  box-shadow: 0 8px 18px -18px rgba(107,63,31,.35);
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
  background: #a77d52;
  color: #fff; border: none; border-radius: .55rem;
  padding: .44rem 1.1rem; font-size: .82rem; font-weight: 700;
  cursor: pointer; letter-spacing: .2px;
  transition: opacity .15s;
}
.kn-filter .btn-filter:hover { opacity: .9; }

/* ===== MAIN WRAPPER — FIX UTAMA ===== */
.kn-wrap {
  display: flex;
  flex-direction: column;
  width: 100%;
  box-sizing: border-box;
  /* TIDAK pakai flex:1 atau overflow:hidden supaya tidak kabur dari parent <main> */
}

/* ===== BOARD ===== */
.kn-board-wrap {
  overflow-x: auto;
  overflow-y: hidden;
  padding: 1.25rem 2.2rem 1.5rem;
  -webkit-overflow-scrolling: touch;
  width: 100%;
  height: calc(100vh - 235px);
  min-height: 560px;
  box-sizing: border-box;
  /* TIDAK pakai flex:1 */
}
.kn-board { display: flex; gap: 1.8rem; align-items: stretch; min-width: max-content; width: max-content; height: 100%; }

/* ===== COLUMN ===== */
.kn-col {
  width: 485px;
  flex: 0 0 485px;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid #e2d9cf;
  display: flex; flex-direction: column;
  height: 100%;
  min-height: 0;
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
  padding: 1.12rem 1.4rem;
  background: #fdf7f0;
  border-bottom: 1px solid #ddd3c4;
  border-radius: 1rem 1rem 0 0;
  position: sticky; top: 0; z-index: 2;
}
.kn-col-title {
  font-size: .96rem; font-weight: 800;
  text-transform: uppercase; letter-spacing: .7px;
  color: #7a4f2a;
}
.kn-col-badge {
  background: #5c3d1e; color: #fff;
  font-size: .96rem; font-weight: 700;
  border-radius: 999px; padding: .25rem .8rem;
  min-width: 40px; text-align: center;
}
.kn-col-body {
  padding: 1.25rem 1.15rem;
  overflow-y: auto; flex: 1;
  min-height: 0;
  display: flex; flex-direction: column; gap: 1.15rem;
  overscroll-behavior: contain;
}
.kn-col-body { scrollbar-width: thin; scrollbar-color: #c9a882 #f7f3ef; }
.kn-col-body::-webkit-scrollbar { width: 8px; }
.kn-col-body::-webkit-scrollbar-track { background: #f7f3ef; border-radius: 999px; }
.kn-col-body::-webkit-scrollbar-thumb { background: #c9a882; border-radius: 999px; }
.kn-col-body::-webkit-scrollbar-thumb:hover { background: #a77d52; }

/* ===== CARD ===== */
.kn-card {
  background: #fff; border: 1px solid #e8dfd4;
  border-radius: 1.2rem; padding: 1.45rem;
  cursor: grab; position: relative;
  transition: transform .15s, box-shadow .15s, border-color .2s, opacity .2s;
  display: flex; flex-direction: column;
  gap: .82rem;
  max-height: none;
  overflow-y: visible;
  word-wrap: break-word;
  word-break: break-word;
}
.kn-card { -ms-overflow-style: none; scrollbar-width: none; }
.kn-card::-webkit-scrollbar { width: 0; height: 0; display: none; }
.kn-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107,63,31,.13);
  border-color: #a77d52;
}
.kn-card:active { cursor: grabbing; }
.kn-card.dragging { opacity: .45; transform: scale(.97); }
.kn-card.card-locked { opacity: .55; cursor: not-allowed; }
.kn-card.card-locked .kn-card-name { color: #8a7060; }

.kn-card-name  { font-weight: 700; font-size: 1.22rem; color: #3d1f08; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kn-card-job   { font-size: 1.02rem; color: #9a7558; margin-top: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kn-card-email { font-size: .96rem; color: #b89070; margin-top: 0; }
.kn-card-role  { font-size: .94rem; color: #c0a080; margin-top: 0; }

.kn-subcard {
  margin-top: 0;
  padding: .7rem .75rem;
  border-radius: .7rem;
  border: 1px solid #eadbcb;
  background: #faf6f1;
}
.kn-subcard.hidden { display: none; }
.kn-subcard-head {
  display: flex; align-items: center; justify-content: space-between; gap: .5rem;
  font-size: .68rem; font-weight: 800; color: #a77d52; text-transform: uppercase; letter-spacing: .35px;
}
.kn-subcard-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: .45rem .7rem; margin-top: .55rem;
}
.kn-subcard-item {
  display: flex; flex-direction: column; gap: .08rem;
}
.kn-subcard-label {
  font-size: .62rem; text-transform: uppercase; letter-spacing: .3px; color: #9a7558;
}
.kn-subcard-value {
  font-size: .8rem; font-weight: 700; color: #3d1f08; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

.kn-pill { display: inline-flex; align-items: center; gap: .3rem; font-size: .94rem; font-weight: 700; padding: .4rem .92rem; border-radius: 999px; letter-spacing: .2px; margin-top: 0; }
.pill-active   { background: #fef3e2; color: #92580b; }
.pill-hired    { background: #e6f4ea; color: #256629; }
.pill-nq       { background: #fdeaea; color: #9b2525; }
.pill-warn     { background: #fff8e1; color: #b7791f; }

.kn-lock-note {
  font-size: .92rem; color: #b05020;
  background: #fff3ed; border: 1px dashed #e8b090;
  border-radius: .6rem; padding: .48rem .86rem;
  margin-top: 0; display: flex; align-items: center; gap: .35rem;
}

.kn-card-actions {
  display: flex; flex-wrap: wrap; gap: .55rem;
  margin-top: 0; padding-top: .95rem; padding-bottom: .7rem;
  border-top: 1px solid #f0e9df;
  position: sticky; bottom: 0; background: #fff; z-index: 10;
}

/* Stage-specific polish: Screening CV/Berkas */
.kn-col[data-stage="screening"] .kn-col-body {
  padding-top: 1.35rem;
  padding-bottom: 1.35rem;
  gap: 1.25rem;
}
.kn-col[data-stage="screening"] .kn-card {
  padding: 1.5rem;
  gap: .9rem;
}
.kn-col[data-stage="screening"] .kn-card-name {
  line-height: 1.35;
}
.kn-col[data-stage="screening"] .kn-card-actions {
  padding-top: 1.05rem;
}

/* ===== FEEDBACK PANEL (collapsible) ===== */
.fb-panel-toggle { background: #d97706; border-color: transparent; color: #fff; cursor: pointer; }
.fb-panel-toggle:hover { opacity: .85; }
.fb-panel { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: 0; padding-top: .6rem; padding-bottom: .4rem; border-top: 1px solid #f0e9df; }
.fb-panel.hidden { display: none; }
.btn-detail-toggle { background: #fff; border-color: #a77d52; color: #7a4f2a; }
.btn-detail-toggle:hover { background: #f7f0e8; border-color: #a77d52; }

/* ===== BUTTONS ===== */
.btn-xs {
  font-size: .98rem; font-weight: 700; padding: .52rem 1.05rem;
  border-radius: .45rem; border: 1.5px solid; cursor: pointer;
  transition: all .12s; white-space: nowrap; line-height: 1.4;
  font-family: inherit;
}

@media (max-width: 768px) {
  .kn-board-wrap {
    padding: 1rem 1rem 1.25rem;
    height: calc(100vh - 250px);
    min-height: 520px;
  }
  .kn-board { gap: 1.2rem; height: 100%; }
  .kn-col { width: 400px; flex-basis: 400px; }
  .kn-card { max-height: none; padding: 1.3rem; overflow-y: visible; }
}
.btn-outline  { background: #fff; border-color: #a77d52; color: #7a4f2a; }
.btn-outline:hover { background: #f7f0e8; border-color: #a77d52; }
.btn-primary  { background: #a77d52; border-color: transparent; color: #fff; }
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
.kn-modal.wide { max-width: 640px; }
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
textarea.fm-ctrl { resize: vertical; min-height: 80px; }

.kn-modal-section {
  font-size: .65rem; font-weight: 800; color: #a77d52;
  padding: 1.2rem 0 .4rem; margin-bottom: .8rem;
  border-bottom: 1px solid #f0e8df;
  text-transform: uppercase; letter-spacing: .8px;
  display: flex; align-items: center; gap: .5rem;
}
.kn-modal-section::after { content: ""; flex: 1; height: 1px; background: #f0e8df; }
.kn-modal-section-icon { width: 18px; height: 18px; opacity: .7; }

/* ===== FEEDBACK CHIP ===== */
.fb-chip { display: flex; align-items: flex-start; gap: .65rem; padding: .8rem; background: #faf6f1; border: 1px solid #e8dfd4; border-radius: .75rem; margin-bottom: .6rem; }
.fb-chip:last-child { margin-bottom: 0; }
.fb-icon { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; flex-shrink: 0; }
.fb-hr    { background: #e8f0fe; color: #1a56db; }
.fb-user  { background: #e6f4ea; color: #256629; }
.fb-employee { background: #fdeaea; color: #9b2525; }
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
    'user_trainer_iv' => 'User & Trainer Interview',
    'offer'           => 'OL (Offering Letter)',
    'mcu'             => 'MCU',
    'mobilisasi'      => 'Mobilisasi',
    'ground_test'     => 'Ground Test',
    'onsite'          => 'Onsite',
    'hired'           => 'Hired',
    'not_qualified'   => 'TIDAK lOLOS',
  ];

  $freeAfter = ['offer','mcu','mobilisasi','ground_test','onsite','hired','not_qualified'];

  $authRole  = auth()->user()->role ?? 'guest';
  $isHR      = in_array($authRole, ['admin', 'hr', 'superadmin']);
  $isTrainer = ($authRole === 'trainer');
  $isKaryawan = ($authRole === 'karyawan');
  $isSuperHR = $isHR || $isTrainer || $isKaryawan;
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
                $fbHR       = $a->feedbacks->where('role','hr')->sortByDesc('created_at')->first();
                $fbUser     = $a->feedbacks->where('role','karyawan')->sortByDesc('created_at')->first();
                $fbTrainer  = $a->feedbacks->where('role','trainer')->sortByDesc('created_at')->first();
                $fbEmployee = $a->feedbacks->where('role','pelamar')->sortByDesc('created_at')->first();

                $isHrLocked = ($stageKey === 'hr_iv' && !$fbHR && $isSuperHR);

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
                data-company="{{ optional($a->job->company)->name }}"
                data-level="{{ $a->job->level_label }}"
                data-poh="{{ optional($a->poh)->name }}"
                data-fb-hr="{{ $fbHR ? json_encode(['notes'=>$fbHR->feedback,'approve'=>$fbHR->approve]) : 'null' }}"
                data-fb-user="{{ $fbUser ? json_encode(['notes'=>$fbUser->feedback,'approve'=>$fbUser->approve]) : 'null' }}"
                data-fb-trainer="{{ $fbTrainer ? json_encode(['notes'=>$fbTrainer->feedback,'approve'=>$fbTrainer->approve]) : 'null' }}"
                data-fb-employee="{{ $fbEmployee ? json_encode(['notes'=>$fbEmployee->feedback,'approve'=>$fbEmployee->approve]) : 'null' }}"
                data-offer-body="{{ optional($a->offer)->body_template }}"
                data-offer-meta="{{ json_encode(optional($a->offer)->meta ?? []) }}"
                data-mcu-meta="{{ json_encode($a->mcu_meta ?? []) }}"
                data-mobilisasi-meta="{{ json_encode($a->mobilisasi_meta ?? []) }}"
                data-gt-meta="{{ json_encode($a->ground_test_meta ?? []) }}"
                data-gt-result="{{ $a->ground_test_result }}"
                data-schedule-url="{{ route('admin.interviews.store', $a) }}"
              >
                <div class="flex items-start justify-between gap-2">
                  <div class="kn-card-name">
                    @if(optional($a->user)->candidateProfile && Route::has('admin.candidates.show'))
                      <a href="{{ route('admin.candidates.show', $a->user->candidateProfile) }}" target="_blank" class="hover:underline">{{ $a->user->name }}</a>
                    @else
                      {{ $a->user->name }}
                    @endif
                  </div>

                  <span class="kn-pill
                    @if($a->overall_status==='hired') pill-hired
                    @elseif($a->overall_status==='not_qualified') pill-nq
                    @else pill-active
                    @endif"
                    style="margin-top: 0; flex-shrink: 0;">
                    {{ $a->overall_status === 'hired' ? '✓ Sudah Keterima' : ($a->overall_status === 'not_qualified' ? '✕ TIDAK lOLOS' : '● Active') }}
                  </span>
                </div>
                <div class="kn-card-job">{{ $a->job->title }}</div>
                <div class="kn-card-email">{{ $a->user->email }}</div>
                <div class="kn-card-role">{{ $a->user->role === 'karyawan' ? 'User' : ($a->user->role === 'pelamar' ? 'Applicant' : ucfirst($a->user->role)) }}</div>

                <div id="detail-{{ $a->id }}" class="hidden kn-subcard">
                  <div class="kn-subcard-head">
                    <span>Detail</span>
                    <span>{{ strtoupper($stageKey) }}</span>
                  </div>
                  <div class="kn-subcard-grid">
                    <div class="kn-subcard-item">
                      <span class="kn-subcard-label">Nama</span>
                      <span class="kn-subcard-value">{{ $a->user->name }}</span>
                    </div>
                    <div class="kn-subcard-item">
                      <span class="kn-subcard-label">Lamar ke</span>
                      <span class="kn-subcard-value">{{ $a->job->title }}</span>
                    </div>
                    <div class="kn-subcard-item">
                      <span class="kn-subcard-label">Stage</span>
                      <span class="kn-subcard-value">{{ $stageLabel }}</span>
                    </div>
                    <div class="kn-subcard-item">
                      <span class="kn-subcard-label">Status</span>
                      <span class="kn-subcard-value">{{ strtoupper($a->overall_status) }}</span>
                    </div>
                  </div>

                  <div class="mt-3 overflow-hidden border rounded-lg border-[#eadbcb] bg-white" style="font-size: 0.7rem;">
                    <div class="px-2 py-1.5 font-bold uppercase text-[#a77d52] bg-[#faf6f1] border-b border-[#eadbcb]">
                      OL Status
                    </div>
                    <div class="px-2 py-2">
                      @if(optional($a->offer)->status === 'accepted')
                        @php
                          $gross = data_get($a->offer->salary, 'gross', data_get($a->offer->salary, 'base', 0));
                          $allow = data_get($a->offer->salary, 'allowance', 0);
                        @endphp
                        <div class="font-semibold text-emerald-700">✓ Accepted</div>
                        <div class="mt-1 text-slate-600">Gaji: Rp {{ is_numeric($gross) ? number_format((float) $gross, 0, ',', '.') : $gross }}</div>
                        @if($allow)
                          <div class="text-slate-600">Tunjangan: Rp {{ is_numeric($allow) ? number_format((float) $allow, 0, ',', '.') : $allow }}</div>
                        @endif
                      @elseif(optional($a->offer)->status === 'declined')
                        <div class="font-semibold text-red-600">✕ Declined</div>
                        @if(optional($a->offer)->rejection_reason)
                          <div class="mt-1 italic text-slate-600">{{ $a->offer->rejection_reason }}</div>
                        @endif
                      @elseif(optional($a->offer)->status === 'sent' || optional($a->offer)->status === 'draft')
                        @php
                          $gross = data_get($a->offer->salary, 'gross', data_get($a->offer->salary, 'base', 0));
                          $allow = data_get($a->offer->salary, 'allowance', 0);
                        @endphp
                        <div class="font-semibold text-amber-700">{{ optional($a->offer)->status === 'sent' ? '⏳ Dikirim' : '📄 Draft' }}</div>
                        <div class="mt-1 text-slate-600">Gaji: Rp {{ is_numeric($gross) ? number_format((float) $gross, 0, ',', '.') : $gross }}</div>
                        @if($allow)
                          <div class="text-slate-600">Tunjangan: Rp {{ is_numeric($allow) ? number_format((float) $allow, 0, ',', '.') : $allow }}</div>
                        @endif
                      @else
                        <div class="text-slate-500">Belum ada OL</div>
                      @endif
                    </div>
                  </div>

                  <div class="mt-3 flex flex-wrap gap-1.5">
                    <button type="button" class="btn-xs btn-detail-toggle" onclick="toggleDetailPanel('detail-{{ $a->id }}', this)">
                      ▲ Tutup
                    </button>
                    <button type="button" class="btn-xs btn-outline" onclick="openHistoryModal('{{ $a->id }}', '{{ addslashes($a->user->name) }}')" title="Lihat history pergerakan stage">
                      📋 History
                    </button>
                    @if(optional($a->user)->candidateProfile && Route::has('admin.candidates.show'))
                      <a class="btn-xs btn-outline" href="{{ route('admin.candidates.show', $a->user->candidateProfile) }}" target="_blank">Profil</a>
                    @endif
                    <a class="btn-xs btn-outline" href="{{ route('jobs.show', $a->job) }}" target="_blank">Job</a>
                  </div>

                  <div class="mt-3 flex flex-wrap gap-1.5">
                    @if($fbHR || $fbUser || $fbTrainer || $fbEmployee)
                      <button type="button" class="btn-xs btn-outline" onclick="openFbModal(this)">View Feedbacks</button>
                    @endif

                    @if(in_array($stageKey, ['hr_iv','user_trainer_iv']))
                      <button type="button" class="btn-xs btn-sched" onclick="openSchedModal(this, '{{ $stageKey }}')">
                        Schedule
                      </button>
                    @endif

                    @if($stageKey === 'user_trainer_iv' && ($isHR || $isTrainer || $isKaryawan))
                      <button type="button" class="btn-xs fb-panel-toggle"
                        onclick="toggleFbPanel('fbp-{{ $a->id }}', this)" title="Tampilkan form feedback">
                        ⚠️ Feedback
                      </button>
                    @endif

                    @if($stageKey === 'offer' && $isHR)
                      <button type="button" class="btn-xs btn-primary"
                        onclick="openSendOlModal('{{ $a->id }}', '{{ addslashes($a->user->name) }}', '{{ optional($a->offer)->salary['gross'] ?? 0 }}', '{{ optional($a->offer)->salary['allowance'] ?? 0 }}', this)">
                        ✉️ Kirim OL ke {{ $a->user->name }}
                      </button>
                      @if(optional($a->offer)->status === 'sent')
                        <button type="button" class="btn-xs btn-primary" style="background-color: #dc2626; border-color: transparent; color: #fff;"
                          onclick="openRejectOlModal('{{ $a->id }}', '{{ addslashes($a->user->name) }}')" title="Tolak Offering Letter">
                          ✕ Tolak OL
                        </button>
                      @endif
                    @endif

                    @if($stageKey === 'mcu' && $isHR)
                      <button type="button" class="btn-xs btn-primary"
                        onclick="openSendMcuModal('{{ $a->id }}', '{{ addslashes($a->user->name) }}', this)">
                        ✉️ Kirim Undangan MCU ke {{ $a->user->name }}
                      </button>
                    @endif

                    @if($stageKey === 'mobilisasi' && ($isHR || $isTrainer))
                      <button type="button" class="btn-xs btn-primary"
                        onclick="openMobilisasiModal('{{ $a->id }}', '{{ addslashes($a->user->name) }}', this)">
                        ✈ Mobilisasi (Tiket & Email)
                      </button>
                    @endif

                    @if($stageKey === 'ground_test' && ($isHR || $isTrainer))
                      <button type="button" class="btn-xs btn-primary"
                        onclick="openGroundTestModal('{{ $a->id }}', '{{ addslashes($a->user->name) }}', this)">
                        📄 Update LAP & Hasil GT
                      </button>
                    @endif

                    @if($stageKey === 'ground_test' && $isHR)
                      <button type="button" class="btn-xs btn-outline"
                        onclick="openFbForm(this, '{{ $a->id }}', '{{ $a->user->name }}', 'ground_test', 'hr')">
                        + Feedback HR GT
                      </button>
                    @endif
                    @if($stageKey === 'ground_test' && $isTrainer)
                      <button type="button" class="btn-xs btn-outline"
                        onclick="openFbForm(this, '{{ $a->id }}', '{{ $a->user->name }}', 'ground_test', 'trainer')">
                        + Feedback Trainer GT
                      </button>
                    @endif
                    @if($stageKey === 'ground_test' && $isKaryawan)
                      <button type="button" class="btn-xs btn-outline"
                        onclick="openFbForm(this, '{{ $a->id }}', '{{ $a->user->name }}', 'ground_test', 'karyawan')">
                        + Feedback User GT
                      </button>
                    @endif

                    @if($stageKey === 'hr_iv' && !$fbHR && $isSuperHR)
                      <button type="button" class="btn-xs btn-primary" style="background-color:#d97706; font-size:.7rem;"
                        onclick="openFbForm(this, '{{ $a->id }}', '{{ $a->user->name }}', 'hr_iv', 'hr')" title="WAJIB isi feedback dan pilih setuju/tidak setuju sebelum bisa pindah ke stage lain">
                        ⚠️ + Isi Feedback HR
                      </button>
                    @endif

                    @if($isFreeMove && $isSuperHR)
                      <div class="free-move-wrap" style="width:100%; margin-top: .45rem;">
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
                    @elseif($stageKey === 'hr_iv' && !$fbHR && $isSuperHR)
                      <div style="width:100%; padding:.6rem; background:#fed7aa; border:1px solid #fb923c; border-radius:.4rem; font-size:.65rem; color:#b45309; margin-top: .45rem; text-align:center; font-weight:600;">
                        ⚠️ Selesaikan Feedback HR untuk pindah ke stage lain
                      </div>
                    @endif
                  </div>
                </div>

                <span class="kn-pill
                  @if($a->overall_status==='hired') pill-hired
                  @elseif($a->overall_status==='not_qualified') pill-nq
                  @else pill-active
                  @endif">
                  {{ $a->overall_status === 'hired' ? '✓ Hired' : ($a->overall_status === 'not_qualified' ? '✕ TIDAK lOLOS' : '● Active') }}
                </span>

                @php
                  $mcuRes = $a->mcu_result;
                @endphp
                @if($mcuRes)
                  <span class="kn-pill {{ $mcuRes === 'fit' ? 'pill-hired' : ($mcuRes === 'fit_note' ? 'pill-warn' : 'pill-nq') }}">
                    {{ $mcuRes === 'fit' ? '✓ FIT' : ($mcuRes === 'fit_note' ? '⚠ FIT NOTE' : '✕ UNFIT') }}
                  </span>
                @endif

                @if(isset($a->mobilisasi_meta['ticket_path']))
                  <span class="kn-pill pill-hired" title="Tiket: {{ $a->mobilisasi_meta['ticket_name'] }}">
                    ✈ TIKET OK
                  </span>
                @endif

                @if(isset($a->ground_test_meta['lap_path']) || $a->ground_test_result)
                  <span class="kn-pill {{ $a->ground_test_result === 'lolos' ? 'pill-hired' : ($a->ground_test_result === 'tidak_lolos' ? 'pill-nq' : 'pill-warn') }}">
                    GT: {{ $a->ground_test_result === 'lolos' ? 'LOLOS' : ($a->ground_test_result === 'tidak_lolos' ? 'TIDAK LOLOS' : 'PENDING') }}
                    @if(isset($a->ground_test_meta['lap_path'])) 📄 @endif
                  </span>
                @endif

                @if($isHrLocked)
                  <div class="kn-lock-note">⚠ Isi feedback HR sebelum dapat dilanjutkan</div>
                @endif

                <div class="kn-card-actions">
                  <button type="button" class="btn-xs btn-detail-toggle"
                    onclick="toggleDetailPanel('detail-{{ $a->id }}', this)">
                    ▼ Detail
                  </button>
                </div>

                @if($stageKey === 'user_trainer_iv')
                  <div id="fbp-{{ $a->id }}" class="hidden fb-panel">
                    @if($isHR)
                      <button type="button" class="btn-xs btn-primary"
                        onclick="openFbForm(this, '{{ $a->id }}', '{{ $a->user->name }}', 'user_trainer_iv', 'hr')">
                        + Isi Feedback HR
                      </button>
                    @endif
                    @if($isTrainer)
                      <button type="button" class="btn-xs btn-primary"
                        onclick="openFbForm(this, '{{ $a->id }}', '{{ $a->user->name }}', 'user_trainer_iv', 'trainer')">
                        + Isi Feedback Trainer
                      </button>
                    @endif
                    @if($isKaryawan)
                      <button type="button" class="btn-xs btn-primary"
                        onclick="openFbForm(this, '{{ $a->id }}', '{{ $a->user->name }}', 'user_trainer_iv', 'karyawan')">
                        + Isi Feedback User
                      </button>
                    @endif
                  </div>
                @endif
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
      <input type="hidden" id="fbform-role" value="hr">
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
      <div class="stage-banner" id="sched-banner" style="display:none;"></div>
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
      <button class="btn-xs btn-primary" onclick="submitSchedule()">Kirim Undangan</button>
    </div>
  </div>
</div>

{{-- ============================= MODAL: KIRIM OL EMAIL ============================= --}}
<div class="hidden kn-overlay" id="overlay-send-ol">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title" id="send-ol-title">Kirim Offering Letter</div>
        <div class="kn-modal-sub">Kirim Offering Letter kepada kandidat (dokumen digenerate otomatis)</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-send-ol')">✕</button>
    </div>
    <div class="kn-modal-body">
      <form id="form-send-ol" method="POST">
        @csrf
        <input type="hidden" name="app_id" id="send-ol-appid">
        <div class="space-y-1.5" style="margin-bottom: 16px;">
        </div>
        <div class="grid grid-cols-2 gap-4" style="margin-bottom: 16px;">
          <div class="space-y-1.5">
            <label for="send-ol-gross" class="fm-label">Gaji Pokok (Rp)</label>
            <input type="number" name="gross" id="send-ol-gross" class="fm-ctrl" required min="0" step="1">
          </div>
          <div class="space-y-1.5">
            <label for="send-ol-allowance" class="fm-label">Site Allowance (Rp)</label>
            <input type="number" name="allowance" id="send-ol-allowance" class="fm-ctrl" required min="0" step="1">
          </div>
        </div>
        {{-- File upload removed: Offering Letter is generated server-side --}}
        <div class="space-y-1.5" style="margin-bottom: 16px;">
          <label for="send-ol-email-body" class="fm-label">Body Email</label>
          <textarea name="email_body" id="send-ol-email-body" class="fm-ctrl" required rows="5" placeholder="Isi email Offering Letter yang akan dikirim ke kandidat..."></textarea>
        </div>
      </form>
    </div>
    <div class="kn-modal-footer">
      <button class="btn-xs btn-outline" onclick="closeModal('overlay-send-ol')">Batal</button>
      <button class="btn-xs btn-primary" id="btn-send-ol" onclick="submitSendOl()">Kirim</button>
    </div>
  </div>
</div>

{{-- ============================= MODAL: KIRIM MCU EMAIL ============================= --}}
<div class="hidden kn-overlay" id="overlay-send-mcu">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title" id="send-mcu-title">Kirim Undangan MCU</div>
        <div class="kn-modal-sub">Upload dokumen Undangan MCU untuk dikirim ke kandidat</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-send-mcu')">✕</button>
    </div>
    <div class="kn-modal-body">
      <form id="form-send-mcu" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="app_id" id="send-mcu-appid">
        <input type="hidden" name="company_name" id="send-mcu-company-name">
        <input type="hidden" name="doc_no" id="send-mcu-doc-no">
        <input type="hidden" name="city" id="send-mcu-city">
        <input type="hidden" name="project_name" id="send-mcu-project-name">
        <input type="hidden" name="clinic_name" id="send-mcu-clinic">
        <input type="hidden" name="clinic_city" id="send-mcu-clinic-city">
        <input type="hidden" name="clinic_address" id="send-mcu-address">
        <input type="hidden" name="mcu_date" id="send-mcu-date">
        <input type="hidden" name="mcu_time" id="send-mcu-time">
        <input type="hidden" name="for_text" id="send-mcu-for">
        <input type="hidden" name="bu_name" id="send-mcu-bu">
        <input type="hidden" name="matrix_owner" id="send-mcu-owner">
        <input type="hidden" name="package" id="send-mcu-package">
        <input type="hidden" name="notes" id="send-mcu-notes">
        <input type="hidden" name="result_emails" id="send-mcu-result-emails">
        <input type="hidden" name="signer_name" id="send-mcu-signer-name">
        <input type="hidden" name="signer_title" id="send-mcu-signer-title">
        <input type="hidden" name="footer_company_name" id="send-mcu-footer-company">
        <input type="hidden" name="footer_address" id="send-mcu-footer-address">
        <input type="hidden" name="footer_email" id="send-mcu-footer-email">
        <input type="hidden" name="footer_website" id="send-mcu-footer-website">
        <input type="hidden" name="email_body" id="send-mcu-body">
        <div style="padding: 20px; border: 2px dashed #ccc; border-radius: 8px; text-align: center; background: #f9f9f9; margin-bottom: 20px;">
          <div style="margin-bottom: 15px;">
            <label class="fm-label">Pilih File Undangan MCU</label>
            <input type="file" name="mcu_file" id="send-mcu-file" class="fm-ctrl" accept=".pdf,.doc,.docx" required style="padding: 15px; cursor: pointer;">
            <div style="font-size: 0.75rem; color: #666; margin-top: 8px;">Format: PDF, DOC, DOCX | Ukuran maks: 10MB</div>
          </div>
        </div>
        <div style="background: #fffbeb; border-left: 4px solid #fbbf24; padding: 12px; border-radius: 4px; margin-bottom: 15px;">
          <div style="font-size: 0.85rem; color: #92400e; font-weight: 500;">
            ℹ️ File akan langsung dikirim ke kandidat sebagai lampiran email Undangan MCU.
          </div>
        </div>
      </form>
    </div>
    <div class="kn-modal-footer">
      <button class="btn-xs btn-outline" onclick="closeModal('overlay-send-mcu')">Batal</button>
      <button class="btn-xs btn-primary" id="btn-send-mcu" onclick="submitSendMcu()">Upload & Kirim</button>
    </div>
  </div>
</div>

{{-- ============================= MODAL: MOBILISASI ============================= --}}
<div class="hidden kn-overlay" id="overlay-mobilisasi">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title">✈ Mobilisasi</div>
        <div class="kn-modal-sub" id="mob-sub">Upload tiket dan kirim email ke kandidat</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-mobilisasi')">✕</button>
    </div>
    <form id="form-mobilisasi" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="kn-modal-body">
        <div class="fm-group">
          <label class="fm-label">Upload Tiket / Dokumen</label>
          <input type="file" name="ticket" class="fm-ctrl" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
          <div id="mob-ticket-existing" style="font-size:0.65rem; color:#888; margin-top:4px;"></div>
        </div>
        <div class="fm-group">
          <label class="fm-label">Catatan Internal</label>
          <textarea name="notes" id="mob-notes" class="fm-ctrl" rows="2" placeholder="Catatan untuk tim HR..."></textarea>
        </div>
        <div class="fm-group">
          <label class="fm-label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="checkbox" name="send_email" id="mob-send-email" value="1" onchange="document.getElementById('mob-email-group').style.display = this.checked ? 'block' : 'none'">
            Kirim Email ke Kandidat
          </label>
        </div>
        <div id="mob-email-group" style="display:none">
          <label class="fm-label">Isi Pesan Email</label>
          <textarea name="email_body" id="mob-email-body" class="fm-ctrl" rows="4" placeholder="Tulis instruksi mobilisasi untuk kandidat..."></textarea>
        </div>
      </div>
      <div class="kn-modal-footer">
        <button type="button" class="btn-xs btn-outline" onclick="closeModal('overlay-mobilisasi')">Batal</button>
        <button type="submit" class="btn-xs btn-primary">Simpan & Kirim</button>
      </div>
    </form>
  </div>
</div>

{{-- ============================= MODAL: GROUND TEST ============================= --}}
<div class="hidden kn-overlay" id="overlay-gt">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title">📄 Ground Test</div>
        <div class="kn-modal-sub" id="gt-sub">Update LAP dan Hasil Ground Test</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-gt')">✕</button>
    </div>
    <form id="form-gt" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="kn-modal-body">
        <div class="fm-group">
          <label class="fm-label">Upload File LAP (Trainer)</label>
          <input type="file" name="lap" class="fm-ctrl" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
          <div id="gt-lap-existing" style="font-size:0.65rem; color:#888; margin-top:4px;">
            <div style="margin-top: 4px;">
              <span id="gt-lap-filename"></span>
              <a id="gt-lap-view-btn" href="#" target="_blank" style="margin-left: 8px; color: #a77d52; text-decoration: underline; font-weight: 500; display: none;">
                📄 Buka File
              </a>
            </div>
          </div>
        </div>
        <div class="fm-group">
          <label class="fm-label">Hasil Ground Test (Tidak Wajib)</label>
          <select name="result" id="gt-result" class="fm-ctrl">
            <option value="">— Belum Ada Hasil —</option>
            <option value="lolos">✓ Lolos</option>
            <option value="tidak_lolos">✕ Tidak Lolos</option>
          </select>
        </div>
        <div class="fm-group">
          <label class="fm-label">Catatan Tambahan</label>
          <textarea name="notes" id="gt-notes" class="fm-ctrl" rows="2" placeholder="Catatan evaluasi GT..."></textarea>
        </div>
      </div>
      <div class="kn-modal-footer">
        <button type="button" class="btn-xs btn-outline" onclick="closeModal('overlay-gt')">Batal</button>
        <button type="submit" class="btn-xs btn-primary">Simpan Hasil</button>
      </div>
    </form>
  </div>
</div>

{{-- ============================= MODAL: REJECT OL ============================= --}}
<div class="hidden kn-overlay" id="overlay-reject-ol">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title" id="reject-ol-title">Tolak Offering Letter</div>
        <div class="kn-modal-sub">Masukkan alasan penolakan Offering Letter</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-reject-ol')">✕</button>
    </div>
    <div class="kn-modal-body">
      <input type="hidden" id="reject-ol-appid">
      <div class="fm-group">
        <label class="fm-label">Alasan Penolakan *</label>
        <textarea class="fm-ctrl" id="reject-ol-reason" rows="4" placeholder="Jelaskan mengapa offering letter ditolak..."></textarea>
      </div>
      <div class="text-[0.75rem] text-[#9a7558] mt-3">
        Catatan ini akan dicatat sebagai bukti penolakan OL.
      </div>
    </div>
    <div class="kn-modal-footer">
      <button class="btn-xs btn-outline" onclick="closeModal('overlay-reject-ol')">Batal</button>
      <button class="btn-xs btn-primary" id="btn-reject-ol" style="background-color: #dc2626; border-color: transparent; color: #fff;" onclick="submitRejectOl()">Tolak OL</button>
    </div>
  </div>
</div>

{{-- ============================= MODAL: HISTORY PERGERAKAN ============================= --}}
<div class="hidden kn-overlay" id="overlay-history">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title" id="history-modal-title">History Pergerakan</div>
        <div class="kn-modal-sub">Riwayat pergerakan dan perubahan stage kandidat</div>
      </div>
      <button class="kn-modal-close" onclick="closeModal('overlay-history')">✕</button>
    </div>
    <div class="kn-modal-body">
      <div id="history-list" class="space-y-2 overflow-y-auto max-h-96"></div>
    </div>
    <div class="kn-modal-footer">
      <button class="btn-xs btn-outline" onclick="closeModal('overlay-history')">Tutup</button>
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

function roleLabel(r) {
  if(r === 'hr') return 'HR';
  if(r === 'user' || r === 'karyawan') return 'User';
  if(r === 'trainer') return 'Trainer';
  if(r === 'pelamar' || r === 'employee') return 'Pelamar';
  return r;
}
function roleIcon(r) {
  if(r === 'hr') return 'HR';
  if(r === 'user' || r === 'karyawan') return 'US';
  if(r === 'trainer') return 'TR';
  if(r === 'pelamar' || r === 'employee') return 'PL';
  return '??';
}
function roleCls(r) {
  if(r === 'hr') return 'fb-hr';
  if(r === 'user' || r === 'karyawan') return 'fb-user';
  if(r === 'trainer') return 'fb-tr';
  if(r === 'pelamar' || r === 'employee') return 'fb-employee';
  return '';
}

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
function openFbModal(btn) {
  const card = getCard(btn);
  const fbHr = getCardData(card, 'fbHr');
  const fbUser = getCardData(card, 'fbUser');
  const fbTrainer = getCardData(card, 'fbTrainer');
  const fbEmployee = getCardData(card, 'fbEmployee');

  let html = '';
  const addBlock = (title, data, color) => {
    if(!data) return;
    const feedbackText = data.notes || data.feedback || data;
    const approveStatus = data.approve;
    html += `
      <div style="margin-bottom:16px; padding:12px; background:#f8fafc; border-radius:8px; border-left:4px solid ${color}">
        <div style="font-weight:bold; font-size:0.75rem; color:#64748b; text-transform:uppercase; margin-bottom:4px;">${title}</div>
        <div style="font-size:0.875rem; color:#a77d52; white-space:pre-wrap;">${feedbackText}</div>
        ${approveStatus ? `<div style="margin-top:8px; font-size:0.75rem; font-weight:bold; color:${approveStatus === 'yes' ? '#10b981' : '#ef4444'}">${approveStatus === 'yes' ? '✓ SETUJU' : '✕ TIDAK SETUJU'}</div>` : ''}
      </div>
    `;
  };

  addBlock('HR Feedback', fbHr, '#3b82f6');
  addBlock('User Feedback', fbUser, '#8b5cf6');
  addBlock('Trainer Feedback', fbTrainer, '#10b981');
  addBlock('Feedback Pelamar', fbEmployee, '#f59e0b');

  if(!html) html = '<div style="text-align:center; color:#94a3b8; padding:20px;">Belum ada feedback untuk kandidat ini.</div>';

  const container = document.getElementById('fb-modal-body');
  if (container) {
    container.innerHTML = html;
    openModal('overlay-fb');
  }
}

function openSendOlModal(appId, name, gross, allow, btn) {
  document.getElementById('send-ol-appid').value = appId;
  document.getElementById('send-ol-title').textContent = 'Kirim Offering Letter — ' + name;
  const form = document.getElementById('form-send-ol');
  form.action = `/admin/applications/${appId}/send-offer`;
  document.getElementById('send-ol-gross').value = gross;
  document.getElementById('send-ol-allowance').value = allow;
  // OL upload handled server-side; no client file input to clear.
  document.getElementById('send-ol-email-body').value = 'Kepada Yth. ' + name + ',\n\nDengan ini kami sampaikan Offering Letter untuk posisi yang Anda lamar. Silakan cek lampiran PDF untuk detail lengkapnya.\n\nHarap konfirmasi penerimaan offering letter ini melalui aplikasi.\n\nTerima kasih.';
  openModal('overlay-send-ol');
}

function submitSendOl() {
  const form = document.getElementById('form-send-ol');
  if(!form.reportValidity()) return;
  document.getElementById('btn-send-ol').textContent = 'Mengirim...';
  document.getElementById('btn-send-ol').disabled = true;
  form.submit();
}

function openSendMcuModal(appId, name, btn) {
  const card = getCard(btn);
  let meta = {};
  try { meta = JSON.parse(card.dataset.mcuMeta || '{}'); } catch(e) { meta = {}; }

  const tpl = @json($mcuTemplate);
  const setValue = (id, value) => { const el = document.getElementById(id); if (el) el.value = value ?? ''; };
  const setText  = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value ?? ''; };

  setValue('send-mcu-appid', appId);
  setText('send-mcu-title', 'Kirim Undangan MCU — ' + name);
  setValue('send-mcu-company-name', meta.company_name || (tpl ? tpl.company_name : 'ANDALAN'));
  setValue('send-mcu-doc-no', meta.doc_no || '');
  setValue('send-mcu-city', meta.city || (tpl ? tpl.city : 'Jakarta'));
  setValue('send-mcu-project-name', meta.project_name || (tpl ? tpl.project_name : 'PROJECT'));
  setValue('send-mcu-clinic', meta.clinic_name || (tpl ? tpl.vendor_name : ''));
  setValue('send-mcu-clinic-city', meta.clinic_city || '');
  setValue('send-mcu-address', meta.clinic_address || (tpl ? tpl.vendor_address : ''));
  setValue('send-mcu-date', meta.mcu_date || '');
  setValue('send-mcu-time', meta.mcu_time || '08:00');
  setValue('send-mcu-for', meta.for_text || (tpl ? tpl.for_text : ''));
  setValue('send-mcu-bu', meta.bu_name || (tpl ? tpl.bu_name : ''));
  setValue('send-mcu-owner', meta.matrix_owner || (tpl ? tpl.matrix_owner : ''));
  setValue('send-mcu-package', meta.package || '');
  setValue('send-mcu-notes', meta.notes || (tpl ? tpl.notes : "1. Bagi kandidat berusia > 40 tahun, diwajibkan menjalani pemeriksaan treadmill.\n2. Mohon cocokan KTP asli dengan identitas kandidat yang akan diperiksa."));
  setValue('send-mcu-result-emails', meta.result_emails || (tpl ? tpl.result_emails : "hendy.fardiansyah@pt-aap.com\nvidya.paramitha.putri@pt-aap.com\nrizal.abu@pt-aap.com"));
  setValue('send-mcu-signer-name', meta.signer_name || (tpl ? tpl.signer_name : 'Roy/Hansen C. Saragi'));
  setValue('send-mcu-signer-title', meta.signer_title || (tpl ? tpl.signer_title : 'General Manager'));
  setValue('send-mcu-footer-company', meta.footer_company_name || (tpl ? tpl.footer_company_name : 'PT. Andalan Artha Primanusa'));
  setValue('send-mcu-footer-address', meta.footer_address || (tpl ? tpl.footer_address : 'Jl. Plaju No.11 Kebon Melati, Tanah Abang Jakarta Pusat 10230 DKI Jakarta – Indonesia'));
  setValue('send-mcu-footer-email', meta.footer_email || (tpl ? tpl.footer_email : 'corporatesecretary@andalan-nusantara.com'));
  setValue('send-mcu-footer-website', meta.footer_website || (tpl ? tpl.footer_website : 'www.andalan-nusantara.com'));
  setValue('send-mcu-body', meta.email_body || "Selamat! Anda telah lolos ke tahap Medical Check Up (MCU).\n\nTerlampir adalah Surat Undangan MCU resmi yang berisi detail jadwal, lokasi, dan instruksi persiapan yang wajib Anda patuhi.\n\nMohon hadir tepat waktu.");

  const form = document.getElementById('form-send-mcu');
  if (form) form.action = `/admin/applications/${appId}/send-mcu`;
  openModal('overlay-send-mcu');
}

function submitSendMcu() {
  const form   = document.getElementById('form-send-mcu');
  const button = document.getElementById('btn-send-mcu');
  if (!form.reportValidity()) return;
  const formData = new FormData(form);
  button.textContent = 'Mengirim...';
  button.disabled = true;
  fetch(form.action, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value, 'Accept': 'application/json' },
    body: formData
  })
  .then(async (response) => {
    const data = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(data.message || 'Gagal mengupload dokumen MCU');
    showToast(data.message || 'Dokumen MCU berhasil diupload.', 'ok');
    closeModal('overlay-send-mcu');
    window.location.reload();
  })
  .catch((error) => { showToast(error.message || 'Gagal mengupload dokumen MCU', 'err'); })
  .finally(() => { button.textContent = 'Upload & Kirim'; button.disabled = false; });
}

function deleteFeedback(appId, role, csrf) {
  if (!confirm('Yakin ingin menghapus feedback ini?')) return;
  fetch('/admin/applications/feedback', {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify({ application_id: appId, role: role })
  }).then(async res => {
    if (!res.ok) { const err = await res.json().catch(()=>({})); throw new Error(err.message || 'Gagal menghapus feedback'); }
    showToast('Feedback berhasil dihapus', 'ok');
    setTimeout(() => window.location.reload(), 800);
  }).catch(e => showToast(e.message, 'err'));
}

function updateMcuResult(selectEl, appId) {
  const result = selectEl.value;
  if (!result) return;
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  fetch(`/admin/applications/${appId}/mcu-result`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify({ result: result })
  })
  .then(async res => {
    if (!res.ok) { const err = await res.json().catch(()=>({})); throw new Error(err.message || 'Gagal update hasil MCU'); }
    return res.json();
  })
  .then(data => { showToast(data.message || 'Hasil MCU diperbarui', 'ok'); setTimeout(() => window.location.reload(), 800); })
  .catch(err => { showToast(err.message, 'err'); });
}

function openMobilisasiModal(appId, name, btn) {
  const card = getCard(btn);
  const meta = JSON.parse(card.dataset.mobilisasiMeta || '{}');
  const form = document.getElementById('form-mobilisasi');
  form.action = `/admin/applications/${appId}/mobilisasi`;
  document.getElementById('mob-sub').textContent = 'Kandidat: ' + name;
  document.getElementById('mob-notes').value = meta.notes || '';
  document.getElementById('mob-send-email').checked = false;
  document.getElementById('mob-email-group').style.display = 'none';
  document.getElementById('mob-email-body').value = '';
  const existing = document.getElementById('mob-ticket-existing');
  existing.textContent = meta.ticket_name ? 'File saat ini: ' + meta.ticket_name : '';
  openModal('overlay-mobilisasi');
}

function openGroundTestModal(appId, name, btn) {
  const card = getCard(btn);
  const meta = JSON.parse(card.dataset.gtMeta || '{}');
  const result = card.dataset.gtResult || '';
  const form = document.getElementById('form-gt');
  form.action = `/admin/applications/${appId}/ground-test`;
  document.getElementById('gt-sub').textContent = 'Kandidat: ' + name;
  document.getElementById('gt-notes').value = meta.notes || '';
  document.getElementById('gt-result').value = result;
  
  const filenameEl = document.getElementById('gt-lap-filename');
  const viewBtn = document.getElementById('gt-lap-view-btn');
  
  if (meta.lap_name) {
    filenameEl.textContent = 'File saat ini: ' + meta.lap_name;
    viewBtn.href = `/admin/applications/${appId}/ground-test/lap`;
    viewBtn.style.display = 'inline';
  } else {
    filenameEl.textContent = '';
    viewBtn.style.display = 'none';
  }
  
  openModal('overlay-gt');
}

/* ============================= ISI FEEDBACK FORM ============================= */
let fbFormCardEl = null;

function fbRoleLabel(role) {
  if (role === 'trainer') return 'Trainer';
  if (role === 'karyawan') return 'User';
  if (role === 'pelamar') return 'Pelamar';
  return 'HR';
}

function toggleFbPanel(panelId, btn) {
  const panel = document.getElementById(panelId);
  if (!panel) return;
  panel.classList.toggle('hidden');
  if (btn) btn.textContent = panel.classList.contains('hidden') ? '⚠️ Feedback' : '✕ Tutup';
}

function toggleDetailPanel(panelId, btn) {
  const panel = document.getElementById(panelId);
  if (!panel) return;
  panel.classList.toggle('hidden');
  if (btn) btn.textContent = panel.classList.contains('hidden') ? '▼ Detail' : '▲ Tutup';
}

function openFbForm(btn, appId, name, stage, role = 'hr') {
  fbFormCardEl = getCard(btn);
  document.getElementById('fbform-title').textContent = 'Isi Feedback ' + fbRoleLabel(role) + ' — ' + name;
  document.getElementById('fbform-app-id').value = appId;
  document.getElementById('fbform-stage').value  = stage;
  document.getElementById('fbform-role').value   = role;
  document.getElementById('fbform-notes').value  = '';
  document.getElementById('fbform-approve').value = '';
  openModal('overlay-fbform');
}

function submitFbForm() {
  const notes   = document.getElementById('fbform-notes').value.trim();
  const approve = document.getElementById('fbform-approve').value;
  const appId   = document.getElementById('fbform-app-id').value;
  const stage   = document.getElementById('fbform-stage').value;
  const role    = document.getElementById('fbform-role').value || 'hr';
  const csrf    = document.querySelector('meta[name="csrf-token"]')?.content
               || (fbFormCardEl && fbFormCardEl.dataset.csrf)
               || '';

  if (!notes)   { showToast('Catatan feedback wajib diisi', 'err'); return; }
  if (!approve) { showToast('Pilih setuju/tidak setuju', 'err');    return; }

  fetch(`/admin/applications/feedback`, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify({ application_id: appId, stage_key: stage, role, feedback: notes, approve }),
  })
  .then(async res => {
    const contentType = res.headers.get('content-type') || '';
    if (!res.ok) {
      if (contentType.includes('application/json')) { const j = await res.json().catch(()=>({})); throw new Error(j.message || 'Gagal simpan'); }
      else { const text = await res.text(); throw new Error('Unexpected response: ' + text.slice(0, 100)); }
    }
    return contentType.includes('application/json') ? res.json() : res.text();
  })
  .then(() => {
    if (fbFormCardEl) {
      if (role === 'hr') fbFormCardEl.dataset.fbHr = JSON.stringify({ notes, approve });
      if (role === 'trainer') fbFormCardEl.dataset.fbTrainer = JSON.stringify({ notes, approve });
      if (role === 'karyawan') fbFormCardEl.dataset.fbUser = JSON.stringify({ notes, approve });
      fbFormCardEl.removeAttribute('draggable');
      fbFormCardEl.setAttribute('draggable', 'true');
      fbFormCardEl.classList.remove('card-locked');
      if (role === 'hr') {
        const lockNote = fbFormCardEl.querySelector('.kn-lock-note');
        if (lockNote) lockNote.remove();
        const warningDiv = Array.from(fbFormCardEl.querySelectorAll('div')).find(d => d.textContent.includes('Selesaikan Feedback HR'));
        if (warningDiv) warningDiv.remove();
      }
      const fbToggle = fbFormCardEl.querySelector('.fb-panel-toggle');
      const stage = fbFormCardEl.dataset.stage;
      if (fbToggle && stage === 'user_trainer_iv') {
        fbToggle.style.display = 'none';
        const fbPanel = fbFormCardEl.querySelector('[id^="fbp-"]');
        if (fbPanel) fbPanel.classList.add('hidden');
      }
      initDrag(fbFormCardEl);
    }
    closeModal('overlay-fbform');
    showToast('✓ Feedback ' + fbRoleLabel(role) + ' tersimpan!', 'ok');
  })
  .catch(err => { showToast(err.message || 'Gagal menyimpan feedback', 'err'); });
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
  document.getElementById('sched-sub').textContent    = candidate + ' — ' + job;
  document.getElementById('sched-title').value        = 'Interview – ' + candidate;
  document.getElementById('sched-to-stage').value     = toStage || '';
  document.getElementById('sched-banner').innerHTML   = `➜ Setelah submit, <b>${candidate}</b> akan dipindah ke <b>${stageLabels[toStage] || toStage}</b>`;
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
  if (!title)         { showToast('Judul interview wajib diisi', 'err');   return; }
  if (!start || !end) { showToast('Waktu mulai & selesai wajib diisi', 'err'); return; }
  if (start >= end)   { showToast('Waktu selesai harus setelah waktu mulai', 'err'); return; }
  const form = document.getElementById('sched-form');
  const csrf = form.querySelector('[name="_token"]').value;
  const data = new FormData(form);
  fetch(form.action, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: data })
  .then(async res => {
    const contentType = res.headers.get('content-type') || '';
    if (!res.ok) {
      if (contentType.includes('application/json')) { const j = await res.json().catch(()=>({})); throw new Error(j.message || 'Gagal kirim'); }
      else { const text = await res.text(); throw new Error('Unexpected response: ' + text.slice(0, 100)); }
    }
    return contentType.includes('application/json') ? res.json() : res.text();
  })
  .then(() => {
    closeModal('overlay-sched');
    showToast('Undangan berhasil dikirim!', 'ok');
    schedCardEl  = null;
    schedToStage = null;
  })
  .catch(err => { closeModal('overlay-sched'); showToast(err.message || 'Gagal mengirim undangan', 'err'); });
}

/* ============================= FREE MOVE ============================= */
function freeMoveCard(selectEl, appId, fromStage, csrf, baseUrl) {
  const toStage = selectEl.value;
  if (!toStage) return;
  const card = selectEl.closest('.kn-card');
  selectEl.value = '';
  fetch(baseUrl + '?id=' + appId, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ to_stage: toStage, from_stage: fromStage }),
  })
  .then(async res => {
    const contentType = res.headers.get('content-type') || '';
    if (!res.ok) {
      if (contentType.includes('application/json')) { const j = await res.json().catch(()=>({})); throw new Error(j.message || 'Gagal pindah'); }
      else { const text = await res.text(); throw new Error('Unexpected response: ' + text.slice(0, 100)); }
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
  .catch(err => { showToast(err.message || 'Gagal memindahkan kartu', 'err'); });
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

const boardWrap = document.querySelector('.kn-board-wrap');
if (boardWrap) {
  boardWrap.addEventListener('wheel', (e) => {
    if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
    const targetColumn = e.target.closest && e.target.closest('.kn-col-body');
    if (targetColumn && targetColumn.scrollHeight > targetColumn.clientHeight) return;
    e.preventDefault();
    boardWrap.scrollLeft += e.deltaY;
  }, { passive: false });
}

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
    body.insertBefore(dragCard, body.firstChild);
    dragCard.dataset.stage = toStage;
    updateCount(from,    -1);
    updateCount(toStage,  1);
    const movedCard = dragCard;
    dragCard = null; dragFrom = null;
    fetch(url + '?id=' + appId, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ to_stage: toStage, from_stage: from }),
    })
    .then(async res => {
      const contentType = res.headers.get('content-type') || '';
      if (!res.ok) {
        if (contentType.includes('application/json')) { const j = await res.json().catch(()=>({})); throw new Error(j.message || 'Move failed'); }
        else { const text = await res.text(); throw new Error('Unexpected response: ' + text.slice(0, 100)); }
      }
      return contentType.includes('application/json') ? res.json() : res.text();
    })
    .then(() => { showToast('Dipindah ke ' + (stageLabels[toStage] || toStage), 'ok'); })
    .catch(err => {
      const fromCol = document.getElementById('col-' + from);
      if (fromCol) fromCol.insertBefore(movedCard, fromCol.firstChild);
      movedCard.dataset.stage = from;
      updateCount(toStage, -1);
      updateCount(from,     1);
      showToast(err.message || 'Gagal memindahkan kartu', 'err');
    });
  });
});

let _knSuppressClick = false;
document.addEventListener('dragstart', () => { _knSuppressClick = true; });
document.addEventListener('dragend', () => { setTimeout(() => { _knSuppressClick = false; }, 300); });
document.addEventListener('click', (e) => {
  if (!_knSuppressClick) return;
  const targetCard = e.target.closest && e.target.closest('.kn-card');
  if (targetCard) { e.preventDefault(); e.stopPropagation(); }
}, true);

/* ============================= REJECT OL ============================= */
function openRejectOlModal(appId, name) {
  document.getElementById('reject-ol-appid').value = appId;
  document.getElementById('reject-ol-title').textContent = 'Tolak Offering Letter — ' + name;
  document.getElementById('reject-ol-reason').value = '';
  openModal('overlay-reject-ol');
}

function submitRejectOl() {
  const appId = document.getElementById('reject-ol-appid').value;
  const reason = document.getElementById('reject-ol-reason').value.trim();
  if (!reason) { showToast('Silakan masukkan alasan penolakan', 'err'); return; }
  document.getElementById('btn-reject-ol').disabled = true;
  fetch(`/admin/applications/${appId}/reject-offer`, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({ rejection_reason: reason }),
  })
  .then(async res => {
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Gagal menolak OL');
    showToast('Offering Letter berhasil ditolak', 'ok');
    closeModal('overlay-reject-ol');
    setTimeout(() => window.location.reload(), 500);
  })
  .catch(err => { showToast(err.message || 'Gagal menolak OL', 'err'); document.getElementById('btn-reject-ol').disabled = false; });
}

/* ============================= VIEW HISTORY ============================= */
function openHistoryModal(appId, name) {
  document.getElementById('history-modal-title').textContent = 'History Pergerakan — ' + name;
  document.getElementById('history-list').innerHTML = '<div class="py-4 text-center">Memuat...</div>';
  fetch(`/admin/applications/${appId}/stages-history`, {
    method: 'GET',
    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Accept': 'application/json' },
  })
  .then(async res => {
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.message || 'Gagal memuat history');
    let html = '';
    if (data.stages && data.stages.length > 0) {
      data.stages.forEach((stage, idx) => {
        const actedByName = stage.acted_by_name || stage.acted_by || 'System';
        const createdAt = new Date(stage.created_at).toLocaleString('id-ID', {
          year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit'
        });
        html += `
          <div class="border-b border-[#e2d9cf] pb-3 mb-3">
            <div class="font-semibold text-[#3d1f08]">${stage.stage_key.replace(/_/g, ' ').toUpperCase()}</div>
            <div class="text-sm text-[#9a7558] mt-1">Status: <span class="font-medium">${stage.status}</span></div>
            <div class="text-sm text-[#b89070] mt-1">Diubah oleh: <span class="font-medium">${actedByName}</span></div>
            <div class="text-sm text-[#b89070]">Waktu: <span class="font-medium">${createdAt}</span></div>
            ${stage.notes ? `<div class="text-sm text-[#7a4f2a] mt-2 italic">Catatan: ${stage.notes}</div>` : ''}
          </div>
        `;
      });
    } else {
      html = '<div class="text-center text-[#9a7558] py-4">Belum ada history pergerakan</div>';
    }
    document.getElementById('history-list').innerHTML = html;
  })
  .catch(err => { document.getElementById('history-list').innerHTML = `<div class="py-4 text-center text-red-600">${err.message}</div>`; });
  openModal('overlay-history');
}

@if(session('ok'))
  showToast("{{ session('ok') }}", 'ok');
@endif
@if(session('error'))
  showToast("{{ session('error') }}", 'err');
@endif
@if($errors->any())
  showToast("{{ $errors->first() }}", 'err');
@endif
</script>

@endsection
