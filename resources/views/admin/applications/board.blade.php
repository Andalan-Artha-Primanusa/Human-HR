@extends('layouts.karir', [ 'title' => 'Admin · Kanban Kandidat' ])

@section('content')
  <div x-data="kanban()" x-init="init()" class="space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-slate-900">Kanban Kandidat</h1>
      <div class="text-sm text-slate-600">Drag & drop kartu kandidat antar stage. Klik "Schedule" untuk kirim undangan interview (ICS).</div>
    </div>

    <div class="grid gap-4 md:grid-cols-4 xl:grid-cols-8">
      @foreach($stages as $stage)
        @php $items = $grouped[$stage]; @endphp
        <section class="card min-h-[60vh] overflow-hidden" @dragover.prevent @drop="onDrop($event, '{{ $stage }}')">
          <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between bg-white">
            <div class="font-semibold">{{ strtoupper($stage) }}</div>
            <span class="badge badge-blue">{{ $items->count() }}</span>
          </div>
          <div class="p-3 space-y-3">
            @foreach($items as $a)
              <article draggable="true" @dragstart="onDragStart('{{ $a->id }}')" id="card-{{ $a->id }}" class="card">
                <div class="card-body py-3">
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <div class="font-medium text-slate-900">{{ $a->user->name }}</div>
                      <div class="text-xs text-slate-500">{{ $a->job->title }}</div>
                    </div>
                    <div class="text-right">
                      <div class="text-xs text-slate-400">{{ $a->created_at->format('d M') }}</div>
                    </div>
                  </div>
                  <div class="mt-2 flex items-center gap-2">
                    @php $last = $a->stages->sortByDesc('created_at')->first(); @endphp
                    @if($last && !is_null($last->score))
                      <span class="badge badge-green">Score {{ number_format($last->score,1) }}</span>
                    @endif
                    <span class="badge {{ $a->overall_status==='active' ? 'badge-blue' : 'badge-amber' }}">{{ strtoupper($a->overall_status) }}</span>
                  </div>
                  <div class="mt-3 flex items-center justify-end gap-2">
                    @if(in_array($stage, ['hr_iv','user_iv']))
                      <button class="btn btn-primary" @click="openSchedule({ id: '{{ $a->id }}', name: '{{ addslashes($a->user->name) }}', job: '{{ addslashes($a->job->title) }}' })">Schedule</button>
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

    <!-- Modal Schedule Interview -->
    <div x-show="modal.open" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4" style="display:none">
      <div class="card w-full max-w-xl">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
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
          this.modal.open = true;
          this.modal.candidate = name;
          this.modal.title = `${name} — ${job}`;
          this.modal.action = `/admin/interviews/${id}`;
          this.modal.mode = 'online';
        },
      }
    }
  </script>
@endsection
