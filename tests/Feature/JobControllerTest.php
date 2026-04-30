<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Job;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $hr;
    protected $pelamar;
    protected $site;
    protected $company;
    protected $job;

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
        $this->company = Company::create([
            'code' => 'COMP-01',
            'name' => 'Test Company',
        ]);

        $this->job = Job::create([
            'title' => 'Software Engineer',
            'slug' => 'software-engineer',
            'code' => 'SE-01',
            'description' => 'Test job description',
            'requirements' => 'Test requirements',
            'status' => 'open',
            'level' => 1,
            'employment_type' => 'fulltime',
            'site_id' => $this->site->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_public_index_renders_for_guest()
    {
        $response = $this->get(route('jobs.index'));
        $response->assertStatus(200);
    }

    public function test_public_index_renders_for_authenticated_user()
    {
        $this->actingAs($this->pelamar);
        $response = $this->get(route('jobs.index'));
        $response->assertStatus(200);
    }

    public function test_public_index_filters_by_status_open_only()
    {
        Job::create([
            'title' => 'Closed Job',
            'slug' => 'closed-job',
            'code' => 'CJ-01',
            'description' => 'Test',
            'status' => 'closed',
            'level' => 1,
            'site_id' => $this->site->id,
        ]);

        $response = $this->get(route('jobs.index'));
        $response->assertStatus(200);
        $response->assertViewHas('jobs');
        $jobs = $response->viewData('jobs');
        foreach ($jobs as $job) {
            $this->assertEquals('open', $job->status);
        }
    }

    public function test_public_index_filter_by_type()
    {
        Job::create([
            'title' => 'Intern Job',
            'slug' => 'intern-job',
            'code' => 'IJ-01',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'employment_type' => 'intern',
            'site_id' => $this->site->id,
        ]);

        $response = $this->get(route('jobs.index', ['type' => 'intern']));
        $response->assertStatus(200);
    }

    public function test_public_index_filter_by_search_term()
    {
        $response = $this->get(route('jobs.index', ['term' => 'Software']));
        $response->assertStatus(200);
    }

    public function test_public_index_sort_by_latest()
    {
        $response = $this->get(route('jobs.index', ['sort' => 'latest']));
        $response->assertStatus(200);
    }

    public function test_public_index_sort_by_oldest()
    {
        $response = $this->get(route('jobs.index', ['sort' => 'oldest']));
        $response->assertStatus(200);
    }

    public function test_public_index_sort_by_title()
    {
        $response = $this->get(route('jobs.index', ['sort' => 'title']));
        $response->assertStatus(200);
    }

    public function test_public_index_pagination()
    {
        $response = $this->get(route('jobs.index', ['per_page' => 5]));
        $response->assertStatus(200);
    }

    public function test_public_index_invalid_sort_is_rejected()
    {
        $response = $this->get(route('jobs.index', ['sort' => 'invalid']));
        $response->assertSessionHasErrors('sort');
    }

    public function test_public_show_renders_job_detail()
    {
        $response = $this->get(route('jobs.show', $this->job));
        $response->assertStatus(200);
        $response->assertViewHas('job');
    }

    public function test_public_show_loads_site_and_company()
    {
        $response = $this->get(route('jobs.show', $this->job));
        $response->assertStatus(200);
        $job = $response->viewData('job');
        $this->assertTrue($job->relationLoaded('site'));
        $this->assertTrue($job->relationLoaded('company'));
    }

    public function test_admin_index_requires_auth()
    {
        $response = $this->get(route('admin.jobs.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_index_renders_for_hr()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.jobs.index'));
        $response->assertStatus(200);
    }

    public function test_admin_index_shows_all_statuses()
    {
        Job::create([
            'title' => 'Draft Job',
            'slug' => 'draft-job',
            'code' => 'DJ-01',
            'description' => 'Test',
            'status' => 'draft',
            'level' => 1,
            'site_id' => $this->site->id,
        ]);

        $this->actingAs($this->hr);
        $response = $this->get(route('admin.jobs.index'));
        $response->assertStatus(200);
        $jobs = $response->viewData('jobs');
        $hasDraft = $jobs->where('status', 'draft')->isNotEmpty();
        $this->assertTrue($hasDraft);
    }

    public function test_admin_create_renders_form()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.jobs.create'));
        $response->assertStatus(200);
        $response->assertViewHas('sites');
        $response->assertViewHas('companies');
    }

    public function test_admin_store_creates_job()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.jobs.store'), [
            'code' => 'NEW-01',
            'title' => 'New Job Position',
            'division' => 'Engineering',
            'level' => 'Junior',
            'employment_type' => 'fulltime',
            'status' => 'draft',
            'description' => 'New job description',
            'site_id' => $this->site->id,
        ]);

        $response->assertRedirect(route('admin.jobs.index'));
        $this->assertDatabaseHas('job_listings', [
            'code' => 'NEW-01',
            'title' => 'New Job Position',
        ]);
    }

    public function test_admin_store_requires_site()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.jobs.store'), [
            'code' => 'NEW-02',
            'title' => 'New Job Without Site',
            'employment_type' => 'fulltime',
            'status' => 'draft',
            'description' => 'Test',
        ]);

        $response->assertSessionHasErrors(['site_id', 'site_code']);
    }

    public function test_admin_store_requires_valid_employment_type()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.jobs.store'), [
            'code' => 'NEW-03',
            'title' => 'Invalid Type Job',
            'employment_type' => 'invalid_type',
            'status' => 'draft',
            'description' => 'Test',
            'site_id' => $this->site->id,
        ]);

        $response->assertSessionHasErrors('employment_type');
    }

    public function test_admin_store_requires_valid_status()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.jobs.store'), [
            'code' => 'NEW-04',
            'title' => 'Invalid Status Job',
            'employment_type' => 'fulltime',
            'status' => 'invalid_status',
            'description' => 'Test',
            'site_id' => $this->site->id,
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_admin_store_json_response()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.jobs.store'), [
            'code' => 'JSON-01',
            'title' => 'JSON Response Job',
            'employment_type' => 'fulltime',
            'status' => 'open',
            'description' => 'Test',
            'site_id' => $this->site->id,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'job', 'redirect']);
    }

    public function test_admin_edit_renders_form()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.jobs.edit', $this->job));
        $response->assertStatus(200);
        $response->assertViewHas('job');
        $response->assertViewHas('sites');
        $response->assertViewHas('companies');
    }

    public function test_admin_update_updates_job()
    {
        $this->actingAs($this->hr);

        $response = $this->put(route('admin.jobs.update', $this->job), [
            'code' => 'SE-01-UPDATED',
            'title' => 'Updated Job Title',
            'division' => 'Updated Division',
            'level' => 'Senior',
            'employment_type' => 'contract',
            'status' => 'closed',
            'description' => 'Updated description',
            'site_id' => $this->site->id,
        ]);

        $response->assertRedirect(route('admin.jobs.index'));
        $this->assertDatabaseHas('job_listings', [
            'id' => $this->job->id,
            'title' => 'Updated Job Title',
            'status' => 'closed',
        ]);
    }

    public function test_admin_update_json_response()
    {
        $this->actingAs($this->hr);

        $response = $this->put(route('admin.jobs.update', $this->job), [
            'code' => 'SE-01-JSON',
            'title' => 'JSON Update Job',
            'employment_type' => 'fulltime',
            'status' => 'open',
            'description' => 'Test',
            'site_id' => $this->site->id,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);
        $response->assertJsonStructure(['message', 'job', 'redirect']);
    }

    public function test_admin_destroy_is_forbidden_for_hr()
    {
        $this->actingAs($this->hr);

        $response = $this->delete(route('admin.jobs.destroy', $this->job));

        $response->assertForbidden();
    }

    public function test_store_with_site_code_instead_of_id()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.jobs.store'), [
            'code' => 'CODE-01',
            'title' => 'Job with Site Code',
            'employment_type' => 'fulltime',
            'status' => 'open',
            'description' => 'Test',
            'site_code' => $this->site->code,
        ]);

        $response->assertRedirect(route('admin.jobs.index'));
        $this->assertDatabaseHas('job_listings', ['code' => 'CODE-01']);
    }

    public function test_store_with_company_code()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.jobs.store'), [
            'code' => 'COMP-02',
            'title' => 'Job with Company Code',
            'employment_type' => 'fulltime',
            'status' => 'open',
            'description' => 'Test',
            'site_id' => $this->site->id,
            'company_code' => $this->company->code,
        ]);

        $response->assertRedirect(route('admin.jobs.index'));
    }

    public function test_store_duplicate_code_per_company_rejected()
    {
        $this->actingAs($this->hr);

        $this->post(route('admin.jobs.store'), [
            'code' => 'UNIQUE-01',
            'title' => 'First Job',
            'employment_type' => 'fulltime',
            'status' => 'open',
            'site_id' => $this->site->id,
            'company_id' => $this->company->id,
        ]);

        $response = $this->post(route('admin.jobs.store'), [
            'code' => 'UNIQUE-01',
            'title' => 'Duplicate Job',
            'employment_type' => 'fulltime',
            'status' => 'open',
            'site_id' => $this->site->id,
            'company_id' => $this->company->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_public_show_loads_pohs_for_admin_user()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('jobs.show', $this->job));
        $response->assertStatus(200);
    }

    public function test_public_index_filter_by_company_id()
    {
        $response = $this->get(route('jobs.index', ['company_id' => $this->company->id]));
        $response->assertStatus(200);
    }

    public function test_public_index_filter_by_company_code()
    {
        $response = $this->get(route('jobs.index', ['company' => $this->company->code]));
        $response->assertStatus(200);
    }

    public function test_public_index_filter_by_division()
    {
        $response = $this->get(route('jobs.index', ['division' => 'Engineering']));
        $response->assertStatus(200);
    }

    public function test_public_index_filter_by_site()
    {
        $response = $this->get(route('jobs.index', ['site' => $this->site->code]));
        $response->assertStatus(200);
    }
}
