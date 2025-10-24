<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserNotificationController extends Controller
{
    /**
     * Tampilkan daftar notifikasi milik user login.
     * - HTML: unread (semua) + read (50 terakhir)
     * - JSON: ringkas untuk topbar (10 terakhir) + unread count
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // === JSON (AJAX polling) ===
        if ($request->wantsJson() || $request->boolean('ajax') || $request->query('format') === 'json') {
            // Ambil hanya kolom yang dibutuhkan agar hemat I/O
            $unreadCount = (int) $user->unreadNotifications()->count();

            $items = $user->notifications()
                ->select(['id','data','created_at','read_at'])
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($n) {
                    $data  = (array) ($n->data ?? []);
                    $title = $data['title'] ?? ($data['message'] ?? 'Notifikasi');
                    $body  = $data['body']  ?? ($data['excerpt'] ?? null);
                    $url   = $data['url']   ?? null;

                    return [
                        'id'         => (string) $n->id,
                        'title'      => Str::limit((string) $title, 120, '…'),
                        'body'       => $body ? Str::limit((string) $body, 180, '…') : null,
                        'url'        => $url,
                        'created_at' => optional($n->created_at)->diffForHumans(),
                        'unread'     => is_null($n->read_at),
                    ];
                });

            return response()->json([
                'unread' => $unreadCount,
                'items'  => $items,
            ]);
        }

        // === HTML ===
        // Unread: tampilkan semua (biasanya tidak banyak). Jika ingin dibatasi, ubah ke paginate/cursorPaginate.
        $unread = $user->unreadNotifications()
            ->select(['id','data','created_at','read_at'])
            ->latest()
            ->get();

        // Read: batasi 50 terakhir agar tidak berat
        $read = $user->readNotifications()
            ->select(['id','data','created_at','read_at'])
            ->latest()
            ->limit(50)
            ->get();

        return view('me.notifications.index', compact('unread','read'));
    }

    /**
     * Tandai semua notifikasi unread menjadi read.
     * - Gunakan UPDATE langsung (tidak load ke memori)
     * - Idempotent
     */
    public function markAllRead(Request $request)
    {
        $request->user()
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
        $affected = $request->user()
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
        $n = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $n->delete();

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'deleted' => true]);
        }

        return back()->with('ok', 'Pemberitahuan dihapus.');
    }
}
