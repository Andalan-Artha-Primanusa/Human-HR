<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Site extends Model
{
    use HasFactory;

    // pakai UUID sebagai primary key
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'region', 'timezone', 'address',
        'is_active', 'meta', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID saat create
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    /* ===== Relationships ===== */
    public function jobs()
    {
        return $this->hasMany(Job::class);
    }
    
    /* ===== Scopes ===== */
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
