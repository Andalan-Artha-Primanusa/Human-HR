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

    protected $fillable = [
        'application_id','test_id','attempt_no','started_at','finished_at','score','is_active'
    ];

    protected $casts = [
        'attempt_no' => 'integer',
        'started_at' => 'datetime',
        'finished_at'=> 'datetime',
        'score'      => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    /** @return BelongsTo<JobApplication,PsychotestAttempt> */
    public function application(): BelongsTo { return $this->belongsTo(JobApplication::class, 'application_id'); }

    /** @return BelongsTo<PsychotestTest,PsychotestAttempt> */
    public function test(): BelongsTo { return $this->belongsTo(PsychotestTest::class, 'test_id'); }

    /** @return HasMany<PsychotestAnswer> */
    public function answers(): HasMany { return $this->hasMany(PsychotestAnswer::class, 'attempt_id'); }
}
