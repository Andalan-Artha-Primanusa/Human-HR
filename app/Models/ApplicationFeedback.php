<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApplicationFeedback extends Model
{
    use HasUuids;

    protected $table = 'application_feedbacks';
    protected $fillable = [
        'application_id',
        'stage_key',
        'role',
        'feedback',
        'approve',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }
}
