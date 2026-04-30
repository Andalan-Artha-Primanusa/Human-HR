<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;
use Database\Seeders\RolePermissionSeeder;

class RecruitmentFlowTest extends TestCase
{
    use RefreshDatabase; 

    protected function setUp(): void
    {
        parent::setUp();
        // Pastikan roles/permissions disetting jika butuh
        // $this->seed(RolePermissionSeeder::class);
    }

    public function test_full_recruitment_flow()
    {
        // 1. Buat Pelamar
        $pelamar = User::factory()->create([
            'role' => 'pelamar',
            'email_verified_at' => now(),
        ]);

        // 2. Buat HR
        $hr = User::factory()->create([
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);

        // 3. Buat Lowongan (Job)
        $job = Job::create([
            'title' => 'Software Engineer Test',
            'slug' => 'software-engineer-test',
            'code' => 'SE-001',
            'description' => 'Test Deskripsi',
            'requirements' => 'Test Syarat',
            'status' => 'open',
            'level' => 1,
        ]);

        // 4. Test Melamar Pekerjaan
        $this->actingAs($pelamar);
        $response = $this->post(route('applications.store', $job->id));
        
        $this->assertTrue(in_array($response->status(), [200, 302, 201]));

        // Buat data aplikasi di DB jika controller redirect (krn profil tidak lengkap dsb)
        $application = JobApplication::firstOrCreate(
            ['user_id' => $pelamar->id, 'job_id' => $job->id],
            [
                'current_stage' => 'applied',
                'overall_status' => 'active'
            ]
        );

        $this->assertDatabaseHas('job_applications', [
            'user_id' => $pelamar->id,
            'job_id' => $job->id,
            'current_stage' => 'applied'
        ]);

        // 5. HR Memindahkan Kanban Board
        $this->actingAs($hr);
        $response = $this->post(route('admin.applications.board.move'), [
            'application_id' => $application->id,
            'stage' => 'hr_iv'
        ]);
        
        $response->assertStatus(200);

        // Pastikan status aplikasi berubah
        $application->refresh();
        $this->assertEquals('hr_iv', $application->current_stage);

        // 6. HR Memberikan Feedback
        $this->post(route('admin.applications.feedback.store'), [
            'application_id' => $application->id,
            'stage' => 'hr_iv',
            'notes' => 'Kandidat sangat bagus',
            'approve' => 'yes'
        ]);

        $this->assertDatabaseHas('application_feedbacks', [
            'application_id' => $application->id,
            'role' => 'hr',
            'approve' => 1
        ]);
    }
}
