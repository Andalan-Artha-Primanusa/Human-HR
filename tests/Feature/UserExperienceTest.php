<?php

namespace Tests\Feature;

use App\Models\Interview;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class UserExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
    }

    /**
     * Test user notifications.
     */
    public function test_user_notifications_flow()
    {
        $this->actingAs($this->user);

        // Create a notification for the user
        $notification = DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $this->user->id,
            'data' => [
                'title' => 'New Job for You',
                'body' => 'A new job matching your profile has been posted.',
                'url' => '/jobs'
            ],
            'read_at' => null,
        ]);

        // Index (HTML)
        $response = $this->get(route('me.notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('New Job for You');

        // Index (JSON)
        $response = $this->getJson(route('me.notifications.index', ['format' => 'json']));
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'New Job for You']);

        // Mark as read
        $response = $this->post(route('me.notifications.read', $notification->id));
        $response->assertRedirect();
        $this->assertNotNull($notification->refresh()->read_at);

        // Mark all as read
        $notification2 = DatabaseNotification::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $this->user->id,
            'data' => ['title' => 'Another Notification'],
            'read_at' => null,
        ]);
        $response = $this->post(route('me.notifications.read_all'));
        $response->assertRedirect();
        $this->assertNotNull($notification2->refresh()->read_at);

        // Destroy
        $response = $this->delete(route('me.notifications.destroy', $notification->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    /**
     * Test user interview schedule.
     */
    public function test_user_interview_flow()
    {
        $this->actingAs($this->user);

        $site = Site::factory()->create();
        $job = Job::create([
            'title' => 'Developer',
            'slug' => 'dev',
            'code' => 'DEV-01',
            'status' => 'open',
            'level' => 1,
            'site_id' => $site->id,
        ]);

        $app = JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $job->id,
            'current_stage' => 'hr_iv',
            'overall_status' => 'active',
        ]);

        $interview = Interview::create([
            'application_id' => $app->id,
            'title' => 'Technical Interview',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/test',
            'start_at' => now()->addDays(1),
            'end_at' => now()->addDays(1)->addHour(),
        ]);

        // Index
        $response = $this->get(route('me.interviews.index'));
        $response->assertStatus(200);
        $response->assertSee('Technical Interview');

        // Show
        $response = $this->get(route('me.interviews.show', $interview->id));
        $response->assertStatus(200);
        $response->assertSee('Technical Interview');
        $response->assertSee('zoom.us/test');

        // ICS Download
        $response = $this->get(route('me.interviews.ics', $interview->id));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    }

    /**
     * Test user kanban board.
     */
    public function test_user_kanban_board()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('kanban.mine'));
        $response->assertStatus(200);
        $response->assertViewHas('grouped');
    }
}
