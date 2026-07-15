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
            'candidate_type' => $row['TypeKandidat'] ?? null,
            'work_location' => $row['LokasiKerja'] ?? null,
            'education_level' => $row['LevelEducation'] ?? null,
            'discipline_description' => $row['DisciplineDescription'] ?? null,
            'description' => implode("\n\n", $descriptionParts),
            'raw' => $row,
        ];
    }
}
