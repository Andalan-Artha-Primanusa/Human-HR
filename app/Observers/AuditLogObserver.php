<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\AuditLog;

class AuditLogObserver
{
    public function created(Model $model)
    {
        $this->log('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model)
    {
        $this->log('updated', $model, $model->getOriginal(), $model->getAttributes());
    }

    public function deleted(Model $model)
    {
        $this->log('deleted', $model, $model->getOriginal(), null);
    }

    protected function log($event, Model $model, $before, $after)
    {
        try {
            AuditLog::create([
                'user_id'     => Auth::id(),
                'event'       => $event,
                'target_type' => get_class($model),
                'target_id'   => $model->getKey(),
                'ip'          => Request::ip(),
                'user_agent'  => Request::header('User-Agent'),
                'before'      => $before,
                'after'       => $after,
            ]);
        } catch (\Throwable $e) {
            // Optional: log error or ignore
        }
    }
}
