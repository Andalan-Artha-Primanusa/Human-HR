<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $allowed = array_map('trim', explode('|', $roles));

        if (! $request->user() || ! in_array($request->user()->role, $allowed, true)) {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}
