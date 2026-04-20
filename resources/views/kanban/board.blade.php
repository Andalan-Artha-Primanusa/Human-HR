@extends('layouts.app', ['title' => 'Kanban Kandidat'])

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

<section class="mb-5 overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
  <div class="relative">
    <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, #a77d52, #8b5e3c);"></div>
    <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, #8b5e3c, #a77d52);"></div>
  </div>
  <div class="p-6 md:p-7">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
      <div class="min-w-0">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Kanban Kandidat</h1>
        <p class="mt-1 text-xs sm:text-sm text-slate-600">Lihat progres seluruh kandidat dan jadwal interview di sini.</p>
      </div>
    </div>
  </div>
</section>

<div class="space-y-4">
  <div class="h-scroll">
    <div class="cols">
      @foreach($stages as $stageKey => $stageLabel)
        @php $items = $grouped[$stageKey] ?? collect(); @endphp
        <section class="card overflow-hidden min-h-[40vh] flex flex-col" data-stage="{{ $stageKey }}">
          <div class="flex items-center justify-between px-4 py-3 border-b stage-header border-slate-200" style="background: linear-gradient(90deg, #f5f1ed, #ede8e2); color: #8b5e3c; font-weight: 600;">
            <div class="tracking-wide">{{ strtoupper($stageLabel) }}</div>
            <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold border rounded-full" style="background-color: #8b5e3c; color: white;">{{ $items->count() }}</span>
          </div>
          <div class="p-3 space-y-3 overflow-auto">
            @forelse($items as $a)
              <article class="relative p-4 bg-white border card card-hover border-slate-200 rounded-xl">
                <div class="flex items-center justify-between gap-2">
                  <div class="font-semibold truncate text-slate-900">{{ $a->job->title ?? '-' }}</div>
                  <div class="text-xs truncate text-slate-500">{{ $a->job->site->name ?? '-' }}</div>
                </div>
                @if(isset($isKaryawanOrTrainer) && $isKaryawanOrTrainer)
                  <div class="mt-1 text-xs text-slate-700">Nama: {{ $a->user->name ?? '-' }}</div>
                  <div class="text-xs text-slate-500">Email: {{ $a->user->email ?? '-' }}</div>
                  <div class="text-xs text-slate-500">Role: {{ $a->user->role ?? '-' }}</div>
                @endif
                <div class="mt-2 text-xs text-slate-500">Status: {{ $a->current_stage }}</div>
                {{-- Form feedback karyawan --}}
                @if($stageKey === 'user_iv' && auth()->user()->role === 'karyawan' && empty($a->stages->where('stage_key','user_trainer_iv')->last()?->notes))
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
                @endif

                {{-- Form feedback trainer --}}
                @if($stageKey === 'user_trainer_iv' && auth()->user()->role === 'trainer' && empty($a->stages->where('stage_key','user_trainer_iv')->where('notes','!=',null)->last()?->notes) )
                                  {{-- Tombol pindah stage untuk admin/hr/superadmin hanya di user_iv dan user_trainer_iv --}}
                                  @if(in_array(auth()->user()->role, ['admin','hr','superadmin']) && in_array($stageKey, ['user_iv','user_trainer_iv']))
                                    <form method="POST" action="{{ route('admin.applications.move', $a) }}" class="mt-3 space-y-2">
                                      @csrf
                                      <input type="hidden" name="from_stage" value="{{ $stageKey }}">
                                      <label class="label">Pindah ke Stage:</label>
                                      <select name="to" class="input" required>
                                        @foreach($stages as $key => $label)
                                          @if($key !== $stageKey)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                          @endif
                                        @endforeach
                                      </select>
                                      <label class="label">Catatan (opsional):</label>
                                      <textarea name="note" class="input" placeholder="Catatan pindah stage..."></textarea>
                                      <button type="submit" class="w-full btn btn-primary btn-sm">Pindah Stage</button>
                                    </form>
                                  @endif
                  <form method="POST" action="{{ route('admin.applications.move', $a) }}" class="mt-3 space-y-2">
                    @csrf
                    <input type="hidden" name="to" value="user_trainer_iv">
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
                @endif

                {{-- Tampilkan feedback jika sudah ada --}}
                @if($a->feedback_hr || $a->approve_hr)
                  <div class="p-2 mt-2 text-xs rounded text-slate-700 bg-slate-50">
                    <b>Feedback HR:</b> {{ $a->feedback_hr ?? '-' }}<br>
                    <b>Setuju HR:</b> {{ $a->approve_hr === 'yes' ? 'Setuju' : ($a->approve_hr === 'no' ? 'Tidak Setuju' : '-') }}
                  </div>
                @endif
                @if(property_exists($a, 'feedback_user') && $a->feedback_user)
                  <div class="p-2 mt-2 text-xs rounded text-slate-700 bg-slate-50">
                    <b>Feedback Karyawan:</b> {{ $a->feedback_user }}<br>
                    <b>Setuju Karyawan:</b> {{ $a->approve_user === 'yes' ? 'Setuju' : ($a->approve_user === 'no' ? 'Tidak Setuju' : '-') }}
                  </div>
                @endif
                @if(property_exists($a, 'feedback_trainer') && $a->feedback_trainer)
                  <div class="p-2 mt-2 text-xs rounded text-slate-700 bg-slate-50">
                    <b>Feedback Trainer:</b> {{ $a->feedback_trainer }}<br>
                    <b>Setuju Trainer:</b> {{ $a->approve_trainer === 'yes' ? 'Setuju' : ($a->approve_trainer === 'no' ? 'Tidak Setuju' : '-') }}
                  </div>
                @endif
                {{-- Riwayat Feedback Interview --}}
                @php
                  $feedbackStages = $a->stages->whereIn('stage_key', ['hr_iv','user_iv','user_trainer_iv']);
                @endphp
                @if($feedbackStages->count())
                  <div class="p-2 mt-2 text-xs rounded bg-slate-100">
                    <b>Riwayat Feedback Interview:</b>
                    <ul class="mt-1 space-y-1">
                      @foreach($feedbackStages as $stage)
                        <li>
                          <span class="font-semibold">{{ strtoupper(str_replace('_iv',' Interview', $stage->stage_key)) }}</span>:
                          <span>{{ $stage->notes ?? '-' }}</span>
                          <span class="text-slate-500">({{ $stage->actor_name ?? '-' }})</span>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                @endif
                @if($a->interviews && count($a->interviews))
                  <div class="mt-2 text-xs text-blue-700">Interview: {{ $a->interviews->first()->start_at ?? '-' }}</div>
                @endif
                @if($a->offer)
                  <div class="mt-2 text-xs text-green-700">Ditawari: {{ $a->offer->created_at ?? '-' }}</div>
                @endif
              </article>
            @empty
              <div class="text-xs text-slate-400">Belum ada lamaran di stage ini.</div>
            @endforelse
          </div>
        </section>
      @endforeach
    </div>
  </div>
</div>
@endsection
