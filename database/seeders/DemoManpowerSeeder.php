<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CandidateProfile;
use App\Models\ApplicationStage;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Poh;
use App\Models\User;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;

class DemoManpowerSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate([
            'name' => 'Demo Company',
        ], [
            'code' => 'DEMO',
        ]);

        $pohJakarta = Poh::firstOrCreate([
            'name' => 'Jakarta',
            'code' => 'JKT',
        ], [
            'address' => 'Jl. Sudirman No.1',
            'description' => 'Head Office',
        ]);

        $pohCikarang = Poh::firstOrCreate([
            'name' => 'Cikarang',
            'code' => 'CKG',
        ], [
            'address' => 'Jl. Industri Raya',
            'description' => 'Plant Area',
        ]);

        $pohKarawang = Poh::firstOrCreate([
            'name' => 'Karawang',
            'code' => 'KRW',
        ], [
            'address' => 'Jl. Raya Tegal',
            'description' => 'Project Site',
        ]);

        $hr = User::where('role', 'hr')->first() ?? User::first();

        $jobs = collect([
            [
                'code' => 'FM-001',
                'title' => 'Foreman',
                'level' => 'foreman',
                'division' => 'plant',
                'openings' => 2,
                'poh' => $pohCikarang,
            ],
            [
                'code' => 'SE-001',
                'title' => 'Software Engineer',
                'level' => 'supervisor',
                'division' => 'it',
                'openings' => 3,
                'poh' => $pohJakarta,
            ],
            [
                'code' => 'SPT-002',
                'title' => 'Superintendent',
                'level' => 'superintendent',
                'division' => 'operations',
                'openings' => 1,
                'poh' => $pohKarawang,
            ],
            [
                'code' => 'ME-002',
                'title' => 'Mechanical Engineer',
                'level' => 'manager',
                'division' => 'engineering',
                'openings' => 2,
                'poh' => $pohCikarang,
            ],
            [
                'code' => 'QA-003',
                'title' => 'Quality Analyst',
                'level' => 'analyst',
                'division' => 'operations',
                'openings' => 2,
                'poh' => $pohKarawang,
            ],
            [
                'code' => 'SP-004',
                'title' => 'Specialist',
                'level' => 'specialist',
                'division' => 'engineering',
                'openings' => 2,
                'poh' => $pohJakarta,
            ],
            [
                'code' => 'EX-005',
                'title' => 'Expert',
                'level' => 'expert',
                'division' => 'it',
                'openings' => 1,
                'poh' => $pohJakarta,
            ],
            [
                'code' => 'TL-004',
                'title' => 'Team Leader Production',
                'level' => 'lead_of',
                'division' => 'plant',
                'openings' => 1,
                'poh' => $pohCikarang,
            ],
            [
                'code' => 'SH-006',
                'title' => 'Section Head',
                'level' => 'section_head',
                'division' => 'operations',
                'openings' => 1,
                'poh' => $pohKarawang,
            ],
            [
                'code' => 'DH-007',
                'title' => 'Department Head',
                'level' => 'dept_head',
                'division' => 'engineering',
                'openings' => 1,
                'poh' => $pohJakarta,
            ],
            [
                'code' => 'PM-008',
                'title' => 'Project Manager',
                'level' => 'project_manager',
                'division' => 'it',
                'openings' => 2,
                'poh' => $pohJakarta,
            ],
            [
                'code' => 'PJO-009',
                'title' => 'PJO Officer',
                'level' => 'pjo',
                'division' => 'operations',
                'openings' => 1,
                'poh' => $pohKarawang,
            ],
        ])->map(function (array $jobData) use ($company) {
            return Job::firstOrCreate([
                'code' => $jobData['code'],
            ], [
                'company_id' => $company->id,
                'title' => $jobData['title'],
                'level' => $jobData['level'],
                'division' => $jobData['division'],
                'site_id' => null,
                'status' => 'open',
                'openings' => $jobData['openings'],
            ]);
        });

        $candidates = [
            [
                'email' => 'demo.sourcing@demo.com',
                'name' => 'Andi Pratama',
                'gender' => 'male',
                'birthdate' => '1993-02-01',
                'age' => 33,
                'last_education' => 'S1',
                'education_major' => 'Teknik Informatika',
                'education_school' => 'Universitas Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'sourcing',
            ],
            [
                'email' => 'demo.onsite@demo.com',
                'name' => 'Bunga Sari',
                'gender' => 'female',
                'birthdate' => '1990-08-11',
                'age' => 35,
                'last_education' => 'D3',
                'education_major' => 'Administrasi Bisnis',
                'education_school' => 'Politeknik Demo',
                'poh' => $pohCikarang,
                'source_channel' => 'onsite',
            ],
            [
                'email' => 'demo.linkedin@demo.com',
                'name' => 'Candra Wijaya',
                'gender' => 'male',
                'birthdate' => '1988-05-05',
                'age' => 37,
                'last_education' => 'S2',
                'education_major' => 'Manajemen',
                'education_school' => 'Universitas Demo',
                'poh' => $pohKarawang,
                'source_channel' => 'linkedin',
            ],
            [
                'email' => 'demo.referral@demo.com',
                'name' => 'Dewi Lestari',
                'gender' => 'female',
                'birthdate' => '1996-12-20',
                'age' => 29,
                'last_education' => 'SMA_SMK',
                'education_major' => 'Akuntansi',
                'education_school' => 'SMK Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'referral',
            ],
            [
                'email' => 'demo.instagram@demo.com',
                'name' => 'Eko Saputra',
                'gender' => 'male',
                'birthdate' => '1998-01-15',
                'age' => 27,
                'last_education' => 'D4',
                'education_major' => 'Teknik Mesin',
                'education_school' => 'Politeknik Demo',
                'poh' => $pohCikarang,
                'source_channel' => 'instagram',
            ],
            [
                'email' => 'demo.jobportal@demo.com',
                'name' => 'Fitri Ayu',
                'gender' => 'female',
                'birthdate' => '1992-11-30',
                'age' => 33,
                'last_education' => 'S1',
                'education_major' => 'Psikologi',
                'education_school' => 'Universitas Demo',
                'poh' => $pohKarawang,
                'source_channel' => 'job_portal',
            ],
            [
                'email' => 'demo.mcu@demo.com',
                'name' => 'Galih Santoso',
                'gender' => 'male',
                'birthdate' => '1991-04-22',
                'age' => 34,
                'last_education' => 'S1',
                'education_major' => 'Teknik Industri',
                'education_school' => 'Universitas Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'sourcing',
            ],
            [
                'email' => 'demo.mobilisasi@demo.com',
                'name' => 'Hana Putri',
                'gender' => 'female',
                'birthdate' => '1995-09-09',
                'age' => 30,
                'last_education' => 'D3',
                'education_major' => 'Akuntansi',
                'education_school' => 'Politeknik Demo',
                'poh' => $pohCikarang,
                'source_channel' => 'onsite',
            ],
            [
                'email' => 'demo.groundtest@demo.com',
                'name' => 'Imam Hidayat',
                'gender' => 'male',
                'birthdate' => '1989-06-18',
                'age' => 36,
                'last_education' => 'S2',
                'education_major' => 'Management',
                'education_school' => 'Universitas Demo',
                'poh' => $pohKarawang,
                'source_channel' => 'referral',
            ],
            [
                'email' => 'demo.applied@demo.com',
                'name' => 'Jihan Ramadhani',
                'gender' => 'female',
                'birthdate' => '1999-03-13',
                'age' => 26,
                'last_education' => 'SMA_SMK',
                'education_major' => 'Administrasi Perkantoran',
                'education_school' => 'SMK Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'job_portal',
            ],
            [
                'email' => 'demo.final@demo.com',
                'name' => 'Kevin Mahendra',
                'gender' => 'male',
                'birthdate' => '1994-07-07',
                'age' => 31,
                'last_education' => 'S1',
                'education_major' => 'Teknik Elektro',
                'education_school' => 'Universitas Demo',
                'poh' => $pohCikarang,
                'source_channel' => 'linkedin',
            ],
            [
                'email' => 'demo.screeningfail@demo.com',
                'name' => 'Lina Puspita',
                'gender' => 'female',
                'birthdate' => '1997-10-10',
                'age' => 28,
                'last_education' => 'D3',
                'education_major' => 'Administrasi',
                'education_school' => 'Politeknik Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'job_portal',
            ],
            [
                'email' => 'demo.hriv@demo.com',
                'name' => 'Maya Salsabila',
                'gender' => 'female',
                'birthdate' => '1993-01-27',
                'age' => 33,
                'last_education' => 'S2',
                'education_major' => 'Manajemen SDM',
                'education_school' => 'Universitas Demo',
                'poh' => $pohKarawang,
                'source_channel' => 'referral',
            ],
            [
                'email' => 'demo.mcu.failed@demo.com',
                'name' => 'Naufal Ardiansyah',
                'gender' => 'male',
                'birthdate' => '1990-06-02',
                'age' => 35,
                'last_education' => 'SMA_SMK',
                'education_major' => 'Teknik Mesin',
                'education_school' => 'SMK Demo',
                'poh' => $pohCikarang,
                'source_channel' => 'sourcing',
            ],
            [
                'email' => 'demo.mobilisasi.pass@demo.com',
                'name' => 'Ocha Permata',
                'gender' => 'female',
                'birthdate' => '1998-12-02',
                'age' => 27,
                'last_education' => 'D4',
                'education_major' => 'Teknik Industri',
                'education_school' => 'Politeknik Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'onsite',
            ],
            [
                'email' => 'demo.ground.pass@demo.com',
                'name' => 'Rizky Aditya',
                'gender' => 'male',
                'birthdate' => '1987-09-14',
                'age' => 38,
                'last_education' => 'S1',
                'education_major' => 'Teknik Sipil',
                'education_school' => 'Universitas Demo',
                'poh' => $pohKarawang,
                'source_channel' => 'referral',
            ],
            [
                'email' => 'demo.acceptedol@demo.com',
                'name' => 'Sinta Rahmawati',
                'gender' => 'female',
                'birthdate' => '1994-02-17',
                'age' => 31,
                'last_education' => 'S1',
                'education_major' => 'Manajemen',
                'education_school' => 'Universitas Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'linkedin',
            ],
            [
                'email' => 'demo.foreman@demo.com',
                'name' => 'Taufan Hermawan',
                'gender' => 'male',
                'birthdate' => '1985-08-22',
                'age' => 41,
                'last_education' => 'D3',
                'education_major' => 'Mekanik',
                'education_school' => 'Politeknik Demo',
                'poh' => $pohCikarang,
                'source_channel' => 'referral',
            ],
            [
                'email' => 'demo.superintendent@demo.com',
                'name' => 'Ursa Wijaya',
                'gender' => 'female',
                'birthdate' => '1990-03-18',
                'age' => 36,
                'last_education' => 'S2',
                'education_major' => 'Teknik Industri',
                'education_school' => 'Universitas Demo',
                'poh' => $pohKarawang,
                'source_channel' => 'sourcing',
            ],
            [
                'email' => 'demo.specialist@demo.com',
                'name' => 'Vito Kusuma',
                'gender' => 'male',
                'birthdate' => '1996-11-05',
                'age' => 29,
                'last_education' => 'S1',
                'education_major' => 'Teknik Perangkat Lunak',
                'education_school' => 'Universitas Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'linkedin',
            ],
            [
                'email' => 'demo.expert@demo.com',
                'name' => 'Widi Nugroho',
                'gender' => 'male',
                'birthdate' => '1986-07-10',
                'age' => 40,
                'last_education' => 'S2',
                'education_major' => 'Sistem Informasi',
                'education_school' => 'Universitas Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'onsite',
            ],
            [
                'email' => 'demo.section.head@demo.com',
                'name' => 'Ximena Sari',
                'gender' => 'female',
                'birthdate' => '1988-12-30',
                'age' => 37,
                'last_education' => 'S2',
                'education_major' => 'Manajemen Operasional',
                'education_school' => 'Universitas Demo',
                'poh' => $pohKarawang,
                'source_channel' => 'job_portal',
            ],
            [
                'email' => 'demo.dept.head@demo.com',
                'name' => 'Yuki Hartono',
                'gender' => 'male',
                'birthdate' => '1980-05-15',
                'age' => 46,
                'last_education' => 'S2',
                'education_major' => 'Teknik Industri',
                'education_school' => 'Universitas Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'referral',
            ],
            [
                'email' => 'demo.project.manager@demo.com',
                'name' => 'Zara Kusuma',
                'gender' => 'female',
                'birthdate' => '1992-09-25',
                'age' => 33,
                'last_education' => 'S1',
                'education_major' => 'Manajemen Proyek',
                'education_school' => 'Universitas Demo',
                'poh' => $pohJakarta,
                'source_channel' => 'linkedin',
            ],
            [
                'email' => 'demo.pjo@demo.com',
                'name' => 'Amara Rindani',
                'gender' => 'female',
                'birthdate' => '1998-01-08',
                'age' => 28,
                'last_education' => 'S1',
                'education_major' => 'Administrasi',
                'education_school' => 'Universitas Demo',
                'poh' => $pohKarawang,
                'source_channel' => 'onsite',
            ],
        ];

        $createdCandidates = [];

        foreach ($candidates as $candidate) {
            $user = User::updateOrCreate(
                ['email' => $candidate['email']],
                [
                    'name' => $candidate['name'],
                    'password' => bcrypt('password'),
                    'role' => 'pelamar',
                ]
            );

            $createdCandidates[] = CandidateProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $candidate['name'],
                    'nickname' => strtok($candidate['name'], ' '),
                    'gender' => $candidate['gender'],
                    'birthplace' => 'Jakarta',
                    'birthdate' => $candidate['birthdate'],
                    'age' => $candidate['age'],
                    'nik' => (string) random_int(1000000000000000, 9999999999999999),
                    'phone' => '08' . random_int(1000000000, 9999999999),
                    'whatsapp' => '08' . random_int(1000000000, 9999999999),
                    'email' => $candidate['email'],
                    'source_channel' => $candidate['source_channel'],
                    'last_education' => $candidate['last_education'],
                    'education_major' => $candidate['education_major'],
                    'education_school' => $candidate['education_school'],
                    'ktp_address' => 'Jl. Demo No.1',
                    'ktp_rt' => '01',
                    'ktp_rw' => '02',
                    'ktp_village' => 'Demo',
                    'ktp_district' => 'Demo Utara',
                    'ktp_city' => 'Jakarta Selatan',
                    'ktp_province' => 'DKI Jakarta',
                    'ktp_postal_code' => '12920',
                    'ktp_residence_status' => 'OWN',
                    'domicile_address' => 'Jl. Demo No.1',
                    'domicile_rt' => '01',
                    'domicile_rw' => '02',
                    'domicile_village' => 'Demo',
                    'domicile_district' => 'Demo Utara',
                    'domicile_city' => 'Jakarta Selatan',
                    'domicile_province' => 'DKI Jakarta',
                    'domicile_postal_code' => '12920',
                    'domicile_residence_status' => 'OWN',
                    'motivation' => 'Ingin berkembang',
                    'has_relatives' => false,
                    'worked_before' => false,
                    'applied_before' => false,
                    'willing_out_of_town' => true,
                    'poh_id' => $candidate['poh']->id,
                    'current_salary' => 8000000,
                    'expected_salary' => 10000000,
                    'expected_facilities' => 'BPJS, Tunjangan',
                    'available_start_date' => now()->addDays(14),
                    'work_motivation' => 'Karir',
                    'status_pernikahan' => 'single',
                ]
            );
        }

        $applications = [
            [
                'job' => $jobs[0],
                'user_email' => 'demo.sourcing@demo.com',
                'poh' => $pohJakarta,
                'stage' => 'hired',
                'overall' => 'hired',
                'source' => 'sourcing',
                'offer_status' => 'accepted',
                'stage_history' => [
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                    ['hired', 'passed'],
                ],
            ],
            [
                'job' => $jobs[0],
                'user_email' => 'demo.onsite@demo.com',
                'poh' => $pohCikarang,
                'stage' => 'not_qualified',
                'overall' => 'not_qualified',
                'source' => 'onsite',
                'offer_status' => null,
                'stage_history' => [
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'failed'],
                ],
            ],
            [
                'job' => $jobs[1],
                'user_email' => 'demo.linkedin@demo.com',
                'poh' => $pohKarawang,
                'stage' => 'offer',
                'overall' => 'active',
                'source' => 'linkedin',
                'offer_status' => 'accepted',
                'stage_history' => [
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                ],
            ],
            [
                'job' => $jobs[2],
                'user_email' => 'demo.referral@demo.com',
                'poh' => $pohJakarta,
                'stage' => 'psychotest',
                'overall' => 'active',
                'source' => 'referral',
                'offer_status' => null,
                'stage_history' => [
                    ['screening', 'passed'],
                    ['psychotest', 'failed'],
                ],
            ],
            [
                'job' => $jobs[3],
                'user_email' => 'demo.instagram@demo.com',
                'poh' => $pohCikarang,
                'stage' => 'user_iv',
                'overall' => 'active',
                'source' => 'instagram',
                'offer_status' => null,
                'stage_history' => [
                    ['screening', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'no-show'],
                ],
            ],
            [
                'job' => $jobs[1],
                'user_email' => 'demo.jobportal@demo.com',
                'poh' => $pohKarawang,
                'stage' => 'screening',
                'overall' => 'active',
                'source' => 'job_portal',
                'offer_status' => null,
                'stage_history' => [
                    ['screening', 'pending'],
                ],
            ],
            [
                'job' => $jobs[2],
                'user_email' => 'demo.mcu@demo.com',
                'poh' => $pohJakarta,
                'stage' => 'mcu',
                'overall' => 'active',
                'source' => 'sourcing',
                'offer_status' => null,
                'stage_history' => [
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                    ['mcu', 'passed'],
                ],
            ],
            [
                'job' => $jobs[3],
                'user_email' => 'demo.mobilisasi@demo.com',
                'poh' => $pohCikarang,
                'stage' => 'mobilisasi',
                'overall' => 'active',
                'source' => 'onsite',
                'offer_status' => null,
                'stage_history' => [
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                    ['mcu', 'passed'],
                    ['mobilisasi', 'pending'],
                ],
            ],
            [
                'job' => $jobs[1],
                'user_email' => 'demo.groundtest@demo.com',
                'poh' => $pohKarawang,
                'stage' => 'ground_test',
                'overall' => 'active',
                'source' => 'referral',
                'offer_status' => null,
                'stage_history' => [
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                    ['ground_test', 'failed'],
                ],
            ],
            [
                'job' => $jobs[0],
                'user_email' => 'demo.applied@demo.com',
                'poh' => $pohJakarta,
                'stage' => 'applied',
                'overall' => 'active',
                'source' => 'job_portal',
                'offer_status' => null,
                'stage_history' => [
                    ['applied', 'pending'],
                ],
            ],
            [
                'job' => $jobs[1],
                'user_email' => 'demo.final@demo.com',
                'poh' => $pohCikarang,
                'stage' => 'final',
                'overall' => 'active',
                'source' => 'linkedin',
                'offer_status' => 'accepted',
                'stage_history' => [
                    ['applied', 'passed'],
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['final', 'passed'],
                ],
            ],
            [
                'job' => $jobs[2],
                'user_email' => 'demo.screeningfail@demo.com',
                'poh' => $pohJakarta,
                'stage' => 'screening',
                'overall' => 'not_qualified',
                'source' => 'job_portal',
                'offer_status' => null,
                'stage_history' => [
                    ['applied', 'passed'],
                    ['screening', 'failed'],
                ],
            ],
            [
                'job' => $jobs[3],
                'user_email' => 'demo.hriv@demo.com',
                'poh' => $pohKarawang,
                'stage' => 'hr_iv',
                'overall' => 'active',
                'source' => 'referral',
                'offer_status' => null,
                'stage_history' => [
                    ['applied', 'passed'],
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                ],
            ],
            [
                'job' => $jobs[0],
                'user_email' => 'demo.mcu.failed@demo.com',
                'poh' => $pohCikarang,
                'stage' => 'mcu',
                'overall' => 'active',
                'source' => 'sourcing',
                'offer_status' => null,
                'stage_history' => [
                    ['applied', 'passed'],
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                    ['mcu', 'failed'],
                ],
            ],
            [
                'job' => $jobs[2],
                'user_email' => 'demo.mobilisasi.pass@demo.com',
                'poh' => $pohJakarta,
                'stage' => 'mobilisasi',
                'overall' => 'active',
                'source' => 'onsite',
                'offer_status' => null,
                'stage_history' => [
                    ['applied', 'passed'],
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                    ['mcu', 'passed'],
                    ['mobilisasi', 'passed'],
                ],
            ],
            [
                'job' => $jobs[3],
                'user_email' => 'demo.ground.pass@demo.com',
                'poh' => $pohKarawang,
                'stage' => 'ground_test',
                'overall' => 'active',
                'source' => 'referral',
                'offer_status' => null,
                'stage_history' => [
                    ['applied', 'passed'],
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                    ['ground_test', 'passed'],
                ],
            ],
            [
                'job' => $jobs[1],
                'user_email' => 'demo.acceptedol@demo.com',
                'poh' => $pohJakarta,
                'stage' => 'hired',
                'overall' => 'hired',
                'source' => 'linkedin',
                'offer_status' => 'accepted',
                'stage_history' => [
                    ['screening', 'passed'],
                    ['psychotest', 'passed'],
                    ['hr_iv', 'passed'],
                    ['user_iv', 'passed'],
                    ['offer', 'passed'],
                    ['hired', 'passed'],
                ],
            ],
        ];

        foreach ($applications as $payload) {
            $user = User::where('email', $payload['user_email'])->firstOrFail();

            $application = JobApplication::updateOrCreate(
                [
                    'job_id' => $payload['job']->id,
                    'user_id' => $user->id,
                ],
                [
                    'poh_id' => $payload['poh']->id,
                    'current_stage' => $payload['stage'],
                    'overall_status' => $payload['overall'],
                ]
            );

            foreach ($payload['stage_history'] as [$stageKey, $status]) {
                ApplicationStage::updateOrCreate(
                    [
                        'application_id' => $application->id,
                        'stage_key' => $stageKey,
                    ],
                    [
                        'status' => $status,
                        'acted_by' => $hr?->id,
                        'user_id' => $user->id,
                        'notes' => 'Demo stage history',
                    ]
                );
            }

            if (($payload['offer_status'] ?? null) === 'accepted') {
                Offer::updateOrCreate(
                    ['application_id' => $application->id],
                    [
                        'status' => 'accepted',
                        'salary' => ['base' => 10000000],
                        'body_template' => 'Selamat! Anda diterima.',
                        'signed_path' => null,
                        'meta' => [],
                    ]
                );

                $daysAgo = match ($payload['user_email']) {
                    'demo.sourcing@demo.com' => 24,
                    'demo.linkedin@demo.com' => 18,
                    'demo.final@demo.com' => 12,
                    'demo.acceptedol@demo.com' => 6,
                    default => 10,
                };

                DB::table('job_listings')
                    ->where('id', $payload['job']->id)
                    ->update([
                        'created_at' => now()->subDays($daysAgo + 10),
                        'updated_at' => now()->subDays($daysAgo + 10),
                    ]);

                DB::table('offers')
                    ->where('application_id', $application->id)
                    ->update([
                        'created_at' => now()->subDays($daysAgo),
                        'updated_at' => now()->subDays($daysAgo),
                    ]);
            }
        }
    }
}