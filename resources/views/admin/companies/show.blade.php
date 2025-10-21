@extends('layouts.app', ['title' => $record->name])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
  <div class="rounded-2xl border p-6 bg-white space-y-2">
    <div class="text-sm text-slate-500">{{ $record->code }}</div>
    <h1 class="text-2xl font-semibold">{{ $record->name }}</h1>
    @if($record->legal_name)
      <div class="text-slate-600">{{ $record->legal_name }}</div>
    @endif
    <div class="text-sm">Status: <span class="px-2 py-1 rounded bg-slate-100">{{ $record->status }}</span></div>
    <div class="grid md:grid-cols-2 gap-2 text-sm">
      <div>Email: {{ $record->email ?: '—' }}</div>
      <div>Phone: {{ $record->phone ?: '—' }}</div>
      <div>Website: {{ $record->website ?: '—' }}</div>
      <div>Jobs: {{ $record->jobs_count }}</div>
    </div>
    @if($record->address)
      <div class="mt-3 text-sm whitespace-pre-line">{{ $record->address }}</div>
    @endif
  </div>

  <div class="flex gap-3">
    <a href="{{ route('admin.companies.edit', $record) }}" class="px-4 py-2 rounded-lg border">Edit</a>
    <form method="post" action="{{ route('admin.companies.destroy',$record) }}" onsubmit="return confirm('Delete this company?')">
      @csrf @method('DELETE')
      <button class="px-4 py-2 rounded-lg border text-red-600">Delete</button>
    </form>
  </div>
</div>
@endsection
