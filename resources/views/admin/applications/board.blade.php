{{-- resources/views/admin/applications/kanban.blade.php --}}
@extends('layouts.app', [ 'title' => 'Admin · Kanban Kandidat' ])

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
      'applied'          => 'Applied',
      'screening'        => 'Screening CV/Berkas Lamaran',
      'psychotest'       => 'Psikotest',
      'hr_iv'            => 'HR Interview',
      'user_iv'          => 'User Interview',
      'user_trainer_iv'  => 'User/Trainer Interview',
      'offer'            => 'OL',
      'mcu'              => 'MCU',
      'mobilisasi'       => 'Mobilisasi',
      'ground_test'      => 'Ground Test',
      'hired'            => 'Hired',
      'not_qualified'    => 'Not Lolos',
    ];

    // Warna header tiap kolom
    $stageColors = [
      'applied'          => 'from-blue-50 to-blue-100 text-blue-800',
      'screening'        => 'from-sky-50 to-sky-100 text-sky-800',
      'psychotest'       => 'from-indigo-50 to-indigo-100 text-indigo-800',
      'hr_iv'            => 'from-amber-50 to-amber-100 text-amber-800',
      'user_iv'          => 'from-emerald-50 to-emerald-100 text-emerald-800',
      'user_trainer_iv'  => 'from-lime-50 to-lime-100 text-lime-800',
      'offer'            => 'from-pink-50 to-pink-100 text-pink-800',
      'mcu'              => 'from-cyan-50 to-cyan-100 text-cyan-800',
      'mobilisasi'       => 'from-orange-50 to-orange-100 text-orange-800',
      'ground_test'      => 'from-purple-50 to-purple-100 text-purple-800',
      'hired'            => 'from-green-50 to-green-100 text-green-800',
      'not_qualified'    => 'from-slate-50 to-slate-100 text-slate-700',
    ];

    $badgeOverall = [
      'active'         => 'badge-blue',
      'hired'          => 'badge-green',
      'not_qualified'  => 'badge-amber',
    ];
  @endphp

  {{-- Header panel --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm mb-5">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>
    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Kanban Kandidat</h1>
          <p class="text-sm text-slate-600">
            Drag & drop kartu antar stage. Klik <b>Schedule</b> untuk kirim undangan interview (ICS).
          </p>
        </div>

        {{-- Filter ringkas --}}
        <form method="GET" class="glass rounded-xl p-3 shadow-sm grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3">
          <input name="q" value="{{ request('q') }}" placeholder="Cari nama / posisi..." class="input col-span-2 md:col-span-1"/>
          <select name="only" class="input">
            <option value="">Semua Stage</option>
            @foreach(array_keys($stages) as $key)
              <option value="{{ $key }}" @selected(request('only')===$key)>{{ strtoupper(str_replace('_',' ',$key)) }}</option>
            @endforeach
          </select>
          <button class="btn btn-primary">Filter</button>
        </form>
      </div>
    </div>
  </div>

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
            <div class="px-4 py-3 stage-header bg-gradient-to-r {{ $stageColors[$stageKey] ?? '' }} border-b border-slate-200 flex items-center justify-between">
              <div class="font-semibold tracking-wide">{{ strtoupper($stageLabel) }}</div>
              <span class="badge badge-blue" x-ref="count-{{ $stageKey }}">{{ $items->count() }}</span>
            </div>

            {{-- Body Stage --}}
            <div class="p-3 space-y-3 overflow-auto" x-ref="col-{{ $stageKey }}">
              @if($items->isEmpty())
                <div class="empty-{{ $stageKey }} rounded-xl border border-dashed border-slate-300/70 p-6 text-center text-slate-500 bg-white/70">
                  Belum ada kandidat di stage ini
                </div>
              @endif

              @foreach($items as $a)
                @php
                  $candidateName = $a->user->name ?? $a->candidate->name ?? $a->name ?? '—';
                  $jobTitle = $a->job->title ?? '—';
                  $last = $a->stages->sortByDesc('created_at')->first();
                  $overall = strtolower($a->overall_status ?? 'active');
                  $overallClass = $badgeOverall[$overall] ?? 'badge-blue';
                @endphp

                <article
                  id="card-{{ $a->id }}"
                  draggable="true"
                  @dragstart="onDragStart('{{ $a->id }}', '{{ route('admin.applications.move', $a) }}', '{{ csrf_token() }}') ; $el.classList.add('dragging')"
                  @dragend="$el.classList.remove('dragging')"
                  class="card card-hover bg-white"
                  data-move-url="{{ route('admin.applications.move', $a) }}"
                  data-schedule-url="{{ route('admin.interviews.store', $a) }}"
                  data-current-stage="{{ $a->current_stage ?? 'applied' }}"
                >
                  <div class="card-body py-3">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <div class="font-medium text-slate-900 truncate">{{ $candidateName }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ $jobTitle }}</div>
                      </div>
                      <div class="text-right shrink-0">
                        <div class="text-[11px] text-slate-400">{{ optional($a->created_at)->format('d M') }}</div>
                      </div>
                    </div>

                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                      @if($last && !is_null($last->score))
                        <span class="badge badge-green">Score {{ number_format($last->score,1) }}</span>
                      @endif
                      <span class="badge {{ $overallClass }}">{{ strtoupper(str_replace('_',' ',$overall)) }}</span>
                    </div>

                    <div class="mt-3 flex items-center justify-end gap-2">
                      @if(in_array($stageKey, ['hr_iv','user_iv','user_trainer_iv']))
                        <button
                          type="button"
                          class="btn btn-primary btn-sm"
                          @click="openSchedule($event)">
                          Schedule
                        </button>
                      @endif
                      <a class="btn btn-outline btn-sm" target="_blank" href="{{ route('jobs.show', $a->job) }}">Job</a>
                    </div>
                  </div>
                </article>
              @endforeach
            </div>
          </section>
        @endforeach
      </div>
    </div>

    {{-- Toast --}}
    <div x-show="toast.show" x-transition.opacity class="fixed bottom-4 right-4 z-50">
      <div class="rounded-lg shadow-lg px-4 py-3 text-sm"
           :class="toast.type==='ok' ? 'bg-green-600 text-white' : 'bg-rose-600 text-white'">
        <span x-text="toast.msg"></span>
      </div>
    </div>

    {{-- Modal Schedule Interview --}}
    <div x-show="modal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display:none">
      <div x-show="modal.open" x-transition.scale.origin.center class="card w-full max-w-xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between bg-white">
          <div>
            <div class="text-sm text-slate-500">Schedule Interview</div>
            <div class="font-semibold text-slate-900" x-text="modal.title"></div>
          </div>
          <button type="button" class="btn btn-ghost" @click="modal.open=false">Close</button>
        </div>
        <form method="POST" :action="modal.action" class="card-body grid gap-4" x-ref="form">@csrf
          <div class="grid md:grid-cols-2 gap-4">
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
          <div class="grid md:grid-cols-2 gap-4">
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

        init(){},

        showToast(msg, type='ok'){
          this.toast.msg = msg; this.toast.type = type; this.toast.show = true;
          setTimeout(()=> this.toast.show=false, 2200);
        },

        onDragStart(id, url, csrf){
          this.draggingId = id;
          this.moveUrl = url;
          this.csrf = csrf;
          this.draggingEl = document.getElementById('card-'+id);
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
