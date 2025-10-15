@extends('layouts.app')

@section('title', 'Lamaran Saya')

@section('content')
  <div class="p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h1 class="text-xl font-semibold text-slate-800">Lamaran Saya</h1>
      <a href="{{ route('jobs.index') }}" class="btn btn-primary">Cari Lowongan</a>
    </div>

    @if(isset($apps) && $apps->count())
      <div class="overflow-x-auto bg-white rounded-2xl shadow">
        <table class="min-w-full text-sm">
          <thead class="bg-slate-50 text-slate-600">
            <tr>
              <th class="px-4 py-3 text-left">Posisi</th>
              <th class="px-4 py-3 text-left">Dibuat</th>
              <th class="px-4 py-3 text-left">Status</th>
              <th class="px-4 py-3 text-left">Tahapan</th>
              <th class="px-4 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach($apps as $app)
              <tr class="hover:bg-slate-50/60">
                <td class="px-4 py-3">
                  <div class="font-medium text-slate-800">
                    {{ $app->job->title ?? '—' }}
                  </div>
                  <div class="text-xs text-slate-500">
                    Kode: {{ $app->job->code ?? $app->job_id }}
                  </div>
                </td>
                <td class="px-4 py-3">
                  {{ optional($app->created_at)->format('d M Y') }}
                </td>
                <td class="px-4 py-3">
                  <span class="badge">
                    {{ $app->status ?? 'submitted' }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  @if($app->relationLoaded('stages') && $app->stages->count())
                    <div class="flex flex-wrap gap-1">
                      @foreach($app->stages as $st)
                        <span class="chip">{{ $st->name }}</span>
                      @endforeach
                    </div>
                  @else
                    <span class="text-slate-400">—</span>
                  @endif
                </td>
                <td class="px-4 py-3 text-right">
                  @if(isset($app->job_id))
                    <a href="{{ route('jobs.show', $app->job_id) }}" class="btn btn-sm btn-primary">Detail Lowongan</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $apps->links() }}
      </div>
    @else
      <div class="rounded-2xl border border-dashed border-slate-300 p-8 text-center">
        <div class="text-slate-600 font-medium">Belum ada lamaran.</div>
        <div class="text-slate-500 text-sm mt-1">Mulai dengan memilih lowongan yang tersedia.</div>
        <a href="{{ route('jobs.index') }}" class="btn btn-primary mt-4">Lihat Lowongan</a>
      </div>
    @endif
  </div>
@endsection
