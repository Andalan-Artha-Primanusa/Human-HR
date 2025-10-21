<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Tampilkan form set password baru.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Proses set password baru dari link reset.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Normalisasi + throttle key
        $email = Str::of($request->string('email'))->trim()->lower()->toString();
        $key   = 'pwd-set:'.sha1($request->ip().'|'.$email);

        // Batasi 5x/menit
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => __("Terlalu banyak percobaan. Coba lagi dalam :seconds detik.", ['seconds' => $seconds]),
            ]);
        }
        RateLimiter::hit($key, 60);

        // Jalankan reset: validasi token + set password
        $status = Password::reset(
            [
                'email'                 => $email,
                'password'              => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token'                 => $request->token,
            ],
            function (User $user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                    // Opsional: auto-verify email jika kebijakan mengizinkan
                    // 'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            // Sukses → arahkan ke login dengan pesan jelas
            return redirect()->route('login')
                ->with('status', 'Password berhasil diperbarui. Silakan masuk.');
        }

        // Gagal (token/email mismatch/expired) → pesan generik, jangan bocorkan detail
        return back()
            ->withInput(['email' => $email])
            ->withErrors(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.']);
    }
}
