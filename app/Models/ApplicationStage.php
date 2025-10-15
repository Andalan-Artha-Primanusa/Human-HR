<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStage extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = ['application_id','stage_key','status','score','payload'];

    protected $casts = [
        'score'   => 'decimal:2',
        'payload' => 'array',
    ];

    /** @return BelongsTo<JobApplication,ApplicationStage> */
    public function application(): BelongsTo { return $this->belongsTo(JobApplication::class, 'application_id'); }
}
