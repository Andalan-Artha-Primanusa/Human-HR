<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Validator;

class LoginRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $request = new LoginRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_requires_email_and_password(): void
    {
        $request = new LoginRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('password', $rules);
    }

    public function test_email_is_required(): void
    {
        $validator = Validator::make(
            ['password' => 'secret'],
            ['email' => ['required', 'string', 'email']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_email_must_be_valid_email(): void
    {
        $validator = Validator::make(
            ['email' => 'not-an-email', 'password' => 'secret'],
            ['email' => ['required', 'string', 'email']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_password_is_required(): void
    {
        $validator = Validator::make(
            ['email' => 'test@example.com'],
            ['password' => ['required', 'string']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_throttle_key_contains_email_and_ip(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $throttleKey = $request->throttleKey();

        $this->assertStringContainsString('test@example.com', $throttleKey);
        $this->assertStringContainsString('127.0.0.1', $throttleKey);
    }

    public function test_throttle_key_is_transliterated_and_lowercased(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'TEST@Example.COM',
            'password' => 'secret',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $throttleKey = $request->throttleKey();

        $this->assertStringContainsString('test@example.com', $throttleKey);
    }

    public function test_authenticate_success_clears_rate_limiter(): void
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('attempt')->once()->andReturn(true);
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('clear')->once();

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $request->authenticate();
    }

    public function test_authenticate_failure_hits_rate_limiter_and_throws(): void
    {
        \Illuminate\Support\Facades\Auth::shouldReceive('attempt')->once()->andReturn(false);
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(false);
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('hit')->once();

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $request->authenticate();
    }

    public function test_ensure_is_not_rate_limited_throws_when_too_many_attempts(): void
    {
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('tooManyAttempts')->once()->andReturn(true);
        \Illuminate\Support\Facades\RateLimiter::shouldReceive('availableIn')->once()->andReturn(120);
        \Illuminate\Support\Facades\Event::fake([\Illuminate\Auth\Events\Lockout::class]);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        try {
            $request->ensureIsNotRateLimited();
            $this->fail('Expected ValidationException was not thrown.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Event::assertDispatched(\Illuminate\Auth\Events\Lockout::class);
            $this->assertArrayHasKey('email', $e->errors());
        }
    }
}
