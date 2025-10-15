<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InterviewInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public $interview,
        public string $icsContent
    ) {}

    public function build()
    {
        return $this->subject('Undangan Interview: '.$this->interview->title)
            ->markdown('emails.interview_invite', [
                'interview' => $this->interview,
                'candidate' => $this->interview->application->user,
                'job'       => $this->interview->application->job,
            ])
            ->attachData($this->icsContent, 'interview.ics', [
                'mime' => 'text/calendar; method=REQUEST; charset=UTF-8',
            ]);
    }
}
