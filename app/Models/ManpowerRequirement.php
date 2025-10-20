<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasUuidPrimaryKey;

class ManpowerRequirement extends Model
{
    use HasUuidPrimaryKey;

    protected $fillable = [
        'job_id',
        'asset_name',              // baris per aset (opsional; boleh null untuk agregat)
        'assets_count',
        'ratio_per_asset',
        'budget_headcount',
        'filled_headcount',
    ];

    protected $casts = [
        'assets_count'     => 'integer',
        'ratio_per_asset'  => 'float',
        'budget_headcount' => 'integer',
        'filled_headcount' => 'integer',
    ];

    // Relasi yang dipakai
    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    // === Computed ===
    public function getComputedBudgetAttribute(): int
    {
        $assets = max(0, (int) $this->assets_count);
        $ratio  = max(0.0, (float) $this->ratio_per_asset);
        return (int) ceil($assets * $ratio);
    }

    // === Hooks ===
    protected static function booted(): void
    {
        // Set budget per baris dari rumus sebelum simpan
        static::saving(function (self $m) {
            $m->budget_headcount = $m->computed_budget;
        });

        // Setelah simpan/hapus: hitung total semua baris lalu sync ke jobs.openings
        $recalc = function (self $m) {
            if (!$m->job_id) return;

            $sum = static::query()
                ->where('job_id', $m->job_id)
                ->sum('budget_headcount');

            // update langsung agar tidak memicu event berantai yang tidak perlu
            $m->job()->update(['openings' => (int) $sum]);
        };

        static::saved($recalc);
        static::deleted($recalc);
    }
}
