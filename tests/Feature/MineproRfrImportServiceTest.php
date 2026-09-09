<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\Site;
use App\Services\MineproRfrImportService;
use App\Services\MineproRfrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MineproRfrImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_new_jobs_and_skips_existing_rfr_codes(): void
    {
        $site = Site::factory()->create(['code' => 'BGG', 'is_active' => true]);
        Job::factory()->create([
            'code' => 'RFR-EXISTS',
            'site_id' => $site->id,
            'title' => 'Existing Job',
        ]);

        $this->app->instance(MineproRfrService::class, new class extends MineproRfrService {
            public function approvedVacancies(string $startDate, ?string $endDate = null): array
            {
                return [
                    [
                        'code' => 'RFR-EXISTS',
                        'title' => 'Existing From API',
                        'qty_required' => 2,
                        'department' => 'OPR',
                        'site_code' => 'BGG',
                        'company_code' => 'AAP',
                        'description' => 'Should be skipped.',
                    ],
                    [
                        'code' => 'RFR-NEW',
                        'title' => 'New From API',
                        'qty_required' => 3,
                        'department' => 'OPR',
                        'site_code' => 'BGG',
                        'company_code' => 'AAP',
                        'description' => 'Should be created.',
                    ],
                ];
            }

            public function lastVacancyMeta(): array
            {
                return ['ok' => true, 'count' => 2];
            }
        });

        $result = app(MineproRfrImportService::class)->import('2026-09-01', '2026-09-30');

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['skipped']);
        $this->assertDatabaseHas('job_listings', [
            'code' => 'RFR-NEW',
            'title' => 'New From API',
            'status' => 'open',
            'employment_type' => 'fulltime',
        ]);
        $this->assertSame(2, Job::query()->count());
    }
}
