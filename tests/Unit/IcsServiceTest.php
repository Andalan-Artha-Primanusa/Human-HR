<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\IcsService;
use Carbon\Carbon;

class IcsServiceTest extends TestCase
{
    public function test_generate_basic_ics_file(): void
    {
        $startAt = Carbon::create(2026, 5, 15, 10, 0, 0, 'Asia/Jakarta');
        $endAt = Carbon::create(2026, 5, 15, 11, 0, 0, 'Asia/Jakarta');

        $ics = IcsService::interviewInvite(
            'Interview Test',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com'
        );

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('END:VEVENT', $ics);
        $this->assertStringContainsString('VERSION:2.0', $ics);
        $this->assertStringContainsString('CALSCALE:GREGORIAN', $ics);
        $this->assertStringContainsString('METHOD:REQUEST', $ics);
    }

    public function test_ics_contains_summary(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Technical Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com'
        );

        $this->assertStringContainsString('SUMMARY:Technical Interview', $ics);
    }

    public function test_ics_contains_datetime_in_utc(): void
    {
        $startAt = Carbon::create(2026, 5, 15, 10, 0, 0, 'Asia/Jakarta');
        $endAt = Carbon::create(2026, 5, 15, 11, 0, 0, 'Asia/Jakarta');

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com'
        );

        $this->assertMatchesRegularExpression('/DTSTART:\d{8}T\d{6}Z/', $ics);
        $this->assertMatchesRegularExpression('/DTEND:\d{8}T\d{6}Z/', $ics);
    }

    public function test_ics_contains_organizer_and_attendee(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'organizer@company.com',
            'attendee@email.com'
        );

        $this->assertStringContainsString('ORGANIZER:mailto:organizer@company.com', $ics);
        $this->assertStringContainsString('ATTENDEE;CN=Kandidat:mailto:attendee@email.com', $ics);
    }

    public function test_ics_contains_location(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com',
            'Jakarta Office'
        );

        $this->assertStringContainsString('LOCATION:Jakarta Office', $ics);
    }

    public function test_ics_contains_description(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com',
            '',
            'Please bring your portfolio'
        );

        $this->assertStringContainsString('DESCRIPTION:Please bring your portfolio', $ics);
    }

    public function test_ics_escapes_newlines_in_description(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com',
            '',
            "Line 1\nLine 2\r\nLine 3"
        );

        $this->assertStringContainsString('DESCRIPTION:Line 1\\nLine 2\\nLine 3', $ics);
    }

    public function test_ics_escapes_special_characters(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview, Technical',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com',
            'Room A; Building B'
        );

        $this->assertStringContainsString('SUMMARY:Interview\\, Technical', $ics);
        $this->assertStringContainsString('LOCATION:Room A\\; Building B', $ics);
    }

    public function test_ics_contains_valarm_reminder(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com'
        );

        $this->assertStringContainsString('BEGIN:VALARM', $ics);
        $this->assertStringContainsString('TRIGGER:-PT15M', $ics);
        $this->assertStringContainsString('ACTION:DISPLAY', $ics);
        $this->assertStringContainsString('END:VALARM', $ics);
    }

    public function test_ics_contains_uid(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com'
        );

        $this->assertMatchesRegularExpression('/UID:[a-f0-9-]+/', $ics);
    }

    public function test_ics_contains_prodid(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com'
        );

        $this->assertStringContainsString('PRODID:-//Andalan//Karir//ID', $ics);
    }

    public function test_ics_contains_status_and_sequence(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com'
        );

        $this->assertStringContainsString('STATUS:CONFIRMED', $ics);
        $this->assertStringContainsString('SEQUENCE:0', $ics);
    }
}
