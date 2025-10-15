<?php

namespace App\Services;

use Illuminate\Support\Str;
use Carbon\Carbon;

class IcsService
{
    /**
     * Generate ICS (calendar invite) for an interview.
     */
    public static function interviewInvite(
        string $title,
        Carbon $startAt,
        Carbon $endAt,
        string $organizerEmail,
        string $attendeeEmail,
        string $location = '',
        string $desc = ''
    ): string {
        $uid    = (string) Str::uuid();
        $dtStart = $startAt->clone()->setTimezone('UTC')->format('Ymd\THis\Z');
        $dtEnd   = $endAt->clone()->setTimezone('UTC')->format('Ymd\THis\Z');
        $now     = now()->setTimezone('UTC')->format('Ymd\THis\Z');

        $ics = "BEGIN:VCALENDAR\r\n"
             . "VERSION:2.0\r\n"
             . "PRODID:-//Andalan//Karir//ID\r\n"
             . "CALSCALE:GREGORIAN\r\n"
             . "METHOD:REQUEST\r\n"
             . "BEGIN:VEVENT\r\n"
             . "UID:{$uid}\r\n"
             . "DTSTAMP:{$now}\r\n"
             . "SUMMARY:" . self::e($title) . "\r\n"
             . "DTSTART:{$dtStart}\r\n"
             . "DTEND:{$dtEnd}\r\n"
             . "LOCATION:" . self::e($location) . "\r\n"
             . "DESCRIPTION:" . self::e($desc) . "\r\n"
             . "ORGANIZER:mailto:{$organizerEmail}\r\n"
             . "ATTENDEE;CN=Kandidat:mailto:{$attendeeEmail}\r\n"
             . "STATUS:CONFIRMED\r\n"
             . "SEQUENCE:0\r\n"
             . "BEGIN:VALARM\r\n"
             . "TRIGGER:-PT15M\r\n"
             . "ACTION:DISPLAY\r\n"
             . "DESCRIPTION:Reminder\r\n"
             . "END:VALARM\r\n"
             . "END:VEVENT\r\n"
             . "END:VCALENDAR\r\n";

        return $ics;
    }

    protected static function e(?string $v): string
    {
        $v = $v ?? '';
        // ICS butuh newline di-escape "\\n" dan karakter khusus di-escape
        $v = str_replace(["\r\n", "\n", "\r"], "\\n", $v);
        return addcslashes($v, ",;");
    }
}
