{{-- resources/views/kanban/mine.blade.php --}}
{{-- Kanban untuk: pelamar, karyawan, trainer (bukan admin/hr/superadmin) --}}
@extends('layouts.app', ['title' => 'Kanban Kandidat'])

@section('content')
<style>
/* ===== BASE ===== */
:root {
  --br-dark: #6b3f1f;
  --br-mid: #8b5e3c;
  --br-light: #a77d52;
  --br-pale: #c9a882;
  --br-bg: #f7f3ef;
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
.kn-header h1 { font-size: 1.5rem; font-weight: 700; letter-spacing: -.4px; }
.kn-header p  { font-size: .82rem; opacity: .85; margin-top: .3rem; }

/* ===== BOARD ===== */
.kn-board-wrap { overflow-x: auto; padding: 1rem 1.25rem 3rem; -webkit-overflow-scrolling: touch; }
.kn-board { display: flex; gap: .9rem; align-items: flex-start; min-width: max-content; }

/* ===== COLUMN ===== */
.kn-col {
  width: 272px;
  background: #fff;
  border-radius: 1rem;
  border: 1px solid #e2d9cf;
  display: flex; flex-direction: column;
  max-height: 80vh;
  box-shadow: 0 2px 14px rgba(107,63,31,.06);
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
  position: relative;
  transition: transform .15s, box-shadow .15s, border-color .2s;
}
.kn-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107,63,31,.13);
  border-color: #c9a882;
}
.kn-card-name  { font-weight: 700; font-size: .87rem; color: #3d1f08; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kn-card-job   { font-size: .72rem; color: #9a7558; margin-top: .1rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.kn-card-meta  { font-size: .7rem; color: #b89070; margin-top: .25rem; }

.kn-pill { display: inline-flex; align-items: center; gap: .25rem; font-size: .65rem; font-weight: 700; padding: .18rem .55rem; border-radius: 999px; letter-spacing: .2px; margin-top: .4rem; }
.pill-active { background: #fef3e2; color: #92580b; }
.pill-hired  { background: #e6f4ea; color: #256629; }
.pill-nq     { background: #fdeaea; color: #9b2525; }

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
  font-family: inherit; text-decoration: none; display: inline-flex; align-items: center;
}
.btn-outline  { background: #fff; border-color: #c9a882; color: #7a4f2a; }
.btn-outline:hover { background: #f7f0e8; border-color: #a77d52; }
.btn-primary  { background: linear-gradient(135deg,#a77d52,#7a4f2a); border-color: transparent; color: #fff; }
.btn-primary:hover { opacity: .9; }

/* ===== FEEDBACK INLINE CHIPS ===== */
.fb-row { margin-top: .5rem; display: flex; flex-direction: column; gap: .4rem; }
.fb-chip {
  display: flex; align-items: flex-start; gap: .55rem;
  padding: .6rem .75rem;
  background: #faf6f1; border: 1px solid #e8dfd4;
  border-radius: .65rem; font-size: .73rem;
}
.fb-icon {
  width: 28px; height: 28px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: .65rem; font-weight: 700; flex-shrink: 0;
}
.fb-hr   { background: #e8f0fe; color: #1a56db; }
.fb-user { background: #e6f4ea; color: #256629; }
.fb-employee { background: #fdeaea; color: #9b2525; }
.fb-tr   { background: #fef3e2; color: #92580b; }
.fb-label { font-size: .65rem; font-weight: 700; color: #9a7558; text-transform: uppercase; letter-spacing: .3px; }
.fb-note  { font-size: .78rem; color: #3d1f08; margin-top: .15rem; line-height: 1.4; }
.fb-badge { display: inline-flex; align-items: center; gap: .2rem; font-size: .65rem; font-weight: 700; margin-top: .25rem; padding: .15rem .45rem; border-radius: 999px; }
.fb-yes   { background: #e6f4ea; color: #256629; }
.fb-no    { background: #fdeaea; color: #9b2525; }

/* ===== FORM FEEDBACK INLINE ===== */
.fb-form { margin-top: .7rem; padding-top: .7rem; border-top: 1px solid #f0e9df; display: flex; flex-direction: column; gap: .55rem; }
.fm-label { font-size: .68rem; font-weight: 700; color: #7a4f2a; text-transform: uppercase; letter-spacing: .4px; display: block; margin-bottom: .2rem; }
.fm-ctrl {
  border: 1.5px solid #ddd3c4; border-radius: .55rem;
  padding: .45rem .7rem; font-size: .8rem; color: #3d1f08;
  background: #faf7f4; outline: none; width: 100%;
  font-family: inherit; transition: border-color .15s, box-shadow .15s;
}
.fm-ctrl:focus { border-color: var(--br-light); box-shadow: 0 0 0 3px rgba(167,125,82,.18); }
textarea.fm-ctrl { resize: vertical; min-height: 68px; }

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
  width: 100%; max-width: 480px;
  box-shadow: 0 24px 64px rgba(40,18,5,.35);
  overflow: hidden;
  animation: modalIn .18s ease;
}
@keyframes modalIn { from { opacity:0; transform:scale(.95) translateY(10px); } }
.kn-modal-head {
  padding: 1rem 1.4rem .85rem;
  border-bottom: 1px solid #ede6dc;
  background: linear-gradient(90deg,#faf6f1,#f4ece1);
  display: flex; align-items: flex-start; justify-content: space-between;
}
.kn-modal-title { font-size: .95rem; font-weight: 700; color: #4a2b0e; }
.kn-modal-sub   { font-size: .75rem; color: #9a7558; margin-top: .12rem; }
.kn-modal-close { background: none; border: none; cursor: pointer; font-size: 1.2rem; color: #9a7558; padding: .1rem .3rem; border-radius: .3rem; }
.kn-modal-close:hover { background: #f0e8df; color: #6b3f1f; }
.kn-modal-body   { padding: 1.1rem 1.4rem; }
.kn-modal-footer { padding: .85rem 1.4rem; border-top: 1px solid #ede6dc; background: #faf6f1; display: flex; justify-content: flex-end; gap: .6rem; }

/* ===== RIWAYAT ===== */
.riwayat-item {
  padding: .55rem .75rem;
  background: #fdf9f5; border: 1px solid #ede6dc;
  border-radius: .6rem; font-size: .75rem; color: #5a3e28;
  margin-bottom: .4rem;
}
.riwayat-item:last-child { margin-bottom: 0; }
.riwayat-stage { font-weight: 700; color: #8b5e3c; font-size: .68rem; text-transform: uppercase; letter-spacing: .4px; }

/* ===== TOAST ===== */
.kn-toast {
  position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 2000;
  background: #3d1f08; color: #fff;
  padding: .65rem 1.2rem; border-radius: .75rem;
  font-size: .82rem; font-weight: 600;
  box-shadow: 0 8px 24px rgba(40,18,5,.35);
  transition: all .3s;
}
.kn-toast.hidden { opacity: 0; transform: translateY(12px); pointer-events: none; }
.kn-toast.ok  { background: #1f6b35; }
.kn-toast.err { background: #9b2525; }

/* ===== EMPTY STATE ===== */
.kn-empty { text-align: center; padding: 1.5rem .5rem; color: #c0a080; font-size: .75rem; }
</style>

@php
  $stages = [
    'applied'         => 'Applied',
    'screening'       => 'Screening CV',
    'psychotest'      => 'Psikotest',
    'hr_iv'           => 'HR Interview',
    'user_trainer_iv' => 'User & Trainer Interview',
    'offer'           => 'OL',
    'mcu'             => 'MCU',
    'mobilisasi'      => 'Mobilisasi',
    'ground_test'     => 'Ground Test',
    'hired'           => 'Hired',
    'not_qualified'   => 'TIDAK lOLOS',
  ];

  $authUser   = auth()->user();
  $authRole   = $authUser->role ?? 'pelamar';
  $isSuperHR  = in_array($authRole, ['admin','hr','superadmin']);
  $isKaryawan = $authRole === 'karyawan';
  $isTrainer  = $authRole === 'trainer';

  // Stage-stage yg tampilkan form feedback langsung di kartu
  $feedbackStages = ['hr_iv', 'user_trainer_iv'];
@endphp

{{-- HEADER --}}
<div class="kn-header">
  <h1>Kanban proses saya</h1>
  <p>
    @if($isKaryawan || $isTrainer)
      Isi feedback di stage <strong>User & Trainer Interview</strong> untuk melanjutkan kandidat.
    @else
      Lihat progres lamaran kamu di setiap stage rekrutmen.
    @endif
  </p>
</div>

{{-- BOARD --}}
<div class="kn-board-wrap">
  <div class="kn-board">

    @foreach($stages as $stageKey => $stageLabel)
      @php $items = $grouped[$stageKey] ?? collect(); @endphp

      <div class="kn-col">
        <div class="kn-col-head">
          <span class="kn-col-title">{{ $stageLabel }}</span>
          <span class="kn-col-badge">{{ $items->count() }}</span>
        </div>
        <div class="kn-col-body">

          @forelse($items as $a)
            @php
              $fbHR       = $a->feedbacks->where('role','hr')->sortByDesc('created_at')->first();
              $fbUser     = $a->feedbacks->where('role','karyawan')->sortByDesc('created_at')->first();
              $fbTrainer  = $a->feedbacks->where('role','trainer')->sortByDesc('created_at')->first();
              $fbEmployee = $a->feedbacks->where('role','pelamar')->sortByDesc('created_at')->first();

              // Apakah user ini yang sedang login adalah si pelamar
              $isOwner = (string)$authUser->id === (string)$a->user_id;

              // Karyawan boleh isi feedback di user_trainer_iv HANYA jika belum ada feedback user
              $canKaryawanFeedback = $isKaryawan && $stageKey === 'user_trainer_iv' && !$fbUser;

              // Trainer boleh isi feedback di user_trainer_iv HANYA jika belum ada feedback trainer
              $canTrainerFeedback  = $isTrainer && $stageKey === 'user_trainer_iv' && !$fbTrainer;
            @endphp

            <div class="kn-card" data-id="{{ $a->id }}">

              {{-- INFO UTAMA --}}
              <div class="kn-card-name">{{ $a->job->title ?? '-' }}</div>
              <div class="kn-card-job">{{ $a->job->site->name ?? '-' }}</div>

              {{-- Tampilkan nama kandidat untuk karyawan/trainer --}}
              @if($isKaryawan || $isTrainer)
                <div class="kn-card-meta">{{ $a->user->name ?? '-' }} &middot; {{ $a->user->email ?? '-' }}</div>
              @endif

              <div class="kn-card-meta">Status: {{ $a->current_stage }}</div>

              {{-- PILL STATUS --}}
              @if($a->overall_status === 'hired')
                <span class="kn-pill pill-hired">✓ Hired</span>
              @elseif($a->overall_status === 'not_qualified')
                <span class="kn-pill pill-nq">✕ TIDAK lOLOS</span>
              @else
                <span class="kn-pill pill-active">● Active</span>
              @endif

              {{-- ====================================================
                   FEEDBACK CHIPS (tampil jika sudah ada data)
                   ==================================================== --}}
              @if($fbHR || $fbUser || $fbTrainer)
                <div class="fb-row">
                  @if($fbHR)
                    <div class="fb-chip">
                      <div class="fb-icon fb-hr">HR</div>
                      <div style="flex:1">
                        <div class="fb-label">Feedback HR</div>
                        <div class="fb-note">{{ $fbHR->feedback }}</div>
                        @if($fbHR->approve === 'yes')
                          <span class="fb-badge fb-yes">✓ Setuju</span>
                        @elseif($fbHR->approve === 'no')
                          <span class="fb-badge fb-no">✕ Tidak Setuju</span>
                        @endif
                      </div>
                    </div>
                  @endif
                  @if($fbUser)
                    <div class="fb-chip">
                      <div class="fb-icon fb-user">US</div>
                      <div style="flex:1">
                        <div class="fb-label">Feedback User</div>
                        <div class="fb-note">{{ $fbUser->feedback }}</div>
                        @if($fbUser->approve === 'yes')
                          <span class="fb-badge fb-yes">✓ Setuju</span>
                        @elseif($fbUser->approve === 'no')
                          <span class="fb-badge fb-no">✕ Tidak Setuju</span>
                        @endif
                      </div>
                    </div>
                  @endif
                  @if($fbEmployee)
                    <div class="fb-chip">
                      <div class="fb-icon fb-employee">PL</div>
                      <div style="flex:1">
                        <div class="fb-label">Feedback Pelamar</div>
                        <div class="fb-note">{{ $fbEmployee->feedback }}</div>
                        @if($fbEmployee->approve === 'yes')
                          <span class="fb-badge fb-yes">✓ Setuju</span>
                        @elseif($fbEmployee->approve === 'no')
                          <span class="fb-badge fb-no">✕ Tidak Setuju</span>
                        @endif
                      </div>
                    </div>
                  @endif
                  @if($fbTrainer)
                    <div class="fb-chip">
                      <div class="fb-icon fb-tr">TR</div>
                      <div style="flex:1">
                        <div class="fb-label">Feedback Trainer</div>
                        <div class="fb-note">{{ $fbTrainer->feedback }}</div>
                        @if($fbTrainer->approve === 'yes')
                          <span class="fb-badge fb-yes">✓ Setuju</span>
                        @elseif($fbTrainer->approve === 'no')
                          <span class="fb-badge fb-no">✕ Tidak Setuju</span>
                        @endif
                      </div>
                    </div>
                  @endif
                </div>
              @endif

              {{-- ====================================================
                   FORM FEEDBACK KARYAWAN (user_trainer_iv, jika belum diisi)
                   ==================================================== --}}
              @if($canKaryawanFeedback)
                <form method="POST"
                      action="{{ route('admin.applications.move', $a) }}"
                      class="fb-form">
                  @csrf
                  <input type="hidden" name="to" value="user_trainer_iv">
                  <div>
                    <label class="fm-label">Feedback User</label>
                    <textarea name="feedback_user" class="fm-ctrl" required
                              placeholder="Tulis penilaian kandidat..."></textarea>
                  </div>
                  <div>
                    <label class="fm-label">Rekomendasi / Setuju Lanjut?</label>
                    <select name="approve_user" class="fm-ctrl" required>
                      <option value="">— Pilih —</option>
                      <option value="yes">✓ Setuju</option>
                      <option value="no">✕ Tidak Setuju</option>
                    </select>
                  </div>
                  <button type="submit" class="btn-xs btn-primary" style="width:100%;justify-content:center">
                    Simpan Feedback
                  </button>
                </form>
              @endif

              {{-- ====================================================
                   FORM FEEDBACK TRAINER (user_trainer_iv, jika belum diisi)
                   ==================================================== --}}
              @if($canTrainerFeedback)
                <form method="POST"
                      action="{{ route('admin.applications.move', $a) }}"
                      class="fb-form">
                  @csrf
                  <input type="hidden" name="to" value="user_trainer_iv">
                  <div>
                    <label class="fm-label">Feedback Trainer</label>
                    <textarea name="feedback_trainer" class="fm-ctrl" required
                              placeholder="Tulis penilaian kandidat..."></textarea>
                  </div>
                  <div>
                    <label class="fm-label">Rekomendasi / Setuju Lanjut?</label>
                    <select name="approve_trainer" class="fm-ctrl" required>
                      <option value="">— Pilih —</option>
                      <option value="yes">✓ Setuju</option>
                      <option value="no">✕ Tidak Setuju</option>
                    </select>
                  </div>
                  <button type="submit" class="btn-xs btn-primary" style="width:100%;justify-content:center">
                    Simpan Feedback
                  </button>
                </form>
              @endif

              {{-- ====================================================
                   RIWAYAT STAGE (klik buka modal)
                   ==================================================== --}}
              @php
                $stageHistory = $a->stages->whereIn('stage_key', ['hr_iv','user_trainer_iv'])->values();
              @endphp

              {{-- ACTIONS --}}
              <div class="kn-card-actions">
                @if($stageHistory->count())
                  <button type="button" class="btn-xs btn-outline"
                    onclick="openRiwayat({{ $stageHistory->map(fn($s) => ['stage'=>$s->stage_key,'notes'=>($authRole === 'pelamar' ? '' : $s->notes),'actor'=>optional($s->actor)->name ?? 'System','created_at'=>$s->created_at?->format('d M Y H:i')])->toJson() }})">
                    Riwayat Interview
                  </button>
                @endif

                @if($a->interviews && $a->interviews->count())
                  <a href="{{ route('me.interviews.show', $a->interviews->first()) }}"
                     class="btn-xs btn-outline">
                    Lihat Jadwal
                  </a>
                @endif

                @if($a->offer)
                  <span class="btn-xs" style="background:#e6f4ea;border-color:#b0d9b5;color:#256629;cursor:default;">
                    ✓ Offering Letter
                  </span>
                @endif

                <a href="{{ route('jobs.show', $a->job) }}" target="_blank" class="btn-xs btn-outline">Job</a>
              </div>

            </div>{{-- /kn-card --}}
          @empty
            <div class="kn-empty">Belum ada lamaran di stage ini.</div>
          @endforelse

        </div>
      </div>
    @endforeach

  </div>
</div>


{{-- ============================= MODAL: RIWAYAT INTERVIEW ============================= --}}
<div class="hidden kn-overlay" id="overlay-riwayat">
  <div class="kn-modal">
    <div class="kn-modal-head">
      <div>
        <div class="kn-modal-title">Riwayat Interview</div>
        <div class="kn-modal-sub">Catatan per tahap interview yang sudah dilalui</div>
      </div>
      <button class="kn-modal-close" onclick="document.getElementById('overlay-riwayat').classList.add('hidden')">✕</button>
    </div>
    <div class="kn-modal-body" id="riwayat-body"></div>
    <div class="kn-modal-footer">
      <button class="btn-xs btn-outline" onclick="document.getElementById('overlay-riwayat').classList.add('hidden')">Tutup</button>
    </div>
  </div>
</div>

{{-- TOAST --}}
<div class="hidden kn-toast" id="kn-toast"></div>

@if(session('ok'))
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const t = document.getElementById('kn-toast');
      t.textContent = '{{ session('ok') }}';
      t.className = 'kn-toast ok';
      setTimeout(() => t.classList.add('hidden'), 3000);
    });
  </script>
@endif

@if(session('error') || $errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const t = document.getElementById('kn-toast');
      t.textContent = '{{ session('error') ?? $errors->first() }}';
      t.className = 'kn-toast err';
      setTimeout(() => t.classList.add('hidden'), 4000);
    });
  </script>
@endif

<script>
const stageLabels = {
  applied:'Applied', screening:'Screening CV', psychotest:'Psikotest',
  hr_iv:'HR Interview', user_trainer_iv:'User & Trainer Interview',
  offer:'OL', mcu:'MCU', mobilisasi:'Mobilisasi', ground_test:'Ground Test',
  hired:'Hired', not_qualified:'TIDAK lOLOS'
};

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

function openRiwayat(data) {
  const body = document.getElementById('riwayat-body');
  if (!data || !data.length) {
    body.innerHTML = '<p style="color:#aaa;font-size:.8rem;text-align:center">Belum ada riwayat.</p>';
  } else {
    body.innerHTML = data.map(item => `
      <div class="riwayat-item">
        <div class="riwayat-stage">${stageLabels[item.stage] || item.stage}</div>
        <div style="margin-top:.2rem">${item.notes || '<span style="color:#aaa">Tidak ada catatan</span>'}</div>
        <div style="margin-top:.25rem;font-size:.68rem;color:#9a7558">
          Oleh: ${item.actor} &middot; ${item.created_at}
        </div>
      </div>
    `).join('');
  }
  document.getElementById('overlay-riwayat').classList.remove('hidden');
}
</script>
@endsection