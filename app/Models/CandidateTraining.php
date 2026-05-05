<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CandidateTraining extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'candidate_profile_id',
        'title',
        'institution',
        'period_start',
        'period_end',
        'certificate_path',
        'certificate_name',
        'cert_valid_from',
        'cert_valid_to',
        'cert_no_expiry',
        'order_no',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'cert_valid_from' => 'date',
        'cert_valid_to' => 'date',
        'cert_no_expiry' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class, 'candidate_profile_id');
    }

    /** Urutkan default */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_no')->orderBy('created_at');
    }

    /** >>> Tambahkan ini <<< */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            // Pastikan PK terisi
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid(); // atau Str::orderedUuid()
            }

            // Kalau order_no tidak dikirim, isi otomatis max+1 per profile
            if (is_null($model->order_no)) {
                $max = static::where('candidate_profile_id', $model->candidate_profile_id)->max('order_no');
                $model->order_no = (int) $max + 1;
            }
        });
    }
}
