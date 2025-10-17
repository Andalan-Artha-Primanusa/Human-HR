<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditLog extends Model
{
    use HasUuids;

    protected $table = 'audit_logs';
    protected $fillable = [
        'user_id','event','target_type','target_id','ip','user_agent','before','after',
    ];

    protected $casts = [
        'before' => 'array',
        'after'  => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
