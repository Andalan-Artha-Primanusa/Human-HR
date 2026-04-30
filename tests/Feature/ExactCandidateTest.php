<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;

class ExactCandidateTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_profile_document_upload()
    {
        $pelamar = User::factory()->create(['role' => 'pelamar']);
        $this->actingAs($pelamar);
        
        Storage::fake('public');
        
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

        $response = $this->post(route('candidate.profile.update.document'), [
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100),
            'ktp_file' => UploadedFile::fake()->image('ktp.jpg'),
            'ijazah_file' => UploadedFile::fake()->create('ijazah.pdf', 100),
            'transkrip_file' => UploadedFile::fake()->create('transkrip.pdf', 100),
            'skck_file' => UploadedFile::fake()->create('skck.pdf', 100),
            'kk_file' => UploadedFile::fake()->image('kk.jpg'),
            'photo_file' => UploadedFile::fake()->image('photo.jpg'),
            'npwp_file' => UploadedFile::fake()->create('npwp.pdf', 100),
            'bpjs_ketenagakerjaan_file' => UploadedFile::fake()->create('bpjs1.pdf', 100),
            'bpjs_kesehatan_file' => UploadedFile::fake()->create('bpjs2.pdf', 100),
        ]);
        
        $response->assertStatus(302);
    }
}
