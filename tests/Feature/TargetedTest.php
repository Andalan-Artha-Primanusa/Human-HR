<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\Company;
use App\Models\Site;
use App\Models\Poh;
use App\Models\JobApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TargetedTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_controller_and_public_jobs()
    {
        $job = Job::create([
            'title' => 'Software Engineer',
            'slug' => 'se-01',
            'code' => 'SE-01',
            'status' => 'open',
            'level' => 1,
            'description' => 'Test',
            'requirements' => 'Test',
        ]);

        $this->get(route('welcome'))->assertStatus(200);
        $this->get(route('jobs.index', ['search' => 'Software']))->assertStatus(200);
        $this->get(route('jobs.show', $job->slug))->assertStatus(200);
    }

    public function test_candidate_profile_all_sections()
    {
        Storage::fake('public');
        $pelamar = User::factory()->create(['role' => 'pelamar']);
        $this->actingAs($pelamar);

        // Upload Profile Picture
        $this->post(route('candidate.profile.update.photo'), [
            'photo' => UploadedFile::fake()->image('photo.jpg')
        ]);

        // Experience Update
        $this->post(route('candidate.profile.experience.store'), [
            'company_name' => 'PT Andalan',
            'position' => 'Staff',
            'start_date' => '2020-01-01',
            'end_date' => '2022-01-01',
            'job_description' => 'Working hard',
            'certificate_file' => UploadedFile::fake()->create('cert.pdf', 100)
        ]);
        
        // Education Update
        $this->post(route('candidate.profile.education.store'), [
            'institution_name' => 'Universitas Indonesia',
            'degree' => 'S1',
            'major' => 'Informatika',
            'start_year' => '2015',
            'end_year' => '2019',
            'gpa' => '3.80'
        ]);
    }

    public function test_offer_and_psychotest_extended()
    {
        $admin = User::factory()->create(['role' => 'hr']);
        $this->actingAs($admin);

        $job = Job::create(['title' => 'T', 'slug' => 't', 'code' => 'T', 'status' => 'open', 'level' => 1]);
        $app = JobApplication::create(['user_id' => User::factory()->create()->id, 'job_id' => $job->id, 'current_stage' => 'applied']);
        
        // Store Offer
        $this->post(route('admin.offers.store'), [
            'application_id' => $app->id,
            'basic_salary' => 10000000,
            'allowances' => '{"makan": 50000}',
            'position' => 'SPV',
            'department' => 'IT',
            'start_date' => '2026-01-01',
            'status' => 'draft',
            'notes' => 'Tolong review'
        ]);

        $this->get(route('admin.offers.index', ['status' => 'draft']))->assertStatus(200);

        // Store Psychotest
        $this->post(route('admin.psychotests.store'), [
            'application_id' => $app->id,
            'test_date' => '2026-01-01',
            'test_time' => '10:00',
            'platform' => 'Internal',
            'link' => 'https://zoom.us',
            'notes' => 'Test'
        ]);

        $this->get(route('admin.psychotests.index'))->assertStatus(200);
    }
}
