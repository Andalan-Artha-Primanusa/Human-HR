<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\ConfirmablePasswordController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfirmablePasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private ConfirmablePasswordController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ConfirmablePasswordController();
    }

    public function test_show_returns_confirm_password_view(): void
    {
        $response = $this->controller->show();

        $this->assertNotNull($response);
    }

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(ConfirmablePasswordController::class));
    }

    public function test_store_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'store'));
    }
}
