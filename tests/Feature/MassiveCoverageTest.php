<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Job;
use App\Models\Poh;
use App\Models\Company;
use App\Models\Site;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MassiveCoverageTest extends TestCase
{
    use RefreshDatabase;
    protected $admin;
    protected $pelamar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'superadmin', 'email_verified_at' => now()]);
        $this->pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
    }

    public function test_candidate_profile_full_flow()
    {
        $this->actingAs($this->pelamar);
        
        $this->post(route('candidate.profile.update.personal'), [
            'ktp_number' => '1234567890123456',
            'phone' => '081234567890',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'religion' => 'Islam',
            'marital_status' => 'S',
            'current_address' => 'Jl Test',
            'ktp_address' => 'Jl Test',
            'emergency_contact_name' => 'Ayah',
            'emergency_contact_phone' => '08123456789',
            'emergency_contact_relation' => 'Ayah',
            'blood_type' => 'O'
        ]);

        $this->post(route('candidate.profile.update.document'), [
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100),
            'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
            'ijazah_file' => UploadedFile::fake()->create('ijazah.pdf', 100),
            'transkrip_file' => UploadedFile::fake()->create('transkrip.pdf', 100),
            'skck_file' => UploadedFile::fake()->create('skck.pdf', 100),
        ]);

        $this->assertDatabaseHas('candidate_profiles', ['ktp_number' => '1234567890123456']);
    }

    public function test_candidate_training_and_experience()
    {
        $this->actingAs($this->pelamar);
        
        $this->post(route('candidate.profile.training.store'), [
            'name' => 'Sertifikasi IT',
            'organizer' => 'Google',
            'year' => '2020',
            'certificate_file' => UploadedFile::fake()->create('cert.pdf', 100)
        ]);
        
        $this->post(route('candidate.profile.experience.store'), [
            'company_name' => 'PT Test',
            'position' => 'Staff',
            'start_date' => '2019-01-01',
            'end_date' => '2021-01-01',
            'job_description' => 'Test Desc',
            'certificate_file' => UploadedFile::fake()->create('exp.pdf', 100)
        ]);

        $this->assertDatabaseHas('candidate_trainings', ['name' => 'Sertifikasi IT']);
        $this->assertDatabaseHas('candidate_experiences', ['company_name' => 'PT Test']);
    }

    public function test_job_controller_full_crud()
    {
        $this->actingAs($this->admin);
        
        $c = Company::create(['name' => 'C', 'code' => 'C']);
        $s = Site::create(['name' => 'S', 'code' => 'S', 'company_id' => $c->id]);
        $p = Poh::create(['name' => 'P', 'code' => 'P']);

        $this->post(route('admin.jobs.store'), [
            'title' => 'Job Manager',
            'code' => 'JM-01',
            'slug' => 'job-manager',
            'department' => 'IT',
            'level' => 3,
            'site_id' => $s->id,
            'poh_id' => $p->id,
            'status' => 'open',
            'description' => 'Desc',
            'requirements' => 'Req'
        ]);

        $job = Job::where('code', 'JM-01')->first();
        $this->assertNotNull($job);

        $this->put(route('admin.jobs.update', $job->id), [
            'title' => 'Job Manager Updated',
            'code' => 'JM-01',
            'level' => 3,
            'site_id' => $s->id,
            'poh_id' => $p->id,
            'status' => 'draft',
            'description' => 'Desc',
            'requirements' => 'Req'
        ]);

        $this->delete(route('admin.jobs.destroy', $job->id));
        $this->assertDatabaseMissing('jobs', ['id' => $job->id]);
    }
}
