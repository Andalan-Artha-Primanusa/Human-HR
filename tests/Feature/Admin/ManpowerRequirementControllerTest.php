<?php

namespace Tests\Feature\Admin;

use App\Models\Job;
use App\Models\ManpowerRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManpowerRequirementControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
    }

    public function test_index_displays_jobs()
    {
        Job::factory()->create(['title' => 'Software Engineer']);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.manpower.index'));

        $response->assertStatus(200);
        $response->assertSee('Software Engineer');
    }

    public function test_index_with_search_query()
    {
        Job::factory()->create(['title' => 'Software Engineer', 'code' => 'SE-01']);
        Job::factory()->create(['title' => 'Product Manager', 'code' => 'PM-01']);

        $this->actingAs($this->admin);

        // Search by title
        $response = $this->get(route('admin.manpower.index', ['q' => 'Software']));
        $response->assertStatus(200);
        $response->assertSee('Software Engineer');
        $response->assertDontSee('Product Manager');

        // Search by code
        $response = $this->get(route('admin.manpower.index', ['q' => 'PM-01']));
        $response->assertStatus(200);
        $response->assertSee('Product Manager');
        $response->assertDontSee('Software Engineer');
    }

    public function test_index_json()
    {
        Job::factory()->create(['title' => 'Software Engineer']);

        $this->actingAs($this->admin);
        $response = $this->getJson(route('admin.manpower.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['jobs']);
    }

    public function test_edit_renders_view()
    {
        $job = Job::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.manpower.edit', $job));

        $response->assertStatus(200);
        $response->assertViewHas('job');
    }

    public function test_update_saves_requirement()
    {
        $job = Job::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->put(route('admin.manpower.update', $job), [
            'asset_name' => 'Excavator',
            'assets_count' => 5,
            'ratio_per_asset' => 2.5
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('manpower_requirements', [
            'job_id' => $job->id,
            'asset_name' => 'Excavator',
            'assets_count' => 5
        ]);
        
        $job->refresh();
        $this->assertEquals(13, $job->openings); // 5 * 2.5 = 12.5 -> ceil = 13
    }

    public function test_destroy_removes_requirement()
    {
        $job = Job::factory()->create();
        $req = ManpowerRequirement::factory()->create(['job_id' => $job->id]);

        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.manpower.destroy', [$job, $req]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('manpower_requirements', ['id' => $req->id]);
    }

    public function test_edit_json()
    {
        $job = Job::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->getJson(route('admin.manpower.edit', $job));

        $response->assertStatus(200);
        $response->assertJsonStructure(['job', 'rows']);
    }

    public function test_update_json()
    {
        $job = Job::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->putJson(route('admin.manpower.update', $job), [
            'assets_count' => 10,
            'ratio_per_asset' => 1.0
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Saved']);
    }

    public function test_destroy_json()
    {
        $job = Job::factory()->create();
        $req = ManpowerRequirement::factory()->create(['job_id' => $job->id]);

        $this->actingAs($this->admin);
        $response = $this->deleteJson(route('admin.manpower.destroy', [$job, $req]));

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Deleted']);
    }

    public function test_preview_returns_calculation()
    {
        $this->actingAs($this->admin);
        $response = $this->postJson(route('admin.manpower.preview'), [
            'assets_count' => 10,
            'ratio_per_asset' => 1.2
        ]);

        $response->assertStatus(200);
        $response->assertJson(['budget_headcount' => 12]);
    }

    public function test_dashboard_invoke_renders_view()
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.dashboard.manpower'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard.manpower');
    }

    public function test_dashboard_with_data()
    {
        ManpowerRequirement::query()->delete();
        $job = Job::factory()->create(['status' => 'open']);
        ManpowerRequirement::query()->where('job_id', $job->id)->delete();

        ManpowerRequirement::factory()->create([
            'job_id' => $job->id,
            'budget_headcount' => 10,
            'filled_headcount' => 2
        ]);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.dashboard.manpower'));

        $response->assertStatus(200);
        $response->assertViewHas('openJobs', 1);
        $response->assertViewHas('budget', 10);
        $response->assertViewHas('filled', 2);
    }
}
