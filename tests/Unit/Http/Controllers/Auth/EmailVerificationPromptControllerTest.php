<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\EmailVerificationPromptController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationPromptControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(EmailVerificationPromptController::class));
    }

    public function test_is_invokable(): void
    {
        $controller = new EmailVerificationPromptController();
        $this->assertTrue(method_exists($controller, '__invoke'));
    }
}
