<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Verified;
use App\Notifications\EmailVerificationCodeNotification;

class VerifyCodeController extends Controller
{
    public function notice()
    {
        return auth()->user()?->email_verified_at
            ? redirect()->route('welcome')
            : redirect()->route('verification.code.form');
    }

    public function showForm(Request $request)
    {
        if ($request->user()->email_verified_at) {
            return redirect()->route('welcome');
        }

        // ⛔️ Jangan kirim otomatis di sini, cukup pastikan ada record valid TANPA email
        $this->ensureCode($request->user(), false);
        return view('auth.verify-code');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required','digits:6']]);

        $user = $request->user();
        $row  = EmailVerificationCode::where('user_id', $user->id)->first();

        if (!$row) {
            $this->ensureCode($user, true);
            return back()->withErrors(['code' => 'Kode tidak ditemukan. Kode baru telah dikirim.']);
        }

        if (now()->greaterThan($row->expires_at)) {
            $this->ensureCode($user, true);
            return back()->withErrors(['code' => 'Kode kedaluwarsa. Kode baru telah dikirim.']);
        }

        $maxAttempts = (int) (config('auth.verify_max_attempts', env('VERIFY_MAX_ATTEMPTS', 5)));
        if ($row->attempts >= $maxAttempts) {
            $this->ensureCode($user, true);
            return back()->withErrors(['code' => 'Percobaan terlalu banyak. Kode baru telah dikirim.']);
        }

        $input = preg_replace('/\D+/', '', (string) $request->code);

        if (!Hash::check($input, (string) $row->code_hash)) {
            $row->increment('attempts');
            return back()->withErrors(['code' => 'Kode salah. Coba lagi.']);
        }

        // success
        $user->forceFill(['email_verified_at' => now()])->save();
        $row->delete();
        event(new Verified($user));

        return redirect()->route('welcome')->with('status', 'email-verified');
    }

    public function resend(Request $request)
    {
        $user = $request->user();
        $cooldown = (int) (config('auth.verify_resend_cooldown', env('VERIFY_RESEND_COOLDOWN', 60)));

        $row = EmailVerificationCode::firstOrNew(['user_id' => $user->id]);

        if ($row->exists && $row->last_sent_at && now()->diffInSeconds($row->last_sent_at) < $cooldown) {
            $left = $cooldown - now()->diffInSeconds($row->last_sent_at);
            return back()->withErrors(['resend' => "Tunggu {$left}s untuk kirim ulang."]);
        }

        $this->ensureCode($user, true); // kirim sinkron, hormati cooldown di dalam
        return back()->with('status', 'verification-link-sent');
    }

    /**
     * Idempotent: kalau masih valid & belum lewat cooldown, tidak kirim.
     */
    private function ensureCode($user, bool $sendMail = true): EmailVerificationCode
    {
        $ttl      = (int) (config('auth.verify_code_ttl', env('VERIFY_CODE_TTL', 10)));
        $cooldown = (int) (config('auth.verify_resend_cooldown', env('VERIFY_RESEND_COOLDOWN', 60)));

        $row = EmailVerificationCode::firstOrNew(['user_id' => $user->id]);

        // Jika masih valid & dalam window cooldown → cukup return
        if ($row->exists &&
            $row->expires_at && now()->lessThan($row->expires_at) &&
            $row->last_sent_at && now()->diffInSeconds($row->last_sent_at) < $cooldown
        ) {
            return $row;
        }

        $plain = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $row->fill([
            'code_hash'    => \Hash::make($plain),
            'expires_at'   => now()->addMinutes($ttl),
            'attempts'     => 0,
            'last_sent_at' => now(),
        ])->save();

        if ($sendMail) {
            Notification::sendNow($user, new EmailVerificationCodeNotification($plain, $ttl));
            session()->flash('status', 'verification-link-sent');
        }

        return $row;
    }
}
