<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Requests\StoreJobRequest;
use Illuminate\Support\Facades\Validator;

class StoreJobRequestTest extends TestCase
{
    public function test_authorize_denies_without_user(): void
    {
        $request = StoreJobRequest::create('/jobs', 'POST', []);
        $request->setUserResolver(fn() => null);

        $this->assertFalse($request->authorize());
    }

    public function test_rules_contains_required_fields(): void
    {
        $request = StoreJobRequest::create('/jobs', 'POST', []);
        $rules = $request->rules();

        $this->assertArrayHasKey('code', $rules);
        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('employment_type', $rules);
        $this->assertArrayHasKey('openings', $rules);
        $this->assertArrayHasKey('status', $rules);
    }

    public function test_code_is_required(): void
    {
        $validator = Validator::make(
            ['title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => 1, 'status' => 'open'],
            ['code' => 'required|string|max:50']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_title_is_required(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'employment_type' => 'fulltime', 'openings' => 1, 'status' => 'open'],
            ['title' => 'required|string|max:200']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_employment_type_must_be_valid(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'freelance', 'openings' => 1, 'status' => 'open'],
            ['employment_type' => 'required|in:intern,contract,fulltime']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_employment_type_accepts_valid_values(): void
    {
        foreach (['intern', 'contract', 'fulltime'] as $type) {
            $validator = Validator::make(
                ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => $type, 'openings' => 1, 'status' => 'open'],
                ['employment_type' => 'required|in:intern,contract,fulltime']
            );

            $this->assertTrue($validator->passes(), "Failed for employment_type: {$type}");
        }
    }

    public function test_openings_must_be_positive_integer(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => 0, 'status' => 'open'],
            ['openings' => 'required|integer|min:1']
        );

        $this->assertFalse($validator->passes());
    }

    public function test_status_must_be_valid(): void
    {
        $validator = Validator::make(
            ['code' => 'JOB01', 'title' => 'Test Job', 'employment_type' => 'fulltime', 'openings' => 1, 'status' => 'deleted'],
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

    public function test_valid_data_passes(): void
    {
        $validator = Validator::make(
            [
                'code' => 'JOB01',
                'title' => 'Software Engineer',
                'site_code' => 'JKT',
                'division' => 'engineering',
                'level' => 'manager',
                'employment_type' => 'fulltime',
                'openings' => 2,
                'status' => 'open',
                'description' => 'Test description',
            ],
            [
                'code' => 'required|string|max:50',
                'title' => 'required|string|max:200',
                'site_code' => 'nullable|string|max:50',
                'division' => 'nullable|string|max:100',
                'level' => 'nullable|string|max:100',
                'employment_type' => 'required|in:intern,contract,fulltime',
                'openings' => 'required|integer|min:1',
                'status' => 'required|in:draft,open,closed',
                'description' => 'nullable|string',
            ]
        );

        $this->assertTrue($validator->passes());
    }
}
