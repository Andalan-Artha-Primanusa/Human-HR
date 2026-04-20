{{-- resources/views/admin/applications/kanban.blade.php --}}
@extends('layouts.app', ['title' => 'Admin · Kanban Kandidat'])

@section('content')
      <style>
        .glass{backdrop-filter:blur(8px);background:rgba(255,255,255,.75)}
        .card-hover{transition:transform .15s, box-shadow .15s}
        .card-hover:hover{transform:translateY(-2px); box-shadow:0 6px 20px -10px rgba(0,0,0,.35)}
        .dragging{opacity:.6; transform:scale(.98)}
        .stage-header{position:sticky; top:0; z-index:10}
        .h-scroll{overflow-x:auto; -webkit-overflow-scrolling:touch}
        .cols{display:grid; grid-auto-flow:column; grid-auto-columns:minmax(280px, 1fr); gap:1rem}
      </style>

      @php
        // === Stage sesuai flow HO & Site (disatukan)
        $stages = [
            'applied' => 'Applied',
            'screening' => 'Screening CV/Berkas Lamaran',
            'psychotest' => 'Psikotest',
            'hr_iv' => 'HR Interview',
            'user_iv' => 'User Interview',
            'user_trainer_iv' => 'User/Trainer Interview',
            'offer' => 'OL',
            'mcu' => 'MCU',
            'mobilisasi' => 'Mobilisasi',
            'ground_test' => 'Ground Test',
            'hired' => 'Hired',
            'not_qualified' => 'Not Lolos',
        ];

        // Warna header tiap kolom (brown theme)
        $stageColor = 'linear-gradient(90deg, #f5f1ed, #ede8e2)';
        $stageTextColor = '#8b5e3c';

        $badgeOverall = [
            'active' => 'badge-blue',
            'hired' => 'badge-green',
            'not_qualified' => 'badge-amber',
        ];
      @endphp

      {{-- Header panel dengan tema brown seperti halaman admin lain --}}
      <section class="mb-5 overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
        <div class="relative">
          <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, #a77d52, #8b5e3c);"></div>
          <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, #8b5e3c, #a77d52);"></div>
        </div>

        <div class="p-6 md:p-7">
          <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Kanban Kandidat</h1>
              <p class="mt-1 text-xs sm:text-sm text-slate-600">Drag & drop kartu antar stage. Klik <b>Schedule</b> untuk kirim undangan interview (ICS).</p>
            </div>

            {{-- Filter form --}}
            <form method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,200px)_minmax(0,150px)_auto] md:items-end">
              <label class="sr-only" for="q">Cari</label>
              <input id="q" name="q" value="{{ request('q') }}" placeholder="Cari nama / posisi..." class="px-4 py-2 text-sm bg-white border rounded-lg border-slate-200 focus:outline-none focus:ring-2" style="--tw-ring-color: #a77d52"/>

              <label class="sr-only" for="only">Stage</label>
              <select id="only" name="only" class="px-4 py-2 text-sm bg-white border rounded-lg border-slate-200 focus:outline-none focus:ring-2" style="--tw-ring-color: #a77d52">
                <option value="">Semua Stage</option>
                @foreach(array_keys($stages) as $key)
                      <option value="{{ $key }}" @selected(request('only') === $key)>{{ strtoupper(str_replace('_', ' ', $key)) }}</option>
                @endforeach
              </select>

              <button type="submit" class="px-5 py-2 text-sm font-semibold text-white rounded-lg bg-[linear-gradient(90deg,_#a77d52,_#8b5e3c)] hover:brightness-105">Filter</button>
            </form>
          </div>
        </div>
      </section>

      {{-- Kanban --}}
      <div x-data="kanban()" x-init="init()" class="space-y-4">
        <div class="h-scroll">
          <div class="cols">
            @foreach($stages as $stageKey => $stageLabel)
                  @php
                    /** @var \Illuminate\Support\Collection $items */
                    $items = $grouped[$stageKey] ?? collect();
                  @endphp

                  <section
                    class="card overflow-hidden min-h-[60vh] flex flex-col"
                    @dragover.prevent
                    @drop="onDrop($event, '{{ $stageKey }}')"
                    data-stage="{{ $stageKey }}"
                  >
                    {{-- Header Stage --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b stage-header border-slate-200" style="background: {{ $stageColor }}; color: {{ $stageTextColor }}; font-weight: 600;">
                      <div class="tracking-wide">{{ strtoupper($stageLabel) }}</div>
                      <span x-ref="count-{{ $stageKey }}" class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold border rounded-full" style="background-color: {{ $stageTextColor }}; color: white;">{{ $items->count() }}</span>
                    </div>

                    {{-- Body Stage --}}
                    <div class="p-3 space-y-3 overflow-auto" x-ref="col-{{ $stageKey }}">

                      @foreach($items as $a)
                        <article id="card-{{ $a->id }}"
                          class="relative p-4 bg-white border card card-hover border-slate-200 rounded-xl @if($stageKey==='hr_iv' && empty($a->feedback_hr)) opacity-50 cursor-not-allowed @endif"
                          draggable="@if($stageKey==='hr_iv' && empty($a->feedback_hr))false @else true @endif"
                          data-current-stage="{{ $stageKey }}"
                          data-schedule-url="{{ route('admin.interviews.store', $a) }}"
                          data-move-url="{{ route('admin.applications.board.move') }}?id={{ $a->id }}"
                          @if($stageKey==='hr_iv' && empty($a->feedback_hr))
                            title="Isi feedback HR dulu sebelum bisa drag & drop ke stage berikutnya"
                          @else
                            @dragstart="onDragStart('{{ $a->id }}', '{{ route('admin.applications.board.move') }}?id={{ $a->id }}', '{{ csrf_token() }}')"
                          @endif
                        >
                          <div class="flex items-center justify-between gap-2">
                            <div class="font-semibold truncate text-slate-900">{{ $a->user->name }}</div>
                            <div class="text-xs truncate text-slate-500">{{ $a->job->title }}</div>
                          </div>
                          <div class="mt-2 text-xs text-slate-500">{{ $a->user->email }}</div>
                          <div class="mt-2 text-xs text-slate-500">{{ $a->user->role }}</div>
                          <div class="mt-2 text-xs text-slate-500">Status: {{ $a->status }}</div>

                          {{-- === FEEDBACK HISTORY TABLE (DISABLED, hanya pakai tombol pop-up) === --}}
                          {{-- <div class="mt-2">
                            ... (tabel feedback disembunyikan)
                          </div> --}}

                          {{-- === FEEDBACK/APPROVAL FORM === --}}
                          <div class="flex flex-wrap gap-2 mt-2">
                            @php
                              $fb_hr = $a->feedbacks->where('role','hr')->sortByDesc('created_at')->first();
                              $fb_user = $a->feedbacks->where('role','karyawan')->sortByDesc('created_at')->first();
                              if (!$fb_user) $fb_user = $a->feedbacks->where('role','pelamar')->sortByDesc('created_at')->first();
                              $fb_trainer = $a->feedbacks->where('role','trainer')->sortByDesc('created_at')->first();
                            @endphp
                            @if($fb_hr)
                              <button type="button" class="btn btn-outline btn-xs" @click="$dispatch('show-feedback', {role: 'hr', notes: {{ json_encode($fb_hr->feedback) }}, approve: {{ json_encode($fb_hr->approve) }}})">View Feedback HR</button>
                            @endif
                            @if($fb_user)
                              <button type="button" class="btn btn-outline btn-xs" @click="$dispatch('show-feedback', {role: 'user', notes: {{ json_encode($fb_user->feedback) }}, approve: {{ json_encode($fb_user->approve) }}})">View Feedback Karyawan</button>
                            @endif
                            @if($fb_trainer)
                              <button type="button" class="btn btn-outline btn-xs" @click="$dispatch('show-feedback', {role: 'trainer', notes: {{ json_encode($fb_trainer->feedback) }}, approve: {{ json_encode($fb_trainer->approve) }}})">View Feedback Trainer</button>
                            @endif
                          </div>
                          @if($stageKey === 'hr_iv')
                            <form method="POST" action="{{ route('admin.applications.move', $a) }}" class="mt-3 space-y-2">
                              @csrf
                              <input type="hidden" name="to" value="user_iv">
                              <div>
                                <label class="label">Feedback HR</label>
                                <textarea name="feedback_hr" class="input" required placeholder="Catatan/feedback HR..."></textarea>
                              </div>
                              <div>
                                <label class="label">Setuju Lanjut?</label>
                                <select name="approve_hr" class="input" required>
                                  <option value="">Pilih</option>
                                  <option value="yes">Setuju</option>
                                  <option value="no">Tidak Setuju</option>
                                </select>
                              </div>
                              <button type="submit" class="w-full btn btn-primary btn-sm">Submit Feedback</button>
                            </form>
                          @elseif($stageKey === 'user_iv')
                            @php $currentUser = auth()->user(); @endphp
                            @if($currentUser && $currentUser->role === 'pelamar' && $a->user_id === $currentUser->id)
                              <form method="POST" action="{{ route('admin.applications.move', $a) }}" class="mt-3 space-y-2">
                                @csrf
                                <input type="hidden" name="to" value="user_trainer_iv">
                                <div>
                                  <label class="label">Feedback Karyawan</label>
                                  <textarea name="feedback_user" class="input" required placeholder="Catatan/feedback karyawan..."></textarea>
                                </div>
                                <div>
                                  <label class="label">Setuju Lanjut?</label>
                                  <select name="approve_user" class="input" required>
                                    <option value="">Pilih</option>
                                    <option value="yes">Setuju</option>
                                    <option value="no">Tidak Setuju</option>
                                  </select>
                                </div>
                                <button type="submit" class="w-full btn btn-primary btn-sm">Submit Feedback</button>
                              </form>
                            @else
                              <div class="flex flex-wrap gap-2 mt-3">
                                <button type="button" class="btn btn-outline btn-sm" @click="showFeedback('user', {{ json_encode($a->feedback_user) }}, {{ json_encode($a->approve_user) }})">View Feedback Karyawan</button>
                                @php
                                  $fb_hr = $a->feedbacks->where('role','hr')->sortByDesc('created_at')->first();
                                @endphp
                                @if($fb_hr)
                                  <button type="button" class="btn btn-outline btn-sm" @click="showFeedback('hr', {{ json_encode($fb_hr->feedback) }}, {{ json_encode($fb_hr->approve) }})">View Feedback HR</button>
                                @endif
                              </div>
                            @endif
                            {{-- Tidak ada dropdown pindah stage untuk admin/hr/superadmin, drag & drop saja --}}
                          @elseif($stageKey === 'user_trainer_iv')
                            @php $currentUser = auth()->user(); @endphp
                            @if($currentUser && $currentUser->role === 'trainer')
                              <form method="POST" action="{{ route('admin.applications.move', $a) }}" class="mt-3 space-y-2">
                                @csrf
                                <input type="hidden" name="to" value="final">
                                <div>
                                  <label class="label">Feedback Trainer</label>
                                  <textarea name="feedback_trainer" class="input" required placeholder="Catatan/feedback trainer..."></textarea>
                                </div>
                                <div>
                                  <label class="label">Setuju Lanjut?</label>
                                  <select name="approve_trainer" class="input" required>
                                    <option value="">Pilih</option>
                                    <option value="yes">Setuju</option>
                                    <option value="no">Tidak Setuju</option>
                                  </select>
                                </div>
                                <button type="submit" class="w-full btn btn-primary btn-sm">Submit Feedback</button>
                              </form>
                            @else
                              <button type="button" class="mt-3 btn btn-outline btn-sm" @click="showFeedback('trainer', {{ json_encode($a->feedback_trainer) }}, {{ json_encode($a->approve_trainer) }})">View Feedback Trainer</button>
                              <button type="button" class="mt-3 btn btn-outline btn-sm" @click="showFeedback('user', {{ json_encode($a->feedback_user) }}, {{ json_encode($a->approve_user) }})">View Feedback Karyawan</button>
                              <button type="button" class="mt-3 btn btn-outline btn-sm" @click="showFeedback('hr', {{ json_encode($a->feedback_hr) }}, {{ json_encode($a->approve_hr) }})">View Feedback HR</button>
                            @endif
                            {{-- Tidak ada dropdown pindah stage untuk admin/hr/superadmin, drag & drop saja --}}
                                <div x-show="feedbackModal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" style="display:none"
                                     @show-feedback.window="showFeedback($event.detail.role, $event.detail.notes, $event.detail.approve)">
                                  <div x-show="feedbackModal.open" x-transition.scale.origin.center class="w-full max-w-md card">
                                    <div class="flex items-center justify-between px-5 py-4 bg-white border-b border-slate-200">
                                      <div class="font-semibold text-slate-900">Feedback <span x-text="feedbackModal.roleLabel"></span></div>
                                      <button type="button" class="btn btn-ghost" @click="feedbackModal.open=false">Close</button>
                                    </div>
                                    <div class="p-5">
                                      <div class="mb-2"><b>Catatan:</b> <span x-text="feedbackModal.notes"></span></div>
                                      <div><b>Setuju Lanjut:</b> <span x-text="feedbackModal.approve"></span></div>
                                    </div>
                                  </div>
                                </div>
                          @endif

                          <div class="flex items-center justify-end gap-2 mt-3">
                            @if(in_array($stageKey, ['hr_iv', 'user_iv', 'user_trainer_iv']))
                              <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                @click="openSchedule($event)">
                                Schedule
                              </button>
                            @endif
                            <a class="btn btn-outline btn-sm" target="_blank" href="{{ route('jobs.show', $a->job) }}">Job</a>
                          </div>
                        </article>
                      @endforeach
                    </div>
                  </section>
            @endforeach
          </div>
        </div>

        {{-- Toast --}}
        <div x-show="toast.show" x-transition.opacity class="fixed z-50 bottom-4 right-4">
          <div class="px-4 py-3 text-sm rounded-lg shadow-lg"
               :class="toast.type==='ok' ? 'bg-green-600 text-white' : 'bg-rose-600 text-white'">
            <span x-text="toast.msg"></span>
          </div>
        </div>

        {{-- Modal Schedule Interview --}}
        <div x-show="modal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" style="display:none">
          <div x-show="modal.open" x-transition.scale.origin.center class="w-full max-w-xl card">
            <div class="flex items-center justify-between px-5 py-4 bg-white border-b border-slate-200">
              <div>
                <div class="text-sm text-slate-500">Schedule Interview</div>
                <div class="font-semibold text-slate-900" x-text="modal.title"></div>
              </div>
              <button type="button" class="btn btn-ghost" @click="modal.open=false">Close</button>
            </div>
            <form method="POST" :action="modal.action" class="grid gap-4 card-body" x-ref="form">@csrf
              <div class="grid gap-4 md:grid-cols-2">
                <div>
                  <label class="label">Title</label>
                  <input class="input" name="title" :value="`Interview - ${modal.candidate}`" required>
                </div>
                <div>
                  <label class="label">Mode</label>
                  <select class="input" name="mode" x-model="modal.mode">
                    <option value="online">Online</option>
                    <option value="onsite">Onsite</option>
                  </select>
                </div>
              </div>
              <template x-if="modal.mode==='online'">
                <div>
                  <label class="label">Meeting Link</label>
                  <input class="input" name="meeting_link" placeholder="https://meet.google.com/...">
                </div>
              </template>
              <template x-if="modal.mode==='onsite'">
                <div>
                  <label class="label">Location</label>
                  <input class="input" name="location" placeholder="R. Interview / Alamat kantor">
                </div>
              </template>
              <div class="grid gap-4 md:grid-cols-2">
                <div>
                  <label class="label">Start</label>
                  <input class="input" type="datetime-local" name="start_at" required>
                </div>
                <div>
                  <label class="label">End</label>
                  <input class="input" type="datetime-local" name="end_at" required>
                </div>
              </div>
              <div>
                <label class="label">Notes</label>
                <textarea class="input" name="notes" placeholder="Bring portfolio / on-time 10 min earlier"></textarea>
              </div>
              <div class="flex items-center justify-end gap-3">
                <button type="button" class="btn btn-ghost" @click="modal.open=false">Cancel</button>
                <button class="btn btn-primary">Send Invite</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <script>
        function kanban(){
          return {
            draggingId: null,
            draggingEl: null,
            draggingFrom: null,
            moveUrl: null,
            csrf: null,
            toast: { show:false, msg:'', type:'ok' },
            modal: { open:false, action:'', candidate:'', title:'', mode:'online' },
            feedbackModal: { open:false, roleLabel:'', notes:'', approve:'' },

            init(){},

            showToast(msg, type='ok'){
              this.toast.msg = msg; this.toast.type = type; this.toast.show = true;
              setTimeout(()=> this.toast.show=false, 2200);
            },

            showFeedback(role, notes, approve) {
              let label = '';
              if(role==='user') label = 'Karyawan';
              else if(role==='hr') label = 'HR';
              else if(role==='trainer') label = 'Trainer';
              this.feedbackModal.roleLabel = label;
              this.feedbackModal.notes = notes || '-';
              this.feedbackModal.approve = approve === 'yes' ? 'Ya' : (approve === 'no' ? 'Tidak' : '-');
              this.feedbackModal.open = true;
            },

            onDragStart(id, url, csrf){
              this.draggingId = id;
              this.draggingEl = document.getElementById('card-'+id);
              this.moveUrl = this.draggingEl?.dataset?.moveUrl || url;
              this.csrf = csrf;
              this.draggingFrom = this.draggingEl?.closest('section')?.dataset?.stage ?? null;
            },

            onDrop(e, toStage){
              if(!this.draggingId || !this.moveUrl) return;
              const card = this.draggingEl;
              const fromStage = this.draggingFrom;
              const targetCol = e.currentTarget.querySelector('[x-ref^="col-"]');
              const empty = e.currentTarget.querySelector('.empty-'+toStage);
              // Optimistic move
              if (empty) empty.remove?.();
              targetCol?.prepend(card);
              this.updateCounters(fromStage, toStage);

              fetch(this.moveUrl, {
                method: 'POST',
                headers: {
                  'X-CSRF-TOKEN': this.csrf,
                  'Accept':'application/json',
                  'Content-Type':'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({ to_stage: toStage })
              })
              .then(async (res) => {
                if(!res.ok){ throw new Error((await res.json())?.message || 'Move failed'); }
                this.showToast('Dipindahkan ke '+toStage.toUpperCase(), 'ok');
                card.dataset.currentStage = toStage;

                // ======= REFRESH OTOMATIS AGAR STATE SINKRON =======
                requestAnimationFrame(() => location.reload());
              })
              .catch(err => {
                // rollback
                const fromCol = document.querySelector(`[data-stage="${fromStage}"] [x-ref^="col-"]`);
                fromCol?.prepend(card);
                this.updateCounters(toStage, fromStage);
                this.showToast(err.message || 'Gagal memindahkan', 'err');
              })
              .finally(()=>{
                this.draggingId = null; this.draggingEl = null; this.moveUrl = null; this.draggingFrom = null;
              });
            },

            updateCounters(from, to){
              if(from){
                const fromBadge = this.$refs['count-'+from]; if(fromBadge){ fromBadge.textContent = (+fromBadge.textContent - 1); }
              }
              if(to){
                const toBadge = this.$refs['count-'+to]; if(toBadge){ toBadge.textContent = (+toBadge.textContent + 1); }
              }
            },

            openSchedule(ev){
              const card = ev.currentTarget.closest('article');
              const scheduleUrl = card?.dataset?.scheduleUrl;
              const title = card?.querySelector('.text-slate-900')?.textContent?.trim() ?? 'Candidate';
              const job   = card?.querySelector('.text-xs.text-slate-500')?.textContent?.trim() ?? 'Job';
              this.modal.open   = true;
              this.modal.candidate = title;
              this.modal.title  = `${title} — ${job}`;
              this.modal.action = scheduleUrl || '#';
              this.modal.mode   = 'online';
            },
          }
        }
      </script>
@endsection
