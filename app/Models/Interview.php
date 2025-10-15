<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'application_id','title','mode','location','meeting_link','start_at','end_at','panel','notes'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'panel'    => 'array',
    ];

    /** @return BelongsTo<JobApplication,Interview> */
    public function application(): BelongsTo { return $this->belongsTo(JobApplication::class, 'application_id'); }
}
