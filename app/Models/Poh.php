<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;

class Poh extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'name',
        'code',
        'address',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
