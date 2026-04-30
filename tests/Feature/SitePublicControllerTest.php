<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePublicControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_displays_active_sites()
    {
        $activeSite = Site::factory()->create(['name' => 'Active Site', 'is_active' => true]);
        $inactiveSite = Site::factory()->create(['name' => 'Inactive Site', 'is_active' => false]);

        $response = $this->get(route('sites.index'));

        $response->assertStatus(200);
        $response->assertSee('Active Site');
        $response->assertDontSee('Inactive Site');
    }

    public function test_index_search_filter()
    {
        Site::factory()->create(['name' => 'SITE_ALPHA', 'is_active' => true]);
        Site::factory()->create(['name' => 'SITE_BETA', 'is_active' => true]);

        $response = $this->get(route('sites.index', ['q' => 'ALPHA']));

        $response->assertStatus(200);
        $response->assertSeeText('SITE_ALPHA');
        $response->assertDontSeeText('SITE_BETA');
    }

    public function test_index_json_response()
    {
        Site::factory()->create(['name' => 'JSON Site', 'is_active' => true]);

        $response = $this->getJson(route('sites.index'));

        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'JSON Site']);
    }

    public function test_show_displays_active_site()
    {
        $site = Site::factory()->create(['is_active' => true]);
        $job = Job::factory()->create(['site_id' => $site->id, 'status' => 'open', 'title' => 'Open Job']);

        $response = $this->get(route('sites.show', $site));

        $response->assertStatus(200);
        $response->assertSee($site->name);
        $response->assertSee('Open Job');
    }

    public function test_show_aborts_for_inactive_site()
    {
        $site = Site::factory()->create(['is_active' => false]);

        $response = $this->get(route('sites.show', $site));

        $response->assertStatus(404);
    }

    public function test_show_json_response()
    {
        $site = Site::factory()->create(['is_active' => true, 'name' => 'JSON Detail Site']);
        Job::factory()->create(['site_id' => $site->id, 'status' => 'open', 'title' => 'JSON Job']);

        $response = $this->getJson(route('sites.show', $site));

        $response->assertStatus(200);
        $response->assertJsonPath('site.name', 'JSON Detail Site');
        $response->assertJsonFragment(['title' => 'JSON Job']);
    }
}
