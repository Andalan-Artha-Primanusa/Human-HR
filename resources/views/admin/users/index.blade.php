{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app', ['title' => 'Users'])

@php
    $ACCENT = '#a77d52';
    $ACCENT_DARK = '#8b5e3c';
    $BORD = '#e5e7eb';
@endphp

@section('content')
    @once
          {{-- Sprite ikon yang dipakai di halaman --}}
          <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
            <symbol id="i-plus" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14"/>
            </symbol>
            <symbol id="i-search" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="11" cy="11" r="7" stroke-width="2"/>
              <path d="M21 21l-3.5-3.5" stroke-width="2" stroke-linecap="round"/>
            </symbol>
            <symbol id="i-edit" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="2" stroke-linecap="round" d="M12 20h9"/>
              <path stroke-width="2" stroke-linecap="round" d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/>
            </symbol>
            <symbol id="i-trash" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-width="2" stroke-linecap="round" d="M3 6h18M8 6v12m8-12v12M5 6l1 14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-14"/>
            </symbol>
            <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
            <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </symbol>
          </svg>
    @endonce

    @php
        $hasActive = \Illuminate\Support\Facades\Schema::hasColumn('users', 'active');
        $hasEmpId = \Illuminate\Support\Facades\Schema::hasColumn('users', 'id_employe');
        $hasRoleCol = \Illuminate\Support\Facades\Schema::hasColumn('users', 'role');

        $q = $q ?? request('q');
        $role = $role ?? request('role');
        $status = $status ?? request('status');
    @endphp

    <div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      {{-- HEADER — shared component --}}
      <x-admin.page-header title="Users" description="Kelola pengguna & peran.">
        <a href="{{ route('admin.users.create') }}" class="ph-action">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          Tambah User
        </a>
      </x-admin.page-header>

      {{-- FILTER / TOOLBAR --}}
      <section class="overflow-hidden bg-white border rounded-2xl" style="border-color: {{ $BORD }}; border-radius: 1rem;">
        <div class="p-6 md:p-6 bg-white">
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
              <button type="submit" class="abtn abtn-primary">
                <svg class="w-4 h-4 text-white"><use href="#i-search"/></svg>
                Cari
              </button>

              @if(request()->filled('q') || request()->filled('role') || request()->filled('status'))
                <a href="{{ route('admin.users.index') }}" class="abtn abtn-neutral">
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

                        <td class="px-4 py-3">
                          <div class="flex items-center justify-end gap-1.5">
                            @if(Route::has('admin.users.edit'))
                              <a href="{{ route('admin.users.edit', $user) }}"
                                 class="abtn-icon" aria-label="Edit">
                                <svg class="w-4 h-4"><use href="#i-edit"/></svg>
                              </a>
                            @endif

                            @if(Route::has('admin.users.destroy'))
                              <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                    data-confirm-title="Hapus user ini?"
                                    data-confirm-message="User yang dihapus tidak dapat menggunakan akun ini lagi.">
                                @csrf @method('DELETE')
                                <button class="abtn-icon abtn-icon-danger" aria-label="Hapus">
                                  <svg class="w-4 h-4"><use href="#i-trash"/></svg>
                                </button>
                              </form>
                            @endif
                          </div>
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

      {{-- PAGINATION --}}
      @if(($users->count() ?? 0) > 0 && method_exists($users, 'lastPage'))
        @php
            $perPage = max(1, (int) $users->perPage());
            $current = (int) $users->currentPage();
            $last = (int) $users->lastPage();
            $total = (int) $users->total();
            $from = ($current - 1) * $perPage + 1;
            $to = min($current * $perPage, $total);

            $pages = [];
            if ($last <= 7) {
                $pages = range(1, $last);
            } else {
                $pages = [1];
                $left = max(2, $current - 1);
                $right = min($last - 1, $current + 1);
                if ($left > 2)
                    $pages[] = '...';
                for ($i = $left; $i <= $right; $i++)
                    $pages[] = $i;
                if ($right < $last - 1)
                    $pages[] = '...';
                $pages[] = $last;
            }

            $pageUrl = function (int $p) use ($users) {
                return $users->appends(request()->except('page'))->url($p);
            };
        @endphp

        <section class="p-3 mt-4 bg-white border shadow-sm rounded-2xl border-slate-200 md:p-4">
          <div class="flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
            <div class="text-slate-700">
              Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
              dari <span class="font-semibold text-slate-900">{{ $total }}</span>
            </div>
            <nav class="ml-auto" aria-label="Pagination">
              <ul class="inline-flex items-stretch overflow-hidden bg-white border rounded-xl border-slate-200">
                {{-- Prev --}}
                <li>
                  @if($current > 1)
                    <a href="{{ $pageUrl($current - 1) }}"
                       class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}" aria-label="Sebelumnya">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </a>
                  @else
                    <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                    </span>
                  @endif
                </li>

                {{-- Pages --}}
                @foreach($pages as $p)
                      @if($p === '...')
                        <li class="grid px-3 select-none place-items-center h-9 text-slate-500">…</li>
                      @else
                        @php $isCur = ((int) $p === $current); @endphp
                        <li class="grid place-items-center h-9">
                          @if($isCur)
                            <span class="inline-flex items-center h-full px-3 font-semibold border-l select-none text-slate-900 bg-slate-100 border-slate-200">{{ $p }}</span>
                          @else
                            <a href="{{ $pageUrl((int) $p) }}"
                               class="inline-flex items-center h-full px-3 border-l text-slate-700 hover:bg-slate-50 border-slate-200 focus:outline-none focus:ring-2"
                               style="--tw-ring-color: {{ $ACCENT }}" aria-label="Halaman {{ $p }}">{{ $p }}</a>
                          @endif
                        </li>
                      @endif
                @endforeach

                {{-- Next --}}
                <li class="border-l border-slate-200">
                  @if($current < $last)
                    <a href="{{ $pageUrl($current + 1) }}"
                       class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $ACCENT }}" aria-label="Berikutnya">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </a>
                  @else
                    <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                      <svg class="w-4 h-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                    </span>
                  @endif
                </li>
              </ul>
            </nav>
          </div>
        </section>
      @endif

    </div>
@endsection
