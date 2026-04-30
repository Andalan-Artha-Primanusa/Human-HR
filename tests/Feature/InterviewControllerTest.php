<?php

namespace Tests\Feature;

use App\Models\Interview;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $hr;
    protected $pelamar;
    protected $site;
    protected $job;
    protected $application;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = User::factory()->create([
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);

        $this->pelamar = User::factory()->create([
            'role' => 'pelamar',
            'email_verified_at' => now(),
        ]);

        $this->site = Site::factory()->create();

        $this->job = Job::create([
            'title' => 'Software Engineer',
            'slug' => 'software-engineer',
            'code' => 'SE-01',
            'description' => 'Test',
            'requirements' => 'Test',
            'status' => 'open',
            'level' => 1,
            'employment_type' => 'fulltime',
            'site_id' => $this->site->id,
        ]);

        $this->application = JobApplication::create([
            'user_id' => $this->pelamar->id,
            'job_id' => $this->job->id,
            'current_stage' => 'hr_iv',
            'overall_status' => 'active',
        ]);
    }

    public function test_admin_index_requires_auth()
    {
        $response = $this->get(route('admin.interviews.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_index_renders_for_hr()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.interviews.index'));
        $response->assertStatus(200);
    }

    public function test_admin_index_is_forbidden_for_pelamar()
    {
        $this->actingAs($this->pelamar);
        $response = $this->get(route('admin.interviews.index'));
        $response->assertForbidden();
    }

    public function test_admin_index_with_search_query()
    {
        Interview::create([
            'application_id' => $this->application->id,
            'title' => 'HR Interview Session',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/test',
            'start_at' => now()->addDays(2),
            'end_at' => now()->addDays(2)->addHour(),
        ]);

        $this->actingAs($this->hr);
        $response = $this->get(route('admin.interviews.index', ['q' => 'HR']));
        $response->assertStatus(200);
    }

    public function test_admin_store_creates_interview()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.interviews.store', $this->application), [
            'title' => 'Technical Interview',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/123456',
            'start_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(3)->addHour()->format('Y-m-d H:i:s'),
            'panel' => ['John Doe', 'Jane Smith'],
            'notes' => 'Bring portfolio',
        ]);

        $response->assertRedirect(route('admin.interviews.index'));
        $this->assertDatabaseHas('interviews', [
            'title' => 'Technical Interview',
            'mode' => 'online',
            'application_id' => $this->application->id,
        ]);
    }

    public function test_admin_store_onsite_requires_location()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.interviews.store', $this->application), [
            'title' => 'Onsite Interview',
            'mode' => 'onsite',
            'start_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(3)->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('location');
    }

    public function test_admin_store_online_requires_meeting_link()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.interviews.store', $this->application), [
            'title' => 'Online Interview',
            'mode' => 'online',
            'start_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(3)->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('meeting_link');
    }

    public function test_admin_store_validates_end_after_start()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.interviews.store', $this->application), [
            'title' => 'Invalid Time Interview',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/123',
            'start_at' => now()->addDays(3)->addHour()->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('end_at');
    }

    public function test_admin_store_is_forbidden_for_pelamar()
    {
        $this->actingAs($this->pelamar);

        $response = $this->post(route('admin.interviews.store', $this->application), [
            'title' => 'Interview',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/123',
            'start_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(3)->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertForbidden();
    }

    public function test_admin_store_creates_interview_with_panel()
    {
        $this->actingAs($this->hr);

        $this->post(route('admin.interviews.store', $this->application), [
            'title' => 'Panel Interview',
            'mode' => 'onsite',
            'location' => 'Office A',
            'start_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(3)->addHour()->format('Y-m-d H:i:s'),
            'panel' => ['Interviewer 1', 'Interviewer 2'],
        ]);

        $interview = Interview::where('title', 'Panel Interview')->first();
        $this->assertNotNull($interview);
        $this->assertCount(2, $interview->panel);
    }

    public function test_admin_store_for_nonexistent_application_returns_404()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.interviews.store', 'non-existent-uuid'), [
            'title' => 'Interview',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/123',
            'start_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(3)->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertNotFound();
    }

    public function test_admin_update_modifies_interview()
    {
        $interview = Interview::create([
            'application_id' => $this->application->id,
            'title' => 'Original Title',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/old',
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHour(),
        ]);

        $this->actingAs($this->hr);

        $response = $this->put(route('admin.interviews.update', $interview), [
            'title' => 'Updated Title',
            'mode' => 'onsite',
            'location' => 'Meeting Room B',
            'start_at' => now()->addDays(6)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(6)->addHour()->format('Y-m-d H:i:s'),
            'notes' => 'Updated notes',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('interviews', [
            'id' => $interview->id,
            'title' => 'Updated Title',
            'mode' => 'onsite',
        ]);
    }

    public function test_admin_update_is_forbidden_for_pelamar()
    {
        $interview = Interview::create([
            'application_id' => $this->application->id,
            'title' => 'Interview',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/test',
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHour(),
        ]);

        $this->actingAs($this->pelamar);

        $response = $this->put(route('admin.interviews.update', $interview), [
            'title' => 'Hacked Title',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/hack',
            'start_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(5)->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertForbidden();
    }

    public function test_admin_destroy_deletes_interview()
    {
        $interview = Interview::create([
            'application_id' => $this->application->id,
            'title' => 'To Be Deleted',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/del',
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHour(),
        ]);

        $this->actingAs($this->hr);

        $response = $this->delete(route('admin.interviews.destroy', $interview));

        $response->assertRedirect();
        $this->assertDatabaseMissing('interviews', ['id' => $interview->id]);
    }

    public function test_admin_destroy_is_forbidden_for_pelamar()
    {
        $interview = Interview::create([
            'application_id' => $this->application->id,
            'title' => 'Protected Interview',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/prot',
            'start_at' => now()->addDays(5),
            'end_at' => now()->addDays(5)->addHour(),
        ]);

        $this->actingAs($this->pelamar);

        $response = $this->delete(route('admin.interviews.destroy', $interview));

        $response->assertForbidden();
    }
}
