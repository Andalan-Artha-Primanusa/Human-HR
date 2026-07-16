<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class EnsurePublicApiLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->resolveUser($request);

        if (! $user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Login dibutuhkan. Pakai Bearer token, Basic Auth, atau parameter email dan password.',
            ], 401);
        }

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email belum diverifikasi.',
            ], 403);
        }

        Auth::setUser($user);
        $request->setUserResolver(fn() => $user);

        return $next($request);
    }

    private function resolveUser(Request $request): ?User
    {
        if ($token = $request->bearerToken()) {
            return User::where('api_token', hash('sha256', $token))->first();
        }

        $email = $request->getUser() ?: $request->input('email');
        $password = $request->getPassword() ?: $request->input('password');

        $email = mb_strtolower(trim((string) $email));
        $password = (string) $password;

        if ($email === '' || $password === '') {
            return null;
        }

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }
}
