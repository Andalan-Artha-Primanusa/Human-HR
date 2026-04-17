<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserNotificationController extends Controller
{
    private const TOPBAR_LIMIT = 10;
    private const HTML_UNREAD_LIMIT = 100;
    private const HTML_READ_LIMIT = 50;

    /**
     * Tampilkan daftar notifikasi milik user login.
     * - HTML: unread (semua) + read (50 terakhir)
     * - JSON: ringkas untuk topbar (10 terakhir) + unread count
     */
    public function index(Request $request)
    {
        $request->validate([
            'format' => ['nullable', 'in:json'],
            'ajax' => ['nullable', 'boolean'],
        ]);

        $user = $request->user();
        abort_if(!$user, 401);

        // === JSON (AJAX polling) ===
        if ($request->wantsJson() || $request->boolean('ajax') || $request->query('format') === 'json') {
            // Ambil hanya kolom yang dibutuhkan agar hemat I/O
            $unreadCount = (int) $user->unreadNotifications()->count();

            $items = $user->notifications()
                ->select(['id', 'data', 'created_at', 'read_at'])
                ->latest()
                ->limit(self::TOPBAR_LIMIT)
                ->get()
                ->map(function ($n) {
                    $data = (array) ($n->data ?? []);
                    $title = $data['title'] ?? ($data['message'] ?? 'Notifikasi');
                    $body = $data['body'] ?? ($data['excerpt'] ?? null);
                    $url = $this->sanitizeNotificationUrl($data['url'] ?? null);

                    return [
                        'id' => (string) $n->id,
                        'title' => Str::limit((string) $title, 120, '…'),
                        'body' => $body ? Str::limit((string) $body, 180, '…') : null,
                        'url' => $url,
                        'created_at' => optional($n->created_at)->diffForHumans(),
                        'unread' => is_null($n->read_at),
                    ];
                });

            return response()->json([
                'unread' => $unreadCount,
                'items' => $items,
            ]);
        }

        // === HTML ===
        // Unread dibatasi agar tidak berat jika akun punya notifikasi sangat banyak.
        $unread = $user->unreadNotifications()
            ->select(['id', 'data', 'created_at', 'read_at'])
            ->latest()
            ->limit(self::HTML_UNREAD_LIMIT)
            ->get();

        // Read: batasi 50 terakhir agar tidak berat
        $read = $user->readNotifications()
            ->select(['id', 'data', 'created_at', 'read_at'])
            ->latest()
            ->limit(self::HTML_READ_LIMIT)
            ->get();

        return view('me.notifications.index', compact('unread', 'read'));
    }

    /**
     * Tandai semua notifikasi unread menjadi read.
     * - Gunakan UPDATE langsung (tidak load ke memori)
     * - Idempotent
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        abort_if(!$user, 401);

        $user
            ->unreadNotifications()
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Semua pemberitahuan telah ditandai dibaca.']);
        }

        return back()->with('ok', 'Semua pemberitahuan telah ditandai dibaca.');
    }

    /**
     * Tandai satu notifikasi sebagai read (by UUID).
     * - Scope ke pemilik
     * - Update langsung agar hemat
     */
    public function markRead(Request $request, string $notification)
    {
        $user = $request->user();
        abort_if(!$user, 401);

        $affected = $user
            ->notifications()
            ->whereKey($notification)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'updated' => (bool) $affected]);
        }

        return back()->with('ok', 'Pemberitahuan ditandai dibaca.');
    }

    /**
     * Hapus satu notifikasi milik user.
     * - Scope ke pemilik
     */
    public function destroy(Request $request, string $notification)
    {
        $user = $request->user();
        abort_if(!$user, 401);

        $n = $user
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $n->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'deleted' => true]);
        }

        return back()->with('ok', 'Pemberitahuan dihapus.');
    }

    private function sanitizeNotificationUrl(mixed $url): ?string
    {
        if (!is_string($url) || $url === '') {
            return null;
        }

        // Relative URL internal selalu diizinkan.
        if (str_starts_with($url, '/')) {
            return $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts) || !isset($parts['scheme'])) {
            return null;
        }

        $scheme = strtolower((string) $parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        // Izinkan absolute URL hanya jika host sama dengan APP_URL.
        $targetHost = strtolower((string) ($parts['host'] ?? ''));
        $appHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        if ($targetHost === '' || $appHost === '' || $targetHost !== $appHost) {
            return null;
        }

        return $url;
    }
}
