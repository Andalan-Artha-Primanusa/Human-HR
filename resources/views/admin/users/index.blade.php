{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app', [ 'title' => 'Users' ])

@php
  $PRIMARY = '#a77d52';
  $SECOND  = '#8b5e3c';
  $BORD = '#e5e7eb';
@endphp

@section('content')

@php
  $hasActive  = \Illuminate\Support\Facades\Schema::hasColumn('users','active');
  $hasEmpId   = \Illuminate\Support\Facades\Schema::hasColumn('users','id_employe');
  $hasRoleCol = \Illuminate\Support\Facades\Schema::hasColumn('users','role');

  $q      = $q      ?? request('q');
  $role   = $role   ?? request('role');
  $status = $status ?? request('status');
@endphp

<div class="mx-auto w-full max-w-[1440px] px-4 sm:px-6 lg:px-8 py-6 space-y-6">

  {{-- HEADER dua-tone + FILTER --}}
  <section class="overflow-hidden bg-white border shadow-sm rounded-2xl" style="border-color: {{ $BORD }}">
    <div class="relative">
      <div class="w-full h-20 sm:h-24" style="background: linear-gradient(90deg, {{ $PRIMARY }}, {{ $SECOND }});"></div>
      <div class="absolute inset-y-0 right-0 w-24 sm:w-36" style="background: linear-gradient(90deg, {{ $SECOND }}, {{ $PRIMARY }});"></div>

      <div class="absolute inset-0 flex flex-col gap-3 px-5 py-4 text-white md:px-6 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
          <h1 class="text-2xl font-semibold tracking-tight text-white sm:text-3xl">Users</h1>
          <p class="text-xs sm:text-sm text-white/90">Kelola pengguna & peran.</p>
        </div>

        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center justify-center w-full gap-2 px-4 py-2 text-sm font-semibold bg-white rounded-lg text-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto"
           style="--tw-ring-color: {{ $PRIMARY }}">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true" style="color: {{ $PRIMARY }}">
            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
          + New User
        </a>
      </div>
    </div>

    {{-- FILTER --}}
    <form method="GET"
      class="mt-3 md:mt-4 grid grid-cols-1 gap-2 md:grid-cols-[1fr_auto_auto_auto] px-3 py-3 md:px-4 md:py-4 shadow-sm"
      role="search" aria-label="Filter Users"
      style="border-color: {{ $BORD }}">

      <input type="text" name="q" value="{{ $q }}"
        placeholder="Search..."
        class="w-full px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}">

      <select name="role"
        class="w-full px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}">
        <option value="">All Role</option>
        @foreach(($roleOptions ?? []) as $opt)
          <option value="{{ $opt }}" @selected($role==$opt)>{{ $opt }}</option>
        @endforeach
      </select>

      <select name="status"
        class="w-full px-3 py-2 text-sm border rounded-lg border-slate-200 focus:outline-none focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>

      <div class="flex gap-2">
        <button type="submit"
          class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg hover:opacity-95 focus:outline-none focus:ring-2 focus:ring-offset-2 md:shrink-0"
          style="background-color:#0f172a; border:1px solid #0f172a; --tw-ring-color: {{ $PRIMARY }};"
          aria-label="Filter">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="11" cy="11" r="7" stroke="#ffffff" stroke-width="2"/>
            <path d="M21 21l-3.5-3.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round"/>
          </svg>
          <span>Filter</span>
        </button>
      </div>
    </form>
  </section>

  {{-- TABLE --}}
  <section class="bg-white border shadow-sm rounded-2xl border-slate-200" style="border-color: {{ $BORD }}">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">

        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-3 text-left">Name</th>
            <th class="px-4 py-3 text-left">Email</th>
            @if($hasEmpId)
              <th class="px-4 py-3 text-left">ID</th>
            @endif
            <th class="px-4 py-3 text-left">Role</th>
            <th class="px-4 py-3 text-left">Status</th>
            <th class="px-4 py-3 text-left">Created</th>
            <th class="px-4 py-3 text-right">Action</th>
          </tr>
        </thead>

        <tbody class="divide-y">
          @forelse($users as $user)
          <tr class="hover:bg-gray-50">

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
              <a href="{{ route('admin.users.edit',$user) }}"
                 class="text-sm font-medium"
                 style="color: {{ $PRIMARY }}">
                Edit
              </a>
            </td>

          </tr>
          @empty
          <tr>
            <td colspan="6" class="py-6 text-center text-gray-500">No data</td>
          </tr>
          @endforelse
        </tbody>

      </table>
    </div>
  </section>

</div>
@endsection