<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Interview;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Site;
use App\Models\User;
use App\Notifications\InterviewScheduled;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

class InterviewScheduledTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $site;
    protected $job;
    protected $application;
    protected $interview;
    protected $notification;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => 'pelamar',
            'email_verified_at' => now(),
        ]);

        $this->site = Site::factory()->create();

        $this->job = Job::create([
            'title' => 'Software Engineer',
            'slug' => 'software-engineer',
            'code' => 'SE-01',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'employment_type' => 'fulltime',
            'site_id' => $this->site->id,
        ]);

        $this->application = JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'hr_iv',
            'overall_status' => 'active',
        ]);

        $this->interview = Interview::create([
            'application_id' => $this->application->id,
            'title' => 'HR Interview',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/123',
            'start_at' => now()->addDays(2)->setTime(10, 0),
            'end_at' => now()->addDays(2)->setTime(11, 0),
            'notes' => 'Prepare portfolio',
        ]);

        $this->notification = new InterviewScheduled($this->interview);
    }

    public function test_notification_via_channel()
    {
        $channels = $this->notification->via($this->user);

        $this->assertContains('database', $channels);
    }

    public function test_to_array_contains_required_fields()
    {
        $data = $this->notification->toArray($this->user);

        $this->assertEquals('interview_scheduled', $data['type']);
        $this->assertEquals($this->interview->id, $data['interview_id']);
        $this->assertEquals('HR Interview', $data['title']);
        $this->assertEquals('Software Engineer', $data['job_title']);
        $this->assertEquals('online', $data['mode']);
        $this->assertEquals('https://zoom.us/123', $data['meeting_link']);
        $this->assertNotNull($data['start_at']);
        $this->assertNotNull($data['end_at']);
    }

    public function test_to_array_contains_notes()
    {
        $data = $this->notification->toArray($this->user);

        $this->assertEquals('Prepare portfolio', $data['notes']);
    }

    public function test_to_array_contains_cta_url()
    {
        $data = $this->notification->toArray($this->user);

        $this->assertArrayHasKey('cta_url', $data);
        $this->assertStringContainsString($this->interview->id, $data['cta_url']);
    }

    public function test_to_array_handles_onsite_mode()
    {
        $this->interview->update([
            'mode' => 'onsite',
            'location' => 'Office A',
            'meeting_link' => null,
        ]);

        $data = (new InterviewScheduled($this->interview))->toArray($this->user);

        $this->assertEquals('onsite', $data['mode']);
        $this->assertEquals('Office A', $data['location']);
    }

    public function test_to_array_handles_missing_site()
    {
        $this->job->site_id = null;
        $this->job->save();

        $data = $this->notification->toArray($this->user);

        $this->assertNull($data['site_name']);
    }

    public function test_to_array_handles_missing_job()
    {
        $appWithoutJob = JobApplication::create([
            'user_id' => $this->user->id,
            'current_stage' => 'hr_iv',
            'overall_status' => 'active',
        ]);

        $interviewWithoutJob = Interview::create([
            'application_id' => $appWithoutJob->id,
            'title' => 'Interview Without Job',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/456',
            'start_at' => now()->addDays(2)->setTime(10, 0),
            'end_at' => now()->addDays(2)->setTime(11, 0),
        ]);

        $data = (new InterviewScheduled($interviewWithoutJob))->toArray($this->user);

        $this->assertNull($data['job_title']);
        $this->assertNull($data['site_name']);
    }

    public function test_to_mail_has_correct_subject()
    {
        $mail = $this->notification->toMail($this->user);

        $this->assertStringContainsString('Jadwal Interview', $mail->subject);
        $this->assertStringContainsString('HR Interview', $mail->subject);
    }

    public function test_to_mail_contains_interview_details()
    {
        $mail = $this->notification->toMail($this->user);

        $rendered = $mail->render();
        $this->assertStringContainsString('Software Engineer', $rendered);
        $this->assertStringContainsString('HR Interview', $rendered);
    }

    public function test_notification_is_sent_to_user()
    {
        Notification::fake();

        $this->user->notify($this->notification);

        Notification::assertSentTo($this->user, InterviewScheduled::class, function ($n) {
            return $n->interview->id === $this->interview->id;
        });
    }
}
