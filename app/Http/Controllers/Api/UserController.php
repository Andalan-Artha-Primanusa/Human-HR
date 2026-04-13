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
        return response()->json([
            'users' => User::query()->latest()->get(['id', 'name', 'email', 'role']),
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
        return response()->json([
            'users' => User::query()->latest()->get(['id', 'name', 'role']),
        ]);
    }

    private function publicUserResponse(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->only(['id', 'name', 'role']),
        ]);
    }
}