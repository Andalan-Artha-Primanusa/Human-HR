<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateReference extends Model
{
    use HasFactory, HasUuids;

    /** UUID primary key */
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'candidate_profile_id',
        'name',
        'job_title',
        'company',
        'contact',
        'order_no',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_profile_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_no')->orderBy('created_at');
    }
}
