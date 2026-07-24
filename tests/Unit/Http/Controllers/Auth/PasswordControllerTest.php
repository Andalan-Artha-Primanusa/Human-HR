<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\PasswordController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private PasswordController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new PasswordController();
    }

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(PasswordController::class));
    }

    public function test_update_method_exists(): void
    {
        $this->assertTrue(method_exists($this->controller, 'update'));
    }
}
