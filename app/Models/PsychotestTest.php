<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PsychotestTest extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = ['name','duration_minutes','scoring'];

    protected $casts = [
        'duration_minutes' => 'integer',
        'scoring'          => 'array',
    ];

    /** @return HasMany<PsychotestQuestion> */
    public function questions(): HasMany { return $this->hasMany(PsychotestQuestion::class, 'test_id'); }
}
