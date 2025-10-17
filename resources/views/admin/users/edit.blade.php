@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<h1 class="text-xl font-semibold text-slate-800 mb-4">Edit User</h1>

@if ($errors->any())
  <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2">
    <ul class="list-disc ml-5">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-4 max-w-xl">
  @csrf @method('PATCH')

  <div>
    <label class="block text-sm text-slate-700 mb-1">Name</label>
    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-3 py-2 rounded-lg border border-slate-300">
  </div>

  <div>
    <label class="block text-sm text-slate-700 mb-1">Email</label>
    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-3 py-2 rounded-lg border border-slate-300">
  </div>

  @if(\Illuminate\Support\Facades\Schema::hasColumn('users','id_employe'))
  <div>
    <label class="block text-sm text-slate-700 mb-1">
      ID Employe <span class="text-slate-400 text-xs">(opsional, unik)</span>
    </label>
    <input type="text" name="id_employe"
           value="{{ old('id_employe', $user->id_employe) }}"
           class="w-full px-3 py-2 rounded-lg border border-slate-300">
    @error('id_employe')
      <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
    @enderror
  </div>
  @endif

  <div>
    <label class="block text-sm text-slate-700 mb-1">Password <span class="text-slate-400">(isi untuk ganti)</span></label>
    <input type="password" name="password" class="w-full px-3 py-2 rounded-lg border border-slate-300">
  </div>

  <div>
    <label class="block text-sm text-slate-700 mb-1">Role</label>
    <select name="role" class="w-full px-3 py-2 rounded-lg border border-slate-300">
      <option value="">— Pilih Role —</option>
      @foreach(($roleOptions ?? []) as $opt)
        <option value="{{ $opt }}" @selected(old('role', (isset($user->role)?$user->role:($user->getRoleNames()->first() ?? ''))) === $opt)>
          {{ ucfirst($opt) }}
        </option>
      @endforeach
    </select>
  </div>

  @if(\Illuminate\Support\Facades\Schema::hasColumn('users','active'))
  <div class="flex items-center gap-2">
    <input type="hidden" name="active" value="0">
    <input type="checkbox" name="active" value="1" id="active" class="rounded"
           {{ old('active', (int)($user->active ?? 1)) ? 'checked' : '' }}>
    <label for="active" class="text-sm text-slate-700">Active</label>
  </div>
  @endif

  <div class="flex items-center gap-2">
    <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded-lg border bg-white hover:bg-slate-50">Cancel</a>
    <button class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Update</button>
  </div>
</form>
@endsection
