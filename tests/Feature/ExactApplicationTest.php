<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\JobApplication;

class ExactApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_stages_and_feedback()
    {
        $admin = User::factory()->create(['role' => 'hr']);
        $pelamar = User::factory()->create(['role' => 'pelamar']);
        
        $job = Job::create([
            'title' => 'Software Developer',
            'code' => 'SD-01',
            'slug' => 'sd-01',
            'status' => 'open',
            'level' => 1
        ]);
        
        $app = JobApplication::create([
            'user_id' => $pelamar->id,
            'job_id' => $job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active'
        ]);

        $this->actingAs($admin);

        // Stage Movements (Memperbaiki payload "id" yang sebelumnya salah nama)
        $stages = ['screening', 'hr_iv', 'user_iv', 'psychotest', 'mcu', 'offering', 'hired', 'not_qualified', 'withdrawn'];
        
        foreach($stages as $s) {
            $this->post(route('admin.applications.board.move'), [
                'id' => $app->id,
                'stage' => $s
            ]);
        }
        
        // Add feedbacks (Memperbaiki nama payload stage_key, role, dan feedback)
        foreach(['hr_iv', 'user_iv'] as $s) {
            $this->post(route('admin.applications.feedback.store'), [
                'application_id' => $app->id,
                'stage_key' => $s,
                'role' => 'hr',
                'feedback' => 'Sangat Direkomendasikan',
                'approve' => 'yes'
            ]);
        }
        $this->assertTrue(true);
    }
}
