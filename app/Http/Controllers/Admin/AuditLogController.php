<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    /**
     * Listing + filter: hemat kolom, aman kalau tabel belum ada.
     */
    public function index(Request $request)
    {
        // Jika tabel belum ada → halaman kosong, tidak 500
        if (! Schema::hasTable('audit_logs')) {
            return view('admin.audit_logs.index', [
                'items' => collect(),
                'tableMissing' => true,
                'filters' => [],
            ]);
        }

        // === Ambil & sanitasi filter ===
        $qRaw       = (string) $request->query('q', '');
        // buang karakter kontrol & batasi panjang (hindari query liar di LIKE)
        $q          = Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '', 120, '');
        $event      = (string) $request->query('event', '');
        $userId     = (string) $request->query('user_id', '');
        $targetType = (string) $request->query('target_type', '');
        $dateFrom   = (string) $request->query('from', '');
        $dateTo     = (string) $request->query('to', '');

        // parse tanggal aman (opsional)
        [$fromAt, $toAt] = $this->parseDateRange($dateFrom, $dateTo);

        // === Query ORM: select kolom secukupnya + eager user (hindari N+1) ===
        $items = AuditLog::query()
            ->select(['id','created_at','user_id','event','target_type','target_id','ip']) // kolom minimal untuk listing
            ->with(['user:id,name']) // hanya id & name
            // cari bebas: bungkus OR dalam group agar precedence benar
            ->when($q !== '', function ($qq) use ($q) {
                $like = '%'.$q.'%';
                $qq->where(function ($w) use ($like) {
                    $w->where('target_id', 'like', $like)
                      ->orWhere('ip', 'like', $like)
                      ->orWhere('user_agent', 'like', $like);
                });
            })
            ->when($event !== '', fn($qq) => $qq->where('event', $event))
            ->when($userId !== '', fn($qq) => $qq->where('user_id', $userId))
            ->when($targetType !== '', fn($qq) => $qq->where('target_type', $targetType))
            ->when($fromAt, fn($qq) => $qq->where('created_at', '>=', $fromAt))
            ->when($toAt,   fn($qq) => $qq->where('created_at', '<=', $toAt))
            // cursorPaginate → stabil & irit pada tabel besar (pakai key stabil)
            ->orderByDesc('id')
            ->cursorPaginate(30)
            ->withQueryString();

        return view('admin.audit_logs.index', [
            'items' => $items,
            'tableMissing' => false,
            'filters' => compact('q','event','userId','targetType','dateFrom','dateTo'),
        ]);
    }

    /**
     * Detail 1 log. Aman jika tabel tidak ada / id tidak ada.
     */
    public function show(string $id)
    {
        if (! Schema::hasTable('audit_logs')) {
            abort(404);
        }

        // findOrFail dengan eager user, pilih kolom yang relevan saja
        $log = AuditLog::with('user:id,name,email')
            ->findOrFail($id, [
                'id','created_at','user_id','event','target_type','target_id',
                'ip','user_agent','meta',
            ]);

        return view('admin.audit_logs.show', compact('log'));
    }

    /**
     * Ekspor CSV streaming: apply filter yang sama, chunkById (lebih efisien).
     */
    public function export(Request $request): StreamedResponse
    {
        if (! Schema::hasTable('audit_logs')) {
            abort(404);
        }

        // Ambil filter yang sama dengan index
        $qRaw       = (string) $request->query('q', '');
        $q          = Str::limit(preg_replace('/[\x00-\x1F\x7F]/u', '', trim($qRaw)) ?? '', 120, '');
        $event      = (string) $request->query('event', '');
        $userId     = (string) $request->query('user_id', '');
        $targetType = (string) $request->query('target_type', '');
        $dateFrom   = (string) $request->query('from', '');
        $dateTo     = (string) $request->query('to', '');
        [$fromAt, $toAt] = $this->parseDateRange($dateFrom, $dateTo);

        $file = 'audit_logs_'.now()->format('Ymd_His').'.csv';

        $callback = function () use ($q, $event, $userId, $targetType, $fromAt, $toAt) {
            @set_time_limit(0);
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','created_at','user_id','event','target_type','target_id','ip']);

            // Query Builder untuk stream ringan; apply filter yang sama
            $builder = DB::table('audit_logs')
                ->select(['id','created_at','user_id','event','target_type','target_id','ip'])
                ->when($q !== '', function ($qq) use ($q) {
                    $like = '%'.$q.'%';
                    $qq->where(function ($w) use ($like) {
                        $w->where('target_id','like',$like)
                          ->orWhere('ip','like',$like)
                          ->orWhere('user_agent','like',$like);
                    });
                })
                ->when($event !== '', fn($qq) => $qq->where('event', $event))
                ->when($userId !== '', fn($qq) => $qq->where('user_id', $userId))
                ->when($targetType !== '', fn($qq) => $qq->where('target_type', $targetType))
                ->when($fromAt, fn($qq) => $qq->where('created_at', '>=', $fromAt))
                ->when($toAt,   fn($qq) => $qq->where('created_at', '<=', $toAt))
                ->orderByDesc('id');

            // chunkById menghindari offset-scan panjang
            $builder->chunkById(1000, function ($rows) use ($out) {
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
            }, 'id');

            fclose($out);
        };

        return response()->streamDownload($callback, $file, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    /**
     * Parse rentang tanggal aman (null jika invalid).
     */
    private function parseDateRange(?string $from, ?string $to): array
    {
        $fromAt = null; $toAt = null;

        if ($from) {
            try { $fromAt = Carbon::parse($from)->startOfDay(); } catch (\Throwable $e) {}
        }
        if ($to) {
            try { $toAt = Carbon::parse($to)->endOfDay(); } catch (\Throwable $e) {}
        }
        return [$fromAt, $toAt];
    }
}
