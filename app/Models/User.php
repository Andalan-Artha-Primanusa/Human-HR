<?php

namespace App\Models;

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
        'role', // pelamar|hr|superadmin
    ];

    /** Hidden */
    protected $hidden = ['password','remember_token'];

    /** Casts */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /** Helpers role */
    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : explode('|', $roles);
        return in_array($this->role, array_map('trim', $roles), true);
    }
    public function isHr(): bool         { return $this->role === 'hr'; }
    public function isSuperadmin(): bool { return $this->role === 'superadmin'; }

    /** Relasi */
    public function profile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
      public function candidateProfile()
    {
        return $this->hasOne(CandidateProfile::class, 'user_id');
    }

}
