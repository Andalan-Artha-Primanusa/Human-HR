<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\Company;
use App\Models\Poh;
use App\Models\Site;
use App\Models\McuTemplate;
use App\Models\JobApplication;

class MegaSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup Super Admin untuk bebas akses ke seluruh modul HRIS
        $this->admin = User::factory()->create([
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);
    }

    public function test_all_admin_index_routes_hit()
    {
        $this->actingAs($this->admin);

        $routes = [
            'admin.companies.index',
            'admin.pohs.index',
            'admin.sites.index',
            'admin.mcu-templates.index',
            'admin.users.index',
            'admin.audit-logs.index',
            'admin.jobs.index',
            'admin.candidates.index',
            'admin.applications.index',
            'admin.applications.board',
            'admin.interviews.index',
            'admin.psychotests.index',
            'admin.offers.index',
            'admin.dashboard.manpower',
        ];

        foreach ($routes as $route) {
            if (\Route::has($route)) {
                $response = $this->get(route($route));
                $this->assertTrue(in_array($response->status(), [200, 302]));
            }
        }
    }

    public function test_all_admin_create_and_edit_routes_hit()
    {
        $this->actingAs($this->admin);
        
        $company = Company::create(['name' => 'Test Company', 'code' => 'TC']);
        $poh = Poh::create(['name' => 'Test POH', 'code' => 'TP']);
        $site = Site::create(['name' => 'Test Site', 'code' => 'TS', 'company_id' => $company->id]);
        $job = Job::create(['title' => 'Test Job', 'slug' => 'test-job', 'code' => 'TJ', 'description' => 'x', 'requirements' => 'x', 'status' => 'open', 'level' => 1]);

        $routes = [
            'admin.companies.create' => [],
            'admin.companies.edit' => ['company' => $company->id],
            'admin.pohs.create' => [],
            'admin.pohs.edit' => ['poh' => $poh->id],
            'admin.sites.create' => [],
            'admin.sites.edit' => ['site' => $site->id],
            'admin.jobs.create' => [],
            'admin.jobs.edit' => ['job' => $job->id],
        ];

        foreach ($routes as $route => $params) {
            if (\Route::has($route)) {
                $response = $this->get(route($route, $params));
                $this->assertTrue(in_array($response->status(), [200, 302]));
            }
        }
    }

    public function test_job_crud_flow_hit()
    {
        $this->actingAs($this->admin);

        // Store
        $this->post(route('admin.jobs.store'), [
            'title' => 'New Job Controller',
            'code' => 'NJC-01',
            'level' => 1,
            'status' => 'open',
            'description' => 'Desc',
            'requirements' => 'Req'
        ]);

        $job = Job::latest()->first();

        // Update
        if($job) {
            $this->put(route('admin.jobs.update', $job->id), [
                'title' => 'Updated Job Controller',
                'code' => 'NJC-01',
                'level' => 1,
                'status' => 'open',
                'description' => 'Desc',
                'requirements' => 'Req'
            ]);
            
            // Show
            $this->get(route('admin.jobs.show', $job->id))->assertStatus(200);

            // Delete
            $this->delete(route('admin.jobs.destroy', $job->id));
        }
    }
    
    public function test_user_crud_flow_hit()
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role' => 'hr',
        ]);
        
        $u = User::where('email', 'newuser@test.com')->first();
        if($u) {
            $this->put(route('admin.users.update', $u->id), [
                'name' => 'Updated User',
                'email' => 'newuser@test.com',
                'role' => 'hr'
            ]);
            $this->get(route('admin.users.show', $u->id));
            $this->delete(route('admin.users.destroy', $u->id));
        }
    }

    public function test_public_and_profile_routes_hit()
    {
        $pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        
        $this->get(route('jobs.index'))->assertStatus(200);
        $this->get(route('sites.index'))->assertStatus(200);
        
        $this->actingAs($pelamar);
        $this->get(route('profile.edit'))->assertStatus(200);
        $this->get(route('dashboard'))->assertStatus(200);
    }
}
