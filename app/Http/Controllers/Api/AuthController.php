<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // Normalize email for case-insensitive comparison
        $email = mb_strtolower(trim($credentials['email']));

        $user = User::query()
            ->select(['id', 'name', 'email', 'password', 'email_verified_at', 'role'])
            ->where('email', $email)
            ->first();

        // Timing-attack resistant: always check password even if user doesn't exist
        $passwordValid = $user && Hash::check($credentials['password'], $user->password);
        $emailVerified = $user?->hasVerifiedEmail();

        if (!$passwordValid || !$emailVerified) {
            return response()->json([
                'message' => 'Kredensial tidak valid atau email belum diverifikasi.',
            ], 422);
        }

        // Use hash for API token (NOT plain random string)
        $plainToken = Str::random(80);
        $hashedToken = hash('sha256', $plainToken);

        $user->forceFill([
            'api_token' => $hashedToken,
        ])->save();

        return response()->json([
            'message' => 'Login berhasil.',
            'token_type' => 'Bearer',
            'token' => $plainToken, // Return plain token once (user must save it)
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()?->only(['id', 'name', 'email', 'role']),
        ]);
    }
}