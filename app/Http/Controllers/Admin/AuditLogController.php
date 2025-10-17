<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Listing + filter sederhana. Aman kalau tabel belum ada.
     */
    public function index(Request $request)
    {
        // Kalau tabel belum ada, tampilkan halaman kosong + warning, jangan 500.
        if (! Schema::hasTable('audit_logs')) {
            return view('admin.audit_logs.index', [
                'items' => collect(),
                'tableMissing' => true,
                'filters' => [],
            ]);
        }

        $q          = trim((string) $request->query('q', ''));
        $event      = (string) $request->query('event', '');
        $userId     = (string) $request->query('user_id', '');
        $targetType = (string) $request->query('target_type', '');
        $dateFrom   = (string) $request->query('from', '');
        $dateTo     = (string) $request->query('to', '');

        $items = AuditLog::with(['user:id,name'])
            ->when($q, function ($qq) use ($q) {
                $qq->where('target_id', 'like', "%{$q}%")
                   ->orWhere('ip', 'like', "%{$q}%")
                   ->orWhere('user_agent', 'like', "%{$q}%");
            })
            ->when($event, fn($qq) => $qq->where('event', $event))
            ->when($userId, fn($qq) => $qq->where('user_id', $userId))
            ->when($targetType, fn($qq) => $qq->where('target_type', $targetType))
            ->when($dateFrom, fn($qq) => $qq->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($qq) => $qq->whereDate('created_at', '<=', $dateTo))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.audit_logs.index', [
            'items' => $items,
            'tableMissing' => false,
            'filters' => compact('q','event','userId','targetType','dateFrom','dateTo'),
        ]);
    }

    /**
     * Detail 1 log, aman jika id tidak ada.
     */
    public function show(string $id)
    {
        if (! Schema::hasTable('audit_logs')) {
            abort(404);
        }

        $log = AuditLog::with('user:id,name,email')->findOrFail($id);
        return view('admin.audit_logs.show', compact('log'));
    }

    /**
     * Ekspor CSV sederhana.
     */
    public function export(Request $request): StreamedResponse
    {
        if (! Schema::hasTable('audit_logs')) {
            abort(404);
        }

        $file = 'audit_logs_'.now()->format('Ymd_His').'.csv';

        $callback = function () use ($request) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','created_at','user_id','event','target_type','target_id','ip']);

            DB::table('audit_logs')
                ->orderByDesc('created_at')
                ->limit(10000) // batasi biar aman
                ->chunk(1000, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r->id,
                            $r->created_at,
                            $r->user_id,
                            $r->event,
                            $r->target_type,
                            $r->target_id,
                            $r->ip,
                        ]);
                    }
                });

            fclose($out);
        };

        return response()->streamDownload($callback, $file, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
