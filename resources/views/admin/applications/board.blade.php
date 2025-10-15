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
    // ==== DEFINISI STAGE (URUTAN TUNGGAL) ====
    $stages = [
      'applied'       => 'Applied',
      'psychotest'    => 'Psychotest',
      'hr_iv'         => 'HR Interview',
      'user_iv'       => 'User Interview',
      'final'         => 'Final',
      'offer'         => 'Offer',
      'hired'         => 'Hired',
      'not_qualified' => 'Not Qualified',
    ];

    // Warna header per stage
    $stageColors = [
      'applied'       => 'from-blue-50 to-blue-100 text-blue-800',
      'psychotest'    => 'from-indigo-50 to-indigo-100 text-indigo-800',
      'hr_iv'         => 'from-amber-50 to-amber-100 text-amber-800',
      'user_iv'       => 'from-emerald-50 to-emerald-100 text-emerald-800',
      'final'         => 'from-purple-50 to-purple-100 text-purple-800',
      'offer'         => 'from-pink-50 to-pink-100 text-pink-800',
      'hired'         => 'from-green-50 to-green-100 text-green-800',
      'not_qualified' => 'from-slate-50 to-slate-100 text-slate-700',
    ];

    // Badge untuk overall_status
    $badgeOverall = [
      'active'         => 'badge-blue',
      'hired'          => 'badge-green',
      'not_qualified'  => 'badge-amber',
    ];
  @endphp

  {{-- Header panel ala bar biru–merah --}}
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
          <p class="text-sm text-slate-600">Drag & drop kartu antar stage. Klik <b>Schedule</b> untuk kirim undangan interview (ICS).</p>
        </div>

        {{-- Filter ringkas --}}
        <form method="GET" class="glass rounded-xl p-3 shadow-sm grid grid-cols-2 md:grid-cols-3 gap-2 md:gap-3">
          <input name="q" value="{{ request('q') }}" placeholder="Cari nama / posisi..." class="input col-span-2 md:col-span-1"/>
          <select name="only" class="input">
            <option value="">Semua Stage</option>
            @foreach(array_keys($stages) as $key)
              <option value="{{ $key }}" @selected(request('only')===$key)>{{ strtoupper($key) }}</option>
            @endforeach
          </select>
          <button class="btn btn-primary">Filter</button>
        </form>
      </div>
    </div>
  </div>

  {{-- ====== SINGLE STRIP KANBAN ====== --}}
  <div x-data="kanban()" x-init="init()" class="space-y-4">
    <div class="h-scroll">
      <div class="cols">
        @foreach($stages as $stageKey => $stageLabel)
          @php
            // $grouped diharapkan: Collection keyed by stageKey => collection of JobApplication
            $items = $grouped[$stageKey] ?? collect();
          @endphp

          <section
            class="card overflow-hidden min-h-[60vh] flex flex-col"
            @dragover.prevent
            @drop="onDrop($event, '{{ $stageKey }}')"
          >
            {{-- Header Stage --}}
            <div class="px-4 py-3 stage-header bg-gradient-to-r {{ $stageColors[$stageKey] ?? '' }} border-b border-slate-200 flex items-center justify-between">
              <div class="font-semibold tracking-wide">{{ strtoupper($stageLabel) }}</div>
              <span class="badge badge-blue">{{ $items->count() }}</span>
            </div>

            {{-- Body Stage --}}
            <div class="p-3 space-y-3 overflow-auto">
              @if($items->isEmpty())
                <div class="rounded-xl border border-dashed border-slate-300/70 p-6 text-center text-slate-500 bg-white/70">
                  Belum ada kandidat di stage ini
                </div>
              @endif

              @foreach($items as $a)
                <article
                  id="card-{{ $a->id }}"
                  draggable="true"
                  @dragstart="onDragStart('{{ $a->id }}'); $el.classList.add('dragging')"
                  @dragend="$el.classList.remove('dragging')"
                  class="card card-hover bg-white"
                >
                  <div class="card-body py-3">
                    <div class="flex items-start justify-between gap-3">
                      <div class="min-w-0">
                        <div class="font-medium text-slate-900 truncate">{{ $a->user->name }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ $a->job->title }}</div>
                      </div>
                      <div class="text-right shrink-0">
                        <div class="text-[11px] text-slate-400">{{ $a->created_at->format('d M') }}</div>
                      </div>
                    </div>

                    <div class="mt-2 flex items-center gap-2 flex-wrap">
                      @php $last = $a->stages->sortByDesc('created_at')->first(); @endphp
                      @if($last && !is_null($last->score))
                        <span class="badge badge-green">Score {{ number_format($last->score,1) }}</span>
                      @endif
                      <span class="badge {{ $badgeOverall[$a->overall_status] ?? 'badge-blue' }}">
                        {{ strtoupper($a->overall_status ?? 'active') }}
                      </span>
                    </div>

                    <div class="mt-3 flex items-center justify-end gap-2">
                      @if(in_array($stageKey, ['hr_iv','user_iv']))
                        <button
                          class="btn btn-primary"
                          @click="openSchedule({ id: '{{ $a->id }}', name: '{{ addslashes($a->user->name) }}', job: '{{ addslashes($a->job->title) }}' })">
                          Schedule
                        </button>
                      @endif
                      <a class="btn btn-outline" target="_blank" href="{{ route('jobs.show', $a->job) }}">Job</a>
                    </div>
                  </div>
                </article>
              @endforeach
            </div>
          </section>
        @endforeach
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
          <button class="btn btn-ghost" @click="modal.open=false">Close</button>
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
        modal: { open:false, action:'', candidate:'', title:'', mode:'online' },

        init(){},

        onDragStart(id){ this.draggingId = id; },

        onDrop(e, toStage){
          if(!this.draggingId) return;
          const id = this.draggingId; this.draggingId = null;

          fetch(`/admin/applications/${id}/move`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept':'application/json' },
            body: new URLSearchParams({ to_stage: toStage })
          }).then(()=>{ location.reload(); });
        },

        openSchedule({id, name, job}){
          this.modal.open   = true;
          this.modal.candidate = name;
          this.modal.title  = `${name} — ${job}`;
          this.modal.action = `/admin/interviews/${id}`;
          this.modal.mode   = 'online';
        },
      }
    }
  </script>
@endsection
