<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Concerns\HasUuidPrimaryKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class HasUuidPrimaryKeyTraitTest extends TestCase
{
    use RefreshDatabase;

    public function test_uses_string_key_type(): void
    {
        $user = new User();
        $this->assertEquals('string', $user->getKeyType());
    }

    public function test_not_incrementing(): void
    {
        $user = new User();
        $this->assertFalse($user->incrementing);
    }

    public function test_auto_generates_uuid_on_create(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->id);
        $this->assertTrue(Str::isUuid($user->id));
    }

    public function test_does_not_override_existing_id(): void
    {
        $customId = (string) Str::uuid();

        $user = new User();
        $user->id = $customId;
        $user->name = 'Test User';
        $user->email = 'unique-' . uniqid() . '@example.com';
        $user->password = bcrypt('password');
        $user->role = 'pelamar';
        $user->email_verified_at = now();
        $user->save();

        $this->assertEquals($customId, $user->id);
    }

    public function test_initialized_key_type_via_trait(): void
    {
        $user = new User();
        $traits = class_uses_recursive($user);
        $this->assertContains(HasUuidPrimaryKey::class, $traits);
    }
}
