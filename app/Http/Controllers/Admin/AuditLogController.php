<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * AuditLogController
 *
 * Mendukung dua skenario:
 * 1) Tabel Spatie Activity Log: 'activity_log'
 * 2) Tabel kustom 'audit_logs' (id uuid, event, table_name, old_values, new_values, user_id, created_at, dll)
 *
 * Controller ini akan otomatis memilih sumber yang tersedia.
 */
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q       = (string) $request->query('q', '');
        $model   = (string) $request->query('model', '');     // filter subject_type / table_name
        $userId  = (string) $request->query('user', '');
        $dateMin = (string) $request->query('from', '');
        $dateMax = (string) $request->query('to', '');

        $source = $this->detectSource();

        if ($source === 'activity_log') {
            $logs = DB::table('activity_log')
                ->when($q, function ($qq) use ($q) {
                    $qq->where('description', 'like', "%{$q}%")
                       ->orWhere('properties', 'like', "%{$q}%");
                })
                ->when($model, fn($qq) => $qq->where('subject_type', $model))
                ->when($userId, fn($qq) => $qq->where('causer_id', $userId))
                ->when($dateMin, fn($qq) => $qq->whereDate('created_at', '>=', $dateMin))
                ->when($dateMax, fn($qq) => $qq->whereDate('created_at', '<=', $dateMax))
                ->orderByDesc('created_at')
                ->paginate(30)
                ->withQueryString();

            return view('admin.audit_logs.index', [
                'logs'   => $logs,
                'source' => $source,
                'filters'=> compact('q','model','userId','dateMin','dateMax'),
            ]);
        }

        // Fallback: audit_logs kustom
        $logs = DB::table('audit_logs')
            ->when($q, function ($qq) use ($q) {
                $qq->where('event', 'like', "%{$q}%")
                   ->orWhere('old_values', 'like', "%{$q}%")
                   ->orWhere('new_values', 'like', "%{$q}%");
            })
            ->when($model, fn($qq) => $qq->where('table_name', $model))
            ->when($userId, fn($qq) => $qq->where('user_id', $userId))
            ->when($dateMin, fn($qq) => $qq->whereDate('created_at', '>=', $dateMin))
            ->when($dateMax, fn($qq) => $qq->whereDate('created_at', '<=', $dateMax))
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit_logs.index', [
            'logs'   => $logs,
            'source' => $source,
            'filters'=> compact('q','model','userId','dateMin','dateMax'),
        ]);
    }

    public function show(Request $request, string $log)
    {
        $source = $this->detectSource();

        if ($source === 'activity_log') {
            $row = DB::table('activity_log')->where('id', $log)->first();
            abort_unless($row, 404);

            $properties = $this->decodeJson($row->properties ?? null);
            // Spatie biasanya menyimpan old & attributes di properties['old'] dan ['attributes']
            $old = $properties['old']        ?? null;
            $new = $properties['attributes'] ?? null;

            return view('admin.audit_logs.show', [
                'log'       => $row,
                'source'    => $source,
                'oldValues' => $old,
                'newValues' => $new,
                'properties'=> $properties,
            ]);
        }

        // Fallback: audit_logs kustom (id UUID/string)
        $row = DB::table('audit_logs')->where('id', $log)->first();
        abort_unless($row, 404);

        $old = $this->decodeJson($row->old_values ?? null);
        $new = $this->decodeJson($row->new_values ?? null);

        return view('admin.audit_logs.show', [
            'log'       => $row,
            'source'    => $source,
            'oldValues' => $old,
            'newValues' => $new,
            'properties'=> null,
        ]);
    }

    /**
     * Export CSV dari audit log (rentang filter sama dgn index)
     */
    public function export(Request $request): StreamedResponse
    {
        $q       = (string) $request->query('q', '');
        $model   = (string) $request->query('model', '');
        $userId  = (string) $request->query('user', '');
        $dateMin = (string) $request->query('from', '');
        $dateMax = (string) $request->query('to', '');

        $source   = $this->detectSource();
        $filename = 'audit-logs-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($q,$model,$userId,$dateMin,$dateMax,$source) {
            $out = fopen('php://output', 'w');

            if ($source === 'activity_log') {
                fputcsv($out, ['id','log_name','description','subject_type','subject_id','causer_id','properties','created_at']);

                DB::table('activity_log')
                    ->when($q, function ($qq) use ($q) {
                        $qq->where('description', 'like', "%{$q}%")
                           ->orWhere('properties', 'like', "%{$q}%");
                    })
                    ->when($model, fn($qq) => $qq->where('subject_type', $model))
                    ->when($userId, fn($qq) => $qq->where('causer_id', $userId))
                    ->when($dateMin, fn($qq) => $qq->whereDate('created_at', '>=', $dateMin))
                    ->when($dateMax, fn($qq) => $qq->whereDate('created_at', '<=', $dateMax))
                    ->orderBy('created_at')
                    ->chunk(500, function ($chunk) use ($out) {
                        foreach ($chunk as $r) {
                            fputcsv($out, [
                                $r->id,
                                $r->log_name,
                                $r->description,
                                $r->subject_type,
                                $r->subject_id,
                                $r->causer_id,
                                $this->minifyJson($r->properties),
                                optional($r->created_at)->toDateTimeString(),
                            ]);
                        }
                    });
            } else {
                fputcsv($out, ['id','event','table_name','user_id','old_values','new_values','created_at']);

                DB::table('audit_logs')
                    ->when($q, function ($qq) use ($q) {
                        $qq->where('event', 'like', "%{$q}%")
                           ->orWhere('old_values', 'like', "%{$q}%")
                           ->orWhere('new_values', 'like', "%{$q}%");
                    })
                    ->when($model, fn($qq) => $qq->where('table_name', $model))
                    ->when($userId, fn($qq) => $qq->where('user_id', $userId))
                    ->when($dateMin, fn($qq) => $qq->whereDate('created_at', '>=', $dateMin))
                    ->when($dateMax, fn($qq) => $qq->whereDate('created_at', '<=', $dateMax))
                    ->orderBy('created_at')
                    ->chunk(500, function ($chunk) use ($out) {
                        foreach ($chunk as $r) {
                            fputcsv($out, [
                                $r->id,
                                $r->event,
                                $r->table_name,
                                $r->user_id,
                                $this->minifyJson($r->old_values),
                                $this->minifyJson($r->new_values),
                                optional($r->created_at)->toDateTimeString(),
                            ]);
                        }
                    });
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function detectSource(): string
    {
        if (DB::getSchemaBuilder()->hasTable('activity_log')) {
            return 'activity_log';
        }
        if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
            return 'audit_logs';
        }
        // Kalau dua-duanya tidak ada, buatkan view kosong dengan info
        abort(500, 'Tidak ditemukan sumber audit log (butuh tabel activity_log atau audit_logs).');
    }

    private function decodeJson($value)
    {
        if (!$value) return null;
        try {
            if (is_string($value)) {
                return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            }
            // Bisa jadi sudah array/stdClass
            return json_decode(json_encode($value), true);
        } catch (\Throwable $e) {
            return ['_raw' => (string) $value];
        }
    }

    private function minifyJson($value): string
    {
        if ($value === null) return '';
        try {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    return (string) $value;
                }
                return json_encode($decoded, JSON_UNESCAPED_UNICODE);
            }
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }
}
