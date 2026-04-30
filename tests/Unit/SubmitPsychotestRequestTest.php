<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class SubmitPsychotestRequestTest extends TestCase
{
    public function test_answers_is_required(): void
    {
        $validator = Validator::make(
            [],
            ['answers' => 'required|array']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_answers_must_be_array(): void
    {
        $validator = Validator::make(
            ['answers' => 'not-an-array'],
            ['answers' => 'required|array']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_valid_answers_passes(): void
    {
        $validator = Validator::make(
            ['answers' => ['q1' => 'a', 'q2' => 'b', 'q3' => 'c']],
            ['answers' => 'required|array']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_empty_answers_array_fails(): void
    {
        $validator = Validator::make(
            ['answers' => []],
            ['answers' => 'required|array']
        );

        $this->assertFalse($validator->passes());
    }
}
