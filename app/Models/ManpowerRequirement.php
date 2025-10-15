<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManpowerRequirement extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = ['job_id','budget_headcount','filled_headcount'];

    /** @return BelongsTo<Job,ManpowerRequirement> */
    public function job(): BelongsTo { return $this->belongsTo(Job::class); }
}
