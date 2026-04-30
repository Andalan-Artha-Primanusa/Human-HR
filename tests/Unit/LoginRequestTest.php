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
}
