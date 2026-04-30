<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Site;
use App\Models\User;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_for_guest()
    {
        Job::factory()->create(['title' => 'Open Job', 'status' => 'open']);
        Site::factory()->create(['name' => 'Main Site']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Open Job');
        $response->assertSee('Main Site');
    }

    public function test_welcome_page_renders_for_authenticated_user()
    {
        $user = User::factory()->create();
        $job = Job::factory()->create(['title' => 'My Job', 'status' => 'open']);
        JobApplication::create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active'
        ]);

        $this->actingAs($user);
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('My Job');
    }
}
