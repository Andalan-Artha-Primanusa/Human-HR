<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationCodeNotification extends Notification
{
    public function __construct(
        public string $code,
        public int $ttlMinutes
    ) {
        $this->ttlMinutes = max(1, (int) $ttlMinutes);
        $this->code = preg_replace('/\D+/', '', $code) ?: $code;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $app      = config('app.name', 'HUMAN Careers');
        $fromAddr = config('mail.from.address');
        $fromName = config('mail.from.name', $app);
        $replyTo  = config('mail.reply_to.address', $fromAddr);

        $displayCode = trim(chunk_split($this->code, 3, ' ')); // "123 456"

        return (new MailMessage)
            ->from($fromAddr, $fromName)
            ->replyTo($replyTo, $fromName)
            ->subject("Kode Verifikasi Anda • {$app}")
            ->view('mail.verify-code', [
                'appName'     => $app,
                'userName'    => $notifiable->name ?? null,
                'code'        => $this->code,
                'codePretty'  => $displayCode,
                'ttlMinutes'  => $this->ttlMinutes,
                'support'     => $fromAddr,
            ]);
    }
}
