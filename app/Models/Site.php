<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = ['code','name','region','is_active','meta'];

    protected $casts = [
        'is_active' => 'boolean',
        'meta'      => 'array',
    ];

    /** @return HasMany<Job> */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function scopeActive($q){ return $q->where('is_active', true); }
}
