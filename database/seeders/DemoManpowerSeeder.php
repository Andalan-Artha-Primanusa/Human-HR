<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CandidateProfile;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Poh;
use App\Models\User;
use App\Models\Offer;
use App\Models\Company;

class DemoManpowerSeeder extends Seeder
{
    public function run(): void
    {
        // ================= COMPANY =================
        $company = Company::firstOrCreate([
            'name' => 'Demo Company',
        ], [
            'code' => 'DEMO',
        ]);

        // ================= POH =================
        $poh = Poh::firstOrCreate([
            'name' => 'Jakarta',
            'code' => 'JKT',
        ], [
            'address' => 'Jl. Sudirman No.1',
            'description' => 'Head Office',
        ]);

        // ================= USER =================
        $user = User::firstOrCreate([
            'email' => 'demo.candidate@demo.com',
        ], [
            'name' => 'Demo Candidate',
            'password' => bcrypt('password'),
            'role' => 'pelamar',
        ]);

        // ================= CANDIDATE =================
        $profile = CandidateProfile::firstOrCreate([
            'user_id' => $user->id,
        ], [
            'full_name' => 'Demo Candidate',
            'nickname' => 'Demo',
            'gender' => 'male',
            'birthplace' => 'Jakarta',
            'birthdate' => '1995-01-01',
            'age' => 31,
            'nik' => (string) rand(1000000000000000, 9999999999999999),
            'phone' => '081234567890',
            'whatsapp' => '081234567890',
            'email' => 'demo.candidate@demo.com',
            'last_education' => 'S1',
            'education_major' => 'Teknik Informatika',
            'education_school' => 'Universitas Demo',
            'ktp_address' => 'Jl. Sudirman No.1',
            'ktp_rt' => '01',
            'ktp_rw' => '02',
            'ktp_village' => 'Karet',
            'ktp_district' => 'Setiabudi',
            'ktp_city' => 'Jakarta Selatan',
            'ktp_province' => 'DKI Jakarta',
            'ktp_postal_code' => '12920',
            'ktp_residence_status' => 'OWN',
            'domicile_address' => 'Jl. Sudirman No.1',
            'domicile_rt' => '01',
            'domicile_rw' => '02',
            'domicile_village' => 'Karet',
            'domicile_district' => 'Setiabudi',
            'domicile_city' => 'Jakarta Selatan',
            'domicile_province' => 'DKI Jakarta',
            'domicile_postal_code' => '12920',
            'domicile_residence_status' => 'OWN',
            'motivation' => 'Ingin berkembang',
            'has_relatives' => false,
            'worked_before' => false,
            'applied_before' => false,
            'willing_out_of_town' => true,
            'poh_id' => $poh->id,
            'current_salary' => 8000000,
            'expected_salary' => 10000000,
            'expected_facilities' => 'BPJS, Tunjangan',
            'available_start_date' => now()->addDays(14),
            'work_motivation' => 'Karir',
            'status_pernikahan' => 'single',
        ]);

        // ================= JOB =================
        $job = Job::firstOrCreate([
            'title' => 'Software Engineer',
        ], [
            'company_id' => $company->id, // ✅ FIX FK
            'code' => 'SE-001',
            'level' => 'staff',
            'division' => 'it',
            'site_id' => null,
            'status' => 'open',
        ]);

        // ================= APPLICATION =================
        $application = JobApplication::firstOrCreate([
            'job_id' => $job->id,
            'user_id' => $user->id,
        ], [
            'poh_id' => $poh->id,
            'current_stage' => 'applied',
            'overall_status' => 'active',
        ]);

        // ================= OFFER =================
        Offer::firstOrCreate([
            'application_id' => $application->id,
        ], [
            'status' => 'accepted',
            'salary' => ['base' => 10000000],
            'body_template' => 'Selamat! Anda diterima.',
            'signed_path' => null,
            'meta' => [],
        ]);
    }
}