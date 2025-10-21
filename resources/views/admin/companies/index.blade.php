@extends('layouts.app', ['title' => 'Companies'])

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-semibold">Companies</h1>
    <a href="{{ route('admin.companies.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">New Company</a>
  </div>

  <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-3">
    <input name="q" value="{{ $q }}" placeholder="Search name/code…" class="border rounded-lg px-3 py-2">
    <select name="status" class="border rounded-lg px-3 py-2">
      <option value="">All status</option>
      <option value="active" @selected($status==='active')>Active</option>
      <option value="inactive" @selected($status==='inactive')>Inactive</option>
    </select>
    <button class="border rounded-lg px-3 py-2">Filter</button>
  </form>

  <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($items as $c)
      <a href="{{ route('admin.companies.show',$c) }}" class="block rounded-xl border p-4 hover:shadow">
        <div class="text-sm text-slate-500">{{ $c->code }}</div>
        <div class="font-semibold text-lg">{{ $c->name }}</div>
        <div class="text-slate-500 text-sm">{{ $c->legal_name }}</div>
        <div class="mt-2 text-xs">Status: <span class="px-2 py-1 rounded bg-slate-100">{{ $c->status }}</span></div>
      </a>
    @empty
      <div class="text-slate-500">No companies.</div>
    @endforelse
  </div>

  <div>{{ $items->links() }}</div>
</div>
@endsection
