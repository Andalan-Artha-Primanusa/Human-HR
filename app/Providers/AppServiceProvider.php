<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register AuditLogObserver for all main models
        \App\Models\User::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Job::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\CandidateProfile::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\JobApplication::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Interview::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Offer::observe(\App\Observers\AuditLogObserver::class);
        \App\Models\Poh::observe(\App\Observers\AuditLogObserver::class);
    }
}
