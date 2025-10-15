<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychotestAnswer extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = ['attempt_id','question_id','answer','is_correct'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /** @return BelongsTo<PsychotestAttempt,PsychotestAnswer> */
    public function attempt(): BelongsTo { return $this->belongsTo(PsychotestAttempt::class, 'attempt_id'); }

    /** @return BelongsTo<PsychotestQuestion,PsychotestAnswer> */
    public function question(): BelongsTo { return $this->belongsTo(PsychotestQuestion::class, 'question_id'); }
}
