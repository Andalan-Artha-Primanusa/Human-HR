<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MobilisasiMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobApplication $application,
        public string $bodyContent,
        public ?string $ticketPath = null,
        public ?string $ticketName = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informasi Mobilisasi - ' . ($this->application->job->title ?? 'Posisi'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.mobilisasi',
            with: [
                'candidateName' => $this->application->user->name ?? 'Kandidat',
                'bodyContent' => $this->bodyContent,
            ],
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->ticketPath && Storage::disk('public')->exists($this->ticketPath)) {
            $attachments[] = Attachment::fromPath(Storage::disk('public')->path($this->ticketPath))
                ->as($this->ticketName ?? 'Tiket-Mobilisasi.pdf');
        }

        return $attachments;
    }
}
