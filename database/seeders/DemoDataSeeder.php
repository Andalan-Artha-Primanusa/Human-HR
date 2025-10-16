<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\{
    User,
    Site,
    Job,
    JobApplication,
    ApplicationStage,
    CandidateProfile,
    PsychotestTest,
    PsychotestQuestion,
    PsychotestAttempt,
    Interview,
    Offer
};

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        /* ===============================
         * USERS (idempotent by email)
         * =============================== */
        $super = User::updateOrCreate(
            ['email' => 'admin@local.test'],
            [
                'name'              => 'Super Admin',
                'email_verified_at' => now(),
                'password'          => Hash::make('password123'),
                'role'              => 'superadmin',
                'remember_token'    => Str::random(10),
            ]
        );

        $hr = User::updateOrCreate(
            ['email' => 'hr@demo.test'],
            [
                'name'              => 'HR User',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => 'hr',
                'remember_token'    => Str::random(10),
            ]
        );

        $c1 = User::updateOrCreate(
            ['email' => 'andi@demo.test'],
            [
                'name'              => 'Andi Pelamar',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => 'pelamar',
                'remember_token'    => Str::random(10),
            ]
        );
        $c2 = User::updateOrCreate(
            ['email' => 'bela@demo.test'],
            [
                'name'              => 'Bela Pelamar',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => 'pelamar',
                'remember_token'    => Str::random(10),
            ]
        );
        $c3 = User::updateOrCreate(
            ['email' => 'cici@demo.test'],
            [
                'name'              => 'Cici Pelamar',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
                'role'              => 'pelamar',
                'remember_token'    => Str::random(10),
            ]
        );

        /* ===============================
         * CANDIDATE PROFILES (upsert)
         * =============================== */
        foreach ([$c1, $c2, $c3] as $u) {
            CandidateProfile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'full_name'      => $u->name,
                    'phone'          => '081234567890',
                    // gunakan kolom yang ada pada migration candidate_profiles:
                    'ktp_address'    => 'Jl. Contoh No.1',
                    'ktp_city'       => 'Jakarta',
                    'domicile_city'  => 'Jakarta',
                    'extras'         => ['portfolio' => null],
                ]
            );
        }

        /* ===============================
         * JOBS + MANPOWER (upsert by code)
         * =============================== */
        $jobDefs = [
            [
                'code'             => 'PLT-ENG-01',
                'title'            => 'Plant Engineer',
                'division'         => 'Plant',
                'site_code'        => 'DBK',
                'level'            => 'Staff',
                'employment_type'  => 'fulltime',
                'openings'         => 2,
                'status'           => 'open',
                'description'      => 'Support maintenance and reliability.',
            ],
            [
                'code'             => 'SCM-BUY-01',
                'title'            => 'Buyer',
                'division'         => 'SCM',
                'site_code'        => 'POS',
                'level'            => 'Officer',
                'employment_type'  => 'contract',
                'openings'         => 1,
                'status'           => 'open',
                'description'      => 'Procurement operations.',
            ],
            [
                'code'             => 'HR-RECR-01',
                'title'            => 'Recruiter',
                'division'         => 'HR',
                'site_code'        => 'HO',
                'level'            => 'Staff',
                'employment_type'  => 'fulltime',
                'openings'         => 1,
                'status'           => 'open',
                'description'      => 'End-to-end hiring.',
            ],
        ];

        $jobs = collect($jobDefs)->map(function ($d) {
            // pastikan site ada berdasarkan code
            $site = Site::firstOrCreate(
                ['code' => $d['site_code']],
                ['name' => $d['site_code']]
            );

            // payload Job tanpa site_code; ganti ke site_id
            $payload = collect($d)->except('site_code')->toArray();
            $payload['site_id'] = $site->id;

            /** @var Job $job */
            $job = Job::updateOrCreate(
                ['code' => $d['code']],
                $payload
            );

            // manpowerRequirement: satu baris per job (null-safe untuk filled_headcount)
            $filled = optional($job->manpowerRequirement)->filled_headcount ?? 0;

            $job->manpowerRequirement()->updateOrCreate(
                ['job_id' => $job->id],
                [
                    'budget_headcount' => $d['openings'],
                    'filled_headcount' => $filled,
                ]
            );

            return $job;
        })->values();

        /* ===============================
         * PSYCHOTEST master (upsert)
         * =============================== */
        /** @var PsychotestTest $test */
        $test = PsychotestTest::updateOrCreate(
            ['name' => 'Tes Dasar'],
            [
                'duration_minutes' => 20,
                'scoring'          => ['pass_ratio' => 0.6],
                'is_active'        => true,
            ]
        );

        $bank = [
            ['Q: 2+2=?',                           '1','2','4','5',       '4'],
            ['Q: Ibu kota Indonesia?',             'Bandung','Jakarta','Medan','Surabaya','Jakarta'],
            ['Q: Benar/Salah: Air membeku di 0°C', 'true','false', null, null, 'true','truefalse'],
            ['Q: 5*3=?',                           '8','15','10','12',    '15'],
            ['Q: Warna bendera: Merah-___',        'Putih','Biru','Hitam','Kuning','Putih'],
        ];

        foreach ($bank as $i => $row) {
            [$q,$a,$b,$c,$d,$key,$type] = array_pad($row, 7, 'mcq');
            PsychotestQuestion::updateOrCreate(
                ['test_id' => $test->id, 'order_no' => $i],
                [
                    'type'       => $type === 'truefalse' ? 'truefalse' : 'mcq',
                    'question'   => $q,
                    'options'    => $type === 'truefalse' ? null : [$a,$b,$c,$d],
                    'answer_key' => $key,
                    'weight'     => 1,
                ]
            );
        }

        /* ===============================
         * Helper bikin APP + artefak (upsert)
         * =============================== */
        $ensureStage = function (JobApplication $app, string $key, string $status = 'pending', ?float $score = null, array $payload = []) {
            if (!ApplicationStage::where('application_id', $app->id)->where('stage_key', $key)->exists()) {
                ApplicationStage::create([
                    'application_id' => $app->id,
                    'stage_key'      => $key,
                    'status'         => $status,
                    'score'          => $score,
                    'payload'        => $payload,
                ]);
            }
        };

        $ensureAttemptScored = function (JobApplication $app) use ($test) {
            PsychotestAttempt::updateOrCreate(
                ['application_id' => $app->id, 'test_id' => $test->id, 'attempt_no' => 1],
                [
                    'user_id'      => $app->user_id,
                    'status'       => 'scored',
                    'started_at'   => now()->subDay(),
                    'finished_at'  => now()->subDay(),
                    'submitted_at' => now()->subDay(),
                    'expires_at'   => now()->addDays(7),
                    'score'        => 3,
                    'is_active'    => false,
                    'meta'         => ['max_score' => 5],
                ]
            );
        };

        $ensureInterview = function (JobApplication $app) {
            Interview::updateOrCreate(
                ['application_id' => $app->id, 'title' => 'HR Interview'],
                [
                    'mode'         => 'online',
                    'meeting_link' => 'https://meet.google.com/demo-hr',
                    'start_at'     => now()->addDay()->setTime(9, 0),
                    'end_at'       => now()->addDay()->setTime(10, 0),
                    'panel'        => [['name' => 'HR User', 'email' => 'hr@demo.test']],
                ]
            );
        };

        $ensureOffer = function (JobApplication $app) {
            Offer::updateOrCreate(
                ['application_id' => $app->id],
                [
                    'status' => 'draft',
                    'salary' => ['gross' => 8_000_000, 'allowance' => 1_000_000],
                ]
            );
        };

        $makeApp = function (User $user, Job $job, string $stage) use ($ensureStage, $ensureAttemptScored, $ensureInterview, $ensureOffer) {
            /** @var JobApplication $app */
            $app = JobApplication::updateOrCreate(
                ['job_id' => $job->id, 'user_id' => $user->id],
                [
                    'current_stage'  => $stage,
                    'overall_status' => $stage === 'hired' ? 'hired' : 'active',
                ]
            );

            // stage awal (applied)
            $ensureStage($app, 'applied', 'pending');

            // psikotes (historis)
            if (in_array($stage, ['psychotest','hr_iv','user_iv','final','offer','hired'], true)) {
                $ensureAttemptScored($app);
                $ensureStage($app, 'psychotest', 'passed', 3, ['max_score' => 5]);
            }

            // hr interview
            if (in_array($stage, ['hr_iv','user_iv','final','offer','hired'], true)) {
                $ensureInterview($app);
                $ensureStage($app, 'hr_iv', 'pending');
            }

            // user interview & final
            if (in_array($stage, ['user_iv','final','offer','hired'], true)) {
                $ensureStage($app, 'user_iv', 'pending');
            }
            if (in_array($stage, ['final','offer','hired'], true)) {
                $ensureStage($app, 'final', 'pending');
            }

            // offer
            if (in_array($stage, ['offer','hired'], true)) {
                $ensureOffer($app);
                $ensureStage($app, 'offer', 'pending');
            }

            // hired
            if ($stage === 'hired') {
                $app->update(['overall_status' => 'hired']);
                optional($job->manpowerRequirement)->increment('filled_headcount');
            }

            return $app;
        };

        /* ===============================
         * Seed sample apps (idempotent)
         * =============================== */
        $makeApp($c1, $jobs[0], 'psychotest');
        $makeApp($c2, $jobs[1], 'hr_iv');
        $makeApp($c3, $jobs[2], 'offer');
    }
}
