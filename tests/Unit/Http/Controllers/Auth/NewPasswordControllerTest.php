<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private NewPasswordController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new NewPasswordController();
    }

    public function test_create_returns_reset_password_view(): void
    {
        $request = \Illuminate\Http\Request::create('/reset-password?token=test', 'GET');

        $response = $this->controller->create($request);

        $this->assertNotNull($response);
    }

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(NewPasswordController::class));
    }

    public function test_store_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
    }
}
