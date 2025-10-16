<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateEmployment extends Model
{
    use HasFactory, HasUuids;

    /** UUID primary key */
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'candidate_profile_id',
        'company',
        'position_start',
        'position_end',
        'period_start',
        'period_end',
        'reason_for_leaving',
        'job_description',
        'order_no',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_profile_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_no')->orderBy('created_at');
    }

    /** Contoh accessor ringkas periode kerja */
    public function getPeriodLabelAttribute(): string
    {
        $start = $this->period_start?->format('M Y');
        $end   = $this->period_end?->format('M Y') ?? 'Sekarang';
        return trim(($start ?: '-') . ' - ' . $end);
    }
}
