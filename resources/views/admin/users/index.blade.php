@extends('layouts.app')

@section('title', 'Users')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-xl font-semibold text-slate-800">Users</h1>
  <div class="flex items-center gap-2">
    @if(Route::has('admin.users.export'))
      <a href="{{ route('admin.users.export') }}"
         class="px-3 py-2 rounded-lg border bg-white hover:bg-slate-50 text-slate-700">
        Export CSV
      </a>
    @endif
    <a href="{{ route('admin.users.create') }}"
       class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">
      + New User
    </a>
  </div>
</div>

{{-- Flash messages --}}
@foreach (['ok'=>'green','warn'=>'amber','err'=>'red'] as $key => $color)
  @if(session($key))
    <div class="mb-3 rounded-lg border border-{{ $color }}-200 bg-{{ $color }}-50 text-{{ $color }}-800 px-3 py-2">
      {{ session($key) }}
    </div>
  @endif
@endforeach
@if(session('import_warnings'))
  <details class="mb-3 rounded-lg border border-amber-200 bg-amber-50 text-amber-900 px-3 py-2">
    <summary class="cursor-pointer font-medium">Import warnings ({{ count(session('import_warnings')) }})</summary>
    <ul class="list-disc ml-5 mt-2 text-sm">
      @foreach(session('import_warnings') as $w)
        <li>{{ $w }}</li>
      @endforeach
    </ul>
  </details>
@endif

{{-- Filters --}}
<form method="GET" class="mb-4 grid gap-2 sm:grid-cols-4">
  <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Cari name/email"
         class="px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring w-full">
  <select name="role" class="px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring w-full">
    <option value="">— Semua Role —</option>
    @foreach(($roleOptions ?? []) as $opt)
      <option value="{{ $opt }}" @selected(($role ?? '')===$opt)>{{ ucfirst($opt) }}</option>
    @endforeach
  </select>
  <select name="status" class="px-3 py-2 rounded-lg border border-slate-300 focus:outline-none focus:ring w-full">
    <option value="">— Semua Status —</option>
    <option value="active" @selected(($status ?? '')==='active')>Active</option>
    <option value="inactive" @selected(($status ?? '')==='inactive')>Inactive</option>
  </select>
  <button class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white w-full">Filter</button>
</form>

{{-- Import CSV --}}
@if(Route::has('admin.users.import'))
<form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data"
      class="mb-6 rounded-lg border border-slate-200 p-3">
  @csrf
  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
    <div class="text-sm text-slate-700">
      <div class="font-medium">Import CSV</div>
      <div class="text-slate-500">Header: <code>name,email,password(optional),role(optional),active(optional)</code></div>
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

{{-- Table --}}
<div class="overflow-auto rounded-xl border border-slate-200">
  <table class="min-w-full text-sm">
    <thead class="bg-slate-50 text-slate-700">
      <tr>
        <th class="text-left px-3 py-2">Name</th>
        <th class="text-left px-3 py-2">Email</th>
        <th class="text-left px-3 py-2">Role(s)</th>
        <th class="text-left px-3 py-2">Status</th>
        <th class="text-left px-3 py-2">Created</th>
        <th class="text-right px-3 py-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $user)
        <tr class="border-t">
          <td class="px-3 py-2 font-medium text-slate-800">{{ $user->name }}</td>
          <td class="px-3 py-2 text-slate-700">{{ $user->email }}</td>
          <td class="px-3 py-2">
            @php
              $roleText = '';
              if (isset($user->role)) {
                $roleText = $user->role;
              } elseif (method_exists($user, 'getRoleNames')) {
                $roleText = $user->getRoleNames()->implode(', ');
              }
            @endphp
            <span class="inline-flex px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">{{ $roleText ?: '—' }}</span>
          </td>
          <td class="px-3 py-2">
            @php
              $hasActive = \Illuminate\Support\Facades\Schema::hasColumn('users','active');
              $isActive = $hasActive ? (bool)$user->active : null;
            @endphp
            @if($hasActive)
              <span class="inline-flex px-2 py-0.5 rounded {{ $isActive ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                {{ $isActive ? 'Active' : 'Inactive' }}
              </span>
            @else
              <span class="text-slate-400">n/a</span>
            @endif
          </td>
          <td class="px-3 py-2 text-slate-600">{{ optional($user->created_at)->format('Y-m-d H:i') }}</td>
          <td class="px-3 py-2 text-right">
            <a href="{{ route('admin.users.edit', $user) }}"
               class="px-2 py-1 rounded border bg-white hover:bg-slate-50 text-slate-700">Edit</a>
            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                  onsubmit="return confirm('Hapus user ini?')">
              @csrf @method('DELETE')
              <button class="px-2 py-1 rounded border bg-white hover:bg-red-50 text-red-700">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="px-3 py-6 text-center text-slate-500">Tidak ada data.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{-- Pagination --}}
<div class="mt-4">{{ $users->links() }}</div>
@endsection
