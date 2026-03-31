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

<div class="mx-auto w-full max-w-[1440px] px-4 py-6 space-y-6">

  {{-- HEADER --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
    <div class="relative h-24 rounded-t-2xl overflow-hidden">

      {{-- 🔥 GRADIENT --}}
      <div class="absolute inset-0"
           style="background: linear-gradient(90deg, {{ $PRIMARY }}, {{ $SECOND }});"></div>

      <div class="absolute right-0 w-32 h-full opacity-30 bg-black"></div>

      <div class="relative h-full px-6 flex items-center justify-between text-white">
        <div>
          <h1 class="text-2xl font-bold">Users</h1>
          <p class="text-sm opacity-90">Kelola pengguna & peran</p>
        </div>

        <div class="flex gap-2">
          <a href="{{ route('admin.users.create') }}"
             class="px-4 py-2 rounded-lg bg-white text-sm font-semibold text-gray-800">
             + New User
          </a>
        </div>
      </div>
    </div>

    {{-- FILTER --}}
    <form method="GET"
      class="mt-4 grid sm:grid-cols-4 gap-3 rounded-xl border bg-white p-4 shadow-sm"
      style="border-color: {{ $BORD }}">

      <input type="text" name="q" value="{{ $q }}"
        placeholder="Search..."
        class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}">

      <select name="role"
        class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}">
        <option value="">All Role</option>
        @foreach(($roleOptions ?? []) as $opt)
          <option value="{{ $opt }}" @selected($role==$opt)>{{ $opt }}</option>
        @endforeach
      </select>

      <select name="status"
        class="rounded-lg border px-3 py-2 text-sm focus:ring-2"
        style="--tw-ring-color: {{ $PRIMARY }}">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>

      <button
        class="rounded-lg text-white font-semibold"
        style="background: {{ $PRIMARY }}">
        Filter
      </button>
    </form>
  </section>

  {{-- TABLE --}}
  <section class="rounded-2xl border bg-white shadow-sm" style="border-color: {{ $BORD }}">
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
              <span class="px-2 py-1 rounded text-sm"
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
            <td colspan="6" class="text-center py-6 text-gray-500">No data</td>
          </tr>
          @endforelse
        </tbody>

      </table>
    </div>
  </section>

</div>
@endsection