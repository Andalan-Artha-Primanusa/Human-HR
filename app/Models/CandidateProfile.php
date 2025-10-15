<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateProfile extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'user_id','full_name','phone','birthdate','address','cv_path','extras'
    ];

    protected $casts = [
        'birthdate' => 'date',
        'extras'    => 'array',
    ];

    /** @return BelongsTo<User,CandidateProfile> */
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** @return HasMany<CandidateExperience> */
    public function experiences(): HasMany { return $this->hasMany(CandidateExperience::class); }
}
