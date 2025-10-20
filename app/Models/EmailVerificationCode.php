<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EmailVerificationCode extends Model
{
    use HasFactory;

    protected $table = 'email_verification_codes';

    // Cara 1 (disarankan): whitelist kolom yang diisi
    protected $fillable = [
        'user_id',
        'code_hash',
        'expires_at',
        'attempts',
        'last_sent_at',
    ];

    // ATAU Cara 2 (lebih longgar):
    // protected $guarded = [];

    // Primary key UUID
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'expires_at'   => 'datetime',
        'last_sent_at' => 'datetime',
        'attempts'     => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
