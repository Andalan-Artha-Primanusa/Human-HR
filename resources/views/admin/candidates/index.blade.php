@extends('layouts.app', ['title' => 'Admin · Candidates'])

@section('content')
<div class="space-y-6">
  <div class="rounded-2xl border bg-white shadow-sm">
    <div class="h-2 rounded-t-2xl overflow-hidden">
      <div class="h-full w-full flex">
        <div class="h-full bg-blue-600" style="width: 85%"></div>
        <div class="h-full bg-red-500"  style="width: 15%"></div>
      </div>
    </div>

    <div class="p-6 md:p-7">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-semibold tracking-tight text-slate-900">Candidate Profiles</h1>
          <p class="text-sm text-slate-600 mt-1">Daftar kandidat yang telah mengisi profil.</p>
        </div>
        <form method="GET" class="w-full sm:w-auto">
          <div class="flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / email / HP / NIK"
                   class="input w-full sm:w-72" />
            <button class="btn btn-primary">Cari</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
    <table class="min-w-full text-sm">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="px-4 py-2 text-left">Nama</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">HP</th>
          <th class="px-4 py-2 text-left">NIK</th>
          <th class="px-4 py-2 text-center">Tr / Emp / Ref</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @forelse($profiles as $p)
          <tr>
            <td class="px-4 py-2">
              <div class="font-medium text-slate-900">{{ $p->full_name }}</div>
              <div class="text-xs text-slate-500">Updated: {{ optional($p->updated_at)->format('d M Y H:i') }}</div>
            </td>
            <td class="px-4 py-2">{{ $p->email }}</td>
            <td class="px-4 py-2">{{ $p->phone }}</td>
            <td class="px-4 py-2">{{ $p->nik }}</td>
            <td class="px-4 py-2 text-center">
              {{ $p->trainings_count }} / {{ $p->employments_count }} / {{ $p->references_count }}
            </td>
            <td class="px-4 py-2 text-right">
              <a href="{{ route('admin.candidates.show', $p) }}" class="text-blue-700 hover:underline">Lihat</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data.</td></tr>
        @endforelse
      </tbody>
    </table>

    <div class="p-4 border-t bg-slate-50">
      {{ $profiles->links() }}
    </div>
  </div>
</div>
@endsection
