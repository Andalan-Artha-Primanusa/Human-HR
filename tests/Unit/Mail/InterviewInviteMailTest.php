<?php

namespace Tests\Unit\Mail;

use App\Mail\InterviewInviteMail;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use stdClass;
use Tests\TestCase;

class InterviewInviteMailTest extends TestCase
{
    public function test_interview_invite_mail_builds_correctly(): void
    {
        $user = new User(['name' => 'John Doe']);
        $job = new Job(['title' => 'Software Engineer']);
        $application = new JobApplication();
        $application->setRelation('user', $user);
        $application->setRelation('job', $job);

        // create a mock interview object
        $interview = new stdClass();
        $interview->title = 'Wawancara Tahap 1';
        $interview->application = $application;

        $icsContent = "BEGIN:VCALENDAR\nVERSION:2.0\nEND:VCALENDAR";

        $mail = new InterviewInviteMail($interview, $icsContent);

        // Build the mail
        $mail->build();

        // Assert subject
        $this->assertEquals('Undangan Interview: Wawancara Tahap 1', $mail->subject);

        // Assert view
        $this->assertEquals('emails.interview_invite', $mail->markdown);

        // Assert view data
        $this->assertArrayHasKey('interview', $mail->viewData);
        $this->assertArrayHasKey('candidate', $mail->viewData);
        $this->assertArrayHasKey('job', $mail->viewData);
        
        $this->assertSame($interview, $mail->viewData['interview']);
        $this->assertSame($user, $mail->viewData['candidate']);
        $this->assertSame($job, $mail->viewData['job']);

        // Assert attachment
        $this->assertCount(1, $mail->rawAttachments);
        $attachment = $mail->rawAttachments[0];
        $this->assertEquals($icsContent, $attachment['data']);
        $this->assertEquals('interview.ics', $attachment['name']);
        $this->assertEquals('text/calendar; method=REQUEST; charset=UTF-8', $attachment['options']['mime']);
    }
}
