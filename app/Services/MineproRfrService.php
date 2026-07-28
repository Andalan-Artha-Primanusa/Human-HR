<?php

namespace App\Services;

use App\Models\JobApplication;
use Illuminate\Support\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MineproRfrService
{
    private const PROCESS_STAGE_ORDER = [
        'applied',
        'screening',
        'psychotest',
        'hr_iv',
        'user_iv',
        'user_trainer_iv',
        'offer',
        'mcu',
        'mobilisasi',
        'ground_test',
        'onsite',
        'hired',
        'not_qualified',
    ];

    private array $lastProcessMeta = [
        'ok' => false,
        'message' => null,
        'status' => null,
        'url' => null,
        'count' => 0,
    ];

    public function lastProcessMeta(): array
    {
        return $this->lastProcessMeta;
    }

    public function processList(string $startDate, string $endDate): array
    {
        $url = (string) config('services.minepro.rfr_process_url');
        $apiKey = (string) config('services.minepro.api_key');
        $username = (string) config('services.minepro.basic_username');
        $password = (string) config('services.minepro.basic_password');

        if ($url === '' || $apiKey === '' || $username === '' || $password === '') {
            $this->lastProcessMeta = [
                'ok' => false,
                'message' => 'Konfigurasi MinePro RFR Process belum lengkap.',
                'status' => null,
                'url' => $url,
                'count' => 0,
            ];

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
                    ['name' => 'EndDate', 'contents' => $endDate],
                ]);

            if (! $response->successful()) {
                $this->lastProcessMeta = [
                    'ok' => false,
                    'message' => 'Request MinePro RFR Process gagal.',
                    'status' => $response->status(),
                    'url' => $url,
                    'count' => 0,
                ];
                Log::warning('MinePro RFR process request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $rows = collect($this->resultRows($response->json('results', [])))
                ->filter(fn($row) => is_array($row) && ! empty($row['RFRRefID']) && ! empty($row['NIK']))
                ->map(fn($row) => $this->normalizeProcess($row))
                ->values()
                ->all();

            $this->lastProcessMeta = [
                'ok' => true,
                'message' => 'MinePro RFR Process berhasil dibaca.',
                'status' => $response->status(),
                'url' => $url,
                'count' => count($rows),
            ];

            return $rows;
        } catch (\Throwable $e) {
            $this->lastProcessMeta = [
                'ok' => false,
                'message' => $e->getMessage(),
                'status' => null,
                'url' => $url,
                'count' => 0,
            ];
            Log::warning('MinePro RFR process request exception.', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }

    public function progressForApplication(JobApplication $application, ?string $startDate = null, ?string $endDate = null): array
    {
        $application->loadMissing([
            'job:id,code',
            'user.candidateProfile:id,user_id,nik',
        ]);

        [$defaultStartDate, $defaultEndDate] = $this->defaultProcessRange($application->created_at);
        $startDate = $this->validDate($startDate) ?: $defaultStartDate;
        $endDate = $this->validDate($endDate) ?: $defaultEndDate;

        $jobCode = trim((string) ($application->job?->code ?? ''));
        $nik = trim((string) ($application->user?->candidateProfile?->nik ?? ''));

        return $this->progressForRfrAndNik($jobCode, $nik, $startDate, $endDate);
    }

    public function progressForRfrAndNik(string $rfrRefId, string $nik, ?string $startDate = null, ?string $endDate = null): array
    {
        [$defaultStartDate, $defaultEndDate] = $this->defaultProcessRange();
        $startDate = $this->validDate($startDate) ?: $defaultStartDate;
        $endDate = $this->validDate($endDate) ?: $defaultEndDate;
        $key = $this->processKey($rfrRefId, $nik);

        if ($key === '|') {
            return $this->emptyProgress($startDate, $endDate);
        }

        $processes = collect($this->processList($startDate, $endDate))
            ->filter(fn($row) => $this->processKey($row['rfr_ref_id'] ?? '', $row['nik'] ?? '') === $key)
            ->values()
            ->all();

        $currentProcess = $this->currentProcess($processes);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'processes' => $processes,
            'current_process' => $currentProcess,
            'current_stage' => $currentProcess['stage'] ?? null,
            'meta' => $this->lastProcessMeta(),
        ];
    }

    public function progressForApplications(iterable $applications): array
    {
        $apps = collect($applications)->values();
        $progress = [];

        $apps->each(fn($application) => $application->loadMissing([
            'job:id,code',
            'user.candidateProfile:id,user_id,nik',
        ]));

        $appsByRange = $apps->groupBy(function (JobApplication $application) {
            [$startDate, $endDate] = $this->defaultProcessRange($application->created_at);

            return $startDate . '|' . $endDate;
        });

        foreach ($appsByRange as $rangeKey => $rangeApps) {
            [$startDate, $endDate] = explode('|', (string) $rangeKey, 2);
            $rows = collect($this->processList($startDate, $endDate));
            $rowsByCandidate = $rows->groupBy(fn($row) => $this->processKey($row['rfr_ref_id'] ?? '', $row['nik'] ?? ''));

            foreach ($rangeApps as $application) {
                $key = $this->processKey(
                    trim((string) ($application->job?->code ?? '')),
                    trim((string) ($application->user?->candidateProfile?->nik ?? ''))
                );

                $processes = $key === '|'
                    ? []
                    : $rowsByCandidate->get($key, collect())->values()->all();
                $currentProcess = $this->currentProcess($processes);

                $progress[$application->getKey()] = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'processes' => $processes,
                    'current_process' => $currentProcess,
                    'current_stage' => $currentProcess['stage'] ?? null,
                    'meta' => $this->lastProcessMeta(),
                ];
            }
        }

        return $progress;
    }

    public function processKey(string $rfrRefId, string $nik): string
    {
        return strtoupper(trim($rfrRefId)) . '|' . preg_replace('/\D+/', '', $nik);
    }

    private function emptyProgress(string $startDate, string $endDate): array
    {
        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'processes' => [],
            'current_process' => null,
            'current_stage' => null,
            'meta' => $this->lastProcessMeta(),
        ];
    }

    private function defaultProcessRange(mixed $fallbackDate = null): array
    {
        $configuredStart = $this->validDate((string) config('services.minepro.process_start_date'));
        $configuredEnd = $this->validDate((string) config('services.minepro.process_end_date'));

        if ($configuredStart && $configuredEnd) {
            return [$configuredStart, $configuredEnd];
        }

        $date = $fallbackDate instanceof Carbon
            ? $fallbackDate->copy()
            : Carbon::parse($fallbackDate ?? now());

        return [
            $date->copy()->startOfMonth()->format('Y-m-d'),
            $date->copy()->endOfMonth()->format('Y-m-d'),
        ];
    }

    private function currentProcess(array $processes): ?array
    {
        $rank = array_flip(self::PROCESS_STAGE_ORDER);

        return collect($processes)
            ->filter(fn($process) => ! empty($process['stage']) && array_key_exists($process['stage'], $rank))
            ->sortBy(fn($process) => $rank[$process['stage']] ?? -1)
            ->last();
    }

    private function validDate(?string $date): ?string
    {
        return is_string($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            ? $date
            : null;
    }

    private function resultRows(mixed $results): array
    {
        if (! is_array($results)) {
            return [];
        }

        if (isset($results['RFRRefID'])) {
            return [$results];
        }

        $first = reset($results);

        if (is_array($first) && isset($first['RFRRefID'])) {
            return $results;
        }

        return Arr::flatten($results, 1);
    }

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

    private function normalizeProcess(array $row): array
    {
        return [
            'id' => $row['IDTr'] ?? null,
            'rfr_ref_id' => trim((string) ($row['RFRRefID'] ?? '')),
            'candidate_id' => $row['CandidateID'] ?? null,
            'nik' => trim((string) ($row['NIK'] ?? '')),
            'process' => trim((string) ($row['Process'] ?? '')),
            'stage' => $this->processToStage($row['Process'] ?? null),
            'doc_status' => $row['DocStatus'] ?? null,
            'start_date' => $row['StartDate'] ?? null,
            'end_date' => $row['EndDate'] ?? null,
            'result' => $row['Result'] ?? null,
            'link' => $row['Link'] ?? null,
            'notes' => $row['Notes'] ?? null,
            'created_date' => $row['CreatedDate'] ?? null,
            'created_by' => $row['CreatedBy'] ?? null,
            'updated_date' => $row['UpdatedDate'] ?? null,
            'updated_by' => $row['UpdateBy'] ?? null,
            'file_name' => $row['FileName'] ?? null,
            'raw' => $row,
        ];
    }

    private function processToStage(?string $process): ?string
    {
        $value = strtolower(trim((string) $process));
        $compact = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';

        if ($compact === '') {
            return null;
        }

        if (str_contains($compact, 'notqualified') || str_contains($compact, 'tidaklolos') || str_contains($compact, 'rejected') || str_contains($compact, 'ditolak')) {
            return 'not_qualified';
        }

        if (str_contains($compact, 'groundtest')) {
            return 'ground_test';
        }

        if (str_contains($compact, 'usertrainer') || str_contains($compact, 'trainerinterview') || str_contains($compact, 'userinterview')) {
            return 'user_trainer_iv';
        }

        if (str_contains($compact, 'hrinterview')) {
            return 'hr_iv';
        }

        if (str_contains($compact, 'psychotest') || str_contains($compact, 'psikotest')) {
            return 'psychotest';
        }

        if (str_contains($compact, 'screening')) {
            return 'screening';
        }

        if (str_contains($compact, 'offering') || $compact === 'offer' || $compact === 'ol') {
            return 'offer';
        }

        if (str_contains($compact, 'mobilisasi') || str_contains($compact, 'mobilization')) {
            return 'mobilisasi';
        }

        if (str_contains($compact, 'onsite')) {
            return 'onsite';
        }

        if (str_contains($compact, 'hired') || str_contains($compact, 'diterima')) {
            return 'hired';
        }

        return match ($value) {
            'applied', 'apply', 'lamaran', 'pengajuan berkas' => 'applied',
            'screening', 'screening cv', 'screening berkas', 'screening cv/berkas', 'screening_cv', 'screening_berkas' => 'screening',
            'psikotest', 'psychotest' => 'psychotest',
            'hrinterview', 'hr interview', 'hr_interview' => 'hr_iv',
            'userinterview', 'user interview', 'user_interview' => 'user_trainer_iv',
            'usertrainerinterview', 'trainerinterview', 'user & trainer interview', 'user/trainer interview' => 'user_trainer_iv',
            'offering', 'offering (ol)', 'offer', 'ol' => 'offer',
            'mcu' => 'mcu',
            'mobilisasi', 'mobilization' => 'mobilisasi',
            'groundtest', 'ground test', 'ground_test' => 'ground_test',
            'onsite', 'on site', 'on_site' => 'onsite',
            'hired', 'diterima' => 'hired',
            'tidak lolos', 'not qualified', 'not_qualified', 'rejected', 'ditolak' => 'not_qualified',
            default => null,
        };
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
