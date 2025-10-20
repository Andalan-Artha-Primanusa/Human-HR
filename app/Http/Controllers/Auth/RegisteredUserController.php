<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan halaman register.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Proses registrasi (verifikasi via OTP/Kode, tanpa link).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // 'agree'  => ['accepted'], // jika ada checkbox persetujuan
        ]);

        // Catatan: $request->string(...) mengembalikan Stringable; cast ke string.
        $user = User::create([
            'name'       => (string) $request->string('name'),
            'email'      => (string) $request->string('email'),
            'password'   => Hash::make((string) $request->string('password')),
            'role'       => $request->input('role', 'pelamar'),
            'id_employe' => $request->input('id_employe'),
        ]);

        // Trigger event standar → listener SendEmailVerificationCode akan generate & kirim OTP
        event(new Registered($user));

        // Login user supaya bisa akses form OTP
        Auth::login($user);

        // >>> TANPA verifikasi link. Langsung ke form input kode OTP.
        return redirect()->route('verification.code.form')
            ->with('status', 'verification-link-sent'); // opsional: ubah pesan jika mau
    }
}
