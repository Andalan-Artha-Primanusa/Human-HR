<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // pakai authenticate() dari Breeze (sudah handle throttle & remember)
        $request->authenticate();

        // penting: regenerasi session id utk cegah fixation
        $request->session()->regenerate();

        // redirect ke intended atau fallback /dashboard
        return redirect()->intended(route('dashboard'));
        // Alternatif paling simpel: return redirect()->intended('/dashboard');
    }

    /**
     * Logout user.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
