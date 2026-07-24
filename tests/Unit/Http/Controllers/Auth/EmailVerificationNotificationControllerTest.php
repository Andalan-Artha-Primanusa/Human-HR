<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationNotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(EmailVerificationNotificationController::class));
    }

    public function test_store_method_exists(): void
    {
        $controller = new EmailVerificationNotificationController();
        $this->assertTrue(method_exists($controller, 'store'));
    }
}
