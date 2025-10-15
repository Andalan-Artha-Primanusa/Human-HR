<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = ['label','path','mime','size_bytes'];

    /** @return MorphTo */
    public function attachable(): MorphTo { return $this->morphTo(); }
}
