<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class ScheduleInterviewRequestTest extends TestCase
{
    public function test_rules_contains_required_fields(): void
    {
        $request = \App\Http\Requests\ScheduleInterviewRequest::create('/', 'POST', []);
        $rules = $request->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('mode', $rules);
        $this->assertArrayHasKey('start_at', $rules);
        $this->assertArrayHasKey('end_at', $rules);
    }

    public function test_title_is_required(): void
    {
        $validator = Validator::make(
            ['mode' => 'online', 'start_at' => '2026-05-15 10:00', 'end_at' => '2026-05-15 11:00'],
            ['title' => 'required|string|max:200']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_mode_must_be_online_or_onsite(): void
    {
        $validator = Validator::make(
            ['title' => 'Interview', 'mode' => 'phone', 'start_at' => '2026-05-15 10:00', 'end_at' => '2026-05-15 11:00'],
            ['mode' => 'required|in:online,onsite']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_mode_accepts_valid_values(): void
    {
        foreach (['online', 'onsite'] as $mode) {
            $validator = Validator::make(
                ['title' => 'Interview', 'mode' => $mode, 'start_at' => '2026-05-15 10:00', 'end_at' => '2026-05-15 11:00'],
                ['mode' => 'required|in:online,onsite']
            );

            $this->assertTrue($validator->passes(), "Failed for mode: {$mode}");
        }
    }

    public function test_meeting_link_must_be_valid_url(): void
    {
        $validator = Validator::make(
            ['meeting_link' => 'not-a-url'],
            ['meeting_link' => 'nullable|url|max:255']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_end_at_must_be_after_start_at(): void
    {
        $validator = Validator::make(
            [
                'title' => 'Interview',
                'mode' => 'online',
                'start_at' => '2026-05-15 11:00',
                'end_at' => '2026-05-15 10:00',
            ],
            [
                'start_at' => 'required|date',
                'end_at' => 'required|date|after:start_at',
            ]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_panel_items_require_name_and_email(): void
    {
        $validator = Validator::make(
            [
                'title' => 'Interview',
                'mode' => 'online',
                'start_at' => '2026-05-15 10:00',
                'end_at' => '2026-05-15 11:00',
                'panel' => [
                    ['name' => 'John', 'email' => 'john@test.com'],
                    ['name' => '', 'email' => 'invalid'],
                ],
            ],
            [
                'title' => 'required|string|max:200',
                'mode' => 'required|in:online,onsite',
                'start_at' => 'required|date',
                'end_at' => 'required|date|after:start_at',
                'panel' => 'nullable|array',
                'panel.*.name' => 'required_with:panel|string|max:120',
                'panel.*.email' => 'required_with:panel|email',
            ]
        );

        $this->assertFalse($validator->passes());
    }

    public function test_valid_data_passes(): void
    {
        $validator = Validator::make(
            [
                'title' => 'Technical Interview',
                'mode' => 'online',
                'location' => 'Jakarta Office',
                'meeting_link' => 'https://zoom.us/j/123',
                'start_at' => '2026-05-15 10:00',
                'end_at' => '2026-05-15 11:00',
                'panel' => [
                    ['name' => 'John Doe', 'email' => 'john@test.com'],
                ],
                'notes' => 'Bring portfolio',
            ],
            [
                'title' => 'required|string|max:200',
                'mode' => 'required|in:online,onsite',
                'location' => 'nullable|string|max:255',
                'meeting_link' => 'nullable|url|max:255',
                'start_at' => 'required|date',
                'end_at' => 'required|date|after:start_at',
                'panel' => 'nullable|array',
                'panel.*.name' => 'required_with:panel|string|max:120',
                'panel.*.email' => 'required_with:panel|email',
                'notes' => 'nullable|string',
            ]
        );

        $this->assertTrue($validator->passes());
    }
}
