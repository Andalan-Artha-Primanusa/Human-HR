<?php

namespace Tests\Feature;

use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_site()
    {
        $site = Site::factory()->make([
            'code' => 'TST',
            'name' => 'Test Site',
            'latitude' => -6.2,
            'longitude' => 106.8,
        ])->toArray();
        $this->actingAs($this->getAdminUser())
            ->post(route('admin.sites.store'), $site)
            ->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseHas('sites', ['code' => 'TST', 'name' => 'Test Site']);
    }

    public function test_admin_can_update_site()
    {
        $site = Site::factory()->create(['code' => 'UPD']);
        $this->actingAs($this->getAdminUser())
            ->put(route('admin.sites.update', $site), ['name' => 'Updated', 'code' => 'UPD'])
            ->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseHas('sites', ['id' => $site->id, 'name' => 'Updated']);
    }

    public function test_admin_can_delete_site()
    {
        $site = Site::factory()->create(['code' => 'DEL']);
        $this->actingAs($this->getAdminUser())
            ->delete(route('admin.sites.destroy', $site))
            ->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
    }

    public function test_index_shows_sites()
    {
        Site::factory()->create(['code' => 'IDX', 'name' => 'Index Site']);
        $this->actingAs($this->getAdminUser())
            ->get(route('admin.sites.index'))
            ->assertSee('Index Site');
    }

    private function getAdminUser()
    {
        $user = \App\Models\User::factory()->create(['role' => 'superadmin']);
        return $user;
    }
}
