<?php

namespace Tests\Feature\Auth;

use App\Notifications\CustomVerifyEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'agree' => '1',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('jobs.index', absolute: false));
        $response->assertSessionHas('verify_email_notice', 'Cek email kamu untuk verifikasi akun.');
        $user = User::where('email', 'test@example.com')->first();
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertCount(1, Notification::sent($user, CustomVerifyEmail::class));
    }

    public function test_duplicate_verification_send_is_suppressed_by_cooldown(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();
        $user->sendEmailVerificationNotification();

        $this->assertCount(1, Notification::sent($user, CustomVerifyEmail::class));
    }
}
