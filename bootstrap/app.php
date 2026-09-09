<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        if (! (bool) config('services.minepro.rfr_auto_sync_enabled', false)) {
            return;
        }

        $frequencyMinutes = max(1, (int) config('services.minepro.rfr_auto_sync_minutes', 10));

        $schedule->command('minepro:rfr-sync --queue')
            ->cron("*/{$frequencyMinutes} * * * *")
            ->name('minepro-rfr-auto-sync');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware kita sendiri
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'api.token' => \App\Http\Middleware\EnsureApiToken::class,
            'public.api.login' => \App\Http\Middleware\EnsurePublicApiLogin::class,
            'candidate.complete' => \App\Http\Middleware\EnsureCandidateProfileComplete::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\AddSecurityHeaders::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\AddSecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
