<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_is_lowercased_and_trimmed(): void
    {
        $user = new User();
        $user->email = '  TEST@Example.COM  ';
        $this->assertEquals('test@example.com', $user->email);
    }

    public function test_email_can_be_null(): void
    {
        $user = new User();
        $user->email = null;
        $this->assertNull($user->email);
    }

    public function test_has_role_with_single_role(): void
    {
        $user = new User();
        $user->role = 'hr';

        $this->assertTrue($user->hasRole('hr'));
        $this->assertFalse($user->hasRole('superadmin'));
    }

    public function test_has_role_with_multiple_roles_pipe_separated(): void
    {
        $user = new User();
        $user->role = 'hr';

        $this->assertTrue($user->hasRole('hr|superadmin'));
        $this->assertFalse($user->hasRole('pelamar|user'));
    }

    public function test_has_role_with_array(): void
    {
        $user = new User();
        $user->role = 'pelamar';

        $this->assertTrue($user->hasRole(['pelamar', 'hr']));
        $this->assertFalse($user->hasRole(['hr', 'superadmin']));
    }

    public function test_is_hr(): void
    {
        $hr = new User();
        $hr->role = 'hr';
        $this->assertTrue($hr->isHr());

        $nonHr = new User();
        $nonHr->role = 'pelamar';
        $this->assertFalse($nonHr->isHr());
    }

    public function test_is_superadmin(): void
    {
        $admin = new User();
        $admin->role = 'superadmin';
        $this->assertTrue($admin->isSuperadmin());

        $nonAdmin = new User();
        $nonAdmin->role = 'hr';
        $this->assertFalse($nonAdmin->isSuperadmin());
    }

    public function test_is_verified_when_email_verified_at_is_set(): void
    {
        $user = new User();
        $user->email_verified_at = now();
        $this->assertTrue($user->isVerified());
    }

    public function test_is_not_verified_when_email_verified_at_is_null(): void
    {
        $user = new User();
        $user->email_verified_at = null;
        $this->assertFalse($user->isVerified());
    }

    public function test_fillable_attributes(): void
    {
        $user = new User();
        $this->assertEquals([
            'name',
            'email',
            'password',
            'role',
            'id_employe',
        ], $user->getFillable());
    }

    public function test_hidden_attributes(): void
    {
        $user = new User();
        $hidden = $user->getHidden();
        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
        $this->assertContains('api_token', $hidden);
    }

    public function test_user_has_profile_relationship(): void
    {
        $user = new User();
        $relation = $user->profile();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    }

    public function test_user_has_applications_relationship(): void
    {
        $user = new User();
        $relation = $user->applications();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    }

    public function test_user_has_candidate_profile_relationship(): void
    {
        $user = new User();
        $relation = $user->candidateProfile();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
    }
}
