@extends('layouts.app', ['title' => 'Applications'])

@section('content')
  {{-- Header panel ala bar biru–merah --}}
  <div class="relative rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 90%"></div>
        <div class="h-full bg-red-500"  style="width: 10%"></div>
      </div>
    </div>
    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Applications</h1>
          <p class="text-slate-600">Daftar semua kandidat & status proses rekrutmen.</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-primary self-start md:self-auto">
          Cari Lowongan
        </a>
      </div>
    </div>
  </div>

  {{-- Filter Bar --}}
  <form method="GET" class="card mb-4">
    <div class="card-body grid gap-3 md:grid-cols-4">
      <div>
        <label class="label">Cari</label>
        <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Nama kandidat / posisi / site">
      </div>

      <div>
        <label class="label">Stage</label>
        @php
          // konsisten dengan controller & kanban
          $stageOptions = [
            '' => 'Semua',
            'applied'       => 'Applied',
            'psychotest'    => 'Psychotest',
            'hr_iv'         => 'HR Interview',
            'user_iv'       => 'User Interview',
            'final'         => 'Final',
            'offer'         => 'Offer',
            'hired'         => 'Hired',
            'not_qualified' => 'Not Qualified',
          ];
        @endphp
        <select name="stage" class="select">
          @foreach($stageOptions as $k => $v)
            <option value="{{ $k }}" @selected(request('stage')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="label">Site</label>
        @php $siteVal = request('site'); @endphp
        @if(!empty($sites ?? null) && is_iterable($sites))
          <select name="site" class="select">
            <option value="">Semua Site</option>
            @foreach($sites as $code => $name)
              <option value="{{ $code }}" @selected($siteVal===$code)>{{ $code }} — {{ $name }}</option>
            @endforeach
          </select>
        @else
          <input type="text" name="site" value="{{ $siteVal }}" class="input" placeholder="DBK / POS / SBS">
        @endif
      </div>

      <div class="flex items-end gap-2">
        <button class="btn btn-primary w-full">Filter</button>
        @if(request()->hasAny(['q','stage','site']))
          <a href="{{ route('admin.applications.index') }}" class="btn btn-ghost">Reset</a>
        @endif
      </div>
    </div>
  </form>

  {{-- Tabel --}}
  <div class="card">
    <div class="card-body overflow-x-auto">
      @if($apps->count())
        <table class="table min-w-[900px]">
          <thead>
            <tr>
              <th class="th">Kandidat</th>
              <th class="th">Posisi</th>
              <th class="th">Divisi</th>
              <th class="th">Site</th>
              <th class="th">Stage</th>
              <th class="th">Overall</th>
              <th class="th">Dibuat</th>
              <th class="th text-right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($apps as $app)
              @php
                $stage = $app->current_stage ?? 'applied';
                // badge stage warna
                $stageBadge = match($stage) {
                  'applied'       => 'badge-blue',
                  'psychotest'    => 'badge-indigo',
                  'hr_iv'         => 'badge-amber',
                  'user_iv'       => 'badge-emerald',
                  'final'         => 'badge-purple',
                  'offer'         => 'badge-pink',
                  'hired'         => 'badge-green',
                  'not_qualified' => 'badge-rose',
                  default => 'badge-slate'
                };
                // candidate name fallback
                $candidate = $app->candidate->name
                  ?? ($app->user->name ?? ($app->name ?? '—'));
                // overall_status badge
                $overall = strtolower($app->overall_status ?? 'active');
                $overallBadge = match($overall) {
                  'hired'          => 'badge-green',
                  'not_qualified'  => 'badge-rose',
                  'inactive'       => 'badge-slate',
                  default          => 'badge-blue',
                };
              @endphp
              <tr>
                <td class="td font-medium text-slate-900">{{ $candidate }}</td>
                <td class="td">{{ $app->job->title ?? '—' }}</td>
                <td class="td">{{ $app->job->division ?? '—' }}</td>
                <td class="td">{{ $app->job->site->code ?? $app->job->site_code ?? '—' }}</td>
                <td class="td">
                  <span class="badge {{ $stageBadge }}">{{ strtoupper($stage) }}</span>
                </td>
                <td class="td">
                  <span class="badge {{ $overallBadge }}">{{ strtoupper($overall) }}</span>
                </td>
                <td class="td">{{ optional($app->created_at)->format('d M Y') }}</td>
                <td class="td text-right">
                  <div class="inline-flex items-center gap-2">
                    <a class="btn btn-outline" target="_blank" href="{{ route('jobs.show', $app->job ?? 0) }}">Detail</a>

                    {{-- Quick Move Stage (konsisten dengan allowed stages) --}}
                    <form action="{{ route('admin.applications.move', $app) }}" method="POST" class="inline-flex items-center gap-2">
                      @csrf
                      <select name="to" class="select select-sm">
                        @foreach(['applied','psychotest','hr_iv','user_iv','final','offer','hired','not_qualified'] as $opt)
                          <option value="{{ $opt }}" @selected($opt===$stage)>{{ ucwords(str_replace('_',' ',$opt)) }}</option>
                        @endforeach
                      </select>
                      <button class="btn btn-primary btn-sm">Pindah</button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        {{-- Empty State --}}
        <div class="py-16 grid place-content-center text-center">
          <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/>
            </svg>
          </div>
          <div class="text-slate-600">Belum ada data aplikasi.</div>
        </div>
      @endif
    </div>
  </div>

  <div class="mt-6">
    {{ method_exists($apps, 'links') ? $apps->withQueryString()->links() : '' }}
  </div>
@endsection
