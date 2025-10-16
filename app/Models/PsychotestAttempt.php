<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PsychotestAttempt extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    /**
     * Kolom yang boleh di-mass assign.
     */
    protected $fillable = [
        'application_id',
        'test_id',
        'user_id',
        'attempt_no',
        'status',
        'started_at',
        'finished_at',
        'submitted_at',
        'expires_at',
        'score',
        'is_active',
        'meta',
    ];

    /**
     * Casting kolom.
     */
    protected $casts = [
        'attempt_no'   => 'integer',
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
        'submitted_at' => 'datetime',
        'expires_at'   => 'datetime',
        'score'        => 'decimal:2',
        'is_active'    => 'boolean',
        'meta'         => 'array',   // penting utk hindari "Array to string conversion"
    ];

    /**
     * Default value saat creating.
     */
    protected static function booted(): void
    {
        static::creating(function (self $m) {
            $m->attempt_no  ??= 1;
            $m->is_active   ??= true;
            $m->status      ??= 'pending'; // allowed: pending|in_progress|submitted|scored|expired|cancelled
        });
    }

    /** @return BelongsTo<JobApplication, self> */
    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    /** @return BelongsTo<PsychotestTest, self> */
    public function test(): BelongsTo
    {
        return $this->belongsTo(PsychotestTest::class, 'test_id');
    }

    /** @return BelongsTo<User, self> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<PsychotestAnswer> */
    public function answers(): HasMany
    {
        return $this->hasMany(PsychotestAnswer::class, 'attempt_id');
    }

    /* --- Scopes opsional --- */

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeForApp($q, $applicationId)
    {
        return $q->where('application_id', $applicationId);
    }
}
