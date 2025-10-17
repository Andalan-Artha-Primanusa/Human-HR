<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Interview;

class MyInterviewController extends Controller
{
    private function forUserOrFail(Request $r, string $id): Interview
    {
        $u = $r->user();

        return Interview::with([
                'application.user:id,name,email',
                'application.job:id,title,division,site_id',
                'application.job.site:id,code,name',
            ])
            ->whereKey($id)
            ->whereHas('application', fn($q) => $q->where('user_id', $u->id))
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        $u = $request->user();

        $interviews = Interview::with([
                'application.job:id,title,division,site_id',
                'application.job.site:id,code,name',
            ])
            ->whereHas('application', fn($q) => $q->where('user_id', $u->id))
            ->orderBy('start_at')
            ->get();

        return view('me.interviews.index', compact('interviews'));
    }

    public function show(Request $request, string $interview)
    {
        $iv = $this->forUserOrFail($request, $interview);
        return view('me.interviews.show', compact('iv'));
    }

    /**
     * Generate ICS dari start_at & end_at (UTC Z).
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

        $ics = implode("\r\n", [
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
            'SUMMARY:'.addcslashes($summary, ",;"),
            'LOCATION:'.addcslashes($loc, ",;"),
            'DESCRIPTION:'.addcslashes($desc, ",;"),
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ]);

        $filename = 'interview-'.$iv->id.'.ics';

        return response()->streamDownload(function () use ($ics) {
            echo $ics;
        }, $filename, [
            'Content-Type' => 'text/calendar; charset=utf-8',
        ]);
    }

    // --- Aksi konfirmasi/decline/reschedule DISABLED ---
    // Skema tabelmu tidak punya kolom status/candidate_note.
    // Jika nanti dibutuhkan, tambahkan kolom2 tsb lalu aktifkan method action.
}
