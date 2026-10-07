<?php

namespace App\Models;

use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Model;

class ApiSecurityLog extends Model
{
    use HasUuidPrimaryKey;

    protected $fillable = [
        'request_id', 'occurred_at', 'actor_type', 'user_id', 'actor_hash',
        'http_method', 'route_name', 'route_template', 'status_code',
        'request_size', 'response_size', 'response_time_ms', 'authenticated',
        'authentication_status', 'authorization_result', 'request_count_10s',
        'request_count_1m', 'request_count_5m', 'same_endpoint_count_1m',
        'unique_endpoint_count_1m', 'failed_auth_count_5m', 'user_role',
        'ip_hash', 'user_agent_category', 'binary_label', 'attack_type', 'scenario_id',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'authenticated' => 'boolean',
            'status_code' => 'integer',
            'request_size' => 'integer',
            'response_size' => 'integer',
            'response_time_ms' => 'integer',
            'request_count_10s' => 'integer',
            'request_count_1m' => 'integer',
            'request_count_5m' => 'integer',
            'same_endpoint_count_1m' => 'integer',
            'unique_endpoint_count_1m' => 'integer',
            'failed_auth_count_5m' => 'integer',
            'binary_label' => 'integer',
        ];
    }
}
