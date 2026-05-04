<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WelcomeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $site;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->user = User::factory()->create([
            'role' => 'pelamar',
            'email_verified_at' => now(),
        ]);

        $this->site = Site::factory()->create();
    }

    public function test_guest_sees_landing_page()
    {
        $response = $this->get(route('welcome'));

        $response->assertStatus(200);
    }

    public function test_guest_sees_only_open_jobs()
    {
        Job::create([
            'title' => 'Open Position',
            'slug' => 'open-position',
            'code' => 'OP-01',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'site_id' => $this->site->id,
        ]);
        Job::create([
            'title' => 'Closed Position',
            'slug' => 'closed-position',
            'code' => 'CP-01',
            'description' => 'Test',
            'status' => 'closed',
            'level' => 1,
            'site_id' => $this->site->id,
        ]);

        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $jobs = $response->viewData('jobs');
        foreach ($jobs as $job) {
            $this->assertEquals('open', $job->status);
        }
    }

    public function test_guest_sees_sites_list()
    {
        Site::factory()->count(3)->create();

        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $this->assertNotEmpty($response->viewData('sitesSimple'));
    }

    public function test_guest_sees_division_counts()
    {
        Job::create([
            'title' => 'Engineering Job',
            'slug' => 'eng-job',
            'code' => 'EJ-01',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'division' => 'engineering',
            'site_id' => $this->site->id,
        ]);
        Job::create([
            'title' => 'IT Job',
            'slug' => 'it-job',
            'code' => 'IT-01',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'division' => 'it',
            'site_id' => $this->site->id,
        ]);

        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $byDivision = $response->viewData('byDivision');
        $this->assertArrayHasKey('engineering', $byDivision->toArray());
        $this->assertArrayHasKey('it', $byDivision->toArray());
    }

    public function test_authenticated_user_sees_their_applications()
    {
        $job = Job::create([
            'title' => 'My Job',
            'slug' => 'my-job',
            'code' => 'MJ-01',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'site_id' => $this->site->id,
        ]);

        JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $job->id,
            'current_stage' => 'screening',
            'overall_status' => 'active',
        ]);

        $this->actingAs($this->user);
        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $myApps = $response->viewData('myApps');
        $this->assertGreaterThan(0, $myApps->count());
    }

    public function test_authenticated_user_sees_application_summary()
    {
        $job = Job::create([
            'title' => 'Summary Job',
            'slug' => 'summary-job',
            'code' => 'SJ-01',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'site_id' => $this->site->id,
        ]);

        JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active',
        ]);

        $this->actingAs($this->user);
        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $summary = $response->viewData('myAppsSummary');
        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('byStatus', $summary);
        $this->assertGreaterThanOrEqual(1, $summary['total']);
    }

    public function test_authenticated_user_sees_progress_data()
    {
        $job = Job::create([
            'title' => 'Progress Job',
            'slug' => 'progress-job',
            'code' => 'PJ-01',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'site_id' => $this->site->id,
        ]);

        JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $job->id,
            'current_stage' => 'hr_iv',
            'overall_status' => 'active',
        ]);

        $this->actingAs($this->user);
        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $progress = $response->viewData('myAppsProgress');
        $this->assertNotEmpty($progress);
    }

    public function test_guest_has_empty_application_data()
    {
        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $myApps = $response->viewData('myApps');
        $this->assertCount(0, $myApps);

        $summary = $response->viewData('myAppsSummary');
        $this->assertEquals(0, $summary['total']);
    }

    public function test_landing_shows_sites_with_coordinates()
    {
        Site::factory()->create([
            'code' => 'COORD-01',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'address' => 'Jakarta',
        ]);

        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $sitesWithCoords = $response->viewData('sitesWithCoords');
        $this->assertNotEmpty($sitesWithCoords);
    }

    public function test_color_from_string_is_consistent()
    {
        $controller = new \App\Http\Controllers\WelcomeController();
        $method = new \ReflectionMethod($controller, 'colorFromString');
        $method->setAccessible(true);

        $color1 = $method->invoke($controller, 'test');
        $color2 = $method->invoke($controller, 'test');

        $this->assertEquals($color1, $color2);
    }

    public function test_color_from_string_is_different_for_different_strings()
    {
        $controller = new \App\Http\Controllers\WelcomeController();
        $method = new \ReflectionMethod($controller, 'colorFromString');
        $method->setAccessible(true);

        $color1 = $method->invoke($controller, 'alpha');
        $color2 = $method->invoke($controller, 'beta');

        $this->assertNotEquals($color1, $color2);
    }

    public function test_stage_pipeline_returns_correct_structure()
    {
        $controller = new \App\Http\Controllers\WelcomeController();
        $method = new \ReflectionMethod($controller, 'stagePipeline');
        $method->setAccessible(true);
        $pipeline = $method->invoke($controller);

        $this->assertArrayHasKey('SUBMITTED', $pipeline);
        $this->assertArrayHasKey('SCREENING', $pipeline);
        $this->assertArrayHasKey('INTERVIEW', $pipeline);
        $this->assertArrayHasKey('OFFERED', $pipeline);
        $this->assertArrayHasKey('HIRED', $pipeline);
        $this->assertArrayHasKey('not_qualified', $pipeline);
    }

    public function test_stage_pipeline_has_correct_step_numbers()
    {
        $controller = new \App\Http\Controllers\WelcomeController();
        $method = new \ReflectionMethod($controller, 'stagePipeline');
        $method->setAccessible(true);
        $pipeline = $method->invoke($controller);

        $this->assertEquals(1, $pipeline['SUBMITTED']['step_no']);
        $this->assertEquals(2, $pipeline['SCREENING']['step_no']);
        $this->assertEquals(3, $pipeline['INTERVIEW']['step_no']);
        $this->assertEquals(4, $pipeline['OFFERED']['step_no']);
        $this->assertEquals(5, $pipeline['HIRED']['step_no']);
        $this->assertEquals(0, $pipeline['not_qualified']['step_no']);
    }

    public function test_next_stage_label_returns_correct_next_step()
    {
        $controller = new \App\Http\Controllers\WelcomeController();
        $method = new \ReflectionMethod($controller, 'nextStageLabel');
        $method->setAccessible(true);

        $this->assertEquals('Screening HR', $method->invoke($controller, 'SUBMITTED'));
        $this->assertEquals('Interview', $method->invoke($controller, 'SCREENING'));
        $this->assertEquals('Offering Letter', $method->invoke($controller, 'INTERVIEW'));
        $this->assertEquals('Diterima', $method->invoke($controller, 'OFFERED'));
        $this->assertNull($method->invoke($controller, 'HIRED'));
    }

    public function test_landing_page_shows_jobs_pagination()
    {
        for ($i = 0; $i < 15; $i++) {
            Job::create([
                'title' => "Job {$i}",
                'slug' => "job-{$i}",
                'code' => "J-{$i}",
                'description' => 'Test',
                'status' => 'open',
                'level' => 1,
                'site_id' => $this->site->id,
            ]);
        }

        $response = $this->get(route('welcome'));
        $response->assertStatus(200);

        $jobs = $response->viewData('jobs');
        $this->assertLessThanOrEqual(9, $jobs->count());
    }
}
