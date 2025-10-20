<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;

class SendEmailVerificationCode
{
    public function handle(Registered $event): void
    {
        $user = $event->user;
        if ($user->email_verified_at) return;

        $ttl      = (int) config('auth.verify_code_ttl', (int) env('VERIFY_CODE_TTL', 10));     // menit
        $cooldown = (int) config('auth.verify_resend_cooldown', (int) env('VERIFY_RESEND_COOLDOWN', 60)); // detik
        $now      = now();

        // Anti dobel: lock 30 detik
        $lock = Cache::lock('reg-otp:'.$user->id, 30);
        if (!$lock->get()) return;

        try {
            DB::transaction(function () use ($user, $ttl, $cooldown, $now) {
                $row = \App\Models\EmailVerificationCode::where('user_id', $user->id)->lockForUpdate()->first();

                // Jika baru saja kirim dan belum lewat cooldown → stop (hindari spam / event ganda)
                if ($row && $row->last_sent_at && $now->diffInSeconds($row->last_sent_at) < $cooldown) {
                    return;
                }

                $plain = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                $data = [
                    'code_hash'    => Hash::make($plain),
                    'expires_at'   => $now->copy()->addMinutes($ttl),
                    'attempts'     => 0,
                    'last_sent_at' => $now,
                ];

                if ($row) {
                    $row->fill($data)->save();
                } else {
                    \App\Models\EmailVerificationCode::create($data + ['user_id' => $user->id]);
                }

                // KIRIM SINKRON (tanpa queue)
                Notification::sendNow($user, new \App\Notifications\EmailVerificationCodeNotification($plain, $ttl));
            });
        } finally {
            optional($lock)->release();
        }
    }
}
