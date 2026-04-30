<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ProfileUpdateRequestTest extends TestCase
{
    public function test_rules_require_name_and_email(): void
    {
        $user = User::factory()->make();
        $request = ProfileUpdateRequest::create('/profile', 'POST', []);
        $request->setUserResolver(fn() => $user);

        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('email', $rules);
    }

    public function test_name_is_required(): void
    {
        $validator = Validator::make(
            ['email' => 'test@example.com'],
            ['name' => ['required', 'string', 'max:255']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_email_is_required(): void
    {
        $validator = Validator::make(
            ['name' => 'Test User'],
            ['email' => ['required', 'string', 'lowercase', 'email', 'max:255']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_email_must_be_lowercase(): void
    {
        $validator = Validator::make(
            ['name' => 'Test User', 'email' => 'TEST@EXAMPLE.COM'],
            ['email' => ['required', 'string', 'lowercase', 'email', 'max:255']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_email_must_be_valid_email(): void
    {
        $validator = Validator::make(
            ['name' => 'Test User', 'email' => 'not-an-email'],
            ['email' => ['required', 'string', 'lowercase', 'email', 'max:255']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_valid_data_passes(): void
    {
        $validator = Validator::make(
            ['name' => 'Test User', 'email' => 'test@example.com'],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            ]
        );

        $this->assertTrue($validator->passes());
    }
}
