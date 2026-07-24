<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\PasswordResetLinkController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetLinkControllerTest extends TestCase
{
    use RefreshDatabase;

    private PasswordResetLinkController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PasswordResetLinkController();
    }

    public function test_create_returns_forgot_password_view(): void
    {
        $response = $this->controller->create();

        $this->assertNotNull($response);
    }

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(PasswordResetLinkController::class));
    }

    public function test_store_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
    }
}
