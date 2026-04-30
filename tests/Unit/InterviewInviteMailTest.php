<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Mail\InterviewInviteMail;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\Job;
use App\Models\User;

class InterviewInviteMailTest extends TestCase
{
    public function test_mail_has_interview_property(): void
    {
        $user = new User();
        $user->name = 'Test Candidate';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $interview = new Interview();
        $interview->title = 'Technical Interview';
        $interview->setRelation('application', $application);

        $mail = new InterviewInviteMail($interview, 'ICS content here');

        $this->assertEquals($interview, $mail->interview);
    }

    public function test_mail_has_ics_content_property(): void
    {
        $user = new User();
        $user->name = 'Test Candidate';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $interview = new Interview();
        $interview->title = 'Technical Interview';
        $interview->setRelation('application', $application);

        $icsContent = 'BEGIN:VCALENDAR...';
        $mail = new InterviewInviteMail($interview, $icsContent);

        $this->assertEquals($icsContent, $mail->icsContent);
    }

    public function test_mail_is_mailable_instance(): void
    {
        $user = new User();
        $user->name = 'Test Candidate';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $interview = new Interview();
        $interview->title = 'Technical Interview';
        $interview->setRelation('application', $application);

        $mail = new InterviewInviteMail($interview, 'ICS content here');

        $this->assertInstanceOf(\Illuminate\Mail\Mailable::class, $mail);
    }

    public function test_mail_uses_queueable_trait(): void
    {
        $user = new User();
        $user->name = 'Test Candidate';

        $job = new Job();
        $job->title = 'Software Engineer';

        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        $interview = new Interview();
        $interview->title = 'Technical Interview';
        $interview->setRelation('application', $application);

        $mail = new InterviewInviteMail($interview, 'ICS content here');

        $this->assertTrue(method_exists($mail, 'queue'));
    }
}
