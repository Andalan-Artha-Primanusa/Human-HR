<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
    }

    public function test_index_displays_staff_and_hired_users()
    {
        User::factory()->create(['name' => 'Staff HR', 'role' => 'hr']);
        $hiredUser = User::factory()->create(['name' => 'Hired Candidate', 'role' => 'pelamar']);
        
        JobApplication::create([
            'user_id' => $hiredUser->id,
            'job_id' => \App\Models\Job::factory()->create()->id,
            'current_stage' => 'hired',
            'overall_status' => 'active'
        ]);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Staff HR');
        $response->assertSee('Hired Candidate');
    }

    public function test_store_creates_user()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'role' => 'trainer',
            'active' => 1
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'role' => 'trainer']);
    }

    public function test_update_modifies_user()
    {
        $user = User::factory()->create(['name' => 'Old Name', 'role' => 'trainer']);

        $this->actingAs($this->admin);
        $response = $this->put(route('admin.users.update', $user), [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => 'hr'
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'role' => 'hr']);
    }

    public function test_destroy_deletes_user()
    {
        $user = User::factory()->create(['role' => 'pelamar']);

        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_import_users_from_csv()
    {
        $this->actingAs($this->admin);

        $csvContent = "name,email,role,active\n";
        $csvContent .= "Imported User,imported@example.com,trainer,1\n";

        $file = UploadedFile::fake()->createWithContent('users.csv', $csvContent);

        $response = $this->post(route('admin.users.import'), [
            'file' => $file
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'imported@example.com', 'name' => 'Imported User']);
    }

    public function test_export_users_to_csv()
    {
        User::factory()->count(3)->create(['role' => 'hr']);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.users.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
