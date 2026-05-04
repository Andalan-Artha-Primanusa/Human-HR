<?php

namespace Tests\Unit\Mail;

use App\Mail\MobilisasiMail;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobilisasiMailTest extends TestCase
{
    public function test_mobilisasi_mail_envelope(): void
    {
        $user = new User(['name' => 'John Doe']);
        $job = new Job(['title' => 'Software Engineer']);
        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new MobilisasiMail($application, 'Body content here', null, null);

        $envelope = $mail->envelope();

        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals('Informasi Mobilisasi - Software Engineer', $envelope->subject);
    }

    public function test_mobilisasi_mail_content(): void
    {
        $user = new User(['name' => 'John Doe']);
        $job = new Job(['title' => 'Software Engineer']);
        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new MobilisasiMail($application, 'Test body content', null, null);

        $content = $mail->content();

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('emails.mobilisasi', $content->view);
        $this->assertEquals([
            'candidateName' => 'John Doe',
            'bodyContent' => 'Test body content',
        ], $content->with);
    }

    public function test_mobilisasi_mail_attachments_with_valid_ticket(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('tickets/test-ticket.pdf', 'dummy content');

        $application = new JobApplication();
        
        $mail = new MobilisasiMail($application, 'Body content', 'tickets/test-ticket.pdf', 'Custom-Ticket-Name.pdf');

        $attachments = $mail->attachments();

        $this->assertCount(1, $attachments);
        $this->assertInstanceOf(Attachment::class, $attachments[0]);
    }

    public function test_mobilisasi_mail_attachments_without_ticket(): void
    {
        $application = new JobApplication();
        
        $mail = new MobilisasiMail($application, 'Body content', null, null);

        $attachments = $mail->attachments();

        $this->assertCount(0, $attachments);
    }

    public function test_mobilisasi_mail_attachments_with_missing_ticket(): void
    {
        Storage::fake('public');
        
        $application = new JobApplication();
        
        $mail = new MobilisasiMail($application, 'Body content', 'tickets/missing-ticket.pdf', null);

        $attachments = $mail->attachments();

        $this->assertCount(0, $attachments);
    }
}
