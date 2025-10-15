<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateExperience extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'candidate_profile_id','company','title','start_date','end_date','is_current','description'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_current' => 'boolean',
    ];

    /** @return BelongsTo<CandidateProfile,CandidateExperience> */
    public function profile(): BelongsTo { return $this->belongsTo(CandidateProfile::class, 'candidate_profile_id'); }
}
