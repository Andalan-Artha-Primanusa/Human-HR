<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Middleware\EnsureApiToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EnsureApiTokenMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    public function test_returns_401_when_no_token_provided(): void
    {
        $middleware = new EnsureApiToken();
        $request = Request::create('/');

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Token', $data['message']);
    }

    public function test_returns_401_when_token_is_invalid(): void
    {
        $middleware = new EnsureApiToken();
        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer invalid_token_here');

        User::where('api_token', hash('sha256', 'invalid_token_here'))->delete();

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertStringContainsString('Token', $data['message']);
    }

    public function test_authenticates_user_with_valid_token(): void
    {
        $middleware = new EnsureApiToken();

        $plainToken = 'test_api_token_123';
        $hashedToken = hash('sha256', $plainToken);

        $user = new User();
        $user->id = 'test-uuid-123';
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->api_token = $hashedToken;

        User::where('api_token', $hashedToken)->delete();
        \DB::table('users')->insert([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => bcrypt('password'),
            'api_token' => $hashedToken,
            'role' => 'pelamar',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer ' . $plainToken);

        $response = $middleware->handle($request, function ($req) use ($user) {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_sets_user_resolver_on_success(): void
    {
        $middleware = new EnsureApiToken();

        $plainToken = 'test_api_token_456';
        $hashedToken = hash('sha256', $plainToken);

        User::where('api_token', $hashedToken)->delete();
        \DB::table('users')->insert([
            'id' => 'test-uuid-456',
            'name' => 'Resolver User',
            'email' => 'resolver@example.com',
            'password' => bcrypt('password'),
            'api_token' => $hashedToken,
            'role' => 'hr',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer ' . $plainToken);

        $resolvedUser = null;
        $response = $middleware->handle($request, function ($req) use (&$resolvedUser) {
            $resolvedUser = $req->user();
            return new Response('OK');
        });

        $this->assertNotNull($resolvedUser);
        $this->assertEquals('test-uuid-456', $resolvedUser->id);
    }
}
