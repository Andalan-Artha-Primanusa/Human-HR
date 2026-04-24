<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasUuidPrimaryKey;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'id_employe',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token'
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |------------------------------------------
    | FIX: Mutator Email
    |------------------------------------------
    */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = is_null($value)
            ? null
            : mb_strtolower(trim($value));
    }

    /*
    |------------------------------------------
    | Helpers Role
    |------------------------------------------
    */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : explode('|', $roles);
        return in_array($this->role, array_map('trim', $roles), true);
    }

    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /*
    |------------------------------------------
    | Helpers Verifikasi
    |------------------------------------------
    */
    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /*
    |------------------------------------------
    | Scopes
    |------------------------------------------
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
    |------------------------------------------
    | Relasi
    |------------------------------------------
    */
    public function profile(): HasOne
    {
        return $this->hasOne(\App\Models\CandidateProfile::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(\App\Models\JobApplication::class);
    }

    public function candidateProfile(): HasOne
    {
        return $this->hasOne(\App\Models\CandidateProfile::class, 'user_id');
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }
}