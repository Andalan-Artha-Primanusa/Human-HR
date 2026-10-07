<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiSecurityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecurityTelemetryController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filtered($request)->latest('occurred_at');
        $sort = $request->string('sort', 'occurred_at')->toString();
        $direction = $request->string('direction', 'desc')->toString() === 'asc' ? 'asc' : 'desc';
        $allowed = ['occurred_at', 'status_code', 'response_time_ms', 'request_count_1m', 'unique_endpoint_count_1m'];
        $query->reorder()->orderBy(in_array($sort, $allowed, true) ? $sort : 'occurred_at', $direction);
        return response()->json($query->paginate(min(200, max(1, (int) $request->input('per_page', 50)))));
    }

    public function show(ApiSecurityLog $apiLog)
    {
        return response()->json($apiLog);
    }

    public function metrics(Request $request)
    {
        $today = ApiSecurityLog::where('traffic_type', 'api')->whereBetween('occurred_at', [now()->startOfDay(), now()->endOfDay()]);
        $recent = ApiSecurityLog::where('traffic_type', 'api')->where('occurred_at', '>=', now()->subMinutes(1));
        return response()->json([
            'requests_today' => (clone $today)->count(),
            'avg_request_per_minute' => round((float) (clone $recent)->count(), 2),
            'authentication_failures' => (clone $today)->where('authentication_status', 'failed')->count(),
            'high_frequency_requests' => (clone $today)->where('request_count_1m', '>=', 60)->count(),
            'unique_api_endpoints' => $this->registeredApiEndpointCount(),
            'active_actors' => (clone $recent)->whereNotNull('actor_hash')->distinct('actor_hash')->count('actor_hash'),
            'avg_response_time_ms' => round((float) ((clone $today)->avg('response_time_ms') ?? 0), 2),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->filtered($request)->latest('occurred_at')->limit(50000)->cursor();
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','request_id','occurred_at','actor_type','user_id','http_method','route_name','route_template','status_code','request_size','response_size','response_time_ms','authenticated','authentication_status','authorization_result','request_count_10s','request_count_1m','request_count_5m','same_endpoint_count_1m','unique_endpoint_count_1m','failed_auth_count_5m','user_role','user_agent_category','binary_label','attack_type','scenario_id']);
            foreach ($rows as $row) fputcsv($out, $row->only(['id','request_id','occurred_at','actor_type','user_id','http_method','route_name','route_template','status_code','request_size','response_size','response_time_ms','authenticated','authentication_status','authorization_result','request_count_10s','request_count_1m','request_count_5m','same_endpoint_count_1m','unique_endpoint_count_1m','failed_auth_count_5m','user_role','user_agent_category','binary_label','attack_type','scenario_id']));
            fclose($out);
        }, 'api-security-telemetry-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filtered(Request $request)
    {
        $q = ApiSecurityLog::query()->where('traffic_type', 'api');
        foreach (['http_method', 'route_name', 'authentication_status', 'user_role'] as $field) if ($request->filled($field)) $q->where($field, $request->input($field));
        if ($request->filled('status_code')) $q->where('status_code', $request->integer('status_code'));
        if ($request->filled('user_id')) $q->where('user_id', $request->input('user_id'));
        if ($request->filled('date_from')) $q->whereDate('occurred_at', '>=', $request->input('date_from'));
        if ($request->filled('date_to')) $q->whereDate('occurred_at', '<=', $request->input('date_to'));
        if ($request->filled('search')) $q->where(function ($x) use ($request) { $term = '%' . $request->input('search') . '%'; $x->where('route_template', 'like', $term)->orWhere('route_name', 'like', $term)->orWhere('request_id', 'like', $term); });
        foreach (['request_count_10s', 'request_count_1m', 'request_count_5m'] as $field) if ($request->filled('min_' . $field)) $q->where($field, '>=', (int) $request->input('min_' . $field));
        return $q;
    }

    private function registeredApiEndpointCount(): int
    {
        return collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => ! str_starts_with($route->uri(), '_ignition'))
            ->flatMap(fn ($route) => collect($route->methods())->reject(fn ($method) => in_array($method, ['HEAD', 'OPTIONS'], true))->map(fn ($method) => $method . ':' . $route->uri()))
            ->unique()
            ->count();
    }
}
