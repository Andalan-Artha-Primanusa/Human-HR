<?php

namespace Tests\Feature\Admin;

use App\Models\McuTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McuTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
    }

    public function test_index_displays_templates()
    {
        McuTemplate::factory()->create(['name' => 'Default Template']);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.mcu-templates.index'));

        $response->assertStatus(200);
        $response->assertSee('Default Template');
    }

    public function test_store_creates_template()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.mcu-templates.store'), [
            'name' => 'New Template',
            'is_active' => 1
        ]);

        $response->assertRedirect(route('admin.mcu-templates.index'));
        $this->assertDatabaseHas('mcu_templates', ['name' => 'New Template', 'is_active' => 1]);
    }

    public function test_update_modifies_template()
    {
        $template = McuTemplate::factory()->create(['name' => 'Old Template']);

        $this->actingAs($this->admin);
        $response = $this->put(route('admin.mcu-templates.update', $template), [
            'name' => 'Updated Template',
            'is_active' => 1
        ]);

        $response->assertRedirect(route('admin.mcu-templates.index'));
        $this->assertDatabaseHas('mcu_templates', ['id' => $template->id, 'name' => 'Updated Template', 'is_active' => 1]);
    }

    public function test_destroy_deletes_template()
    {
        $template = McuTemplate::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.mcu-templates.destroy', $template));

        $response->assertRedirect(route('admin.mcu-templates.index'));
        $this->assertDatabaseMissing('mcu_templates', ['id' => $template->id]);
    }
}
