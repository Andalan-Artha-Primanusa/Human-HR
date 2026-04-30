<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\Admin\Company\StoreCompanyRequest;
use Illuminate\Support\Facades\Validator;

class StoreCompanyRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $request = new StoreCompanyRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_returns_array(): void
    {
        $request = new StoreCompanyRequest();
        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('code', $rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('legal_name', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('phone', $rules);
        $this->assertArrayHasKey('website', $rules);
        $this->assertArrayHasKey('logo', $rules);
        $this->assertArrayHasKey('address', $rules);
        $this->assertArrayHasKey('city', $rules);
        $this->assertArrayHasKey('province', $rules);
        $this->assertArrayHasKey('country', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertArrayHasKey('meta', $rules);
    }

    public function test_code_is_required(): void
    {
        $validator = Validator::make(
            ['name' => 'Test Company', 'status' => 'active'],
            ['code' => ['required', 'string', 'max:50']]
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('code', $validator->errors()->getMessages());
    }

    public function test_name_is_required(): void
    {
        $validator = Validator::make(
            ['code' => 'TC01', 'status' => 'active'],
            ['name' => ['required', 'string', 'max:255']]
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->getMessages());
    }

    public function test_status_is_required(): void
    {
        $validator = Validator::make(
            ['code' => 'TC01', 'name' => 'Test Company'],
            ['status' => ['required', 'in:active,inactive']]
        );

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->getMessages());
    }

    public function test_status_must_be_active_or_inactive(): void
    {
        $validator = Validator::make(
            ['code' => 'TC01', 'name' => 'Test Company', 'status' => 'deleted'],
            ['status' => ['required', 'in:active,inactive']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_email_must_be_valid_email(): void
    {
        $validator = Validator::make(
            ['email' => 'not-an-email'],
            ['email' => ['nullable', 'email', 'max:255']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_website_must_be_valid_url(): void
    {
        $validator = Validator::make(
            ['website' => 'not-a-url'],
            ['website' => ['nullable', 'url', 'max:255']]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_valid_data_passes(): void
    {
        $validator = Validator::make(
            [
                'code' => 'TC01',
                'name' => 'Test Company',
                'legal_name' => 'PT Test Company',
                'email' => 'test@company.com',
                'phone' => '1234567890',
                'website' => 'https://company.com',
                'address' => 'Jl. Test No. 1',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'country' => 'Indonesia',
                'status' => 'active',
                'meta' => ['key' => 'value'],
            ],
            [
                'code' => ['required', 'string', 'max:50'],
                'name' => ['required', 'string', 'max:255'],
                'legal_name' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:100'],
                'website' => ['nullable', 'url', 'max:255'],
                'address' => ['nullable', 'string'],
                'city' => ['nullable', 'string', 'max:100'],
                'province' => ['nullable', 'string', 'max:100'],
                'country' => ['nullable', 'string', 'max:100'],
                'status' => ['required', 'in:active,inactive'],
                'meta' => ['nullable', 'array'],
            ]
        );

        $this->assertTrue($validator->passes());
    }
}
