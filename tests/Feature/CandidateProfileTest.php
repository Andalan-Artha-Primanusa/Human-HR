<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class CandidateProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_update_basic_profile()
    {
        $pelamar = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        
        $this->actingAs($pelamar);
        
        $response = $this->post(route('candidate.profile.update.personal'), [
            'ktp_number' => '3201010101010101',
            'phone' => '081234567890',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'religion' => 'Islam',
            'marital_status' => 'S',
            'current_address' => 'Jl. Jenderal Sudirman',
            'ktp_address' => 'Jl. Jenderal Sudirman',
            'emergency_contact_name' => 'Budi',
            'emergency_contact_phone' => '081234567890',
            'emergency_contact_relation' => 'Ayah',
            'blood_type' => 'O',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('candidate_profiles', [
            'user_id' => $pelamar->id,
            'ktp_number' => '3201010101010101'
        ]);
    }
}
