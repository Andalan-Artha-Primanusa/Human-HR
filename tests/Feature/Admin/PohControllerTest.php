<?php

namespace Tests\Feature\Admin;

use App\Models\Poh;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PohControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
    }

    public function test_index_displays_pohs()
    {
        Poh::factory()->create(['name' => 'Jakarta Office']);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.pohs.index'));

        $response->assertStatus(200);
        $response->assertSee('Jakarta Office');
    }

    public function test_index_with_search_query()
    {
        Poh::factory()->create(['name' => 'Jakarta Office', 'code' => 'JKT']);
        Poh::factory()->create(['name' => 'Surabaya Office', 'code' => 'SBY']);

        $this->actingAs($this->admin);
        
        // Search by name
        $response = $this->get(route('admin.pohs.index', ['q' => 'Jakarta']));
        $response->assertStatus(200);
        $response->assertSee('Jakarta Office');
        $response->assertDontSee('Surabaya Office');

        // Search by code
        $response = $this->get(route('admin.pohs.index', ['q' => 'SBY']));
        $response->assertStatus(200);
        $response->assertSee('Surabaya Office');
        $response->assertDontSee('Jakarta Office');
    }

    public function test_store_creates_poh()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.pohs.store'), [
            'name' => 'New POH',
            'code' => 'POH01',
            'is_active' => 1
        ]);

        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseHas('pohs', ['name' => 'New POH', 'code' => 'POH01']);
    }

    public function test_update_modifies_poh()
    {
        $poh = Poh::factory()->create(['name' => 'Old POH']);

        $this->actingAs($this->admin);
        $response = $this->put(route('admin.pohs.update', $poh), [
            'name' => 'Updated POH',
            'code' => $poh->code,
            'is_active' => 1
        ]);

        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseHas('pohs', ['id' => $poh->id, 'name' => 'Updated POH']);
    }

    public function test_destroy_deletes_poh()
    {
        $poh = Poh::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.pohs.destroy', $poh));

        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseMissing('pohs', ['id' => $poh->id]);
    }
}
