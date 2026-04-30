<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;

class ApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $pelamar;
    protected $job;
    protected $application;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);
        
        $this->pelamar = User::factory()->create([
            'role' => 'pelamar',
            'email_verified_at' => now(),
        ]);

        $this->job = Job::create([
            'title' => 'Software Engineer',
            'slug' => 'software-engineer',
            'code' => 'SE-01',
            'description' => 'Test',
            'requirements' => 'Test',
            'status' => 'open',
            'level' => 1,
        ]);

        $this->application = JobApplication::create([
            'user_id' => $this->pelamar->id,
            'job_id' => $this->job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active',
        ]);
    }

    public function test_kanban_board_renders()
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.applications.board'));
        $response->assertStatus(200);
    }

    public function test_move_application_stage()
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.applications.board.move'), [
            'application_id' => $this->application->id,
            'stage' => 'screening',
        ]);
        
        $response->assertStatus(200);
        $this->assertEquals('screening', $this->application->fresh()->current_stage);
    }

    public function test_store_feedback_hr()
    {
        $this->actingAs($this->admin);
        $this->application->update(['current_stage' => 'hr_iv']);

        $response = $this->post(route('admin.applications.feedback.store'), [
            'application_id' => $this->application->id,
            'stage' => 'hr_iv',
            'notes' => 'Sangat merekomendasikan',
            'approve' => 'yes'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('application_feedbacks', [
            'application_id' => $this->application->id,
            'role' => 'hr',
            'stage_key' => 'hr_iv',
            'approve' => 1
        ]);
    }

    public function test_move_to_hired()
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.applications.board.move'), [
            'application_id' => $this->application->id,
            'stage' => 'hired',
        ]);

        $this->application->refresh();
        $this->assertEquals('hired', $this->application->current_stage);
        $this->assertEquals('hired', $this->application->overall_status);
    }

    public function test_move_to_not_qualified()
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.applications.board.move'), [
            'application_id' => $this->application->id,
            'stage' => 'not_qualified',
        ]);

        $this->application->refresh();
        $this->assertEquals('not_qualified', $this->application->current_stage);
        $this->assertEquals('not_qualified', $this->application->overall_status);
    }
}
