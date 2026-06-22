<?php

namespace Tests\Feature;

use App\Models\Interview;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\CandidateEmployment;
use App\Models\CandidateProfile;
use App\Models\CandidateReference;
use App\Models\CandidateTraining;
use App\Models\Offer;
use App\Models\Poh;
use App\Models\PsychotestAttempt;
use App\Models\PsychotestQuestion;
use App\Models\PsychotestTest;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruitmentUiJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected $hr;
    protected $pelamar;
    protected $site;
    protected $job;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = User::factory()->create([
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);

        $this->pelamar = User::factory()->create([
            'role' => 'pelamar',
            'email_verified_at' => now(),
        ]);

        $this->site = Site::factory()->create(['name' => 'Jakarta HQ', 'code' => 'JKT']);

        $this->job = Job::create([
            'title' => 'Senior Frontend Developer',
            'slug' => 'senior-frontend-developer',
            'code' => 'SFD-001',
            'description' => 'We are looking for a senior frontend developer.',
            'requirements' => 'React, Tailwind, Vite.',
            'status' => 'open',
            'level' => 3,
            'employment_type' => 'fulltime',
            'site_id' => $this->site->id,
        ]);
    }

    /**
     * Test the full recruitment journey from Pelamar's perspective.
     */
    public function test_complete_recruitment_ui_journey()
    {
        // 1. Pelamar views job details
        $this->actingAs($this->pelamar);
        $response = $this->get(route('jobs.show', $this->job));
        $response->assertStatus(200);
        $response->assertSee('Senior Frontend Developer');
        $response->assertSee('Jakarta HQ');

        // 2. Pelamar applies for the job
        $poh = Poh::factory()->create(['is_active' => true]);
        $this->createCompleteProfile($this->pelamar, $poh);

        $response = $this->post(route('applications.store', $this->job), [
            'poh_id' => $poh->id,
        ]);
        $response->assertRedirect(route('candidate.profiles.edit', ['job' => $this->job->id]));
        
        $application = JobApplication::where('user_id', $this->pelamar->id)
            ->where('job_id', $this->job->id)
            ->first();
        
        $this->assertNotNull($application);
        $this->assertEquals('applied', $application->current_stage);

        // 3. HR moves application to Psychotest stage
        $this->actingAs($this->hr);
        $this->post(route('admin.applications.board.move'), [
            'id' => $application->id,
            'to' => 'psychotest',
        ]);

        $application->refresh();
        $this->assertEquals('psychotest', $application->current_stage);

        // Create a test and an attempt for the pelamar
        $test = PsychotestTest::create([
            'name' => 'Technical Assessment',
            'duration_minutes' => 30,
            'scoring' => ['pass_ratio' => 0.5],
        ]);

        $q = PsychotestQuestion::create([
            'test_id' => $test->id,
            'type' => 'mcq',
            'question' => 'What is 1+1?',
            'options' => ['1', '2', '3'],
            'answer_key' => '2',
            'weight' => 1,
            'order_no' => 1,
        ]);

        $attempt = PsychotestAttempt::create([
            'application_id' => $application->id,
            'test_id' => $test->id,
            'user_id' => $this->pelamar->id,
            'status' => 'pending',
        ]);

        // 4. Pelamar takes the Psychotest
        $this->actingAs($this->pelamar);
        $response = $this->get(route('psychotest.show', $attempt));
        $response->assertStatus(200);
        $response->assertSee('Technical Assessment');
        $response->assertSee('What is 1+1?');

        // Submit the test
        $response = $this->post(route('psychotest.submit', $attempt), [
            'answers' => [
                $q->id => '2'
            ]
        ]);
        $response->assertRedirect(route('applications.mine'));

        $attempt->refresh();
        $this->assertEquals('scored', $attempt->status);
        $this->assertEquals(1.0, (float)$attempt->score);

        // 5. HR moves application to Interview stage
        $this->actingAs($this->hr);
        $this->post(route('admin.applications.board.move'), [
            'id' => $application->id,
            'to' => 'hr_iv',
        ]);

        // Schedule an interview
        $this->post(route('admin.interviews.store', $application), [
            'title' => 'HR Interview Session',
            'mode' => 'online',
            'meeting_link' => 'https://zoom.us/j/123456789',
            'start_at' => now()->addDays(2)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s'),
            'end_at' => now()->addDays(2)->setHour(11)->setMinute(0)->format('Y-m-d H:i:s'),
            'panel' => ['HR Lead'],
            'notes' => 'Be prepared to talk about your React experience.'
        ]);

        // 6. Pelamar views the interview schedule
        $this->actingAs($this->pelamar);
        $response = $this->get(route('me.interviews.index'));
        $response->assertStatus(200);
        $response->assertSee('HR Interview Session');

        $interview = Interview::where('application_id', $application->id)->first();
        $response = $this->get(route('me.interviews.show', $interview));
        $response->assertStatus(200);
        $response->assertSee('HR Interview Session');
        $response->assertSee('https://zoom.us/j/123456789');

        // 7. HR moves application to Offer stage
        $this->actingAs($this->hr);
        $this->post(route('admin.applications.board.move'), [
            'id' => $application->id,
            'to' => 'offer',
        ]);

        // Create an offer
        $this->post(route('admin.offers.store', $application), [
            'gross_salary' => 15000000,
            'allowance' => 2000000,
            'notes' => 'Competitive offer for senior position.'
        ]);

        $offer = Offer::where('application_id', $application->id)->first();
        $this->assertNotNull($offer);

        // 8. Pelamar (theoretically) views the offer (if route existed, but let's check admin pdf)
        // In this project, currently offers might be viewed via PDF or Email.
        // Let's check if the admin can see the PDF.
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.offers.pdf', $offer));
        $response->assertStatus(200);
    }

    private function createCompleteProfile(User $user, Poh $poh): CandidateProfile
    {
        $profile = CandidateProfile::create([
            'user_id' => $user->id,
            'poh_id' => $poh->id,
            'full_name' => $user->name,
            'gender' => 'male',
            'age' => 25,
            'birthplace' => 'Jakarta',
            'birthdate' => '2001-01-01',
            'nik' => '3201010101010001',
            'email' => $user->email,
            'phone' => '081234567890',
            'last_education' => 'S1',
            'education_major' => 'Informatika',
            'education_school' => 'Universitas Test',
            'ktp_address' => 'Jl. KTP',
            'ktp_village' => 'KTP Village',
            'ktp_district' => 'KTP District',
            'ktp_city' => 'Jakarta',
            'ktp_province' => 'DKI Jakarta',
            'ktp_postal_code' => '12345',
            'domicile_address' => 'Jl. Domisili',
            'domicile_village' => 'Dom Village',
            'domicile_district' => 'Dom District',
            'domicile_city' => 'Jakarta',
            'domicile_province' => 'DKI Jakarta',
            'domicile_postal_code' => '12345',
            'cv_path' => 'candidates/test/cv.pdf',
        ]);

        CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Safety Training',
            'institution' => 'Training Center',
            'period_start' => '2025-01-01',
        ]);

        CandidateEmployment::create([
            'candidate_profile_id' => $profile->id,
            'company' => 'Company Test',
            'position_start' => 'Staff',
            'period_start' => '2024-01-01',
        ]);

        CandidateReference::create([
            'candidate_profile_id' => $profile->id,
            'name' => 'Reference Test',
            'job_title' => 'Manager',
            'company' => 'Company Test',
            'contact' => '081234567891',
        ]);

        return $profile;
    }
}
