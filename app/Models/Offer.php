<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'application_id',
        'status',
        'salary',
        'body_template',
        'signed_path',
        'meta',
        'rejection_reason',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'salary' => 'array',
        'meta' => 'array',
        'rejected_at' => 'datetime',
    ];

    /** @return BelongsTo<JobApplication,Offer> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /** @return BelongsTo<User,Offer> */
    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}