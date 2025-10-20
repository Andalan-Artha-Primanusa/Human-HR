<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use App\Listeners\SendEmailVerificationCode;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [SendEmailVerificationCode::class],
    ];

    // Pastikan discovery dimatikan jika Anda pakai $listen
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
