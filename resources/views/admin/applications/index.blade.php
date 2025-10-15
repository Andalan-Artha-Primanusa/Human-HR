@extends('layouts.app', ['title' => 'Applications'])

@section('content')
  <div class="mb-4">
    <h1 class="text-2xl font-semibold text-slate-900">Applications</h1>
    <p class="text-slate-600">Daftar semua kandidat & status proses rekrutmen.</p>
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
          $stages = ['' => 'Semua', 'applied'=>'Applied', 'psychotest'=>'Psychotest', 'interview'=>'Interview', 'offer'=>'Offer', 'hired'=>'Hired', 'rejected'=>'Rejected'];
        @endphp
        <select name="stage" class="select">
          @foreach($stages as $k => $v)
            <option value="{{ $k }}" @selected(request('stage')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="label">Site (opsional)</label>
        <input type="text" name="site" value="{{ request('site') }}" class="input" placeholder="DBK / POS / SBS">
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
      <table class="table">
        <thead>
          <tr>
            <th class="th">Kandidat</th>
            <th class="th">Posisi</th>
            <th class="th">Divisi</th>
            <th class="th">Site</th>
            <th class="th">Stage</th>
            <th class="th">Status</th>
            <th class="th">Dibuat</th>
            <th class="th text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($apps as $app)
            @php
              $stage = $app->current_stage ?? 'applied';
              $badge = in_array($stage, ['applied','psychotest']) ? 'badge-blue' : ($stage==='offer' ? 'badge-amber' : ($stage==='rejected' ? 'badge-rose' : 'badge-green'));
              $candidate = $app->candidate->name ?? ($app->user->name ?? ($app->name ?? '—'));
            @endphp
            <tr>
              <td class="td font-medium text-slate-900">{{ $candidate }}</td>
              <td class="td">{{ $app->job->title ?? '—' }}</td>
              <td class="td">{{ $app->job->division ?? '—' }}</td>
              <td class="td">{{ $app->job->site_code ?? '—' }}</td>
              <td class="td">
                <span class="badge {{ $badge }}">{{ strtoupper($stage) }}</span>
              </td>
              <td class="td">{{ strtoupper($app->overall_status ?? 'active') }}</td>
              <td class="td">{{ optional($app->created_at)->format('d M Y') }}</td>
              <td class="td text-right">
                <div class="inline-flex items-center gap-2">
                  <a class="btn btn-outline" href="{{ route('jobs.show', $app->job ?? 0) }}">Detail</a>

                  {{-- Quick Move Stage --}}
                  <form action="{{ route('admin.applications.move', $app) }}" method="POST" class="inline-flex items-center gap-2">
                    @csrf
                    <select name="to" class="select select-sm">
                      @foreach(['applied','psychotest','interview','offer','hired','rejected'] as $opt)
                        <option value="{{ $opt }}" @selected($opt===$stage)>{{ ucfirst($opt) }}</option>
                      @endforeach
                    </select>
                    <button class="btn btn-primary btn-sm">Pindah</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td class="td" colspan="8">Belum ada data aplikasi.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-6">
    {{ method_exists($apps, 'links') ? $apps->withQueryString()->links() : '' }}
  </div>
@endsection
