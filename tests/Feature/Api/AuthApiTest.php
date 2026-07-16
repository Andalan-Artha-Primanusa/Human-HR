<?php

namespace Tests\Feature\Api;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private function bearerToken(?User $user = null): string
    {
        $user ??= User::factory()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return (string) $response->json('token');
    }

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
            'api_token' => hash('sha256', $response->json('token')),
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

        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $meResponse->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_public_user_show_requires_login_token(): void
    {
        $user = User::factory()->create();
        $token = $this->bearerToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/public/users/' . $user->id);

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.name', $user->name)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_public_users_index_returns_all_users(): void
    {
        User::factory()->count(3)->create();
        $token = $this->bearerToken();

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/public/users');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('count', 4)
            ->assertJsonCount(4, 'data');
    }

    public function test_public_jobs_index_returns_jobs_with_applicant_count(): void
    {
        $token = $this->bearerToken();
        $job = Job::factory()->create();
        $applicants = User::factory()->count(2)->create();
        foreach ($applicants as $applicant) {
            JobApplication::factory()->create([
                'job_id' => $job->id,
                'user_id' => $applicant->id,
            ]);
        }

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/public/jobs');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.applications_count', 2)
            ->assertJsonCount(2, 'data.0.applications');
    }

    public function test_public_jobs_index_accepts_basic_auth_login(): void
    {
        $user = User::factory()->create();
        Job::factory()->create();

        $response = $this
            ->withHeaders([
                'Authorization' => 'Basic ' . base64_encode($user->email . ':password'),
            ])
            ->getJson('/api/public/jobs');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');
    }

    public function test_users_index_requires_token(): void
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    public function test_user_show_requires_token(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson('/api/users/' . $user->id);

        $response->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_staff_api_users(): void
    {
        $user = User::factory()->create([
            'role' => 'pelamar',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $login->json('token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertForbidden();
    }

    public function test_staff_user_can_access_staff_api_users(): void
    {
        $user = User::factory()->create([
            'role' => 'hr',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $login->json('token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/users');

        $response->assertOk();
    }

    public function test_unverified_user_cannot_login_api(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Email belum diverifikasi.');
    }
}
