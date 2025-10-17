@extends('layouts.app', [ 'title' => 'Admin · Audit Logs' ])

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-semibold">Audit Logs</h1>
    @if (!empty($tableMissing) && $tableMissing)
        <div class="mt-4 p-4 rounded-lg border border-amber-200 bg-amber-50 text-amber-800">
            Tabel <code>audit_logs</code> belum ada. Jalankan <code>php artisan migrate</code>.
        </div>
    @endif
</div>

@if (empty($tableMissing) || !$tableMissing)
<form method="get" class="mb-4 grid gap-3 sm:grid-cols-6">
    <input class="form-input sm:col-span-2" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari target_id / IP / User-Agent">
    <input class="form-input" type="text" name="event" value="{{ $filters['event'] ?? '' }}" placeholder="Event (created/updated/deleted)">
    <input class="form-input" type="text" name="user_id" value="{{ $filters['userId'] ?? '' }}" placeholder="User ID">
    <input class="form-input" type="text" name="target_type" value="{{ $filters['targetType'] ?? '' }}" placeholder="Target Type">
    <input class="form-input" type="date" name="from" value="{{ $filters['dateFrom'] ?? '' }}">
    <input class="form-input" type="date" name="to" value="{{ $filters['dateTo'] ?? '' }}">
    <div class="sm:col-span-6 flex gap-2">
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.audit_logs.export') }}" class="btn btn-outline">Export CSV</a>
    </div>
</form>

<div class="bg-white rounded-xl border overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-3 py-2 text-left">Time</th>
                <th class="px-3 py-2 text-left">Event</th>
                <th class="px-3 py-2 text-left">User</th>
                <th class="px-3 py-2 text-left">Target</th>
                <th class="px-3 py-2 text-left">IP</th>
                <th class="px-3 py-2"></th>
            </tr>
        </thead>
        <tbody>
        @forelse ($items as $it)
            <tr class="border-t">
                <td class="px-3 py-2 whitespace-nowrap">{{ $it->created_at }}</td>
                <td class="px-3 py-2">{{ $it->event }}</td>
                <td class="px-3 py-2">{{ $it->user->name ?? '-' }}</td>
                <td class="px-3 py-2">
                    <div class="text-slate-700">{{ $it->target_type ?? '-' }}</div>
                    <div class="text-slate-500 text-xs">{{ $it->target_id ?? '-' }}</div>
                </td>
                <td class="px-3 py-2">{{ $it->ip ?? '-' }}</td>
                <td class="px-3 py-2 text-right">
                    <a class="text-blue-600 hover:underline" href="{{ route('admin.audit_logs.show', $it->id) }}">Detail</a>
                </td>
            </tr>
        @empty
            <tr><td class="px-3 py-6 text-center text-slate-500" colspan="6">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endif
@endsection
