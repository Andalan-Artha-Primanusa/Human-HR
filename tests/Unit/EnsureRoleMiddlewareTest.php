<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\EnsureRole;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EnsureRoleMiddlewareTest extends TestCase
{
    public function test_allows_user_with_matching_role(): void
    {
        $middleware = new EnsureRole();
        $user = new User();
        $user->role = 'hr';

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        }, 'hr');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_allows_user_with_one_of_multiple_roles(): void
    {
        $middleware = new EnsureRole();
        $user = new User();
        $user->role = 'superadmin';

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        }, 'hr|superadmin');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_user_without_matching_role(): void
    {
        $middleware = new EnsureRole();
        $user = new User();
        $user->role = 'pelamar';

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = null;
        try {
            $response = $middleware->handle($request, function ($req) {
                return new Response('OK');
            }, 'hr');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            return;
        }

        $this->fail('Expected HttpException was not thrown');
    }

    public function test_blocks_unauthenticated_user(): void
    {
        $middleware = new EnsureRole();

        $request = Request::create('/');
        $request->setUserResolver(function () {
            return null;
        });

        $response = null;
        try {
            $response = $middleware->handle($request, function ($req) {
                return new Response('OK');
            }, 'hr');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            return;
        }

        $this->fail('Expected HttpException was not thrown');
    }

    public function test_trims_whitespace_from_roles(): void
    {
        $middleware = new EnsureRole();
        $user = new User();
        $user->role = 'hr';

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        }, ' hr | superadmin ');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_user_with_different_role(): void
    {
        $middleware = new EnsureRole();
        $user = new User();
        $user->role = 'pelamar';

        $request = Request::create('/');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $response = null;
        try {
            $response = $middleware->handle($request, function ($req) {
                return new Response('OK');
            }, 'hr|superadmin');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
            return;
        }

        $this->fail('Expected HttpException was not thrown');
    }
}
