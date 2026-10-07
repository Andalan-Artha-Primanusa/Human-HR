<?php

namespace App\Http\Middleware;

use App\Models\ApiSecurityLog;
use App\Services\Security\RequestCounterService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiSecurityTelemetry
{
    public function __construct(private RequestCounterService $counters) {}

    public function handle(Request $request, Closure $next)
    {
        $started = microtime(true);
        $response = $next($request);

        try {
            $route = $request->route();
            $template = $route?->uri() ?: $this->fallbackTemplate($request->path());
            $routeName = $route?->getName();
            $user = $request->user();
            $ipHash = $this->hashValue((string) $request->ip());
            $actorHash = $user ? null : $ipHash;
            $actorKey = $user ? 'user:' . $user->getAuthIdentifier() : 'guest:' . $ipHash;
            $status = (int) $response->getStatusCode();
            $isLoginFailure = str_contains(strtolower((string) ($routeName ?: $request->path())), 'login') && $status >= 400 && $status < 500;
            $failedAuth = $status === 401 || $isLoginFailure;
            $metrics = $this->counters->snapshot($actorKey, $request->method() . ':' . $template, $failedAuth);
            $body = $response->getContent();
            ApiSecurityLog::create(array_merge($metrics, [
                'request_id' => (string) ($request->header('X-Request-ID') ?: Str::uuid()),
                'occurred_at' => now(),
                'actor_type' => $user ? 'user' : 'guest',
                'user_id' => $user?->getAuthIdentifier(),
                'actor_hash' => $actorHash,
                'http_method' => strtoupper($request->method()),
                'route_name' => $routeName,
                'route_template' => '/' . ltrim($template, '/'),
                'status_code' => $status,
                'request_size' => (int) ($request->header('Content-Length') ?: strlen((string) $request->getContent())),
                'response_size' => strlen((string) $body),
                'response_time_ms' => (int) round((microtime(true) - $started) * 1000),
                'authenticated' => (bool) $user,
                'authentication_status' => $user ? 'success' : ($failedAuth ? 'failed' : 'anonymous'),
                'authorization_result' => $status === 403 ? 'denied' : ($failedAuth ? 'auth_failed' : ($status >= 500 ? 'error' : ($status < 400 ? 'allowed' : 'rejected'))),
                'user_role' => $user?->role,
                'ip_hash' => $ipHash,
                'user_agent_category' => $this->userAgentCategory((string) $request->userAgent()),
            ]));
        } catch (\Throwable) {
            // Telemetry must never change the API business response.
        }

        return $response;
    }

    private function hashValue(string $value): string
    {
        return hash('sha256', config('app.key', '') . '|' . $value);
    }

    private function fallbackTemplate(string $path): string
    {
        return preg_replace(['/\/[0-9a-f]{8}-[0-9a-f-]{27,36}/i', '/\/\d+(?=\/|$)/'], '/{id}', '/' . ltrim($path, '/')) ?: '/' . ltrim($path, '/');
    }

    private function userAgentCategory(string $ua): string
    {
        $ua = strtolower($ua);
        if ($ua === '') return 'unknown';
        if (str_contains($ua, 'bot') || str_contains($ua, 'spider') || str_contains($ua, 'crawler')) return 'bot';
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) return 'mobile';
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) return 'tablet';
        return 'browser';
    }
}
