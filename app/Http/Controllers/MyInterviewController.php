<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Interview;

class MyInterviewController extends Controller
{
    /**
     * Ambil interview milik user saat ini atau 404.
     * - Pilih kolom secukupnya (hemat I/O)
     * - Eager ketat untuk relasi yang dipakai view/ICS
     */
    private function forUserOrFail(Request $r, string $id): Interview
    {
        $u = $r->user();

        return Interview::query()
            ->select(['id','application_id','title','mode','start_at','end_at','location','meeting_link','notes'])
            ->with([
                'application:id,user_id,job_id',
                'application.user:id,name,email',
                'application.job:id,title,division,site_id',
                'application.job.site:id,code,name',
            ])
            ->whereKey($id)
            ->whereHas('application', fn($q) => $q->where('user_id', $u->id))
            ->firstOrFail();
    }

    /**
     * Daftar interview user.
     * - Select minimal kolom
     * - cursorPaginate untuk hemat memori pada dataset panjang
     */
    public function index(Request $request)
    {
        $u = $request->user();

        $interviews = Interview::query()
            ->select(['id','application_id','title','mode','start_at','end_at','location','meeting_link'])
            ->with([
                'application:id,job_id,user_id',
                'application.job:id,title,division,site_id',
                'application.job.site:id,code,name',
            ])
            ->whereHas('application', fn($q) => $q->where('user_id', $u->id))
            ->orderBy('start_at')
            ->cursorPaginate(20)
            ->withQueryString();

        return view('me.interviews.index', compact('interviews'));
    }

    public function show(Request $request, string $interview)
    {
        $iv = $this->forUserOrFail($request, $interview);
        return view('me.interviews.show', compact('iv'));
    }

    /**
     * Generate ICS dari start_at & end_at (UTC Z).
     * - Escape field sesuai RFC5545 sederhana (\, \; dan newline -> \\n)
     * - Lipat baris (folding) <= 75 chars untuk kompatibilitas
     */
    public function ics(Request $request, string $interview): StreamedResponse
    {
        $iv = $this->forUserOrFail($request, $interview);

        $uid     = Str::uuid().'@andalan-careers';
        $dtStart = optional($iv->start_at)->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
        $dtEnd   = optional($iv->end_at)->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
        $dtStamp = now()->setTimezone('UTC')->format('Ymd\THis\Z');

        $summary = $iv->title ?: ('Interview: '.$iv->application->job->title);
        $loc     = $iv->mode === 'online'
            ? ($iv->meeting_link ?: 'Online')
            : ($iv->location ?: 'TBD');

        $desc = trim(($iv->notes ?: '') . "\n\n"
            .'Job: '.$iv->application->job->title
            .($iv->application->job->site?->name ? ' @ '.$iv->application->job->site->name : '')
        );

        // Sanitize & escape untuk ICS
        $e = fn(string $v) => str_replace(
            ["\\", "\r\n", "\n", "\r", ",", ";"],
            ["\\\\","\\n","\\n","\\n","\\,", "\\;"],
            $v
        );

        $summary = $e($summary);
        $loc     = $e($loc);
        $desc    = $e($desc);

        // Folding baris panjang (<=75 chars) agar kompat
        $fold = function (string $line): string {
            $out = '';
            while (strlen($line) > 75) {
                $out .= substr($line, 0, 75) . "\r\n ";
                $line = substr($line, 75);
            }
            return $out . $line;
        };

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Andalan Careers//Interview//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$dtStamp,
            'DTSTART:'.$dtStart,
            'DTEND:'.$dtEnd,
            'SUMMARY:'.$summary,
            'LOCATION:'.$loc,
            'DESCRIPTION:'.$desc,
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ];

        // Terapkan folding
        $ics = implode("\r\n", array_map($fold, $lines));

        // Filename aman
        $safeId = preg_replace('/[^A-Za-z0-9\-_.]/', '', (string) $iv->id) ?: 'interview';
        $filename = 'interview-'.$safeId.'.ics';

        return response()->streamDownload(function () use ($ics) {
            echo $ics;
        }, $filename, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }
}
