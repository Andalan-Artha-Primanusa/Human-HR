<?php

namespace Tests\Feature;

use App\Models\CandidateProfile;
use App\Models\Job;
use App\Models\Poh;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CandidateProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $job;
    protected $poh;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        $this->poh = Poh::factory()->create(['is_active' => true]);
        $site = Site::factory()->create();
        $this->job = Job::factory()->create(['site_id' => $site->id, 'status' => 'open']);
    }

    public function test_edit_renders_profile_wizard()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('candidate.profiles.edit', $this->job));

        $response->assertStatus(200);
        $response->assertViewIs('candidates.profile_wizard');
        $response->assertViewHas('profile');
    }

    public function test_update_validates_and_saves_profile()
    {
        Storage::fake('public');
        $this->actingAs($this->user);

        $data = [
            'poh_id' => $this->poh->id,
            'full_name' => 'John Doe',
            'gender' => 'male',
            'age' => 30,
            'birthplace' => 'Jakarta',
            'birthdate' => '1994-01-01',
            'nik' => '1234567890123456',
            'email' => 'john@example.com',
            'phone' => '081234567890',
            'last_education' => 'S1',
            'education_major' => 'Informatics',
            'education_school' => 'University A',
            'ktp_address' => 'Street A',
            'ktp_village' => 'Village A',
            'ktp_district' => 'District A',
            'ktp_city' => 'City A',
            'ktp_province' => 'Province A',
            'ktp_postal_code' => '12345',
            'domicile_address' => 'Street B',
            'domicile_village' => 'Village B',
            'domicile_district' => 'District B',
            'domicile_city' => 'City B',
            'domicile_province' => 'Province B',
            'domicile_postal_code' => '54321',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'trainings' => [
                [
                    'title' => 'Training A',
                    'institution' => 'Inst A',
                    'period_start' => '2020-01-01',
                    'period_end' => '2020-01-02',
                ]
            ],
            'employments' => [
                [
                    'company' => 'Company A',
                    'position_start' => 'Junior',
                    'period_start' => '2021-01-01',
                ]
            ],
            'references' => [
                [
                    'name' => 'Ref A',
                    'job_title' => 'Manager',
                    'company' => 'Comp Ref',
                    'contact' => '08111',
                ]
            ],
        ];

        $response = $this->post(route('candidate.profiles.update', $this->job), $data);

        $response->assertRedirect(route('jobs.show', $this->job));
        $this->assertDatabaseHas('candidate_profiles', [
            'user_id' => $this->user->id,
            'full_name' => 'John Doe',
            'nik' => '1234567890123456',
        ]);

        $this->assertDatabaseHas('candidate_trainings', ['title' => 'Training A']);
        $this->assertDatabaseHas('candidate_employments', ['company' => 'Company A']);
        $this->assertDatabaseHas('candidate_references', ['name' => 'Ref A']);

        $profile = CandidateProfile::where('user_id', $this->user->id)->first();
        $this->assertNotNull($profile->cv_path);
        Storage::disk('public')->assertExists($profile->cv_path);
    }

    public function test_admin_index_displays_candidates()
    {
        CandidateProfile::factory()->create(['full_name' => 'Candidate Alpha', 'user_id' => User::factory()->create()->id]);
        CandidateProfile::factory()->create(['full_name' => 'Candidate Beta', 'user_id' => User::factory()->create()->id]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.candidates.index'));

        $response->assertStatus(200);
        $response->assertSee('Candidate Alpha');
        $response->assertSee('Candidate Beta');
    }

    public function test_admin_show_displays_candidate_details()
    {
        $profile = CandidateProfile::factory()->create(['full_name' => 'Detail Candidate', 'user_id' => User::factory()->create()->id]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.candidates.show', $profile));

        $response->assertStatus(200);
        $response->assertSee('Detail Candidate');
    }

    public function test_admin_cv_downloads_file()
    {
        Storage::fake('public');
        $path = 'candidates/test_cv.pdf';
        Storage::disk('public')->put($path, "%PDF-1.4\n%dummy pdf content");

        $profile = CandidateProfile::factory()->create([
            'full_name' => 'Test Download',
            'cv_path' => $path,
            'user_id' => User::factory()->create()->id
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.candidates.cv', $profile));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
