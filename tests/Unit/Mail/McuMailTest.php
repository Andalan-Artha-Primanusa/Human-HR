<?php

namespace Tests\Unit\Mail;

use App\Mail\McuMail;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\View;
use Barryvdh\DomPDF\Facade\Pdf;

class McuMailTest extends TestCase
{
    public function test_mcu_mail_envelope(): void
    {
        $user = new User(['name' => 'John Doe']);
        $job = new Job(['title' => 'Software Engineer']);
        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new McuMail($application, 'Body content here');

        $envelope = $mail->envelope();

        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals('Undangan Medical Check Up (MCU) - Software Engineer', $envelope->subject);
    }

    public function test_mcu_mail_content(): void
    {
        $user = new User(['name' => 'John Doe']);
        $job = new Job(['title' => 'Software Engineer']);
        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new McuMail($application, 'Test body content');

        $content = $mail->content();

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('emails.mcu', $content->view);
        $this->assertEquals([
            'candidateName' => 'John Doe',
            'jobTitle' => 'Software Engineer',
            'companyName' => 'Andalan Careers',
            'bodyContent' => 'Test body content',
        ], $content->with);
    }

    public function test_mcu_mail_attachments(): void
    {
        Pdf::shouldReceive('loadHTML')->andReturnSelf();
        Pdf::shouldReceive('setPaper')->with('a4')->andReturnSelf();
        Pdf::shouldReceive('setWarnings')->with(false)->andReturnSelf();
        Pdf::shouldReceive('output')->andReturn('pdf content dummy');

        View::shouldReceive('render')->andReturn('html content');
        View::shouldReceive('make')->andReturnSelf();

        $user = new User(['name' => 'John Doe']);
        $job = new Job(['title' => 'Software Engineer']);
        $job->setRelation('site', null);
        $application = new JobApplication();
        $application->id = 'app-123';
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new McuMail($application, 'Body content');

        $attachments = $mail->attachments();

        $this->assertCount(1, $attachments);
        $this->assertInstanceOf(Attachment::class, $attachments[0]);
    }
}
