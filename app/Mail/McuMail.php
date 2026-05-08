<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class McuMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $bodyContent;
    public $mcuFilePath;

    /**
     * Create a new message instance.
     */
    public function __construct(JobApplication $application, $bodyContent = null, $mcuFilePath = null)
    {
        $this->application = $application;
        $this->bodyContent = $bodyContent;
        $this->mcuFilePath = $mcuFilePath;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $jobTitle = $this->application->job?->title ?? 'Posisi';
        return new Envelope(
            subject: 'Undangan Medical Check Up (MCU) - ' . $jobTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.mcu',
            with: [
                'candidateName' => $this->application->user?->name ?? 'Kandidat',
                'jobTitle' => $this->application->job?->title ?? 'Posisi',
                'companyName' => 'Andalan Careers',
                'bodyContent' => $this->bodyContent,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     * If mcuFilePath is provided, attach only that file (no PDF generation).
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // Only attach the uploaded MCU file, don't generate PDF
        if ($this->mcuFilePath) {
            $path = storage_path('app/public/' . $this->mcuFilePath);
            if (file_exists($path)) {
                return [
                    \Illuminate\Mail\Mailables\Attachment::fromPath($path)
                        ->as('UndanganMCU-' . $this->application->id . '.' . pathinfo($path, PATHINFO_EXTENSION))
                ];
            }
        }
        return [];
    }
}
