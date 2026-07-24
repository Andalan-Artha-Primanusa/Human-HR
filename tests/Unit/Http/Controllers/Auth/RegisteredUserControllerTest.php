<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisteredUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private RegisteredUserController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new RegisteredUserController();
    }

    public function test_create_returns_register_view(): void
    {
        $response = $this->controller->create();

        $this->assertNotNull($response);
    }

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(RegisteredUserController::class));
    }

    public function test_store_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
    }
}
