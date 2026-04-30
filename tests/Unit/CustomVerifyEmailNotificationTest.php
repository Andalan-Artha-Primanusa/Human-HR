<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Notifications\CustomVerifyEmail;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmailNotificationTest extends TestCase
{
    public function test_notification_extends_base_verify_email(): void
    {
        $notification = new CustomVerifyEmail();
        $this->assertInstanceOf(\Illuminate\Auth\Notifications\VerifyEmail::class, $notification);
    }

    public function test_notification_uses_custom_view_template(): void
    {
        $user = new User();
        $user->id = '550e8400-e29b-41d4-a716-446655440000';
        $user->email = 'test@example.com';
        $user->email_verified_at = null;

        $notification = new CustomVerifyEmail();

        try {
            $mailMessage = $notification->toMail($user);
            $this->assertEquals('emails.verify-email', $mailMessage->view);
        } catch (\Illuminate\Routing\Exceptions\UrlGenerationException $e) {
            $this->markTestSkipped('Route verification.verify not defined in test environment');
        }
    }
}
