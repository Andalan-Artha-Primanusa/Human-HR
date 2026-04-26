{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app', ['title' => 'Users'])

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
@endphp

@section('content')

    @php
        $hasActive = \Illuminate\Support\Facades\Schema::hasColumn('users', 'active');
        $hasEmpId = \Illuminate\Support\Facades\Schema::hasColumn('users', 'id_employe');
        $hasRoleCol = \Illuminate\Support\Facades\Schema::hasColumn('users', 'role');

        $q = $q ?? request('q');
        $role = $role ?? request('role');
        $status = $status ?? request('status');
    @endphp

    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- HEADER + FILTER seperti Sites --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="relative">
          <div class="w-full h-20 sm:h-24 bg-[#a77d52]"></div>

          <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
              <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Users</h1>
              <p class="text-xs sm:text-sm text-white/90">Kelola pengguna & peran.</p>
            </div>

            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
               style="--tw-ring-color: #a77d52">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="color: #a77d52">
                <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
              Tambah User
            </a>
          </div>
        </div>

        <div class="p-6 border-t md:p-7 bg-white" style="border-color: {{ $BORD }}">
          <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end" role="search" aria-label="Filter Users">
            <label class="sr-only" for="q">Cari</label>
            <input id="q" type="text" name="q" value="{{ $q }}"
                   class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $ACCENT }}" placeholder="Cari nama / email…" autocomplete="off">

            <label class="sr-only" for="role">Role</label>
            <select id="role" name="role"
                    class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
              <option value="">Semua Role</option>
              @foreach(($roleOptions ?? []) as $opt)
                <option value="{{ $opt }}" @selected($role == $opt)>{{ $opt }}</option>
              @endforeach
            </select>

            <label class="sr-only" for="status">Status</label>
            <select id="status" name="status"
                    class="w-full px-4 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 focus:outline-none focus:ring-2"
                    style="--tw-ring-color: {{ $ACCENT }}">
              <option value="">Semua Status</option>
              <option value="active" @selected($status === 'active')>Active</option>
              <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>

            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
              <button type="submit"
                      class="inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl bg-[#a77d52] shadow-sm hover:brightness-105 focus:outline-none focus:ring-2"
                      style="--tw-ring-color: {{ $ACCENT }}">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
                  <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Cari
              </button>

              @if(request()->filled('q') || request()->filled('role') || request()->filled('status'))
                <a href="{{ route('admin.users.index') }}"
                   class="inline-flex items-center justify-center px-5 py-3 text-sm bg-white border shadow-sm rounded-xl border-slate-200 hover:bg-slate-50 text-slate-900">
                  Reset
                </a>
              @endif
            </div>
          </form>
        </div>
      </section>

      {{-- TABLE --}}
      <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
        <div class="overflow-x-auto">
          @if(($users->count() ?? 0) > 0)
              <table class="min-w-full text-sm">
                <thead class="text-white bg-[#a77d52]">
                  <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    @if($hasEmpId)
                          <th class="px-4 py-3 text-left">ID Karyawan</th>
                    @endif
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Dibuat</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                  @forelse($users as $user)
                      <tr class="align-top hover:bg-[#f8f5f2] transition">

                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>

                        @if($hasEmpId)
                              <td class="px-4 py-3">{{ $user->id_employe }}</td>
                        @endif

                        <td class="px-4 py-3">
                          <span class="px-2 py-1 text-sm rounded"
                                style="background:#f5efe8; color:#8b5e3c">
                            {{ $user->role ?? '-' }}
                          </span>
                        </td>

                        <td class="px-4 py-3">
                          @if($hasActive)
                            <span class="px-2 py-1 rounded text-xs {{ $user->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                              {{ $user->active ? 'Active' : 'Inactive' }}
                            </span>
                          @endif
                        </td>

                        <td class="px-4 py-3">
                          {{ optional($user->created_at)->format('Y-m-d') }}
                        </td>

                        <td class="px-4 py-3 text-right">
                          <a href="{{ route('admin.users.edit', $user) }}"
                             class="text-sm font-medium hover:underline"
                             style="color: {{ $ACCENT }}">
                            Edit
                          </a>
                        </td>

                      </tr>
                  @empty
                      <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">Belum ada user</td>
                      </tr>
                  @endforelse
                </tbody>

              </table>
          @else
            {{-- EMPTY STATE --}}
            <section class="p-10 text-center bg-white border border-dashed rounded-2xl border-slate-300">
              <div class="grid w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-100 place-content-center text-slate-400">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 7V6a3 3 0 1 1 6 0v1M5 11h14m-1 8H6a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2Z"/>
                </svg>
              </div>
              <div class="font-medium text-slate-700">Belum ada user.</div>
              <div class="mt-1 text-sm text-slate-500">Coba ubah filter atau buat user baru.</div>
            </section>
          @endif
        </div>
      </section>

    </div>
@endsection