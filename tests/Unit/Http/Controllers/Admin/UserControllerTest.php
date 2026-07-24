<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\UserController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private UserController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new UserController();
    }

    public function test_index_returns_view(): void
    {
        User::factory()->count(3)->create(['role' => 'hr']);

        $request = \Illuminate\Http\Request::create('/admin/users', 'GET');
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_role(): void
    {
        User::factory()->create(['role' => 'hr']);
        User::factory()->create(['role' => 'admin']);

        $request = \Illuminate\Http\Request::create('/admin/users?role=hr', 'GET', ['role' => 'hr']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_searches_by_name(): void
    {
        User::factory()->create(['name' => 'John Doe', 'role' => 'hr']);

        $request = \Illuminate\Http\Request::create('/admin/users?q=John', 'GET', ['q' => 'John']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_store_validates_required_fields(): void
    {
        $request = \Illuminate\Http\Request::create('/admin/users', 'POST', []);

        try {
            $this->controller->store($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('name', $e->errors());
            $this->assertArrayHasKey('email', $e->errors());
        }
    }

    public function test_store_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@test.com']);

        $request = \Illuminate\Http\Request::create('/admin/users', 'POST', [
            'name' => 'Dup Email',
            'email' => 'existing@test.com',
            'password' => 'password123',
        ]);

        try {
            $this->controller->store($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }
    }

    public function test_store_validates_id_employe_unique(): void
    {
        User::factory()->create(['id_employe' => 'EMP001']);

        $request = \Illuminate\Http\Request::create('/admin/users', 'POST', [
            'name' => 'Dup Emp',
            'email' => 'dupemp@test.com',
            'id_employe' => 'EMP001',
        ]);

        try {
            $this->controller->store($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('id_employe', $e->errors());
        }
    }

    public function test_store_validates_min_password_length(): void
    {
        $request = \Illuminate\Http\Request::create('/admin/users', 'POST', [
            'name' => 'Short Pass',
            'email' => 'shortpass@test.com',
            'password' => '12345',
        ]);

        try {
            $this->controller->store($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors());
        }
    }

    public function test_store_creates_user(): void
    {
        $request = \Illuminate\Http\Request::create('/admin/users', 'POST', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role' => 'hr',
        ]);

        $this->controller->store($request);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'role' => 'hr',
        ]);
    }

    public function test_destroy_deletes_user(): void
    {
        $user = User::factory()->create();

        $this->controller->destroy($user);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_allowed_roles_contains_expected_roles(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('allowedRoles');
        $method->setAccessible(true);

        $roles = $method->invoke($this->controller);

        $this->assertContains('pelamar', $roles);
        $this->assertContains('hr', $roles);
        $this->assertContains('superadmin', $roles);
        $this->assertContains('admin', $roles);
        $this->assertContains('trainer', $roles);
        $this->assertContains('karyawan', $roles);
    }

    public function test_can_assign_role_non_superadmin(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('canAssignRole');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->controller, 'hr'));
        $this->assertTrue($method->invoke($this->controller, 'admin'));
        $this->assertTrue($method->invoke($this->controller, 'pelamar'));
    }

    public function test_current_user_role_returns_null_when_not_authenticated(): void
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('currentUserRole');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller);

        $this->assertNull($result);
    }
}
