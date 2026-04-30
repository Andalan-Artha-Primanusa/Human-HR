<?php

namespace Tests\Feature\Admin;

use App\Models\Site;
use App\Models\User;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
    }

    public function test_index_displays_sites()
    {
        Site::factory()->create(['name' => 'Main Site']);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.sites.index'));

        $response->assertStatus(200);
        $response->assertSee('Main Site');
    }

    public function test_store_creates_site()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.sites.store'), [
            'code' => 'SITE01',
            'name' => 'New Site',
            'region' => 'Jakarta'
        ]);

        $response->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseHas('sites', ['code' => 'SITE01', 'name' => 'New Site', 'is_active' => 1]);
    }

    public function test_update_modifies_site()
    {
        $site = Site::factory()->create(['name' => 'Old Site']);

        $this->actingAs($this->admin);
        $response = $this->put(route('admin.sites.update', $site), [
            'code' => $site->code,
            'name' => 'Updated Site'
        ]);

        $response->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseHas('sites', ['id' => $site->id, 'name' => 'Updated Site']);
    }

    public function test_destroy_deletes_site_without_jobs()
    {
        $site = Site::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.sites.destroy', $site));

        $response->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
    }

    public function test_destroy_prevents_deletion_with_jobs()
    {
        $site = Site::factory()->create();
        Job::factory()->create(['site_id' => $site->id]);

        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.sites.destroy', $site));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('sites', ['id' => $site->id]);
    }
}
