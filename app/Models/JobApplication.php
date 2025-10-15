<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplication extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = ['job_id','user_id','current_stage','overall_status'];

    /** @return BelongsTo<Job,JobApplication> */
    public function job(): BelongsTo { return $this->belongsTo(Job::class); }

    /** @return BelongsTo<User,JobApplication> */
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** @return HasMany<ApplicationStage> */
    public function stages(): HasMany { return $this->hasMany(ApplicationStage::class, 'application_id'); }

    /** @return HasMany<Interview> */
    public function interviews(): HasMany { return $this->hasMany(Interview::class, 'application_id'); }

    /** @return HasMany<PsychotestAttempt> */
    public function psychotestAttempts(): HasMany { return $this->hasMany(PsychotestAttempt::class, 'application_id'); }

    /** @return HasOne<Offer> */
    public function offer(): HasOne { return $this->hasOne(Offer::class, 'application_id'); }
}
