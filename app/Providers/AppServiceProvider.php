<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
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
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Reset Password Akun Andalan HR')
                ->greeting('Halo, ' . $notifiable->name)
                ->line('Kami menerima permintaan untuk mengatur ulang password akun Andalan HR kamu.')
                ->line('Klik tombol di bawah ini untuk membuat password baru. Link ini hanya berlaku selama 60 menit.')
                ->action('Reset Password', $url)
                ->line('Kalau kamu tidak meminta reset password, abaikan email ini. Password lama kamu tetap aman.');
        });

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
