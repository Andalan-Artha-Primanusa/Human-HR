<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateTraining extends Model
{
    use HasFactory, HasUuids;

    /** UUID primary key */
    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'candidate_profile_id',
        'title',
        'institution',
        'period_start',
        'period_end',
        'certificate_path',
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

    /** Urutkan default berdasarkan order_no, lalu created_at */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_no')->orderBy('created_at');
    }
}
