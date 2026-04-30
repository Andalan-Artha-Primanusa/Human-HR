<?php

namespace Tests\Feature\Admin;

use App\Models\McuTemplate;
use App\Models\Poh;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupControllersTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
    }

    /**
     * Test POH CRUD operations.
     */
    public function test_poh_crud_flow()
    {
        $this->actingAs($this->admin);

        // Create
        $response = $this->post(route('admin.pohs.store'), [
            'name' => 'Jakarta Office',
            'code' => 'JKT-01',
            'address' => 'Jl. Sudirman No. 1',
            'description' => 'Main office in Jakarta',
            'is_active' => true,
        ]);
        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseHas('pohs', ['code' => 'JKT-01']);

        $poh = Poh::where('code', 'JKT-01')->first();

        // Index
        $response = $this->get(route('admin.pohs.index'));
        $response->assertStatus(200);
        $response->assertSee('Jakarta Office');

        // Update
        $response = $this->put(route('admin.pohs.update', $poh), [
            'name' => 'Jakarta HQ',
            'code' => 'JKT-01',
            'address' => 'Jl. Sudirman No. 10',
            'is_active' => true,
        ]);
        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseHas('pohs', ['name' => 'Jakarta HQ']);

        // Delete
        $response = $this->delete(route('admin.pohs.destroy', $poh));
        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseMissing('pohs', ['id' => $poh->id]);
    }

    /**
     * Test MCU Template CRUD operations.
     */
    public function test_mcu_template_crud_flow()
    {
        $this->actingAs($this->admin);

        // Create
        $response = $this->post(route('admin.mcu-templates.store'), [
            'name' => 'Standard MCU',
            'company_name' => 'PT Test Indonesia',
            'is_active' => true,
        ]);
        $response->assertRedirect(route('admin.mcu-templates.index'));
        $this->assertDatabaseHas('mcu_templates', ['name' => 'Standard MCU']);

        $template = McuTemplate::where('name', 'Standard MCU')->first();

        // Update
        $response = $this->put(route('admin.mcu-templates.update', $template), [
            'name' => 'Standard MCU v2',
            'company_name' => 'PT Test Indonesia Updated',
            'is_active' => true,
        ]);
        $response->assertRedirect(route('admin.mcu-templates.index'));
        $this->assertDatabaseHas('mcu_templates', ['name' => 'Standard MCU v2']);

        // Delete
        $response = $this->delete(route('admin.mcu-templates.destroy', $template));
        $response->assertRedirect(route('admin.mcu-templates.index'));
        $this->assertDatabaseMissing('mcu_templates', ['id' => $template->id]);
    }
}
