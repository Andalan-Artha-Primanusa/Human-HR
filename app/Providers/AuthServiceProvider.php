<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Job;
use App\Models\JobApplication;
use App\Policies\JobPolicy;
use App\Policies\JobApplicationPolicy;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Map policies (UUID-friendly)
        Gate::policy(Job::class, JobPolicy::class);
        Gate::policy(JobApplication::class, JobApplicationPolicy::class);
    }
}
