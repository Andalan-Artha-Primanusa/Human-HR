<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // ⬅️ penting utk verified
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use App\Models\Concerns\HasUuidPrimaryKey; // UUID PK
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuidPrimaryKey;

    /** Mass assignable */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',       // pelamar|hr|superadmin
        'id_employe', // Nomor/NIK karyawan (ikuti ejaan kolom di DB)
    ];

    /** Hidden */
    protected $hidden = ['password', 'remember_token'];

    /** Casts */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Mutators / Normalizers
    |--------------------------------------------------------------------------
    */
    // Pastikan email selalu lowercase (biar login case-insensitive & unik)
    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = is_null($value) ? null : mb_strtolower(trim($value));
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers Role
    |--------------------------------------------------------------------------
    */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : explode('|', $roles);
        return in_array($this->role, array_map('trim', $roles), true);
    }
    public function isHr(): bool         { return $this->role === 'hr'; }
    public function isSuperadmin(): bool { return $this->role === 'superadmin'; }

    /*
    |--------------------------------------------------------------------------
    | Helpers Verifikasi
    |--------------------------------------------------------------------------
    */
    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeVerified($q)
    {
        return $q->whereNotNull('email_verified_at');
    }

    public function scopeUnverified($q)
    {
        return $q->whereNull('email_verified_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi
    |--------------------------------------------------------------------------
    */
    public function profile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    // Alias lain (kalau masih dipakai di beberapa tempat)
    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class, 'user_id');
    }
}
