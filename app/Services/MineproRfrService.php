<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MineproRfrService
{
    public function approvedVacancies(string $startDate): array
    {
        $url = (string) config('services.minepro.rfr_url');
        $apiKey = (string) config('services.minepro.api_key');
        $username = (string) config('services.minepro.basic_username');
        $password = (string) config('services.minepro.basic_password');

        if ($url === '' || $apiKey === '' || $username === '' || $password === '') {
            return [];
        }

        try {
            $response = Http::timeout((int) config('services.minepro.timeout', 15))
                ->withBasicAuth($username, $password)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->asMultipart()
                ->post($url, [
                    ['name' => 'StartDate', 'contents' => $startDate],
                ]);

            if (! $response->successful()) {
                Log::warning('MinePro RFR request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $rows = collect(Arr::flatten($response->json('results', []), 1))
                ->filter(fn($row) => is_array($row) && ! empty($row['RFRRefID']))
                ->map(fn($row) => $this->normalizeRfr($row))
                ->unique('code')
                ->sortByDesc('posting_date')
                ->values()
                ->all();

            return $rows;
        } catch (\Throwable $e) {
            Log::warning('MinePro RFR request exception.', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    private function normalizeRfr(array $row): array
    {
        $descriptionParts = array_filter([
            $row['BriefJobSpecs'] ?? null,
            $row['ExpRequirement'] ?? null,
            $row['TrainingAndDevDesc'] ?? null,
            $row['KetDocRFR'] ?? null,
        ]);

        $workLocation = $row['LokasiKerja'] ?? $row['ProjectID'] ?? $row['Location'] ?? null;
        [$companyCode, $siteCode] = $this->splitWorkLocation($workLocation);

        return [
            'code' => trim((string) ($row['RFRRefID'] ?? '')),
            'position_ref' => $row['Position_Ref'] ?? null,
            'title' => trim((string) ($row['Position_Description'] ?? '')),
            'qty_required' => (int) ((float) ($row['QtyRequired'] ?? 0)),
            'sex_required' => $row['SexRequired'] ?? null,
            'commencing_date' => $row['CommencingDate'] ?? null,
            'posting_date' => $row['PostingDate'] ?? null,
            'project_id' => $row['ProjectID'] ?? null,
            'department' => $row['Department'] ?? null,
            'facilities' => $row['Fasilitas'] ?? null,
            'work_experience' => $row['WorkExperience'] ?? null,
            'status_position' => $row['StatusPosition'] ?? null,
            'level' => $this->inferLevel($row['Position_Description'] ?? null, $row['StatusPosition'] ?? null),
            'candidate_type' => $row['TypeKandidat'] ?? null,
            'work_location' => $workLocation,
            'company_code' => $companyCode,
            'site_code' => $siteCode,
            'education_level' => $row['LevelEducation'] ?? null,
            'discipline_description' => $row['DisciplineDescription'] ?? null,
            'description' => implode("\n\n", $descriptionParts),
            'raw' => $row,
        ];
    }

    private function splitWorkLocation(?string $workLocation): array
    {
        $value = trim((string) $workLocation);

        if ($value === '') {
            return [null, null];
        }

        $parts = array_values(array_filter(array_map('trim', explode('-', $value))));

        if (count($parts) >= 2) {
            return [
                strtoupper($parts[0]),
                strtoupper(implode('-', array_slice($parts, 1))),
            ];
        }

        return [null, strtoupper($value)];
    }

    private function inferLevel(?string $title, ?string $statusPosition): ?string
    {
        $source = strtolower(trim(($title ?? '') . ' ' . ($statusPosition ?? '')));

        $matches = [
            'department head' => 'dept_head',
            'dept head' => 'dept_head',
            'section head' => 'section_head',
            'project manager' => 'project_manager',
            'superintendent' => 'superintendent',
            'supervisor' => 'supervisor',
            'foreman' => 'foreman',
            'manager' => 'manager',
            'analyst' => 'analyst',
            'specialist' => 'specialist',
            'expert' => 'expert',
            'lead of' => 'lead_of',
            'staff' => 'staff',
            'non staff' => 'non_staff',
            'non-staff' => 'non_staff',
            'pjo' => 'pjo',
        ];

        foreach ($matches as $needle => $level) {
            if (str_contains($source, $needle)) {
                return $level;
            }
        }

        return null;
    }
}
