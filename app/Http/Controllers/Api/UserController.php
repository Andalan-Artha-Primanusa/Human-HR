<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'users' => User::query()->latest()->get(),
        ]);
    }

    public function publicIndex(): JsonResponse
    {
        return response()->json([
            'users' => User::query()->latest()->get(),
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user,
        ]);
    }

    public function publicShow(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user,
        ]);
    }
}