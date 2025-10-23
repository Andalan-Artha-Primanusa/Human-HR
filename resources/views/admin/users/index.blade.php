{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Users' ])

@php
  $BLUE = '#1d4ed8'; // blue-700
  $RED  = '#dc2626'; // red-600
  $BORD = '#e5e7eb'; // slate-200
  $DARK = '#0f172a'; // slate-900-like untuk tombol gelap
@endphp

@section('content')
@php
  $hasActive  = \Illuminate\Support\Facades\Schema::hasColumn('users','active');
  $hasEmpId   = \Illuminate\Support\Facades\Schema::hasColumn('users','id_employe');
  $hasRoleCol = \Illuminate\Support\Facades\Schema::hasColumn('users','role');

  // nilai filter
  $q      = $q      ?? request('q');
  $role   = $role   ?? request('role');
  $status = $status ?? request('status');
@endphp

@once
  {{-- Sprite ikon kecil (pagination) --}}
  <svg xmlns="http://www.w3.org/2000/svg" class="hidden" aria-hidden="true" focusable="false">
    <symbol id="i-chevron-left" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M15 19l-7-7 7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
    <symbol id="i-chevron-right" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </symbol>
  </svg>
@endonce

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER dua-tone + actions --}}
  <section class="relative rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-20 sm:h-24 rounded-t-2xl overflow-hidden">
      <div class="absolute inset-0" style="background: {{ $BLUE }}"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: {{ $RED }}"></div>

      <div class="relative h-full px-5 md:px-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0 self-center sm:self-auto">
          <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-white">Users</h1>
          <p class="text-xs sm:text-sm text-white/90">Kelola pengguna & peran.</p>
        </div>
        <div class="flex items-center gap-2 self-center sm:self-auto">
          @if(Route::has('admin.users.export'))
            <a href="{{ route('admin.users.export') }}"
               class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2"
               style="--tw-ring-color: {{ $BLUE }}">
              Export CSV
            </a>
          @endif
          <a href="{{ route('admin.users.create') }}"
             class="inline-flex items-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2"
             style="--tw-ring-color: {{ $BLUE }}">
            + New User
          </a>
        </div>
      </div>
    </div>

    {{-- FILTER (punya jarak, bukan nempel) --}}
    <form method="GET"
          class="mt-3 md:mt-4 grid grid-cols-1 sm:grid-cols-4 gap-2 rounded-xl border bg-white px-3 py-3 md:px-4 md:py-4 shadow-sm"
          role="search" aria-label="Filter Users" style="border-color: {{ $BORD }}">
      <input type="text" name="q" value="{{ e($q ?? '') }}"
             placeholder="Cari name/email{{ $hasEmpId ? '/ID Karyawan' : '' }}"
             class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
             style="--tw-ring-color: {{ $BLUE }}" autocomplete="off">

      <select name="role"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
              style="--tw-ring-color: {{ $BLUE }}">
        <option value="">— Semua Role —</option>
        @foreach(($roleOptions ?? []) as $opt)
          <option value="{{ $opt }}" @selected(($role ?? '')===$opt)>{{ ucfirst($opt) }}</option>
        @endforeach
      </select>

      <select name="status"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:outline-none focus:ring-2"
              style="--tw-ring-color: {{ $BLUE }}">
        <option value="">— Semua Status —</option>
        <option value="active" @selected(($status ?? '')==='active')>Active</option>
        <option value="inactive" @selected(($status ?? '')==='inactive')>Inactive</option>
      </select>

      {{-- Tombol Filter gelap + ikon putih inline (tanpa currentColor) --}}
      <button type="submit"
              class="inline-flex items-center justify-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold text-white
                     hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2"
              style="background-color: {{ $DARK }}; border:1px solid {{ $DARK }}; --tw-ring-color: {{ $BLUE }};">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
          <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span>Filter</span>
      </button>
    </form>
  </section>

  {{-- FLASH messages (tetap) --}}
  @foreach (['ok'=>'green','warn'=>'amber','err'=>'red'] as $key => $color)
    @if(session($key))
      <div class="rounded-lg border px-3 py-2 bg-{{ $color }}-50 text-{{ $color }}-800 border-{{ $color }}-200">
        {{ session($key) }}
      </div>
    @endif
  @endforeach
  @if(session('import_warnings'))
    <details class="rounded-lg border border-amber-200 bg-amber-50 text-amber-900 px-3 py-2">
      <summary class="cursor-pointer font-medium">Import warnings ({{ count(session('import_warnings')) }})</summary>
      <ul class="list-disc ml-5 mt-2 text-sm">
        @foreach(session('import_warnings') as $w)
          <li>{{ $w }}</li>
        @endforeach
      </ul>
    </details>
  @endif

  {{-- IMPORT CSV (tetap) --}}
  @if(Route::has('admin.users.import'))
    <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data"
          class="rounded-2xl border bg-white p-3 md:p-4 shadow-sm" style="border-color: {{ $BORD }}">
      @csrf
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <div class="text-sm text-slate-700">
          <div class="font-medium">Import CSV</div>
          <div class="text-slate-500">
            Header: <code>name,email,password(optional),role(optional),active(optional){{ $hasEmpId ? ',id_employe(optional)' : '' }}</code>
          </div>
        </div>
        <input type="file" name="file" accept=".csv,text/csv"
               class="px-3 py-2 rounded-lg border border-slate-300 w-full sm:w-auto">
        <button class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white">Upload</button>
      </div>
      @error('file')
        <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
      @enderror
    </form>
  @endif

  {{-- TABEL --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-slate-700">
          <tr>
            <th class="text-left px-4 py-3">Name</th>
            <th class="text-left px-4 py-3">Email</th>
            @if($hasEmpId)
              <th class="text-left px-4 py-3">ID Employe</th>
            @endif
            <th class="text-left px-4 py-3">Role(s)</th>
            <th class="text-left px-4 py-3">Status</th>
            <th class="text-left px-4 py-3">Created</th>
            <th class="text-right px-4 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($users as $user)
            <tr class="hover:bg-slate-50/60">
              <td class="px-4 py-3 font-medium text-slate-800">{{ e($user->name) }}</td>
              <td class="px-4 py-3 text-slate-700">{{ e($user->email) }}</td>

              @if($hasEmpId)
                <td class="px-4 py-3 text-slate-700">{{ e($user->id_employe ?? '—') }}</td>
              @endif

              <td class="px-4 py-3">
                @php
                  $roleText = '';
                  if ($hasRoleCol && isset($user->role)) {
                    $roleText = $user->role;
                  } elseif (method_exists($user, 'getRoleNames')) {
                    $roleText = $user->getRoleNames()->implode(', ');
                  }
                @endphp
                <span class="inline-flex px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                  {{ $roleText ?: '—' }}
                </span>
              </td>

              <td class="px-4 py-3">
                @if($hasActive)
                  @php $isActive = (bool)($user->active ?? false); @endphp
                  <span class="inline-flex px-2 py-0.5 rounded {{ $isActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                    {{ $isActive ? 'Active' : 'Inactive' }}
                  </span>
                @else
                  <span class="text-slate-400">n/a</span>
                @endif
              </td>

              <td class="px-4 py-3 text-slate-600">{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>

              <td class="px-4 py-3 text-right">
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="px-2 py-1 rounded border border-slate-200 bg-white hover:bg-slate-50 text-slate-700">Edit</a>
                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                      onsubmit="return confirm('Hapus user ini?')">
                  @csrf @method('DELETE')
                  <button class="px-2 py-1 rounded border border-slate-200 bg-white hover:bg-red-50 text-red-700">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ 6 + ($hasEmpId ? 1 : 0) }}" class="px-4 py-6 text-center text-slate-500">Tidak ada data.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- PAGINATION (kapsul custom) --}}
  @php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $users */
    $hasData = ($users->count() ?? 0) > 0;
  @endphp

  @if($hasData)
    @php
      $perPage = max(1, (int) $users->perPage());
      $current = (int) $users->currentPage();
      $last    = (int) $users->lastPage();
      $total   = (int) $users->total();
      $from    = ($current - 1) * $perPage + 1;
      $to      = min($current * $perPage, $total);

      $pages = [];
      if ($last <= 7) {
        $pages = range(1, $last);
      } else {
        $pages = [1];
        $left  = max(2, $current - 1);
        $right = min($last - 1, $current + 1);
        if ($left > 2) $pages[] = '...';
        for ($i = $left; $i <= $right; $i++) $pages[] = $i;
        if ($right < $last - 1) $pages[] = '...';
        $pages[] = $last;
      }

      $pageUrl = function (int $p) use ($users) {
        return $users->appends(request()->except('page'))->url($p);
      };
    @endphp

    <section class="rounded-2xl border border-slate-200 bg-white p-3 md:p-4 shadow-sm">
      <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between text-sm">
        <div class="text-slate-700">
          Menampilkan <span class="font-semibold text-slate-900">{{ $from }}–{{ $to }}</span>
          dari <span class="font-semibold text-slate-900">{{ $total }}</span>
        </div>
        <div class="hidden md:block text-slate-700">
          Showing <span class="font-semibold text-slate-900">{{ $from }}</span>
          to <span class="font-semibold text-slate-900">{{ $to }}</span>
          of <span class="font-semibold text-slate-900">{{ $total }}</span> results
        </div>

        <nav class="ml-auto" aria-label="Pagination">
          <ul class="inline-flex items-stretch overflow-hidden rounded-xl border border-slate-200 bg-white">
            {{-- Prev --}}
            <li>
              @if($current > 1)
                <a href="{{ $pageUrl($current - 1) }}"
                   class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $BLUE }}" aria-label="Previous">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </a>
              @else
                <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-left"/></svg>
                </span>
              @endif
            </li>

            {{-- Pages --}}
            @foreach($pages as $p)
              @if($p === '...')
                <li class="grid place-items-center px-3 h-9 text-slate-500 select-none">…</li>
              @else
                @php $isCur = ((int)$p === $current); @endphp
                <li class="grid place-items-center h-9">
                  @if($isCur)
                    <span class="px-3 h-full inline-flex items-center font-semibold text-slate-900 bg-slate-100 border-l border-slate-200 select-none">{{ $p }}</span>
                  @else
                    <a href="{{ $pageUrl((int)$p) }}"
                       class="px-3 h-full inline-flex items-center text-slate-700 hover:bg-slate-50 border-l border-slate-200 focus:outline-none focus:ring-2"
                       style="--tw-ring-color: {{ $BLUE }}" aria-label="Page {{ $p }}">{{ $p }}</a>
                  @endif
                </li>
              @endif
            @endforeach

            {{-- Next --}}
            <li class="border-l border-slate-200">
              @if($current < $last)
                <a href="{{ $pageUrl($current + 1) }}"
                   class="grid place-items-center px-2.5 h-9 hover:bg-slate-50 focus:outline-none focus:ring-2"
                   style="--tw-ring-color: {{ $BLUE }}" aria-label="Next">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-right"/></svg>
                </a>
              @else
                <span class="grid place-items-center px-2.5 h-9 opacity-40 cursor-not-allowed" aria-hidden="true">
                  <svg class="h-4 w-4 text-slate-700"><use href="#i-chevron-right"/></svg>
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
