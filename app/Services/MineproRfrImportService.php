<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Job;
use App\Models\ManpowerRequirement;
use App\Models\Site;
use Illuminate\Support\Facades\DB;

class MineproRfrImportService
{
    public function __construct(private readonly MineproRfrService $rfrService)
    {
    }

    public function import(string $startDate, ?string $endDate = null): array
    {
        $rows = $this->rfrService->approvedVacancies($startDate, $endDate);
        $result = [
            'created' => 0,
            'skipped' => 0,
            'failed' => 0,
            'total' => count($rows),
            'meta' => $this->rfrService->lastVacancyMeta(),
            'errors' => [],
        ];

        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code === '') {
                $result['skipped']++;
                continue;
            }

            try {
                $created = false;

                DB::transaction(function () use ($row, $code, &$created) {
                    $siteId = $this->resolveSiteId($row['site_code'] ?? null);
                    $companyId = $this->resolveCompanyId($row['company_code'] ?? null);
                    $openings = max(0, (int) ($row['qty_required'] ?? 0));

                    $job = Job::firstOrCreate(
                        ['code' => $code],
                        [
                            'title' => trim((string) ($row['title'] ?? '')) ?: $code,
                            'division' => $row['department'] ?? null,
                            'level' => $row['level'] ?? null,
                            'employment_type' => 'fulltime',
                            'status' => 'open',
                            'description' => $row['description'] ?? null,
                            'skills' => [],
                            'keywords' => '',
                            'site_id' => $siteId,
                            'company_id' => $companyId,
                            'openings' => 0,
                        ],
                    );

                    if (! $job->wasRecentlyCreated) {
                        return;
                    }

                    $created = true;

                    if ($openings > 0) {
                        ManpowerRequirement::create([
                            'job_id' => $job->id,
                            'site_id' => $siteId,
                            'asset_name' => 'RFR MinePro',
                            'assets_count' => $openings,
                            'ratio_per_asset' => 1,
                            'filled_headcount' => 0,
                        ]);

                        $job->update(['openings' => (int) $job->manpowerRequirements()->sum('budget_headcount')]);
                    }
                });

                $created ? $result['created']++ : $result['skipped']++;
            } catch (\Throwable $e) {
                $result['failed']++;
                $result['errors'][] = [
                    'code' => $code,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $result;
    }

    private function resolveSiteId(?string $siteCode): string
    {
        $code = trim((string) $siteCode);
        if ($code === '') {
            $code = 'AST';
        }

        $site = Site::firstOrCreate(
            ['code' => $code],
            [
                'name' => $code,
                'is_active' => true,
                'meta' => ['source' => 'minepro_rfr_sync'],
            ],
        );

        return (string) $site->id;
    }

    private function resolveCompanyId(?string $companyCode): ?string
    {
        $code = trim((string) $companyCode);
        if ($code === '') {
            return null;
        }

        $company = Company::firstOrCreate(
            ['code' => $code],
            [
                'name' => $code,
                'status' => 'active',
                'meta' => ['source' => 'minepro_rfr_sync'],
            ],
        );

        return (string) $company->id;
    }
}
