<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\StoreApplicationRequest;
use App\Models\User;

class StoreApplicationRequestTest extends TestCase
{
    public function test_authorize_allows_authenticated_user(): void
    {
        $user = new User();
        $user->role = 'pelamar';

        $request = StoreApplicationRequest::create('/jobs/1/apply', 'POST', []);
        $request->setUserResolver(fn() => $user);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_denies_unauthenticated_user(): void
    {
        $request = StoreApplicationRequest::create('/jobs/1/apply', 'POST', []);
        $request->setUserResolver(fn() => null);

        $this->assertFalse($request->authorize());
    }

    public function test_rules_returns_empty_array(): void
    {
        $request = StoreApplicationRequest::create('/jobs/1/apply', 'POST', []);
        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertEmpty($rules);
    }
}
