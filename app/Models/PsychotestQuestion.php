<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasUuidPrimaryKey;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsychotestQuestion extends Model
{
    use HasFactory, HasUuidPrimaryKey;

    protected $fillable = [
        'test_id','type','question','options','answer_key','weight','order_no'
    ];

    protected $casts = [
        'options'  => 'array',
        'weight'   => 'decimal:2',
        'order_no' => 'integer',
    ];

    /** @return BelongsTo<PsychotestTest,PsychotestQuestion> */
    public function test(): BelongsTo { return $this->belongsTo(PsychotestTest::class, 'test_id'); }
}
