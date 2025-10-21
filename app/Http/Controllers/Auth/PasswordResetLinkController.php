<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Throwable;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan form "Lupa Password".
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim link reset password ke email (jika terdaftar).
     * - Anti enumeration: selalu tampilkan pesan sukses yang sama
     * - Rate limit: 5 permintaan / menit per IP+email
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Normalisasi dan key throttle
        $email = Str::of($request->string('email'))->trim()->lower()->toString();
        $key   = 'pwd-reset:'.sha1($request->ip().'|'.$email);

        // Batasi 5x/menit
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => __("Terlalu banyak percobaan. Coba lagi dalam :seconds detik.", ['seconds' => $seconds]),
            ]);
        }

        // Hit sebelum kirim (decay 60 detik)
        RateLimiter::hit($key, 60);

        try {
            // Laravel akan *tetap* mengembalikan status, tapi kita tidak bocorkan ke user
            // apakah email ada/tiada.
            $status = Password::sendResetLink(['email' => $email]);

            // Optional: kalau mau logging internal
            if ($status !== Password::RESET_LINK_SENT) {
                // \Log::warning('Reset link not sent', ['email' => $email, 'status' => $status]);
            }
        } catch (Throwable $e) {
            // Log error mail transport/konfigurasi, tapi ke user tetap balasan generik
            // \Log::error('Reset password mail failed', ['email' => $email, 'error' => $e->getMessage()]);
        }

        // Selalu balikan pesan generik (anti enumeration)
        return back()->with('status', 'Jika email terdaftar, tautan reset telah dikirim.');
    }
}
