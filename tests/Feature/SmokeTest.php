<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\Company;
use App\Models\Poh;
use App\Models\Site;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_routes()
    {
        $this->get('/')->assertStatus(200);
        $this->get('/jobs')->assertStatus(200);
        $this->get('/sites')->assertStatus(200);
    }

    public function test_auth_routes()
    {
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
    }

    public function test_dashboard_redirection()
    {
        $pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        $hr = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);

        $this->actingAs($pelamar)->get('/dashboard')->assertStatus(200);
        $this->actingAs($hr)->get('/dashboard')->assertRedirect(route('admin.dashboard.manpower'));
    }

    public function test_admin_company_crud()
    {
        $hr = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        $this->actingAs($hr);

        // Index
        $this->get(route('admin.companies.index'))->assertStatus(200);

        // Store
        $this->post(route('admin.companies.store'), [
            'name' => 'PT Test Company',
            'code' => 'TC01'
        ])->assertRedirect(route('admin.companies.index'));

        $company = Company::first();

        // Update
        $this->put(route('admin.companies.update', $company->id), [
            'name' => 'PT Test Updated',
            'code' => 'TC02'
        ])->assertRedirect(route('admin.companies.index'));

        // Delete
        $this->delete(route('admin.companies.destroy', $company->id))->assertRedirect(route('admin.companies.index'));
    }

    public function test_admin_poh_crud()
    {
        $hr = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        $this->actingAs($hr);

        $this->get(route('admin.pohs.index'))->assertStatus(200);

        $this->post(route('admin.pohs.store'), [
            'name' => 'Jakarta POH',
            'code' => 'JKT'
        ])->assertRedirect(route('admin.pohs.index'));
        
        $poh = Poh::first();
        
        $this->put(route('admin.pohs.update', $poh->id), [
            'name' => 'Bandung POH',
            'code' => 'BDO'
        ])->assertRedirect(route('admin.pohs.index'));

        $this->delete(route('admin.pohs.destroy', $poh->id))->assertRedirect(route('admin.pohs.index'));
    }

    public function test_admin_site_crud()
    {
        $hr = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        $this->actingAs($hr);

        $company = Company::create(['name' => 'C1', 'code' => 'C1']);

        $this->get(route('admin.sites.index'))->assertStatus(200);

        $this->post(route('admin.sites.store'), [
            'name' => 'Site A',
            'code' => 'SA',
            'company_id' => $company->id
        ])->assertRedirect(route('admin.sites.index'));

        $site = Site::first();

        $this->put(route('admin.sites.update', $site->id), [
            'name' => 'Site B',
            'code' => 'SB',
            'company_id' => $company->id
        ])->assertRedirect(route('admin.sites.index'));

        $this->delete(route('admin.sites.destroy', $site->id))->assertRedirect(route('admin.sites.index'));
    }

    public function test_job_listing()
    {
        $job = Job::create([
            'title' => 'Software Engineer Test',
            'slug' => 'software-engineer-test',
            'code' => 'SE-001',
            'description' => 'Test Deskripsi',
            'requirements' => 'Test Syarat',
            'status' => 'open',
            'level' => 1,
        ]);

        $this->get(route('jobs.show', $job->slug))->assertStatus(200);
    }
}
