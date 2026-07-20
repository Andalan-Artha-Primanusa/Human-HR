<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiDateFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->usersResponse();
    }

    public function publicIndex(Request $request): JsonResponse
    {
        return $this->publicUsersResponse($request);
    }

    public function show(User $user): JsonResponse
    {
        return $this->userResponse($user);
    }

    public function publicShow(User $user): JsonResponse
    {
        return $this->publicUserResponse($user);
    }

    private function usersResponse(): JsonResponse
    {
        // Add pagination to prevent dumping all users at once
        $users = User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->with($this->userRelations())
            ->latest('id')
            ->paginate(50); // Limit to 50 per page

        return response()->json([
            'users' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }

    private function userResponse(User $user): JsonResponse
    {
        $user->load($this->userRelations());

        return response()->json([
            'user' => $user,
        ]);
    }

    private function publicUsersResponse(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'create_at' => ['nullable', 'date_format:Y-m-d'],
            'created_at' => ['nullable', 'date_format:Y-m-d'],
            'created_from' => ['nullable', 'date_format:Y-m-d'],
            'created_to' => ['nullable', 'date_format:Y-m-d'],
            'code' => ['nullable', 'string', 'max:80'],
            'code_id' => ['nullable', 'string', 'max:80'],
        ]);

        $createdAt = $filters['created_at'] ?? $filters['create_at'] ?? null;
        $createdFrom = $filters['created_from'] ?? null;
        $createdTo = $filters['created_to'] ?? null;
        $code = $filters['code_id'] ?? $filters['code'] ?? null;

        $users = User::query()
            ->select(['id', 'name', 'role', 'created_at'])
            ->with($this->userRelations($code))
            ->when($code, function ($query) use ($code) {
                $query->whereHas('jobApplications.job', fn($job) => $job->where('code', $code));
            })
            ->when($createdAt, fn($query) => $query->whereDate('created_at', $createdAt))
            ->when($createdFrom, fn($query) => $query->whereDate('created_at', '>=', $createdFrom))
            ->when($createdTo, fn($query) => $query->whereDate('created_at', '<=', $createdTo))
            ->latest('created_at')
            ->latest('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Public users retrieved successfully.',
            'filters' => [
                'created_at' => $createdAt,
                'created_from' => $createdFrom,
                'created_to' => $createdTo,
                'code' => $code,
            ],
            'count' => $users->count(),
            'data' => ApiDateFormatter::format($users->toArray()),
        ]);
    }

    private function publicUserResponse(User $user): JsonResponse
    {
        $user->load($this->userRelations());

        return response()->json([
            'user' => ApiDateFormatter::format($user->toArray()),
        ]);
    }

    private function userRelations(?string $jobCode = null): array
    {
        return [
            'candidateProfile.poh',
            'candidateProfile.trainings',
            'candidateProfile.employments',
            'candidateProfile.references',
            'candidateProfile.attachments',
            'jobApplications' => function ($query) use ($jobCode) {
                $query
                    ->when($jobCode, fn($q) => $q->whereHas('job', fn($job) => $job->where('code', $jobCode)))
                    ->latest('created_at');
            },
            'jobApplications.job.site',
            'jobApplications.job.company',
            'jobApplications.poh',
            'jobApplications.stages.actor',
            'jobApplications.stages.user',
            'jobApplications.interviews',
            'jobApplications.psychotestAttempts.test',
            'jobApplications.psychotestAttempts.answers',
            'jobApplications.offer',
            'jobApplications.feedbacks.user',
            'jobApplications.attachments',
        ];
    }
}
