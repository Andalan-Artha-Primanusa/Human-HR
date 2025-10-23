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
    ManpowerRequirement, // ⬅️ gunakan model budget_headcount
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
                    'ktp_address'    => 'Jl. Contoh No.1',
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
            'SBS' => 'Site Sabas', // ⬅️ ditambah
        ];
        $siteMap = [];
        foreach ($sitesByCode as $code => $name) {
            $site = Site::updateOrCreate(['code' => $code], ['name' => $name]);
            $siteMap[$code] = $site;
        }

        /* ===============================
         * JOBS + MANPOWER per-site (upsert)
         * Deskripsi HTML lengkap + skills + keywords
         * =============================== */
        $manpowerMatrix = [
            // site_code => [assets_count, ratio]
            'DBK' => ['assets' => 12, 'ratio' => 2.50],
            'POS' => ['assets' =>  5, 'ratio' => 2.60],
            'HO'  => ['assets' =>  3, 'ratio' => 2.50],
            'SBS' => ['assets' =>  7, 'ratio' => 2.40],
        ];

        // Helper deskripsi HTML (ringkas, rapi)
        $desc = fn(array $d) => sprintf(
            '<div>
               <p class="mb-2">%s</p>
               <h4 class="mt-3 mb-1"><strong>Tanggung Jawab</strong></h4>
               <ul>
                 %s
               </ul>
               <h4 class="mt-3 mb-1"><strong>Kualifikasi</strong></h4>
               <ul>
                 %s
               </ul>
               <h4 class="mt-3 mb-1"><strong>Benefit</strong></h4>
               <ul>
                 %s
               </ul>
             </div>',
            e($d['intro']),
            collect($d['resp'])->map(fn($li) => '<li>'.e($li).'</li>')->implode(''),
            collect($d['reqs'])->map(fn($li) => '<li>'.e($li).'</li>')->implode(''),
            collect($d['benefit'])->map(fn($li) => '<li>'.e($li).'</li>')->implode('')
        );

        $jobDefs = [
            // ===== Existing (diperkaya) =====
            [
                'code'             => 'PLT-ENG-01',
                'title'            => 'Plant Engineer',
                'division'         => 'Plant',
                'site_code'        => 'DBK',
                'level'            => 'Staff',
                'employment_type'  => 'fulltime',
                'openings'         => 2, // akan di-recalc
                'status'           => 'open',
                'skills'           => ['CMMS', 'Preventive Maintenance', 'Root Cause Analysis', 'Reliability'],
                'keywords'         => 'maintenance,routes,plant,engineer,cmms,rca',
                'description'      => $desc([
                    'intro'   => 'Bertanggung jawab pada pemeliharaan peralatan plant untuk mencapai availability & reliability target.',
                    'resp'    => [
                        'Menyusun jadwal preventive & predictive maintenance.',
                        'Koordinasi perbaikan breakdown & root cause analysis (RCA).',
                        'Update master data aset & history di CMMS.',
                        'Kolaborasi dengan HSE terkait perizinan pekerjaan dan safety lockout/tagout.',
                    ],
                    'reqs'    => [
                        'Min. S1 Teknik (Mesin/Elektro/Industri) atau setara.',
                        'Pengalaman 1–3 tahun di area plant/maintenance.',
                        'Mahir membaca P&ID, manual teknis, dan SOP.',
                        'Mampu menggunakan CMMS & basic MS Office.',
                    ],
                    'benefit' => [
                        'Asuransi kesehatan dasar + rawat jalan.',
                        'Mess/akomodasi site (bila penempatan site).',
                        'Kesempatan pelatihan teknis & sertifikasi.',
                    ],
                ]),
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
                'skills'           => ['Procurement', 'Vendor Management', 'PO/PR', 'Negotiation'],
                'keywords'         => 'buyer,scm,purchasing,procurement,pr,po,vendor',
                'description'      => $desc([
                    'intro'   => 'Menangani proses pembelian (PR–PO) untuk memastikan ketersediaan barang/jasa tepat mutu, biaya, dan waktu.',
                    'resp'    => [
                        'Review PR & mengeksekusi RFQ ke vendor terpilih.',
                        'Negosiasi harga, kualitas, SLA, dan syarat pembayaran.',
                        'Pembuatan PO & tindak lanjut pengiriman.',
                        'Evaluasi vendor & perbarui master data vendor.',
                    ],
                    'reqs'    => [
                        'Min. D3/S1 semua jurusan.',
                        'Pengalaman 1–2 tahun di pembelian/SCM diutamakan.',
                        'Komunikatif, teliti, dan negosiator yang baik.',
                    ],
                    'benefit' => [
                        'BPJS & tunjangan transport.',
                        'Kesempatan rotasi lintas site.',
                    ],
                ]),
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
                'skills'           => ['Sourcing', 'Interviewing', 'ATS', 'Candidate Experience'],
                'keywords'         => 'recruiter,hr,hiring,interview,ats,sourcing',
                'description'      => $desc([
                    'intro'   => 'End-to-end recruitment: mulai dari sourcing hingga offering, memastikan candidate experience yang baik.',
                    'resp'    => [
                        'Sourcing kandidat dari job board & referral.',
                        'Screening CV & interview awal (HR interview).',
                        'Koordinasi psikotes & user interview.',
                        'Menyusun offering & follow-up onboarding.',
                    ],
                    'reqs'    => [
                        'Min. S1 Psikologi/Manajemen/SDM.',
                        'Memahami teknik interview & penilaian kompetensi.',
                        'Terbiasa menggunakan ATS atau spreadsheet tracking.',
                    ],
                    'benefit' => [
                        'Asuransi kesehatan & tunjangan komunikasi.',
                        'Hybrid WFO/WFH (sesuai kebijakan).',
                    ],
                ]),
            ],

            // ===== Paket INTERN (lengkap division + site) =====
            [
                'code'             => 'IT-INT-01',
                'title'            => 'IT Support Intern',
                'division'         => 'IT',
                'site_code'        => 'HO',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 1,
                'status'           => 'open',
                'skills'           => ['Helpdesk', 'Asset Tagging', 'Troubleshooting', 'Windows'],
                'keywords'         => 'intern,it support,helpdesk,tagging,troubleshooting',
                'description'      => $desc([
                    'intro'   => 'Mendukung aktivitas helpdesk, penandaan aset, dan troubleshooting endpoint.',
                    'resp'    => [
                        'Menangani tiket harian (hardware/software).',
                        'Asset tagging & update inventory.',
                        'Install/konfigurasi aplikasi standar perusahaan.',
                    ],
                    'reqs'    => [
                        'Mahasiswa aktif/semester akhir TI/SI/Ilkom.',
                        'Paham OS Windows & dasar jaringan.',
                    ],
                    'benefit' => [
                        'Uang saku & sertifikat magang.',
                        'Akses pembelajaran internal (KB/Docs).',
                    ],
                ]),
            ],
            [
                'code'             => 'HR-INT-01',
                'title'            => 'HR Intern',
                'division'         => 'HR',
                'site_code'        => 'HO',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 2,
                'status'           => 'open',
                'skills'           => ['Coordination', 'Data Entry', 'Communication'],
                'keywords'         => 'intern,hr,recruitment,data entry,coordination',
                'description'      => $desc([
                    'intro'   => 'Mendukung koordinasi rekrutmen & entri data HRIS.',
                    'resp'    => [
                        'Jadwalkan interview & follow up kandidat.',
                        'Input data kandidat/pegawai ke HRIS.',
                        'Dokumentasi administrasi HR.',
                    ],
                    'reqs'    => [
                        'Mahasiswa Psikologi/Manajemen/SDM.',
                        'Rapi & teliti, komunikatif.',
                    ],
                    'benefit' => [
                        'Uang saku & sertifikat magang.',
                        'Exposure proses HR end-to-end.',
                    ],
                ]),
            ],
            [
                'code'             => 'FIN-INT-01',
                'title'            => 'Finance Intern',
                'division'         => 'Finance',
                'site_code'        => 'HO',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 1,
                'status'           => 'open',
                'skills'           => ['Budgeting', 'Reporting', 'Spreadsheet'],
                'keywords'         => 'intern,finance,budget,reporting,excel',
                'description'      => $desc([
                    'intro'   => 'Mendukung budgeting & reporting dasar.',
                    'resp'    => [
                        'Entri & rekonsiliasi data transaksi.',
                        'Membantu laporan rutin bulanan.',
                    ],
                    'reqs'    => [
                        'Mahasiswa Akuntansi/Keuangan.',
                        'Menguasai spreadsheet (formula dasar).',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Mentoring dari tim Finance.',
                    ],
                ]),
            ],
            [
                'code'             => 'QA-INT-01',
                'title'            => 'QA Intern',
                'division'         => 'QA',
                'site_code'        => 'SBS',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 1,
                'status'           => 'open',
                'skills'           => ['Inspection', 'Documentation', '5S'],
                'keywords'         => 'intern,qa,inspection,documentation,quality',
                'description'      => $desc([
                    'intro'   => 'Mendukung inline inspection & dokumentasi mutu.',
                    'resp'    => [
                        'Sampling & pencatatan hasil pemeriksaan.',
                        'Bantu perbaikan dokumen SOP/IK.',
                    ],
                    'reqs'    => [
                        'Mahasiswa Teknik/Industri.',
                        'Teliti & rapi.',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Pengenalan sistem mutu di site.',
                    ],
                ]),
            ],
            [
                'code'             => 'PLT-INT-01',
                'title'            => 'Plant Intern',
                'division'         => 'Plant',
                'site_code'        => 'DBK',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 2,
                'status'           => 'open',
                'skills'           => ['Maintenance Basics', 'Safety', 'CMMS'],
                'keywords'         => 'intern,plant,maintenance,cmms,safety',
                'description'      => $desc([
                    'intro'   => 'Belajar rutin pemeliharaan & dasar reliability.',
                    'resp'    => [
                        'Mendampingi preventive maintenance.',
                        'Update data aset di CMMS.',
                    ],
                    'reqs'    => [
                        'Mahasiswa Teknik Mesin/Elektro.',
                        'Tertib keselamatan kerja.',
                    ],
                    'benefit' => [
                        'Uang saku & mess (jika perlu).',
                        'Rotasi exposure unit alat.',
                    ],
                ]),
            ],
            [
                'code'             => 'SCM-INT-01',
                'title'            => 'Supply Chain Intern',
                'division'         => 'SCM',
                'site_code'        => 'POS',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 2,
                'status'           => 'open',
                'skills'           => ['Inventory', 'Purchasing', 'Documentation'],
                'keywords'         => 'intern,scm,inventory,purchasing,stock',
                'description'      => $desc([
                    'intro'   => 'Mendukung purchasing & monitoring inventory.',
                    'resp'    => [
                        'Update status PR/PO.',
                        'Cek & input stok barang.',
                    ],
                    'reqs'    => [
                        'Mahasiswa Manajemen/Logistik.',
                        'Rapi administrasi & komunikatif.',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Belajar alur SCM end-to-end.',
                    ],
                ]),
            ],
            [
                'code'             => 'MKT-INT-01',
                'title'            => 'Marketing Intern',
                'division'         => 'Marketing',
                'site_code'        => 'HO',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 1,
                'status'           => 'open',
                'skills'           => ['Content', 'Copywriting', 'Analytics'],
                'keywords'         => 'intern,marketing,content,copywriting,analytics',
                'description'      => $desc([
                    'intro'   => 'Bantu pembuatan konten & pelacakan campaign.',
                    'resp'    => [
                        'Membuat materi konten dasar.',
                        'Rekap performa sederhana (view/click).',
                    ],
                    'reqs'    => [
                        'Mahasiswa Komunikasi/Manajemen.',
                        'Mampu menulis & presentasi ringkas.',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Portofolio kampanye nyata.',
                    ],
                ]),
            ],
            [
                'code'             => 'LEG-INT-01',
                'title'            => 'Legal Intern',
                'division'         => 'Legal',
                'site_code'        => 'HO',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 1,
                'status'           => 'open',
                'skills'           => ['Contract Review', 'Filing', 'Research'],
                'keywords'         => 'intern,legal,contract,filing,research',
                'description'      => $desc([
                    'intro'   => 'Mendukung review kontrak & kearsipan dokumen.',
                    'resp'    => [
                        'Kompilasi & filing dokumen legal.',
                        'Riset regulasi sederhana.',
                    ],
                    'reqs'    => [
                        'Mahasiswa Hukum.',
                        'Rapi administrasi & detail oriented.',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Eksposur dokumen kontrak riil.',
                    ],
                ]),
            ],
            [
                'code'             => 'GA-INT-01',
                'title'            => 'General Affairs Intern',
                'division'         => 'GA',
                'site_code'        => 'POS',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 1,
                'status'           => 'open',
                'skills'           => ['Office Admin', 'Vendor Follow-up', 'Documentation'],
                'keywords'         => 'intern,ga,admin,vendor,office',
                'description'      => $desc([
                    'intro'   => 'Mendukung administrasi kantor & follow-up dokumen vendor.',
                    'resp'    => [
                        'Arsip dokumen & surat-menyurat.',
                        'Follow up kebutuhan operasional.',
                    ],
                    'reqs'    => [
                        'Mahasiswa semua jurusan.',
                        'Komunikatif & rapi administrasi.',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Pengalaman operasional harian.',
                    ],
                ]),
            ],
            [
                'code'             => 'RND-INT-01',
                'title'            => 'R&D Intern',
                'division'         => 'R&D',
                'site_code'        => 'SBS',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 1,
                'status'           => 'open',
                'skills'           => ['Experiment', 'Lab Notes', 'Data Entry'],
                'keywords'         => 'intern,rd,lab,experiment,data',
                'description'      => $desc([
                    'intro'   => 'Mendukung eksperimen & pencatatan lab.',
                    'resp'    => [
                        'Menyiapkan alat & bahan dasar.',
                        'Mencatat hasil uji sederhana.',
                    ],
                    'reqs'    => [
                        'Mahasiswa Sains/Teknik.',
                        'Rapi & teliti.',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Paparan metode eksperimen.',
                    ],
                ]),
            ],
            [
                'code'             => 'SALES-INT-01',
                'title'            => 'Sales Intern',
                'division'         => 'Sales',
                'site_code'        => 'HO',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 2,
                'status'           => 'open',
                'skills'           => ['CRM', 'Prospecting', 'Communication'],
                'keywords'         => 'intern,sales,crm,prospect,lead',
                'description'      => $desc([
                    'intro'   => 'Mendukung kurasi lead & update CRM.',
                    'resp'    => [
                        'Riset prospek & input CRM.',
                        'Follow up jadwal meeting tim.',
                    ],
                    'reqs'    => [
                        'Mahasiswa Manajemen/Pemasaran.',
                        'Komunikasi lisan & tulisan baik.',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Belajar siklus sales.',
                    ],
                ]),
            ],
            [
                'code'             => 'HSE-INT-01',
                'title'            => 'HSE Intern',
                'division'         => 'HSE',
                'site_code'        => 'DBK',
                'level'            => 'Intern',
                'employment_type'  => 'intern',
                'openings'         => 1,
                'status'           => 'open',
                'skills'           => ['Safety Walk', 'PPE Check', 'Reporting'],
                'keywords'         => 'intern,hse,safety,inspection,ppe',
                'description'      => $desc([
                    'intro'   => 'Mendukung safety walk-through & pemeriksaan PPE.',
                    'resp'    => [
                        'Checklist PPE & housekeeping.',
                        'Bantu input laporan hazard/incident.',
                    ],
                    'reqs'    => [
                        'Mahasiswa K3/Tehnik.',
                        'Memahami dasar K3.',
                    ],
                    'benefit' => [
                        'Uang saku.',
                        'Paparan sistem HSE di site.',
                    ],
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
                    'ratio_per_asset' => $ratio, // budget_headcount dihitung di model hook (saving)
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

        // Upsert jobs + manpower
        $jobs = collect($jobDefs)->map(function ($d) use ($siteMap, $manpowerMatrix, $ensureManpower, $recalcJobOpenings) {
            // Site utama
            $site = $siteMap[$d['site_code']] ?? Site::firstOrCreate(
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

            // Manpower baris utama
            $mainAssets = $manpowerMatrix[$d['site_code']]['assets'] ?? $d['openings'];
            $mainRatio  = $manpowerMatrix[$d['site_code']]['ratio']  ?? 2.50;
            $ensureManpower($job, $site, (int) $mainAssets, (float) $mainRatio);

            // Manpower baris tambahan (multi-site demo)
            foreach ($manpowerMatrix as $code => $cfg) {
                if ($code === $d['site_code']) continue;
                if (!isset($siteMap[$code])) continue;
                if (($cfg['assets'] ?? 0) > 0) {
                    $ensureManpower($job, $siteMap[$code], (int) $cfg['assets'], (float) $cfg['ratio']);
                }
            }

            // Recalc openings
            $recalcJobOpenings($job);

            return $job->fresh();
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

        $makeApp = function (User $user, Job $job, string $stage) use ($ensureStage, $ensureAttemptScored, $ensureInterview, $ensureOffer, $incFilledForJobSite) {
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

            // hired → increment filled_headcount spesifik site job
            if ($stage === 'hired') {
                $app->update(['overall_status' => 'hired']);
                $incFilledForJobSite($job);
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
