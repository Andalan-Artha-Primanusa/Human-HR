<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Offer extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'application_id','status','salary','body_template','signed_path'
    ];

    protected $casts = [
        'salary' => 'array',
    ];

    /** @return BelongsTo<JobApplication,Offer> */
    public function application(): BelongsTo { return $this->belongsTo(JobApplication::class, 'application_id'); }
}
