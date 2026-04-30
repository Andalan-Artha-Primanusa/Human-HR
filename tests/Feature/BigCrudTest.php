<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Poh;
use App\Models\Company;
use App\Models\Site;

class BigCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
    }

    public function test_all_admin_dashboard_metrics()
    {
        $this->actingAs($this->admin);
        
        $response = $this->get(route('admin.dashboard.manpower'));
        $response->assertStatus(200);

        $response = $this->get(route('admin.applications.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.interviews.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.candidates.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.offers.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.psychotests.index'));
        $response->assertStatus(200);
    }
    
    public function test_create_and_manage_applications()
    {
        $this->actingAs($this->admin);
        $pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        $job = Job::create(['title' => 'Test', 'slug' => 'test-j', 'code' => 'T', 'status' => 'open', 'level' => 1]);
        
        $app = JobApplication::create([
            'user_id' => $pelamar->id,
            'job_id' => $job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active'
        ]);

        $this->get(route('admin.applications.show', $app->id))->assertStatus(200);
        $this->delete(route('admin.applications.destroy', $app->id));
        
        $this->assertDatabaseMissing('job_applications', ['id' => $app->id]);
    }

    public function test_user_management_extended()
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.users.store'), [
            'name' => 'HR Staff',
            'email' => 'hrstaff@pt-aap.com',
            'password' => 'password123',
            'role' => 'hr'
        ]);

        $u = User::where('email', 'hrstaff@pt-aap.com')->first();
        
        $this->put(route('admin.users.update', $u->id), [
            'name' => 'HR Staff Updated',
            'email' => 'hrstaff@pt-aap.com',
            'role' => 'admin'
        ]);

        $this->get(route('admin.users.edit', $u->id))->assertStatus(200);
        
        $this->delete(route('admin.users.destroy', $u->id));
    }
}
