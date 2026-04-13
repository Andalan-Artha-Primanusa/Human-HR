<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_bearer_token_and_user(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token_type',
                'token',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'api_token' => $response->json('token'),
        ]);
    }

    public function test_me_requires_valid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $response->json('token');

        $meResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/me');

        $meResponse->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_public_user_show_works_without_token(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/public/users/'.$user->id);

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_public_users_index_returns_all_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/public/users');

        $response->assertOk()
            ->assertJsonCount(3, 'users');
    }

    public function test_users_index_requires_token(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    public function test_user_show_requires_token(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/users/'.$user->id);

        $response->assertUnauthorized();
    }
}