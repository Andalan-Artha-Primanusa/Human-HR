<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class MoveStageRequestTest extends TestCase
{
    public function test_to_stage_is_required(): void
    {
        $validator = Validator::make(
            [],
            ['to_stage' => 'required|in:applied,psychotest,hr_iv,user_iv,final,offer,hired,not_qualified']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_to_stage_must_be_valid_value(): void
    {
        $validator = Validator::make(
            ['to_stage' => 'invalid_stage'],
            ['to_stage' => 'required|in:applied,psychotest,hr_iv,user_iv,final,offer,hired,not_qualified']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_to_stage_accepts_all_valid_values(): void
    {
        $stages = ['applied', 'psychotest', 'hr_iv', 'user_iv', 'final', 'offer', 'hired', 'not_qualified'];

        foreach ($stages as $stage) {
            $validator = Validator::make(
                ['to_stage' => $stage],
                ['to_stage' => 'required|in:applied,psychotest,hr_iv,user_iv,final,offer,hired,not_qualified']
            );

            $this->assertTrue($validator->passes(), "Failed for stage: {$stage}");
        }
    }

    public function test_status_is_nullable(): void
    {
        $validator = Validator::make(
            ['to_stage' => 'applied'],
            ['status' => 'nullable|in:pending,passed,failed,no-show,reschedule']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_status_must_be_valid(): void
    {
        $validator = Validator::make(
            ['to_stage' => 'applied', 'status' => 'invalid'],
            ['status' => 'nullable|in:pending,passed,failed,no-show,reschedule']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_note_is_nullable(): void
    {
        $validator = Validator::make(
            ['to_stage' => 'applied', 'note' => 'Some notes'],
            ['note' => 'nullable|string']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_score_is_nullable_numeric(): void
    {
        $validator = Validator::make(
            ['to_stage' => 'applied', 'score' => 'abc'],
            ['score' => 'nullable|numeric']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_valid_data_passes(): void
    {
        $validator = Validator::make(
            [
                'to_stage' => 'hr_iv',
                'status' => 'passed',
                'note' => 'Good candidate',
                'score' => 85.5,
            ],
            [
                'to_stage' => 'required|in:applied,psychotest,hr_iv,user_iv,final,offer,hired,not_qualified',
                'status' => 'nullable|in:pending,passed,failed,no-show,reschedule',
                'note' => 'nullable|string',
                'score' => 'nullable|numeric',
            ]
        );

        $this->assertTrue($validator->passes());
    }
}
