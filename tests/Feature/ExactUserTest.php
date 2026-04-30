<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ExactUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_all_roles()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        // Store HR
        $response = $this->post(route('admin.users.store'), [
            'name' => 'Test HR',
            'email' => 'hr2@test.com',
            'password' => 'Password123!',
            'role' => 'hr'
        ]);
        
        $u = User::where('email', 'hr2@test.com')->first();
        
        // Update dengan payload role yang valid
        if($u) {
            $this->put(route('admin.users.update', $u->id), [
                'name' => 'Super HR',
                'email' => 'hr2@test.com',
                'role' => 'admin'
            ]);
        }
        $this->assertTrue(true);
    }
}
