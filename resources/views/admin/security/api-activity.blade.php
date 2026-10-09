@extends('layouts.app')

@section('title', 'API Activity Monitoring')

@section('content')
<div class="max-w-7xl px-4 py-6 mx-auto space-y-5">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div><h1 class="text-2xl font-bold text-slate-900">API Activity Monitoring</h1><p class="mt-1 text-sm text-slate-500">Telemetry perilaku request API dan autentikasi.</p></div>
    <a href="{{ route('admin.security.api-activity.export', request()->query()) }}" class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-[#b28a57]">Export CSV</a>
  </div>
  <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-6">
    @foreach(['requests_today'=>'Requests Today','avg_request_per_minute'=>'Avg Request/Minute','authentication_failures'=>'Authentication Failures','high_frequency_requests'=>'High Frequency Requests','unique_api_endpoints'=>'Unique API/Web Endpoints','active_actors'=>'Active Actors'] as $key => $label)
      <div class="p-4 bg-white border rounded-xl border-slate-200"><div class="text-xs text-slate-500">{{ $label }}</div><div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($metrics[$key] ?? 0) }}</div></div>
    @endforeach
  </div>
  <form class="grid grid-cols-1 gap-3 p-4 bg-white border rounded-xl border-slate-200 md:grid-cols-4" method="GET">
    <input name="search" value="{{ request('search') }}" placeholder="Cari route / request ID" class="rounded-lg border-slate-300">
    <input name="ip_hash" value="{{ request('ip_hash') }}" placeholder="Cari IP hash" class="rounded-lg border-slate-300">
    <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-slate-300">
    <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-slate-300">
    <select name="traffic_type" class="rounded-lg border-slate-300"><option value="">Web + API</option><option value="web" @selected(request('traffic_type')==='web')>Web</option><option value="api" @selected(request('traffic_type')==='api')>API</option></select>
    <select name="http_method" class="rounded-lg border-slate-300"><option value="">Semua Method</option>@foreach(['GET','POST','PUT','PATCH','DELETE'] as $method)<option value="{{ $method }}" @selected(request('http_method')===$method)>{{ $method }}</option>@endforeach</select>
    <select name="authentication_status" class="rounded-lg border-slate-300"><option value="">Semua autentikasi</option>@foreach(['success','failed','anonymous'] as $s)<option value="{{ $s }}" @selected(request('authentication_status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
    <button class="px-4 py-2 font-semibold text-white rounded-lg bg-[#b28a57] md:col-span-4">Terapkan Filter</button>
  </form>
  <div class="overflow-x-auto bg-white border rounded-xl border-slate-200"><table class="w-full text-sm"><thead class="text-left bg-slate-50"><tr><th class="p-3">Waktu</th><th class="p-3">Sumber</th><th class="p-3">IP Hash</th><th class="p-3">Event</th><th class="p-3">Route</th><th class="p-3">Status</th><th class="p-3">Auth</th><th class="p-3">Rate</th><th class="p-3">Response</th><th class="p-3">Actor</th></tr></thead><tbody>
    @forelse($logs as $log)<tr class="border-t border-slate-100"><td class="p-3 whitespace-nowrap">{{ optional($log->occurred_at)->format('d M Y H:i:s') }}</td><td class="p-3 uppercase">{{ $log->traffic_type }}</td><td class="p-3 font-mono text-xs" title="{{ $log->ip_hash }}">{{ $log->ip_hash ? substr($log->ip_hash, 0, 12).'…' : '-' }}</td><td class="p-3 font-semibold">{{ $log->http_method }} {{ $log->authorization_result ?: 'request' }}</td><td class="p-3 max-w-[28rem] truncate" title="{{ $log->route_template }}">{{ $log->route_name ?: $log->route_template }}</td><td class="p-3"><span class="px-2 py-1 text-xs rounded-full {{ $log->status_code >= 400 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $log->status_code }}</span></td><td class="p-3">{{ $log->authentication_status }}</td><td class="p-3 whitespace-nowrap">10s {{ $log->request_count_10s }} · 1m {{ $log->request_count_1m }} · 5m {{ $log->request_count_5m }}</td><td class="p-3">{{ $log->response_time_ms }} ms</td><td class="p-3">{{ $log->actor_type }}{{ $log->user_role ? ' · '.$log->user_role : '' }}</td></tr>@empty<tr><td colspan="10" class="p-8 text-center text-slate-500">Belum ada telemetry.</td></tr>@endforelse
  </tbody></table></div>
  {{ $logs->links() }}
</div>
@endsection

