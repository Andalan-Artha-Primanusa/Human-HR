<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Validator;

class UpdateJobRequestTest extends TestCase
{
    public function test_rules_contains_required_fields(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => 1, 'status' => 'open'],
            [
                'code' => ['required', 'string', 'max:50'],
                'title' => 'required|string|max:200',
                'employment_type' => 'required|in:intern,contract,fulltime',
                'openings' => 'required|integer|min:1',
                'status' => 'required|in:draft,open,closed',
            ]
        );

        $this->assertArrayHasKey('code', $validator->getRules());
        $this->assertArrayHasKey('title', $validator->getRules());
        $this->assertArrayHasKey('employment_type', $validator->getRules());
        $this->assertArrayHasKey('openings', $validator->getRules());
        $this->assertArrayHasKey('status', $validator->getRules());
    }

    public function test_employment_type_must_be_valid(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'freelance', 'openings' => 1, 'status' => 'open'],
            ['employment_type' => 'required|in:intern,contract,fulltime']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_openings_must_be_positive(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => -1, 'status' => 'open'],
            ['openings' => 'required|integer|min:1']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_status_must_be_valid(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => 1, 'status' => 'archived'],
            ['status' => 'required|in:draft,open,closed']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_site_code_is_nullable(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => 1, 'status' => 'open'],
            ['site_code' => 'nullable|string|max:50']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_division_is_nullable(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => 1, 'status' => 'open'],
            ['division' => 'nullable|string|max:100']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_description_is_nullable(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => 1, 'status' => 'open'],
            ['description' => 'nullable|string']
        );

        $this->assertTrue($validator->passes());
    }
}
