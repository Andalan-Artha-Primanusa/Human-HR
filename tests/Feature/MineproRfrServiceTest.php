<?php

namespace Tests\Feature;

use App\Models\CandidateProfile;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Site;
use App\Models\User;
use App\Services\MineproRfrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MineproRfrServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_list_normalizes_flexible_process_names(): void
    {
        Config::set('services.minepro.rfr_process_url', 'https://minepro.test/rfr-process-list');
        Config::set('services.minepro.api_key', 'test-key');
        Config::set('services.minepro.basic_username', 'user');
        Config::set('services.minepro.basic_password', 'pass');

        Http::fake([
            'minepro.test/*' => Http::response([
                'status' => 'success',
                'results' => [[
                    [
                        'RFRRefID' => '0001/AAP-BGG/RFR/NS/06/2026',
                        'NIK' => '3174082608890004',
                        'Process' => 'Ground Test 2',
                        'Result' => 'Lulus',
                    ],
                ]],
            ], 200),
        ]);

        $rows = app(MineproRfrService::class)->processList('2026-06-01', '2026-06-30');

        $this->assertSame('ground_test', $rows[0]['stage']);
    }

    public function test_application_progress_uses_configured_wide_range_but_still_matches_rfr_and_nik(): void
    {
        Config::set('services.minepro.rfr_process_url', 'https://minepro.test/rfr-process-list');
        Config::set('services.minepro.api_key', 'test-key');
        Config::set('services.minepro.basic_username', 'user');
        Config::set('services.minepro.basic_password', 'pass');
        Config::set('services.minepro.process_start_date', '2020-01-01');
        Config::set('services.minepro.process_end_date', '2030-12-31');

        Http::fake([
            'minepro.test/*' => Http::response([
                'status' => 'success',
                'results' => [[
                    [
                        'RFRRefID' => 'OTHER/RFR/01/2026',
                        'NIK' => '3471070907830001',
                        'Process' => 'Hired',
                    ],
                    [
                        'RFRRefID' => '0001/AAP-BGG/RFR/NS/06/2026',
                        'NIK' => '3471070907830001',
                        'Process' => 'GroundTest',
                    ],
                ]],
            ], 200),
        ]);

        $user = User::factory()->create();
        CandidateProfile::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'nik' => '3471070907830001',
        ]);
        $site = Site::factory()->create();
        $job = Job::create([
            'title' => 'Department Head Operation',
            'code' => '0001/AAP-BGG/RFR/NS/06/2026',
            'description' => 'Test',
            'status' => 'open',
            'level' => 'dept_head',
            'employment_type' => 'fulltime',
            'site_id' => $site->id,
        ]);
        $application = JobApplication::create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active',
        ]);

        $progress = app(MineproRfrService::class)->progressForApplication($application);

        Http::assertSent(fn($request) => str_contains($request->body(), '2020-01-01')
            && str_contains($request->body(), '2030-12-31'));
        $this->assertSame('ground_test', $progress['current_stage']);
        $this->assertCount(1, $progress['processes']);
    }
}
