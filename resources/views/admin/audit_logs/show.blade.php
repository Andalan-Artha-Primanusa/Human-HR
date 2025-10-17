@extends('layouts.app', [ 'title' => 'Admin · Audit Log Detail' ])

@section('content')
<h1 class="text-2xl font-semibold mb-4">Audit Log Detail</h1>

<div class="grid gap-4 md:grid-cols-2">
  <div class="bg-white border rounded-xl p-4">
    <div class="text-sm text-slate-500">Waktu</div>
    <div class="font-medium">{{ $log->created_at }}</div>

    <div class="mt-3 text-sm text-slate-500">User</div>
    <div class="font-medium">{{ $log->user->name ?? '-' }} ({{ $log->user_id ?? '-' }})</div>

    <div class="mt-3 text-sm text-slate-500">Event</div>
    <div class="font-medium">{{ $log->event }}</div>

    <div class="mt-3 text-sm text-slate-500">Target</div>
    <div class="font-medium">{{ $log->target_type ?? '-' }} — {{ $log->target_id ?? '-' }}</div>

    <div class="mt-3 text-sm text-slate-500">IP / UA</div>
    <div class="font-medium">{{ $log->ip ?? '-' }}</div>
    <div class="text-xs text-slate-500 break-all">{{ $log->user_agent ?? '-' }}</div>
  </div>

  <div class="bg-white border rounded-xl p-4">
    <div class="text-sm text-slate-500 mb-1">Before</div>
    <pre class="text-xs bg-slate-50 p-3 rounded-lg overflow-auto">{{ json_encode($log->before, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
    <div class="text-sm text-slate-500 mt-4 mb-1">After</div>
    <pre class="text-xs bg-slate-50 p-3 rounded-lg overflow-auto">{{ json_encode($log->after, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
  </div>
</div>

<div class="mt-6">
  <a href="{{ route('admin.audit_logs.index') }}" class="btn btn-outline">Kembali</a>
</div>
@endsection
