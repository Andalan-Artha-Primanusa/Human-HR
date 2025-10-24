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
    Offer,
    ManpowerRequirement, // budget_headcount dihitung di model
};

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        /* ===============================
         * USERS (idempotent by email)
         * =============================== */
        $staff = [
            ['email' => 'admin@local.test', 'name' => 'Super Admin', 'role' => 'superadmin', 'password' => 'password123'],
            ['email' => 'hr@demo.test',     'name' => 'HR User',     'role' => 'hr',         'password' => 'password'],
            ['email' => 'hr2@demo.test',    'name' => 'HR Senior',   'role' => 'hr',         'password' => 'password'],
        ];

        $candidates = [
            ['email' => 'andi@demo.test',  'name' => 'Andi Pelamar'],
            ['email' => 'bela@demo.test',  'name' => 'Bela Pelamar'],
            ['email' => 'cici@demo.test',  'name' => 'Cici Pelamar'],
            ['email' => 'dodi@demo.test',  'name' => 'Dodi Pelamar'],
            ['email' => 'eko@demo.test',   'name' => 'Eko Pelamar'],
            ['email' => 'fira@demo.test',  'name' => 'Fira Pelamar'],
            ['email' => 'gina@demo.test',  'name' => 'Gina Pelamar'],
            ['email' => 'hadi@demo.test',  'name' => 'Hadi Pelamar'],
            ['email' => 'intan@demo.test', 'name' => 'Intan Pelamar'],
            ['email' => 'joni@demo.test',  'name' => 'Joni Pelamar'],
        ];

        $users = [];

        foreach ($staff as $s) {
            $users[$s['email']] = User::updateOrCreate(
                ['email' => $s['email']],
                [
                    'name'              => $s['name'],
                    'email_verified_at' => now(),
                    'password'          => Hash::make($s['password']),
                    'role'              => $s['role'],
                    'remember_token'    => Str::random(10),
                ]
            );
        }

        foreach ($candidates as $c) {
            $users[$c['email']] = User::updateOrCreate(
                ['email' => $c['email']],
                [
                    'name'              => $c['name'],
                    'email_verified_at' => now(),
                    'password'          => Hash::make('password'),
                    'role'              => 'pelamar',
                    'remember_token'    => Str::random(10),
                ]
            );
        }

        /* ===============================
         * CANDIDATE PROFILES (upsert)
         * =============================== */
        foreach ($candidates as $c) {
            $u = $users[$c['email']];
            CandidateProfile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'full_name'      => $u->name,
                    'phone'          => '081234567890',
                    'ktp_address'    => 'Jl. Contoh No. 1',
                    'ktp_city'       => 'Jakarta',
                    'domicile_city'  => 'Jakarta',
                    'extras'         => ['portfolio' => null],
                ]
            );
        }

        /* ===============================
         * Master Sites (idempotent)
         * =============================== */
        $sitesByCode = [
            'HO'  => 'Head Office',
            'DBK' => 'Site Debukit',
            'POS' => 'Site Pos',
            'SBS' => 'Site Sabas',
            'MKS' => 'Site Makassar',
        ];
        $siteMap = [];
        foreach ($sitesByCode as $code => $name) {
            $siteMap[$code] = Site::updateOrCreate(['code' => $code], ['name' => $name]);
        }

        /* ===============================
         * JOBS + MANPOWER per-site (upsert)
         * =============================== */
        $manpowerMatrix = [
            // site_code => assets, ratio
            'DBK' => ['assets' => 12, 'ratio' => 2.50],
            'POS' => ['assets' =>  5, 'ratio' => 2.60],
            'HO'  => ['assets' =>  3, 'ratio' => 2.50],
            'SBS' => ['assets' =>  7, 'ratio' => 2.40],
            'MKS' => ['assets' =>  6, 'ratio' => 2.30],
        ];

        // Helper deskripsi HTML rapi
        $desc = fn(array $d) => sprintf(
            '<div>
               <p class="mb-2">%s</p>
               <h4 class="mt-3 mb-1"><strong>Tanggung Jawab</strong></h4>
               <ul>%s</ul>
               <h4 class="mt-3 mb-1"><strong>Kualifikasi</strong></h4>
               <ul>%s</ul>
               <h4 class="mt-3 mb-1"><strong>Benefit</strong></h4>
               <ul>%s</ul>
             </div>',
            e($d['intro']),
            collect($d['resp'])->map(fn($li) => '<li>' . e($li) . '</li>')->implode(''),
            collect($d['reqs'])->map(fn($li) => '<li>' . e($li) . '</li>')->implode(''),
            collect($d['benefit'])->map(fn($li) => '<li>' . e($li) . '</li>')->implode('')
        );

        // ====== Banyak lowongan
        $jobDefs = [
            // Plant & Maintenance
            [
                'code' => 'PLT-ENG-01',
                'title' => 'Plant Engineer',
                'division' => 'Plant',
                'site_code' => 'DBK',
                'level' => 'Staff',
                'employment_type' => 'fulltime',
                'openings' => 2,
                'status' => 'open',
                'skills' => ['CMMS', 'Preventive Maintenance', 'Root Cause Analysis', 'Reliability'],
                'keywords' => 'maintenance,routes,plant,engineer,cmms,rca',
                'description' => $desc([
                    'intro' => 'Bertanggung jawab pemeliharaan peralatan plant untuk availability & reliability target.',
                    'resp' => ['Jadwal preventive & predictive maintenance', 'Koordinasi breakdown & RCA', 'Update histori CMMS', 'Kolaborasi HSE perizinan kerja'],
                    'reqs' => ['S1 Teknik Mesin/Elektro/Industri', '1–3 tahun di maintenance/plant', 'Baca P&ID, manual, SOP', 'CMMS & MS Office'],
                    'benefit' => ['Asuransi kesehatan', 'Mess site', 'Pelatihan & sertifikasi'],
                ]),
            ],
            [
                'code' => 'PLT-MECH-01',
                'title' => 'Mechanic',
                'division' => 'Plant',
                'site_code' => 'DBK',
                'level' => 'Staff',
                'employment_type' => 'fulltime',
                'openings' => 3,
                'status' => 'open',
                'skills' => ['Engine', 'Hydraulic', 'Welding'],
                'keywords' => 'mechanic,plant,maintenance,engine',
                'description' => $desc([
                    'intro' => 'Melakukan perbaikan dan perawatan unit mekanikal.',
                    'resp' => ['Troubleshooting', 'Overhaul ringan', 'Perawatan berkala'],
                    'reqs' => ['SMK/D3 Mesin', 'Pengalaman 1 tahun'],
                    'benefit' => ['Asuransi', 'Mess', 'Uang lembur'],
                ]),
            ],
            [
                'code' => 'ELEC-TECH-01',
                'title' => 'Electrical Technician',
                'division' => 'Plant',
                'site_code' => 'SBS',
                'level' => 'Staff',
                'employment_type' => 'fulltime',
                'openings' => 2,
                'status' => 'open',
                'skills' => ['PLC', 'Troubleshooting', 'Panel'],
                'keywords' => 'electrical,plc,panel,troubleshoot',
                'description' => $desc([
                    'intro' => 'Menangani sistem kelistrikan dan PLC.',
                    'resp' => ['Perbaikan panel', 'Cek sensor/actuator', 'Update wiring'],
                    'reqs' => ['D3 Elektro', 'Baca diagram kelistrikan'],
                    'benefit' => ['Asuransi', 'Mess', 'Pelatihan'],
                ]),
            ],

            // SCM & Warehouse
            [
                'code' => 'SCM-BUY-01',
                'title' => 'Buyer',
                'division' => 'SCM',
                'site_code' => 'POS',
                'level' => 'Officer',
                'employment_type' => 'contract',
                'openings' => 1,
                'status' => 'open',
                'skills' => ['Procurement', 'Vendor Management', 'PO/PR', 'Negotiation'],
                'keywords' => 'buyer,scm,purchasing,procurement,pr,po,vendor',
                'description' => $desc([
                    'intro' => 'Mengelola PR–PO untuk ketersediaan barang/jasa.',
                    'resp' => ['RFQ & negosiasi', 'Terbitkan PO & follow-up', 'Evaluasi vendor'],
                    'reqs' => ['D3/S1', 'Pengalaman 1–2 tahun'],
                    'benefit' => ['BPJS', 'Transport'],
                ]),
            ],
            [
                'code' => 'WH-ADM-01',
                'title' => 'Warehouse Admin',
                'division' => 'SCM',
                'site_code' => 'POS',
                'level' => 'Staff',
                'employment_type' => 'fulltime',
                'openings' => 2,
                'status' => 'open',
                'skills' => ['Inventory', 'FIFO', 'Documentation'],
                'keywords' => 'warehouse,inventory,admin,fifo',
                'description' => $desc([
                    'intro' => 'Administrasi gudang & inventory.',
                    'resp' => ['Penerimaan barang', 'Stok opname', 'Dokumentasi keluar masuk'],
                    'reqs' => ['SMK/D3', 'Teliti & rapi'],
                    'benefit' => ['BPJS', 'Uang makan'],
                ]),
            ],

            // HR & Office
            [
                'code' => 'HR-RECR-01',
                'title' => 'Recruiter',
                'division' => 'HR',
                'site_code' => 'HO',
                'level' => 'Staff',
                'employment_type' => 'fulltime',
                'openings' => 1,
                'status' => 'open',
                'skills' => ['Sourcing', 'Interviewing', 'ATS', 'Candidate Experience'],
                'keywords' => 'recruiter,hr,hiring,interview,ats,sourcing',
                'description' => $desc([
                    'intro' => 'End-to-end recruitment dengan candidate experience yang baik.',
                    'resp' => ['Sourcing', 'Screening CV', 'Koordinasi tes & interview', 'Offering'],
                    'reqs' => ['S1 Psikologi/Manajemen/SDM', 'Familiar ATS'],
                    'benefit' => ['Asuransi', 'Hybrid'],
                ]),
            ],
            [
                'code' => 'IT-INT-01',
                'title' => 'IT Support Intern',
                'division' => 'IT',
                'site_code' => 'HO',
                'level' => 'Intern',
                'employment_type' => 'intern',
                'openings' => 1,
                'status' => 'open',
                'skills' => ['Helpdesk', 'Asset Tagging', 'Troubleshooting', 'Windows'],
                'keywords' => 'intern,it support,helpdesk,tagging,troubleshooting',
                'description' => $desc([
                    'intro' => 'Mendukung helpdesk & inventory aset.',
                    'resp' => ['Tiket harian', 'Asset tagging', 'Install aplikasi'],
                    'reqs' => ['Mahasiswa TI/SI/Ilkom', 'Dasar jaringan'],
                    'benefit' => ['Uang saku', 'Sertifikat'],
                ]),
            ],

            // HSE & QA
            [
                'code' => 'HSE-OFF-01',
                'title' => 'HSE Officer',
                'division' => 'HSE',
                'site_code' => 'DBK',
                'level' => 'Staff',
                'employment_type' => 'fulltime',
                'openings' => 1,
                'status' => 'open',
                'skills' => ['Safety Audit', 'PPE', 'Incident Report'],
                'keywords' => 'hse,safety,audit,ppe',
                'description' => $desc([
                    'intro' => 'Menjaga kepatuhan K3 di area kerja.',
                    'resp' => ['Safety walk', 'Inspeksi PPE', 'Laporan insiden'],
                    'reqs' => ['D3/S1 K3/Teknik', 'Paham peraturan K3'],
                    'benefit' => ['Asuransi', 'Mess'],
                ]),
            ],
            [
                'code' => 'QA-TECH-01',
                'title' => 'QA Technician',
                'division' => 'QA',
                'site_code' => 'SBS',
                'level' => 'Staff',
                'employment_type' => 'fulltime',
                'openings' => 1,
                'status' => 'open',
                'skills' => ['Sampling', '5S', 'Documentation'],
                'keywords' => 'qa,sampling,5s,doc',
                'description' => $desc([
                    'intro' => 'Mendukung inline inspection dan dokumentasi mutu.',
                    'resp' => ['Sampling', 'Pencatatan hasil', 'Perbaikan dokumen SOP/IK'],
                    'reqs' => ['D3/S1 Teknik/Industri', 'Teliti & rapi'],
                    'benefit' => ['Asuransi', 'Uang makan'],
                ]),
            ],

            // Operations
            [
                'code' => 'OPS-DRV-01',
                'title' => 'Driver Operasional',
                'division' => 'Operations',
                'site_code' => 'MKS',
                'level' => 'Non Staff',
                'employment_type' => 'fulltime',
                'openings' => 4,
                'status' => 'open',
                'skills' => ['Defensive Driving', 'Logbook'],
                'keywords' => 'driver,operational,logbook',
                'description' => $desc([
                    'intro' => 'Mengemudi untuk kebutuhan operasional site.',
                    'resp' => ['Antar-jemput personel', 'Perawatan kendaraan ringan', 'Isi logbook'],
                    'reqs' => ['SIM A aktif', 'Pengalaman 1 tahun'],
                    'benefit' => ['Asuransi', 'Uang makan', 'Uang lembur'],
                ]),
            ],
            [
                'code' => 'OPS-OPR-01',
                'title' => 'Operator Alat Berat',
                'division' => 'Operations',
                'site_code' => 'DBK',
                'level' => 'Non Staff',
                'employment_type' => 'fulltime',
                'openings' => 5,
                'status' => 'open',
                'skills' => ['Excavator', 'Loader', 'Safety'],
                'keywords' => 'operator,alat berat,excavator',
                'description' => $desc([
                    'intro' => 'Mengoperasikan alat berat sesuai SOP & K3.',
                    'resp' => ['Operasikan unit', 'Periksa harian (P2H)', 'Lapor abnormal'],
                    'reqs' => ['Sertifikat operator', 'Pengalaman 1 tahun'],
                    'benefit' => ['Asuransi', 'Mess', 'Insentif'],
                ]),
            ],
        ];

        // Helper: buat / update 1 baris manpower per (job, site)
        $ensureManpower = function (Job $job, Site $site, int $assets, float $ratio): ManpowerRequirement {
            /** @var ManpowerRequirement $m */
            $m = ManpowerRequirement::updateOrCreate(
                ['job_id' => $job->id, 'site_id' => $site->id],
                [
                    'assets_count'    => $assets,
                    'ratio_per_asset' => $ratio, // budget_headcount via model hook
                ]
            );
            return $m->fresh();
        };

        // Helper: hitung ulang total openings (SUM budget semua site)
        $recalcJobOpenings = function (Job $job): void {
            $sum = (int) ManpowerRequirement::where('job_id', $job->id)->sum('budget_headcount');
            if ($sum > 0 && (int) $job->openings !== $sum) {
                $job->update(['openings' => $sum]);
            }
        };

        // Upsert jobs + manpower (termasuk multi-site demo)
        $jobs = collect($jobDefs)->map(function ($d) use ($siteMap, $manpowerMatrix, $ensureManpower, $recalcJobOpenings) {
            $site = $siteMap[$d['site_code']] ?? Site::firstOrCreate(['code' => $d['site_code']], ['name' => $d['site_code']]);

            $payload = collect($d)->except('site_code')->toArray();
            $payload['site_id'] = $site->id;

            /** @var Job $job */
            $job = Job::updateOrCreate(['code' => $d['code']], $payload);

            // baris utama
            $mainAssets = $manpowerMatrix[$d['site_code']]['assets'] ?? $d['openings'];
            $mainRatio  = $manpowerMatrix[$d['site_code']]['ratio']  ?? 2.50;
            $ensureManpower($job, $site, (int) $mainAssets, (float) $mainRatio);

            // baris tambahan untuk contoh multi-site
            foreach ($manpowerMatrix as $code => $cfg) {
                if ($code === $d['site_code']) continue;
                if (!isset($siteMap[$code])) continue;
                if (($cfg['assets'] ?? 0) > 0) {
                    $ensureManpower($job, $siteMap[$code], (int) $cfg['assets'], (float) $cfg['ratio']);
                }
            }

            $recalcJobOpenings($job);

            return $job->fresh();
        })->keyBy('code');

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
            ['Q: 2+2=?',                           '1', '2', '4', '5',       '4'],
            ['Q: Ibu kota Indonesia?',             'Bandung', 'Jakarta', 'Medan', 'Surabaya', 'Jakarta'],
            ['Q: Benar/Salah: Air membeku di 0°C', 'true', 'false', null, null, 'true', 'truefalse'],
            ['Q: 5*3=?',                           '8', '15', '10', '12',    '15'],
            ['Q: Warna bendera: Merah-___',        'Putih', 'Biru', 'Hitam', 'Kuning', 'Putih'],
        ];

        foreach ($bank as $i => $row) {
            [$q, $a, $b, $c, $d, $key, $type] = array_pad($row, 7, 'mcq');
            PsychotestQuestion::updateOrCreate(
                ['test_id' => $test->id, 'order_no' => $i],
                [
                    'type'       => $type === 'truefalse' ? 'truefalse' : 'mcq',
                    'question'   => $q,
                    'options'    => $type === 'truefalse' ? null : [$a, $b, $c, $d],
                    'answer_key' => $key,
                    'weight'     => 1,
                ]
            );
        }

        /* ===============================
         * HELPERS: artefak proses lengkap
         * =============================== */
        $ensureStage = function (JobApplication $app, string $key, string $status = 'pending', ?float $score = null, array $payload = []) {
            // status harus salah satu dari enum: pending|passed|failed|no-show|reschedule
            if (!in_array($status, ['pending', 'passed', 'failed', 'no-show', 'reschedule'], true)) {
                $status = 'passed'; // fallback aman
            }

            if (!ApplicationStage::where('application_id', $app->id)->where('stage_key', $key)->exists()) {
                ApplicationStage::create([
                    'application_id' => $app->id,
                    'stage_key'      => $key,
                    'status'         => $status,
                    'score'          => $score,
                    'payload'        => $payload,
                ]);
            } else {
                ApplicationStage::where('application_id', $app->id)
                    ->where('stage_key', $key)
                    ->update(['status' => $status, 'score' => $score, 'payload' => $payload]);
            }
        };

        $ensureAttemptScored = function (JobApplication $app) use ($test) {
            PsychotestAttempt::updateOrCreate(
                ['application_id' => $app->id, 'test_id' => $test->id, 'attempt_no' => 1],
                [
                    'user_id'      => $app->user_id,
                    'status'       => 'scored',
                    'started_at'   => now()->subDays(3),
                    'finished_at'  => now()->subDays(3)->addMinutes(18),
                    'submitted_at' => now()->subDays(3)->addMinutes(18),
                    'expires_at'   => now()->addDays(7),
                    'score'        => 4,
                    'is_active'    => false,
                    'meta'         => ['max_score' => 5],
                ]
            );
        };

        $ensureInterview = function (JobApplication $app, string $title, string $mode = 'online', ?string $link = null, ?string $location = null, int $addDays = 1) {
            Interview::updateOrCreate(
                ['application_id' => $app->id, 'title' => $title],
                [
                    'mode'         => $mode,
                    'meeting_link' => $link,
                    'location'     => $location,
                    'start_at'     => now()->addDays($addDays)->setTime(9, 0),
                    'end_at'       => now()->addDays($addDays)->setTime(10, 0),
                    'panel'        => [['name' => 'HR User', 'email' => 'hr@demo.test']],
                ]
            );
        };

        $ensureOffer = function (JobApplication $app, string $status = 'accepted') {
            Offer::updateOrCreate(
                ['application_id' => $app->id],
                [
                    'status' => $status, // bebas (bukan enum di tabel stages)
                    'salary' => ['gross' => 8_000_000, 'allowance' => 1_000_000],
                ]
            );
        };

        // Increment filled_headcount spesifik ke site job saat hired
        $incFilledForJobSite = function (Job $job): void {
            if (!$job->site_id) return;
            $m = ManpowerRequirement::where('job_id', $job->id)
                ->where('site_id', $job->site_id)
                ->first();
            if ($m) {
                $m->increment('filled_headcount');
            }
        };

        /* ===============================
         * SATU PROGRES LAMARAN (FULL FLOW)
         * =============================== */
        $seedFullJourney = function (User $user, Job $job) use (
            $ensureStage,
            $ensureAttemptScored,
            $ensureInterview,
            $ensureOffer,
            $incFilledForJobSite
        ) {
            /** @var JobApplication $app */
            $app = JobApplication::updateOrCreate(
                ['job_id' => $job->id, 'user_id' => $user->id],
                [
                    'current_stage'  => 'hired',
                    'overall_status' => 'hired',
                ]
            );

            // 1. applied
            $ensureStage($app, 'applied', 'passed');

            // 2. screening
            $ensureStage($app, 'screening', 'passed', null, ['notes' => 'CV OK']);

            // 3. psychotest
            $ensureAttemptScored($app);
            $ensureStage($app, 'psychotest', 'passed', 4, ['max_score' => 5]);

            // 4. HR interview
            $ensureInterview($app, 'HR Interview', 'online', 'https://meet.google.com/demo-hr', null, 1);
            $ensureStage($app, 'hr_iv', 'passed', null, ['result' => 'Fit cultural']);

            // 5. User interview
            $ensureInterview($app, 'User Interview', 'onsite', null, 'R. User 1', 2);
            $ensureStage($app, 'user_iv', 'passed', null, ['result' => 'Teknis OK']);

            // 6. User/Trainer interview
            $ensureInterview($app, 'User/Trainer Interview', 'online', 'https://meet.google.com/demo-trainer', null, 3);
            $ensureStage($app, 'user_trainer_iv', 'passed');

            // 7. Offer (diterima)
            $ensureOffer($app, 'accepted');
            $ensureStage($app, 'offer', 'passed');

            // 8. MCU
            $ensureStage($app, 'mcu', 'passed', null, ['provider' => 'Klinik Demo']);

            // 9. Mobilisasi
            $ensureStage($app, 'mobilisasi', 'passed', null, ['transport' => 'PO-TRF-001']);

            // 10. Ground Test
            $ensureStage($app, 'ground_test', 'passed', null, ['unit' => 'Komatsu-01']);

            // 11. Hired (final)
            $ensureStage($app, 'hired', 'passed');
            $app->update(['current_stage' => 'hired', 'overall_status' => 'hired']);

            // update KPI manpower utk site utama job
            $incFilledForJobSite($job);

            return $app->fresh();
        };

        /* ===============================
         * SEED BERBAGAI LAMARAN (banyak user)
         * =============================== */
        // referensi job cepat
        $J = fn($code) => $jobs[$code];

        // Full journey → Hired
        $seedFullJourney($users['andi@demo.test'],  $J('PLT-ENG-01'));

        // Aktif berhenti di user_iv
        $bela = JobApplication::updateOrCreate(
            ['job_id' => $J('SCM-BUY-01')->id, 'user_id' => $users['bela@demo.test']->id],
            ['current_stage' => 'user_iv', 'overall_status' => 'active']
        );
        $ensureStage($bela, 'applied', 'passed');
        $ensureStage($bela, 'screening', 'passed');
        $ensureAttemptScored($bela);
        $ensureStage($bela, 'psychotest', 'passed', 4, ['max_score' => 5]);
        $ensureInterview($bela, 'HR Interview', 'online', 'https://meet.google.com/demo-hr', null, 1);
        $ensureStage($bela, 'hr_iv', 'passed');
        $ensureInterview($bela, 'User Interview', 'onsite', null, 'R. User POS', 2);
        $ensureStage($bela, 'user_iv', 'pending');

        // Aktif berhenti di offer
        $cici = JobApplication::updateOrCreate(
            ['job_id' => $J('HR-RECR-01')->id, 'user_id' => $users['cici@demo.test']->id],
            ['current_stage' => 'offer', 'overall_status' => 'active']
        );
        $ensureStage($cici, 'applied', 'passed');
        $ensureStage($cici, 'screening', 'passed');
        $ensureAttemptScored($cici);
        $ensureStage($cici, 'psychotest', 'passed', 4, ['max_score' => 5]);
        $ensureInterview($cici, 'HR Interview', 'online', 'https://meet.google.com/demo-hr', null, 1);
        $ensureStage($cici, 'hr_iv', 'passed');
        $ensureInterview($cici, 'User Interview', 'online', 'https://meet.google.com/demo-user', null, 2);
        $ensureStage($cici, 'user_iv', 'passed');
        $ensureOffer($cici, 'draft');
        $ensureStage($cici, 'offer', 'pending');

        // Beragam stage untuk user lain (contoh singkat)
        $pairs = [
            ['dodi@demo.test',  'PLT-MECH-01', 'psychotest'],
            ['eko@demo.test',   'ELEC-TECH-01', 'hr_iv'],
            ['fira@demo.test',  'WH-ADM-01',  'applied'],
            ['gina@demo.test',  'HSE-OFF-01', 'final'],
            ['hadi@demo.test',  'OPS-DRV-01', 'mobilisasi'],
            ['intan@demo.test', 'OPS-OPR-01', 'ground_test'],
            ['joni@demo.test',  'PLT-ENG-01', 'user_trainer_iv'],
        ];

        foreach ($pairs as [$email, $jobCode, $stage]) {
            $u = $users[$email];
            $job = $J($jobCode);

            $app = JobApplication::updateOrCreate(
                ['job_id' => $job->id, 'user_id' => $u->id],
                ['current_stage' => $stage, 'overall_status' => 'active']
            );

            // minimal rantai sesuai stage saat ini
            $ensureStage($app, 'applied', 'passed');
            $ensureStage($app, 'screening', 'passed');

            if (in_array($stage, ['psychotest', 'hr_iv', 'user_iv', 'user_trainer_iv', 'final', 'offer', 'mcu', 'mobilisasi', 'ground_test', 'hired'], true)) {
                $ensureAttemptScored($app);
                $ensureStage($app, 'psychotest', 'passed', 3.5, ['max_score' => 5]);
            }
            if (in_array($stage, ['hr_iv', 'user_iv', 'user_trainer_iv', 'final', 'offer', 'mcu', 'mobilisasi', 'ground_test', 'hired'], true)) {
                $ensureInterview($app, 'HR Interview', 'online', 'https://meet.google.com/hr-iv', null, 1);
                $ensureStage($app, 'hr_iv', 'passed');
            }
            if (in_array($stage, ['user_iv', 'user_trainer_iv', 'final', 'offer', 'mcu', 'mobilisasi', 'ground_test', 'hired'], true)) {
                $ensureInterview($app, 'User Interview', 'onsite', null, 'R. User', 2);
                $ensureStage($app, 'user_iv', 'passed');
            }
            if (in_array($stage, ['user_trainer_iv', 'final', 'offer', 'mcu', 'mobilisasi', 'ground_test', 'hired'], true)) {
                $ensureInterview($app, 'User/Trainer Interview', 'online', 'https://meet.google.com/trainer', null, 3);
                $ensureStage($app, 'user_trainer_iv', 'passed');
            }
            if (in_array($stage, ['final', 'offer', 'mcu', 'mobilisasi', 'ground_test', 'hired'], true)) {
                $ensureStage($app, 'final', 'passed');
            }
            if (in_array($stage, ['offer', 'mcu', 'mobilisasi', 'ground_test', 'hired'], true)) {
                $ensureOffer($app, 'draft');
                $ensureStage($app, 'offer', 'pending');
            }
            if (in_array($stage, ['mcu', 'mobilisasi', 'ground_test', 'hired'], true)) {
                $ensureStage($app, 'mcu', 'passed', null, ['provider' => 'Klinik Demo']);
            }
            if (in_array($stage, ['mobilisasi', 'ground_test', 'hired'], true)) {
                $ensureStage($app, 'mobilisasi', 'passed');
            }
            if (in_array($stage, ['ground_test', 'hired'], true)) {
                $ensureStage($app, 'ground_test', 'passed');
            }
            if ($stage === 'hired') {
                $ensureStage($app, 'hired', 'passed');
                $app->update(['overall_status' => 'hired']);
                $incFilledForJobSite($job);
            }
        }
    }
}
