<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    /**
     * Tampilkan daftar notifikasi milik user login.
     * - $unread: semua yang belum dibaca
     * - $read  : riwayat (dibatasi 50 terakhir)
     */
    public function index(Request $request)
    {
        $user   = $request->user();

        // data utama (html)
        $unread = $user->unreadNotifications()->latest()->get();
        $read   = $user->readNotifications()->latest()->limit(50)->get();

        // === NEW: jika diminta JSON (AJAX polling dari topbar) ===
        if ($request->wantsJson() || $request->boolean('ajax') || $request->query('format') === 'json') {
            $unreadCount = $user->unreadNotifications()->count();

            $items = $user->notifications()
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
                        'title'      => $title,
                        'body'       => $body,
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

        // fallback tampilan HTML biasa
        return view('me.notifications.index', compact('unread', 'read'));
    }

    /**
     * Tandai semua notifikasi unread menjadi read.
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('ok', 'Semua pemberitahuan telah ditandai dibaca.');
    }

    /**
     * Tandai satu notifikasi sebagai read.
     * Param $notification adalah UUID dari tabel notifications.id
     */
    public function markRead(Request $request, string $notification)
    {
        $n = $request->user()
            ->notifications()               // scope by owner
            ->whereKey($notification)       // cari by primary key (UUID)
            ->firstOrFail();

        if (is_null($n->read_at)) {
            $n->markAsRead();
        }

        return back()->with('ok', 'Pemberitahuan ditandai dibaca.');
    }

    /**
     * Hapus satu notifikasi milik user.
     */
    public function destroy(Request $request, string $notification)
    {
        $n = $request->user()
            ->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $n->delete();

        return back()->with('ok', 'Pemberitahuan dihapus.');
    }
}
