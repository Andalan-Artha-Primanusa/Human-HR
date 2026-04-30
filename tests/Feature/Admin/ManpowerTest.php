<?php

namespace Tests\Feature\Admin;

use App\Models\Job;
use App\Models\ManpowerRequirement;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManpowerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $job;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        
        $site = Site::factory()->create();
        $this->job = Job::create([
            'title' => 'Software Engineer',
            'slug' => 'software-engineer',
            'code' => 'SE-001',
            'status' => 'open',
            'level' => 1,
            'site_id' => $site->id,
        ]);
    }

    /**
     * Test manpower requirement management.
     */
    public function test_manpower_management_flow()
    {
        $this->actingAs($this->admin);

        // Index
        $response = $this->get(route('admin.manpower.index'));
        $response->assertStatus(200);
        $response->assertSee('Software Engineer');

        // Edit
        $response = $this->get(route('admin.manpower.edit', $this->job));
        $response->assertStatus(200);

        // Update (Add Requirement)
        $response = $this->put(route('admin.manpower.update', $this->job), [
            'asset_name' => 'PC High End',
            'assets_count' => 5,
            'ratio_per_asset' => 1.5,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('manpower_requirements', [
            'job_id' => $this->job->id,
            'asset_name' => 'PC High End',
            'assets_count' => 5
        ]);

        $mr = ManpowerRequirement::where('job_id', $this->job->id)->first();

        // Preview
        $response = $this->postJson(route('admin.manpower.preview'), [
            'assets_count' => 10,
            'ratio_per_asset' => 2,
        ]);
        $response->assertJson(['budget_headcount' => 20]);

        // Destroy
        $response = $this->delete(route('admin.manpower.destroy', [$this->job, $mr]));
        $response->assertRedirect();
        $this->assertDatabaseMissing('manpower_requirements', ['id' => $mr->id]);
    }

    /**
     * Test manpower dashboard.
     */
    public function test_manpower_dashboard()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.dashboard.manpower'));
        $response->assertStatus(200);
        $response->assertViewHas('openJobs');
    }
}
