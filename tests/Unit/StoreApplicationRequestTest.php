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

    public function test_job_method_returns_job_from_route(): void
    {
        $job = new \App\Models\Job();
        $job->id = 'job-1';
        
        $request = StoreApplicationRequest::create('/jobs/1/apply', 'POST', []);
        $request->setRouteResolver(function () use ($job, $request) {
            $route = new \Illuminate\Routing\Route('POST', '/jobs/{job}/apply', []);
            $route->bind($request);
            $route->setParameter('job', $job);
            return $route;
        });

        $this->assertSame($job, $request->job());
    }
}
