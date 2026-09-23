<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Support\ApiDateFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicJobController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:draft,open,closed'],
            'create_at' => ['nullable', 'date_format:Y-m-d'],
            'created_at' => ['nullable', 'date_format:Y-m-d'],
            'created_from' => ['nullable', 'date_format:Y-m-d'],
            'created_to' => ['nullable', 'date_format:Y-m-d'],
            'job_created_from' => ['nullable', 'date_format:Y-m-d'],
            'job_created_to' => ['nullable', 'date_format:Y-m-d'],
            'create_from' => ['nullable', 'date_format:Y-m-d'],
            'create_to' => ['nullable', 'date_format:Y-m-d'],
            'applied_from' => ['nullable', 'date_format:Y-m-d'],
            'applied_to' => ['nullable', 'date_format:Y-m-d'],
            'code_id' => ['nullable', 'string', 'max:80'],
            'code' => ['nullable', 'string', 'max:80'],
            'nik' => ['nullable', 'string', 'max:17'],
        ]);

        $code = $filters['code_id'] ?? $filters['code'] ?? null;
        $code = is_string($code) ? trim($code) : $code;
        $createdAt = $filters['created_at'] ?? $filters['create_at'] ?? null;
        // `created_from/to` now refer to application.created_at.
        $appliedFrom = $filters['created_from'] ?? $filters['create_from'] ?? $filters['applied_from'] ?? null;
        $appliedTo = $filters['created_to'] ?? $filters['create_to'] ?? $filters['applied_to'] ?? null;
        $jobCreatedFrom = $filters['job_created_from'] ?? null;
        $jobCreatedTo = $filters['job_created_to'] ?? null;
        $nik = isset($filters['nik']) ? trim((string) $filters['nik']) : null;
        $applicationFilter = function ($query) use ($appliedFrom, $appliedTo) {
            $query->when($appliedFrom, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                ->when($appliedTo, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
        };
        if ($nik !== null && $nik !== '') {
            $applicationFilter = function ($query) use ($appliedFrom, $appliedTo, $nik) {
                $query->when($appliedFrom, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                    ->when($appliedTo, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
                    ->whereHas('user.candidateProfile', fn ($q) => $q->where('nik', $nik));
            };
        }
        $relations = $this->relations();
        if ($appliedFrom || $appliedTo) {
            $relations['applications'] = $applicationFilter;
        }

        $jobs = Job::query()
            ->with($relations)
            ->withCount(['applications' => $applicationFilter])
            ->when($code, fn($query) => $query->whereRaw('LOWER(TRIM(code)) = ?', [mb_strtolower($code)]))
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($createdAt, fn($query, $date) => $query->whereDate('created_at', $date))
            ->when($jobCreatedFrom, fn($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($jobCreatedTo, fn($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when($appliedFrom || $appliedTo || ($nik !== null && $nik !== ''), fn ($query) => $query->whereHas('applications', $applicationFilter))
            ->latest('created_at')
            ->latest('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Public jobs retrieved successfully.',
            'filters' => [
                'status' => $filters['status'] ?? null,
                'created_at' => $createdAt,
                'created_from' => $appliedFrom,
                'created_to' => $appliedTo,
                'code_id' => $code,
            ],
            'count' => (int) $jobs->sum('applications_count'),
            'data' => ApiDateFormatter::format($jobs->toArray()),
        ]);
    }

    public function show(Job $job): JsonResponse
    {
        $job->load($this->relations())->loadCount('applications');

        return response()->json([
            'status' => 'success',
            'message' => 'Public job retrieved successfully.',
            'data' => ApiDateFormatter::format($job->toArray()),
        ]);
    }

    public function showByCode(string $code): JsonResponse
    {
        $job = Job::query()
            ->where('code', $code)
            ->with($this->relations())
            ->withCount('applications')
            ->first();

        if (! $job) {
            return response()->json([
                'status' => 'error',
                'message' => 'Job tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Public job retrieved successfully.',
            'data' => ApiDateFormatter::format($job->toArray()),
        ]);
    }

    private function relations(): array
    {
        return [
            'site',
            'company',
            'creator',
            'updater',
            'manpowerRequirements',
            'applications.user.candidateProfile.poh',
            'applications.user.candidateProfile.trainings',
            'applications.user.candidateProfile.employments',
            'applications.user.candidateProfile.references',
            'applications.user.candidateProfile.attachments',
            'applications.poh',
            'applications.stages.actor',
            'applications.stages.user',
            'applications.interviews',
            'applications.psychotestAttempts.test',
            'applications.psychotestAttempts.answers',
            'applications.offer',
            'applications.feedbacks.user',
            'applications.attachments',
        ];
    }
}
