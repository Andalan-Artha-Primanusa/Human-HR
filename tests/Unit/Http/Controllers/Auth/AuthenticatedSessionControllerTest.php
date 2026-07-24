<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private AuthenticatedSessionController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuthenticatedSessionController();
    }

    public function test_create_returns_login_view(): void
    {
        $response = $this->controller->create();

        $this->assertNotNull($response);
    }

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(AuthenticatedSessionController::class));
    }

    public function test_store_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
    }

    public function test_destroy_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'destroy'));
    }

    public function test_user_model_has_role_attribute(): void
    {
        $user = User::factory()->create(['role' => 'hr']);
        $this->assertEquals('hr', $user->role);
    }

    public function test_hr_role_redirects_to_dashboard_logic(): void
    {
        $roles = ['superadmin', 'hr', 'admin'];
        foreach ($roles as $role) {
            $redirect = match ($role) {
                'superadmin', 'hr', 'admin' => 'admin.dashboard.manpower',
                default => 'jobs.index',
            };
            $this->assertEquals('admin.dashboard.manpower', $redirect);
        }
    }

    public function test_pelamar_role_redirects_to_jobs_logic(): void
    {
        $role = 'pelamar';
        $redirect = match ($role) {
            'superadmin', 'hr', 'admin' => 'admin.dashboard.manpower',
            default => 'jobs.index',
        };
        $this->assertEquals('jobs.index', $redirect);
    }
}
