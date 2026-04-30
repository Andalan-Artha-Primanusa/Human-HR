<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\McuMail;
use App\Models\JobApplication;
use App\Models\Job;
use App\Models\User;

class McuMailTest extends TestCase
{
    public function test_mail_has_subject(): void
    {
        $user = new User();
        $user->name = 'Test Candidate';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new McuMail($application);
        $envelope = $mail->envelope();

        $this->assertStringContainsString('Medical Check Up', $envelope->subject);
        $this->assertStringContainsString('Software Engineer', $envelope->subject);
    }

    public function test_mail_has_subject_with_fallback(): void
    {
        $application = new JobApplication();

        $mail = new McuMail($application);
        $envelope = $mail->envelope();

        $this->assertStringContainsString('Medical Check Up', $envelope->subject);
        $this->assertStringContainsString('Posisi', $envelope->subject);
    }

    public function test_mail_uses_correct_view(): void
    {
        $user = new User();
        $user->name = 'Test Candidate';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new McuMail($application);
        $content = $mail->content();

        $this->assertEquals('emails.mcu', $content->view);
    }

    public function test_mail_passes_candidate_name_to_view(): void
    {
        $user = new User();
        $user->name = 'John Doe';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new McuMail($application);
        $content = $mail->content();

        $this->assertEquals('John Doe', $content->with['candidateName']);
    }

    public function test_mail_passes_job_title_to_view(): void
    {
        $user = new User();
        $user->name = 'Test Candidate';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $mail = new McuMail($application);
        $content = $mail->content();

        $this->assertEquals('Software Engineer', $content->with['jobTitle']);
    }

    public function test_mail_uses_fallback_candidate_name(): void
    {
        $application = new JobApplication();

        $mail = new McuMail($application);
        $content = $mail->content();

        $this->assertEquals('Kandidat', $content->with['candidateName']);
    }

    public function test_mail_uses_fallback_job_title(): void
    {
        $application = new JobApplication();

        $mail = new McuMail($application);
        $content = $mail->content();

        $this->assertEquals('Posisi', $content->with['jobTitle']);
    }

    public function test_mail_has_company_name_in_view(): void
    {
        $application = new JobApplication();

        $mail = new McuMail($application);
        $content = $mail->content();

        $this->assertEquals('Andalan Careers', $content->with['companyName']);
    }

    public function test_mail_passes_body_content_to_view(): void
    {
        $application = new JobApplication();

        $mail = new McuMail($application);
        $mail->bodyContent = '<p>Custom body content</p>';
        $content = $mail->content();

        $this->assertEquals('<p>Custom body content</p>', $content->with['bodyContent']);
    }

    public function test_mail_implements_should_queue(): void
    {
        $application = new JobApplication();
        $mail = new McuMail($application);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $mail);
    }
}
