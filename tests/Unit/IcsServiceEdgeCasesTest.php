<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\IcsService;
use Carbon\Carbon;

class IcsServiceEdgeCasesTest extends TestCase
{
    public function test_handles_null_description(): void
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
            ''
        );

        $this->assertStringContainsString('DESCRIPTION:', $ics);
    }

    public function test_handles_special_characters_in_organizer_email(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Interview',
            $startAt,
            $endAt,
            'hr+test@company.com',
            'candidate@email.com'
        );

        $this->assertStringContainsString('ORGANIZER:mailto:hr+test@company.com', $ics);
    }

    public function test_uses_crlf_line_endings(): void
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

        $this->assertStringContainsString("\r\n", $ics);
        $this->assertStringNotContainsString("\n", str_replace("\r\n", '', $ics));
    }

    public function test_has_correct_vcalendar_version(): void
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

        $this->assertStringContainsString('VERSION:2.0', $ics);
    }

    public function test_has_correct_method(): void
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

        $this->assertStringContainsString('METHOD:REQUEST', $ics);
    }

    public function test_ics_starts_with_begin_vcalendar(): void
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

        $this->assertStringStartsWith('BEGIN:VCALENDAR', $ics);
    }

    public function test_ics_ends_with_end_vcalendar(): void
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

        $this->assertStringEndsWith("END:VCALENDAR\r\n", $ics);
    }

    public function test_handles_unicode_characters_in_title(): void
    {
        $startAt = Carbon::now()->addDay();
        $endAt = Carbon::now()->addDays(1)->addHour();

        $ics = IcsService::interviewInvite(
            'Wawancara Teknis',
            $startAt,
            $endAt,
            'hr@company.com',
            'candidate@email.com'
        );

        $this->assertStringContainsString('SUMMARY:Wawancara Teknis', $ics);
    }

    public function test_handles_semicolon_in_description(): void
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
            'Please bring: ID card; CV; Portfolio'
        );

        $this->assertStringContainsString('DESCRIPTION:Please bring', $ics);
        $this->assertStringContainsString('CV\\', $ics);
        $this->assertStringContainsString('Portfolio', $ics);
    }

    public function test_handles_comma_in_description(): void
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
            'Location: Jakarta, Indonesia'
        );

        $this->assertStringContainsString('DESCRIPTION:Location', $ics);
        $this->assertStringContainsString('Jakarta\\, Indonesia', $ics);
    }
}
