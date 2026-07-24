<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Auth;

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_controller_class_exists(): void
    {
        $this->assertTrue(class_exists(VerifyEmailController::class));
    }

    public function test_verify_email_controller_is_invokable(): void
    {
        $controller = new VerifyEmailController();
        $this->assertTrue(method_exists($controller, '__invoke'));
    }

    public function test_user_model_has_mark_email_as_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertFalse($user->hasVerifiedEmail());

        $result = $user->markEmailAsVerified();

        $this->assertTrue($result);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_user_model_has_send_email_verification_notification(): void
    {
        $user = User::factory()->unverified()->create();

        $this->assertTrue(method_exists($user, 'sendEmailVerificationNotification'));
    }
}
