<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, HasUuidPrimaryKey, SoftDeletes;

    protected $fillable = [
        'code','name','legal_name','email','phone','website','logo_path',
        'address','city','province','country','status','meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /** Relasi: satu company punya banyak job */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /** Scope pencarian sederhana */
    public function scopeSearch($q, ?string $term)
    {
        $term = trim((string) $term);
        if ($term === '') return $q;

        return $q->where(function($w) use ($term) {
            $w->where('name','like',"%{$term}%")
              ->orWhere('code','like',"%{$term}%")
              ->orWhere('legal_name','like',"%{$term}%");
        });
    }

    public function scopeActive($q) { return $q->where('status','active'); }
}
