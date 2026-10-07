<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Redis;

class RequestCounterService
{
    public function snapshot(string $actorKey, string $endpointKey, bool $failedAuth = false): array
    {
        $now = microtime(true);
        $member = bin2hex(random_bytes(12));
        $base = 'security:api:' . hash('sha256', $actorKey);

        try {
            $redis = Redis::connection();
            $keys = [
                '10s' => $base . ':requests:10s',
                '1m' => $base . ':requests:1m',
                '5m' => $base . ':requests:5m',
                'endpoint' => $base . ':endpoint:1m:' . hash('sha256', $endpointKey),
                'unique' => $base . ':unique:1m',
                'auth' => $base . ':auth:5m',
            ];
            foreach ([$keys['10s'] => 10, $keys['1m'] => 60, $keys['5m'] => 300, $keys['endpoint'] => 60] as $key => $ttl) {
                $redis->zadd($key, $now, $member);
                $redis->zremrangebyscore($key, '-inf', $now - $ttl);
                $redis->expire($key, $ttl + 5);
            }
            $redis->zadd($keys['unique'], $now, hash('sha256', $endpointKey));
            $redis->zremrangebyscore($keys['unique'], '-inf', $now - 60);
            $redis->expire($keys['unique'], 65);
            if ($failedAuth) {
                $redis->zadd($keys['auth'], $now, $member);
                $redis->zremrangebyscore($keys['auth'], '-inf', $now - 300);
                $redis->expire($keys['auth'], 305);
            }
            return [
                'request_count_10s' => (int) $redis->zcard($keys['10s']),
                'request_count_1m' => (int) $redis->zcard($keys['1m']),
                'request_count_5m' => (int) $redis->zcard($keys['5m']),
                'same_endpoint_count_1m' => (int) $redis->zcard($keys['endpoint']),
                'unique_endpoint_count_1m' => (int) $redis->zcard($keys['unique']),
                'failed_auth_count_5m' => (int) $redis->zcard($keys['auth']),
            ];
        } catch (\Throwable) {
            return array_fill_keys(['request_count_10s', 'request_count_1m', 'request_count_5m', 'same_endpoint_count_1m', 'unique_endpoint_count_1m', 'failed_auth_count_5m'], 0);
        }
    }
}
