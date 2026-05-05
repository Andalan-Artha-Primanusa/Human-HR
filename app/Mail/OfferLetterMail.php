<?php

namespace App\Mail;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class OfferLetterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $bodyContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Offer $offer, $bodyContent = null)
    {
        $this->offer = $offer;
        $this->bodyContent = $bodyContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $jobTitle = $this->offer->application?->job?->title ?? 'Posisi';
        return new Envelope(
            subject: 'Offering Letter - ' . $jobTitle,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.offer_letter',
            with: [
                'candidateName' => $this->offer->application?->user?->name ?? 'Kandidat',
                'jobTitle' => $this->offer->application?->job?->title ?? 'Posisi',
                'companyName' => 'Andalan Careers',
                'bodyContent' => $this->bodyContent,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     * Returns PDF attachment if generation succeeds; empty array if it fails (email still sends).
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        try {
            $this->offer->loadMissing('application.user', 'application.job', 'application.job.site');
            
            $app = $this->offer->application;
            $user = $app?->user?->name ?? '—';
            $title = $app?->job?->title ?? '—';
            $site = $app?->job?->site?->code ?? '—';
            $gross = number_format((float) ($this->offer->salary['gross'] ?? 0), 0, ',', '.');
            $allow = number_format((float) ($this->offer->salary['allowance'] ?? 0), 0, ',', '.');
            $date = now()->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y');

            $html = <<<'HTML'
<!doctype html><html><head><meta charset="utf-8"><title>Offering Letter</title>
<style>@page{margin:28px}body{font-family:DejaVu Sans,Arial,Helvetica,sans-serif;font-size:12px;color:#111}
h1{font-size:18px;margin:0 0 8px}.tbl{width:100%;border-collapse:collapse;margin-top:8px}
.tbl th,.tbl td{border:1px solid #ddd;padding:6px 8px}</style></head><body>
<h1>Offering Letter</h1>
<p><strong>Candidate:</strong> %%USER%%<br>
<strong>Position:</strong> %%TITLE%% @ %%SITE%%<br>
<strong>Date:</strong> %%DATE%%</p>
<table class="tbl">
<tr><th>Gross Salary</th><td>Rp %%GROSS%%</td></tr>
<tr><th>Allowance</th><td>Rp %%ALLOW%%</td></tr>
</table>
</body></html>
HTML;
            $html = strtr($html, [
                '%%USER%%' => e($user),
                '%%TITLE%%' => e($title),
                '%%SITE%%' => e($site),
                '%%DATE%%' => e($date),
                '%%GROSS%%' => e($gross),
                '%%ALLOW%%' => e($allow),
            ]);

            if (view()->exists('offers.pdf')) {
                $offer = $this->offer;
                $html = view('offers.pdf', compact('offer'))->render();
            }

            $pdf = Pdf::loadHTML($html)->setPaper('a4')->setWarnings(false);

            \Log::info("PDF generated successfully for OfferLetterMail: {$this->offer->id}");

            return [
                Attachment::fromData(fn () => $pdf->output(), 'OfferingLetter-' . $this->offer->id . '.pdf')
                        ->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            \Log::warning("Failed to generate PDF attachment for OfferLetterMail {$this->offer->id}: " . $e->getMessage());
            // Return empty array so email still sends without attachment
            return [];
        }
    }
}
