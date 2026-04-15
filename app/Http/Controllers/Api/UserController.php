<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->usersResponse();
    }

    public function publicIndex(): JsonResponse
    {
        return $this->publicUsersResponse();
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
        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    private function publicUsersResponse(): JsonResponse
    {
        // Add pagination for public endpoint (prevent scraping)
        $users = User::query()
            ->select(['id', 'name', 'role'])
            ->latest('id')
            ->paginate(30); // Smaller page size for public
        
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

    private function publicUserResponse(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->only(['id', 'name', 'role']),
        ]);
    }
}