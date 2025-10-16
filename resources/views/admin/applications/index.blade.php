{{-- resources/views/admin/applications/index.blade.php --}}
@extends('layouts.app', ['title' => 'Applications'])

@section('content')
  {{-- HEADER: panel biru–merah --}}
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
          <p class="text-slate-600 text-sm">Daftar semua kandidat & status proses rekrutmen.</p>
        </div>
        <a href="{{ route('admin.jobs.index') }}" class="btn btn-primary inline-flex items-center gap-2 self-start md:self-auto">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M21 21l-3.5-3.5M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0z"/></svg>
          Cari Lowongan
        </a>
      </div>
    </div>
  </div>

  {{-- FILTER BAR --}}
  <form method="GET" class="rounded-2xl border border-slate-200 bg-white shadow-sm mb-4">
    <div class="p-5 grid gap-3 md:grid-cols-4">
      <div>
        <label class="label">Cari</label>
        <input type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Nama kandidat / posisi / site" autocomplete="off">
      </div>

      <div>
        <label class="label">Stage</label>
        @php
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
        <select name="stage" class="input">
          @foreach($stageOptions as $k => $v)
            <option value="{{ $k }}" @selected(request('stage')===$k)>{{ $v }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="label">Site</label>
        @php $siteVal = request('site'); @endphp
        @if(!empty($sites ?? null) && is_iterable($sites))
          <select name="site" class="input">
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
        <button class="btn btn-primary inline-flex items-center gap-2">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M21 21l-3.5-3.5"/></svg>
          Filter
        </button>
        @if(request()->hasAny(['q','stage','site']))
          <a href="{{ route('admin.applications.index') }}" class="btn btn-ghost">Reset</a>
        @endif
      </div>
    </div>
  </form>

  {{-- TABEL --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="p-0 overflow-x-auto">
      @if($apps->count())
        <table class="min-w-[960px] w-full text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 text-left">Kandidat</th>
              <th class="px-4 py-3 text-left">Posisi</th>
              <th class="px-4 py-3 text-left w-40">Divisi</th>
              <th class="px-4 py-3 text-left w-24">Site</th>
              <th class="px-4 py-3 text-center w-32">Stage</th>
              <th class="px-4 py-3 text-center w-28">Overall</th>
              <th class="px-4 py-3 text-left w-28">Dibuat</th>
              <th class="px-4 py-3 text-right w-[320px]">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($apps as $app)
              @php
                $stage = $app->current_stage ?? 'applied';
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
                $candidate = $app->candidate->name
                  ?? ($app->user->name ?? ($app->name ?? '—'));
                $overall = strtolower($app->overall_status ?? 'active');
                $overallBadge = match($overall) {
                  'hired'          => 'badge-green',
                  'not_qualified'  => 'badge-rose',
                  'inactive'       => 'badge-slate',
                  default          => 'badge-blue',
                };
              @endphp
              <tr class="hover:bg-slate-50/60 align-top">
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-900">{{ $candidate }}</div>
                  @if(!empty($app->candidate?->email))
                    <div class="text-xs text-slate-500">{{ $app->candidate->email }}</div>
                  @endif
                </td>

                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800">{{ $app->job->title ?? '—' }}</div>
                  <div class="mt-0.5 text-xs text-slate-500">
                    @if(!empty($app->job?->employment_type))
                      <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                        {{ ucfirst($app->job->employment_type) }}
                      </span>
                    @endif
                    @if(!empty($app->job?->code))
                      <span class="inline-flex px-1.5 py-0.5 rounded border border-slate-200 bg-slate-50">
                        #{{ $app->job->code }}
                      </span>
                    @endif
                  </div>
                </td>

                <td class="px-4 py-3">{{ $app->job->division ?? '—' }}</td>

                <td class="px-4 py-3">
                  <span class="font-mono text-slate-700">{{ $app->job->site->code ?? $app->job->site_code ?? '—' }}</span>
                </td>

                <td class="px-4 py-3 text-center">
                  <span class="badge {{ $stageBadge }}">{{ strtoupper(str_replace('_',' ',$stage)) }}</span>
                </td>

                <td class="px-4 py-3 text-center">
                  <span class="badge {{ $overallBadge }}">{{ strtoupper(str_replace('_',' ',$overall)) }}</span>
                </td>

                <td class="px-4 py-3">{{ optional($app->created_at)->format('d M Y') }}</td>

                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    {{-- Lihat Job (public detail atau admin detail—pakai public sesuai route yang ada) --}}
                    <a class="btn btn-outline btn-sm inline-flex items-center gap-1.5"
                       target="_blank" href="{{ route('jobs.show', $app->job ?? 0) }}">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-width="2" d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3" stroke-width="2"/>
                      </svg>
                      Lihat Job
                    </a>

                    {{-- Quick Move Stage --}}
                    <form action="{{ route('admin.applications.move', $app) }}" method="POST"
                          class="inline-flex items-center gap-2">
                      @csrf
                      <select name="to" class="input !h-8 !py-1 !px-2 text-xs">
                        @foreach(['applied','psychotest','hr_iv','user_iv','final','offer','hired','not_qualified'] as $opt)
                          <option value="{{ $opt }}" @selected($opt===$stage)>{{ ucwords(str_replace('_',' ',$opt)) }}</option>
                        @endforeach
                      </select>
                      <button class="btn btn-primary btn-sm inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M5 12h14M13 5l7 7-7 7"/></svg>
                        Pindah
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        {{-- EMPTY STATE --}}
        <div class="py-16 grid place-content-center text-center">
          <div class="mx-auto w-12 h-12 rounded-2xl bg-slate-100 grid place-content-center text-slate-400 mb-3">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
            </svg>
          </div>
          <div class="text-slate-700 font-medium">Belum ada data aplikasi.</div>
          <div class="text-slate-500 text-sm mt-1">Coba ubah filter atau cari lowongan.</div>
          <a href="{{ route('admin.jobs.index') }}" class="btn btn-primary mt-3 inline-flex items-center gap-2">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M21 21l-3.5-3.5M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0z"/></svg>
            Cari Lowongan
          </a>
        </div>
      @endif
    </div>
  </div>

  {{-- PAGINATION --}}
  <div class="mt-6">
    {{ method_exists($apps, 'links') ? $apps->withQueryString()->links() : '' }}
  </div>
@endsection
