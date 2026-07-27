<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Job;
use App\Models\Poh;
use App\Models\Site;
use App\Models\User;

class CandidateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_update_basic_profile()
    {
        $pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        $poh = Poh::factory()->create(['is_active' => true]);
        $site = Site::factory()->create();
        $job = Job::factory()->create(['site_id' => $site->id, 'status' => 'open']);
        
        $this->actingAs($pelamar);
        
        $response = $this->post(route('candidate.profiles.update', $job), [
            'poh_id' => $poh->id,
            'full_name' => $pelamar->name,
            'gender' => 'male',
            'age' => 30,
            'birth_place' => 'Jakarta',
            'birthplace' => 'Jakarta',
            'birthdate' => '1990-01-01',
            'nik' => '3201010101010101',
            'email' => $pelamar->email,
            'phone' => '081234567890',
            'last_education' => 'S1',
            'education_major' => 'Informatika',
            'education_school' => 'Universitas Test',
            'ktp_address' => 'Jl. Jenderal Sudirman',
            'ktp_village' => 'Karet',
            'ktp_district' => 'Setiabudi',
            'ktp_city' => 'Jakarta Selatan',
            'ktp_province' => 'DKI Jakarta',
            'ktp_postal_code' => '12920',
            'domicile_address' => 'Jl. Jenderal Sudirman',
            'domicile_village' => 'Karet',
            'domicile_district' => 'Setiabudi',
            'domicile_city' => 'Jakarta Selatan',
            'domicile_province' => 'DKI Jakarta',
            'domicile_postal_code' => '12920',
            'trainings' => [[
                'title' => 'Basic Safety',
                'institution' => 'AAP Training Center',
                'period_start' => '2024-01-01',
            ]],
            'employments' => [[
                'company' => 'PT Test',
                'position_start' => 'Staff',
                'period_start' => '2023-01-01',
            ]],
            'references' => [[
                'name' => 'Budi',
                'job_title' => 'Manager',
                'company' => 'PT Test',
                'contact' => '081234567891',
            ]],
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('candidate_profiles', [
            'user_id' => $pelamar->id,
            'nik' => '3201010101010101'
        ]);
    }
}
