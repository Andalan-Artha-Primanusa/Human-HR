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
            'code_id' => ['nullable', 'string', 'max:80'],
            'code' => ['nullable', 'string', 'max:80'],
        ]);

        $code = $filters['code_id'] ?? $filters['code'] ?? null;
        $createdAt = $filters['created_at'] ?? $filters['create_at'] ?? null;

        $jobs = Job::query()
            ->with($this->relations())
            ->withCount('applications')
            ->when($code, fn($query) => $query->where('code', $code))
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($createdAt, fn($query, $date) => $query->whereDate('created_at', $date))
            ->when($filters['created_from'] ?? null, fn($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['created_to'] ?? null, fn($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest('created_at')
            ->latest('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Public jobs retrieved successfully.',
            'filters' => [
                'status' => $filters['status'] ?? null,
                'created_at' => $createdAt,
                'created_from' => $filters['created_from'] ?? null,
                'created_to' => $filters['created_to'] ?? null,
                'code_id' => $code,
            ],
            'count' => $jobs->count(),
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
