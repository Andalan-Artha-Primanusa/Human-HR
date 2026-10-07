<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiSecurityLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityTelemetryController extends Controller
{
    public function index(Request $request)
    {
        $logs = $this->filtered($request)->latest('occurred_at')->paginate(50)->withQueryString();
        $today = ApiSecurityLog::whereDate('occurred_at', today());
        $metrics = [
            'requests_today' => (clone $today)->count(),
            'avg_request_per_minute' => (clone $today)->count() ? round((clone $today)->count() / max(1, now()->diffInMinutes(today()) + 1), 2) : 0,
            'authentication_failures' => (clone $today)->where('authentication_status', 'failed')->count(),
            'high_frequency_requests' => (clone $today)->where('request_count_1m', '>=', 60)->count(),
            'unique_api_endpoints' => (clone $today)->distinct('route_template')->count('route_template'),
            'active_actors' => (clone $today)->whereNotNull('actor_hash')->distinct('actor_hash')->count('actor_hash'),
        ];
        return view('admin.security.api-activity', compact('logs', 'metrics'));
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->filtered($request)->latest('occurred_at')->limit(50000)->cursor();
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['occurred_at','actor_type','user_id','http_method','route_name','route_template','status_code','request_size','response_size','response_time_ms','authenticated','authentication_status','authorization_result','request_count_10s','request_count_1m','request_count_5m','same_endpoint_count_1m','unique_endpoint_count_1m','failed_auth_count_5m','user_role','user_agent_category','binary_label','attack_type','scenario_id']);
            foreach ($rows as $row) fputcsv($out, $row->only(['occurred_at','actor_type','user_id','http_method','route_name','route_template','status_code','request_size','response_size','response_time_ms','authenticated','authentication_status','authorization_result','request_count_10s','request_count_1m','request_count_5m','same_endpoint_count_1m','unique_endpoint_count_1m','failed_auth_count_5m','user_role','user_agent_category','binary_label','attack_type','scenario_id']));
            fclose($out);
        }, 'api-security-telemetry-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request)
    {
        $q = ApiSecurityLog::query();
        foreach (['http_method', 'route_name', 'authentication_status', 'user_role'] as $field) if ($request->filled($field)) $q->where($field, $request->input($field));
        if ($request->filled('status_code')) $q->where('status_code', $request->integer('status_code'));
        if ($request->filled('date_from')) $q->whereDate('occurred_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to')) $q->whereDate('occurred_at', '<=', $request->input('date_to'));
        if ($request->filled('search')) $q->where(function ($x) use ($request) { $term = '%' . $request->input('search') . '%'; $x->where('route_template', 'like', $term)->orWhere('route_name', 'like', $term)->orWhere('request_id', 'like', $term); });
        foreach (['request_count_10s', 'request_count_1m', 'request_count_5m'] as $field) if ($request->filled('min_' . $field)) $q->where($field, '>=', (int) $request->input('min_' . $field));
        return $q;
    }
}
