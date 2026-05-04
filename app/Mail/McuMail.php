<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class McuMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $application;
    public $bodyContent;

    /**
     * Create a new message instance.
     */
    public function __construct(JobApplication $application, $bodyContent = null)
    {
        $this->application = $application;
        $this->bodyContent = $bodyContent;
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
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $application = $this->application->loadMissing('user', 'job', 'job.site');
        $mcu_meta = $application->mcu_meta ?? [];
        
        $html = view('mcu.pdf', compact('application', 'mcu_meta'))->render();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4')->setWarnings(false);

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $pdf->output(), 'UndanganMCU-' . $this->application->id . '.pdf')
                    ->withMime('application/pdf'),
        ];
    }
}
