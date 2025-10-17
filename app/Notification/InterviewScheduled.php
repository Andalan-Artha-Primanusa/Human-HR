<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Interview;

class InterviewScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Interview $interview) {}

    public function via($notifiable): array
    {
        return ['database']; // tambahkan 'mail' jika ingin email
    }

    public function toArray($notifiable): array
    {
        $iv  = $this->interview;
        $job = $iv->application->job ?? null;

        return [
            'type'          => 'interview_scheduled',
            'interview_id'  => $iv->id,
            'title'         => $iv->title,
            'job_title'     => $job?->title,
            'site_name'     => $job?->site?->name,
            'mode'          => $iv->mode,
            'location'      => $iv->location,
            'meeting_link'  => $iv->meeting_link,
            'start_at'      => optional($iv->start_at)->toIso8601String(),
            'end_at'        => optional($iv->end_at)->toIso8601String(),
            'notes'         => $iv->notes,
            'cta_url'       => route('me.interviews.show', $iv->id),
        ];
    }

    public function toMail($notifiable)
    {
        $iv  = $this->interview;
        $job = $iv->application->job ?? null;

        return (new MailMessage)
            ->subject('Jadwal Interview: '.$iv->title.' — '.$job?->title)
            ->greeting('Halo, '.$notifiable->name)
            ->line('Kamu dijadwalkan interview untuk posisi: '.$job?->title)
            ->line('Judul: '.$iv->title)
            ->line('Waktu: '.optional($iv->start_at)->format('d M Y H:i').' - '.optional($iv->end_at)->format('H:i'))
            ->line('Mode: '.strtoupper($iv->mode))
            ->line('Lokasi/Link: '.($iv->mode === 'online' ? ($iv->meeting_link ?: '-') : ($iv->location ?: '-')))
            ->action('Lihat Detail', route('me.interviews.show', $iv->id))
            ->line('Terima kasih.');
    }
}
