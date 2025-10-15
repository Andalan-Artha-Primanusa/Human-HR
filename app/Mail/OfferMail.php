<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public $offer) {}

    public function build()
    {
        return $this->subject('Offering Letter: '.$this->offer->application->job->title)
            ->markdown('emails.offer', [
                'offer'      => $this->offer,
                'candidate'  => $this->offer->application->user,
                'job'        => $this->offer->application->job,
                'gross'      => $this->offer->salary['gross'] ?? 0,
                'allowance'  => $this->offer->salary['allowance'] ?? 0,
            ]);
    }
}
