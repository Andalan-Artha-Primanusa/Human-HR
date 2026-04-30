<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Company;
use App\Models\Poh;
use App\Models\Site;

class AdvancedCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_interview_scheduling()
    {
        $this->actingAs($this->admin);
        
        $pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        $job = Job::create(['title' => 'Test', 'slug' => 'test-j', 'code' => 'T', 'status' => 'open', 'level' => 1]);
        $app = JobApplication::create(['user_id' => $pelamar->id, 'job_id' => $job->id, 'current_stage' => 'applied']);
        
        $response = $this->post(route('admin.interviews.store'), [
            'application_id' => $app->id,
            'interview_type' => 'hr',
            'interview_date' => now()->addDays(2)->format('Y-m-d'),
            'interview_time' => '10:00',
            'interviewer_ids' => [$this->admin->id],
            'location' => 'Online Zoom',
            'link' => 'https://zoom.us/test'
        ]);

        $this->assertDatabaseHas('interviews', [
            'application_id' => $app->id,
            'type' => 'hr'
        ]);
        
        $response->assertStatus(302);
    }

    public function test_psychotest_scheduling()
    {
        $this->actingAs($this->admin);
        
        $pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        $job = Job::create(['title' => 'Test', 'slug' => 'test-j', 'code' => 'T', 'status' => 'open', 'level' => 1]);
        $app = JobApplication::create(['user_id' => $pelamar->id, 'job_id' => $job->id, 'current_stage' => 'applied']);
        
        $response = $this->post(route('admin.psychotests.store'), [
            'application_id' => $app->id,
            'test_date' => now()->addDays(1)->format('Y-m-d'),
            'test_time' => '09:00',
            'platform' => 'Internal',
            'link' => 'https://test.com',
            'notes' => 'Test',
        ]);

        $this->assertDatabaseHas('psychotest_attempts', [
            'application_id' => $app->id,
        ]);
    }

    public function test_offer_creation()
    {
        $this->actingAs($this->admin);
        
        $pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        $job = Job::create(['title' => 'Test', 'slug' => 'test-j', 'code' => 'T', 'status' => 'open', 'level' => 1]);
        $app = JobApplication::create(['user_id' => $pelamar->id, 'job_id' => $job->id, 'current_stage' => 'applied']);
        
        $response = $this->post(route('admin.offers.store'), [
            'application_id' => $app->id,
            'basic_salary' => 5000000,
            'allowances' => '{"transport": 500000}',
            'position' => 'Staff',
            'department' => 'IT',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('offers', [
            'application_id' => $app->id,
            'basic_salary' => 5000000
        ]);
    }
}
