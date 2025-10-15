<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{
    User, Job, ManpowerRequirement, JobApplication, ApplicationStage,
    CandidateProfile, PsychotestTest, PsychotestQuestion, PsychotestAttempt,
    Interview, Offer
};

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Users (plain password -> auto hashed by casts) + simple role =====
        $super = User::factory()->create([
            'name'              => 'Super Admin',
            'email'             => 'admin@local.test',
            'email_verified_at' => now(),
            'password'          => 'password123',
            'role'              => 'superadmin',
        ]);

        $hr = User::factory()->create([
            'name'              => 'HR User',
            'email'             => 'hr@demo.test',
            'email_verified_at' => now(),
            'password'          => 'password',
            'role'              => 'hr',
        ]);

        $c1 = User::factory()->create([
            'name'              => 'Andi Pelamar',
            'email'             => 'andi@demo.test',
            'email_verified_at' => now(),
            'password'          => 'password',
            'role'              => 'pelamar',
        ]);

        $c2 = User::factory()->create([
            'name'              => 'Bela Pelamar',
            'email'             => 'bela@demo.test',
            'email_verified_at' => now(),
            'password'          => 'password',
            'role'              => 'pelamar',
        ]);

        $c3 = User::factory()->create([
            'name'              => 'Cici Pelamar',
            'email'             => 'cici@demo.test',
            'email_verified_at' => now(),
            'password'          => 'password',
            'role'              => 'pelamar',
        ]);

        // ===== Candidate profiles =====
        foreach ([$c1,$c2,$c3] as $u) {
            CandidateProfile::firstOrCreate(['user_id'=>$u->id], [
                'full_name' => $u->name,
                'phone'     => '081234567890',
                'address'   => 'Jakarta',
                'extras'    => ['portfolio'=>null],
            ]);
        }

        // ===== Jobs + manpower =====
        $jobs = collect([
            [
                'code'=>'PLT-ENG-01','title'=>'Plant Engineer','division'=>'Plant','site_code'=>'DBK',
                'level'=>'Staff','employment_type'=>'fulltime','openings'=>2,'status'=>'open',
                'description'=>'Support maintenance and reliability.',
            ],
            [
                'code'=>'SCM-BUY-01','title'=>'Buyer','division'=>'SCM','site_code'=>'POS',
                'level'=>'Officer','employment_type'=>'contract','openings'=>1,'status'=>'open',
                'description'=>'Procurement operations.',
            ],
            [
                'code'=>'HR-RECR-01','title'=>'Recruiter','division'=>'HR','site_code'=>'HO',
                'level'=>'Staff','employment_type'=>'fulltime','openings'=>1,'status'=>'open',
                'description'=>'End-to-end hiring.',
            ],
        ])->map(function($d){
            $job = Job::create($d);
            $job->manpowerRequirement()->create([
                'budget_headcount' => $d['openings'],
                'filled_headcount' => 0,
            ]);
            return $job;
        })->values();

        // ===== Psychotest master =====
        $test = PsychotestTest::create([
            'name' => 'Tes Dasar',
            'duration_minutes' => 20,
            'scoring' => ['pass_ratio'=>0.6],
        ]);

        foreach ([
            ['Q: 2+2=?','1','2','4','5','4'],
            ['Q: Ibu kota Indonesia?','Bandung','Jakarta','Medan','Surabaya','Jakarta'],
            ['Q: Benar/Salah: Air membeku di 0°C','true','false',null,null,'true','truefalse'],
            ['Q: 5*3=?','8','15','10','12','15'],
            ['Q: Warna bendera: Merah-___','Putih','Biru','Hitam','Kuning','Putih'],
        ] as $i => $row) {
            [$q,$a,$b,$c,$d,$key,$type] = array_pad($row,7,'mcq');
            PsychotestQuestion::create([
                'test_id'    => $test->id,
                'type'       => $type==='truefalse' ? 'truefalse' : 'mcq',
                'question'   => $q,
                'options'    => $type==='truefalse' ? null : [$a,$b,$c,$d],
                'answer_key' => $key,
                'weight'     => 1,
                'order_no'   => $i,
            ]);
        }

        // ===== Applications & stages helper =====
        $makeApp = function($user,$job,$stage) use ($test){
            $app = JobApplication::create([
                'job_id'         => $job->id,
                'user_id'        => $user->id,
                'current_stage'  => $stage,
                'overall_status' => 'active',
            ]);

            ApplicationStage::create([
                'application_id'=>$app->id,'stage_key'=>'applied','status'=>'pending'
            ]);

            if (in_array($stage,['psychotest','hr_iv','user_iv','final','offer','hired'])) {
                PsychotestAttempt::create([
                    'application_id'=>$app->id,
                    'test_id'   => $test->id,
                    'attempt_no'=> 1,
                    'started_at'=> now()->subDay(),
                    'finished_at'=> now()->subDay(),
                    'score'     => 3,
                    'is_active' => false,
                ]);
                ApplicationStage::create([
                    'application_id'=>$app->id,'stage_key'=>'psychotest',
                    'status'=>'passed','score'=>3,'payload'=>['max_score'=>5],
                ]);
            }

            if (in_array($stage,['hr_iv','user_iv','final','offer','hired'])) {
                Interview::create([
                    'application_id'=>$app->id,
                    'title'=>'HR Interview',
                    'mode'=>'online',
                    'meeting_link'=>'https://meet.google.com/demo-hr',
                    'start_at'=>now()->addDay()->setTime(9,0),
                    'end_at'  =>now()->addDay()->setTime(10,0),
                    'panel'   =>[['name'=>'HR User','email'=>'hr@demo.test']],
                ]);
                ApplicationStage::create(['application_id'=>$app->id,'stage_key'=>'hr_iv','status'=>'pending']);
            }

            if (in_array($stage,['user_iv','final','offer','hired'])) {
                ApplicationStage::create(['application_id'=>$app->id,'stage_key'=>'user_iv','status'=>'pending']);
            }
            if (in_array($stage,['final','offer','hired'])) {
                ApplicationStage::create(['application_id'=>$app->id,'stage_key'=>'final','status'=>'pending']);
            }
            if (in_array($stage,['offer','hired'])) {
                Offer::create([
                    'application_id'=>$app->id,
                    'status'=>'draft',
                    'salary'=>['gross'=>8000000,'allowance'=>1000000],
                ]);
                ApplicationStage::create(['application_id'=>$app->id,'stage_key'=>'offer','status'=>'pending']);
            }
            if ($stage==='hired') {
                $app->update(['overall_status'=>'hired']);
                optional($job->manpowerRequirement)->increment('filled_headcount');
            }
            return $app;
        };

        // ===== Seed sample apps =====
        $makeApp($c1, $jobs[0], 'psychotest');
        $makeApp($c2, $jobs[1], 'hr_iv');
        $makeApp($c3, $jobs[2], 'offer');
    }
}
