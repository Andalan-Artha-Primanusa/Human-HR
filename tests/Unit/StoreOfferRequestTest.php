<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class StoreOfferRequestTest extends TestCase
{
    public function test_gross_salary_is_required(): void
    {
        $validator = Validator::make(
            [],
            ['gross_salary' => 'required|numeric|min:0']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_gross_salary_must_be_numeric(): void
    {
        $validator = Validator::make(
            ['gross_salary' => 'not-a-number'],
            ['gross_salary' => 'required|numeric|min:0']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_gross_salary_must_not_be_negative(): void
    {
        $validator = Validator::make(
            ['gross_salary' => -100],
            ['gross_salary' => 'required|numeric|min:0']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_allowance_is_nullable(): void
    {
        $validator = Validator::make(
            ['gross_salary' => 5000000],
            ['allowance' => 'nullable|numeric|min:0']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_allowance_must_not_be_negative(): void
    {
        $validator = Validator::make(
            ['gross_salary' => 5000000, 'allowance' => -500],
            ['allowance' => 'nullable|numeric|min:0']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_notes_is_nullable(): void
    {
        $validator = Validator::make(
            ['gross_salary' => 5000000, 'notes' => 'Some notes'],
            ['notes' => 'nullable|string']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_html_is_nullable(): void
    {
        $validator = Validator::make(
            ['gross_salary' => 5000000, 'html' => '<p>Offer details</p>'],
            ['html' => 'nullable|string']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_valid_data_passes(): void
    {
        $validator = Validator::make(
            [
                'gross_salary' => 10000000,
                'allowance' => 2000000,
                'notes' => 'Good candidate',
                'html' => '<p>Offer letter content</p>',
            ],
            [
                'gross_salary' => 'required|numeric|min:0',
                'allowance' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'html' => 'nullable|string',
            ]
        );

        $this->assertTrue($validator->passes());
    }
}
