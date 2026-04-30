<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
    }

    public function test_index_renders_with_logs()
    {
        AuditLog::factory()->count(5)->create();

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.audit_logs.index'));

        $response->assertStatus(200);
        $response->assertViewHas('items');
    }

    public function test_index_filters_by_event()
    {
        AuditLog::factory()->create(['event' => 'login']);
        AuditLog::factory()->create(['event' => 'updated']);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.audit_logs.index', ['event' => 'login']));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->viewData('items')->count());
    }

    public function test_show_displays_log_details()
    {
        $log = AuditLog::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.audit_logs.show', $log));

        $response->assertStatus(200);
        $response->assertSee($log->ip);
    }

    public function test_export_returns_streamed_response()
    {
        AuditLog::factory()->count(3)->create();

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.audit_logs.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
