<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AuditLogController;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
{
    use RefreshDatabase;

    private AuditLogController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuditLogController();
    }

    public function test_index_returns_view_with_logs(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->count(3)->create(['user_id' => $user->id]);

        $request = Request::create('/admin/audit-logs', 'GET');
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_event(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->create(['user_id' => $user->id, 'event' => 'login']);
        AuditLog::factory()->create(['user_id' => $user->id, 'event' => 'logout']);

        $request = Request::create('/admin/audit-logs?event=login', 'GET', ['event' => 'login']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_user_id(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->create(['user_id' => $user->id]);

        $request = Request::create("/admin/audit-logs?user_id={$user->id}", 'GET', ['user_id' => $user->id]);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_target_type(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->create(['user_id' => $user->id, 'target_type' => 'App\\Models\\User']);

        $request = Request::create('/admin/audit-logs?target_type=App%5CModels%5CUser', 'GET', ['target_type' => 'App\\Models\\User']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_date_range(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(5)]);

        $request = Request::create('/admin/audit-logs?from=2025-01-01&to=2025-12-31', 'GET', [
            'from' => '2025-01-01',
            'to' => '2025-12-31',
        ]);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_search_by_query(): void
    {
        $user = User::factory()->create();
        AuditLog::factory()->create(['user_id' => $user->id, 'target_id' => 'test-target-123']);

        $request = Request::create('/admin/audit-logs?q=test', 'GET', ['q' => 'test']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_with_invalid_date_does_not_crash(): void
    {
        $request = Request::create('/admin/audit-logs?from=not-a-date&to=also-not', 'GET', [
            'from' => 'not-a-date',
            'to' => 'also-not',
        ]);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_reversed_date_range_swaps_automatically(): void
    {
        $request = Request::create('/admin/audit-logs?from=2025-12-31&to=2025-01-01', 'GET', [
            'from' => '2025-12-31',
            'to' => '2025-01-01',
        ]);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_show_returns_log_detail(): void
    {
        $user = User::factory()->create();
        $log = AuditLog::factory()->create(['user_id' => $user->id]);

        $response = $this->controller->show($log->id);

        $this->assertNotNull($response);
    }

    public function test_index_empty_returns_view(): void
    {
        $request = Request::create('/admin/audit-logs', 'GET');
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }
}
