<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\McuTemplateController;
use App\Models\McuTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class McuTemplateControllerTest extends TestCase
{
    use RefreshDatabase;

    private McuTemplateController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new McuTemplateController();
    }

    public function test_store_creates_template(): void
    {
        $request = Request::create('/admin/mcu-templates', 'POST', [
            'name' => 'Template MCU 1',
            'company_name' => 'Test Company',
            'city' => 'Jakarta',
            'is_active' => false,
        ]);

        $this->controller->store($request);

        $this->assertDatabaseHas('mcu_templates', [
            'name' => 'Template MCU 1',
            'company_name' => 'Test Company',
        ]);
    }

    public function test_store_validates_required_name(): void
    {
        $request = Request::create('/admin/mcu-templates', 'POST', [
            'company_name' => 'No Name',
        ]);

        try {
            $this->controller->store($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('name', $e->errors());
        }
    }

    public function test_store_sets_is_active_deactivates_others(): void
    {
        McuTemplate::factory()->create(['is_active' => true, 'name' => 'Old Active']);

        $request = Request::create('/admin/mcu-templates', 'POST', [
            'name' => 'New Active Template',
            'is_active' => true,
        ]);

        $this->controller->store($request);

        $this->assertDatabaseHas('mcu_templates', ['name' => 'Old Active', 'is_active' => false]);
        $this->assertDatabaseHas('mcu_templates', ['name' => 'New Active Template', 'is_active' => true]);
    }

    public function test_store_all_nullable_fields(): void
    {
        $request = Request::create('/admin/mcu-templates', 'POST', [
            'name' => 'Minimal Template',
        ]);

        $this->controller->store($request);

        $template = McuTemplate::where('name', 'Minimal Template')->first();
        $this->assertNotNull($template);
        $this->assertNull($template->company_name);
        $this->assertNull($template->city);
    }

    public function test_update_modifies_template(): void
    {
        $template = McuTemplate::factory()->create(['name' => 'Old']);

        $request = Request::create("/admin/mcu-templates/{$template->id}", 'PUT', [
            'name' => 'Updated',
            'is_active' => false,
        ]);

        $this->controller->update($request, $template);

        $this->assertDatabaseHas('mcu_templates', [
            'id' => $template->id,
            'name' => 'Updated',
        ]);
    }

    public function test_update_activating_deactivates_others(): void
    {
        $old = McuTemplate::factory()->create(['is_active' => true]);
        $new = McuTemplate::factory()->create(['is_active' => false]);

        $request = Request::create("/admin/mcu-templates/{$new->id}", 'PUT', [
            'name' => $new->name,
            'is_active' => true,
        ]);

        $this->controller->update($request, $new);

        $this->assertDatabaseHas('mcu_templates', ['id' => $old->id, 'is_active' => false]);
        $this->assertDatabaseHas('mcu_templates', ['id' => $new->id, 'is_active' => true]);
    }

    public function test_update_deactivating_keeps_others_unchanged(): void
    {
        $active = McuTemplate::factory()->create(['is_active' => true]);
        $target = McuTemplate::factory()->create(['is_active' => true]);

        $request = Request::create("/admin/mcu-templates/{$target->id}", 'PUT', [
            'name' => $target->name,
        ]);

        $this->controller->update($request, $target);

        $this->assertDatabaseHas('mcu_templates', ['id' => $active->id, 'is_active' => true]);
        $this->assertDatabaseHas('mcu_templates', ['id' => $target->id, 'is_active' => false]);
    }

    public function test_destroy_deletes_template(): void
    {
        $template = McuTemplate::factory()->create();

        $this->controller->destroy($template);

        $this->assertDatabaseMissing('mcu_templates', ['id' => $template->id]);
    }

    public function test_store_allows_nullable_string_fields(): void
    {
        $request = Request::create('/admin/mcu-templates', 'POST', [
            'name' => 'Full Template',
            'vendor_name' => 'Vendor A',
            'vendor_address' => 'Address A',
            'subject' => 'MCU Subject',
            'for_text' => 'Pre-Employment',
            'bu_name' => 'BU Name',
            'owner_name' => 'Owner',
            'matrix_owner' => 'Matrix',
            'package' => 'Standard',
            'notes' => 'Some notes',
            'result_emails' => 'test@example.com',
            'signer_name' => 'Signer',
            'signer_title' => 'GM',
            'footer_company_name' => 'Footer Co',
            'footer_address' => 'Footer Addr',
            'footer_email' => 'footer@example.com',
            'footer_website' => 'www.example.com',
        ]);

        $this->controller->store($request);

        $template = McuTemplate::where('name', 'Full Template')->first();
        $this->assertNotNull($template);
        $this->assertEquals('Vendor A', $template->vendor_name);
    }
}
