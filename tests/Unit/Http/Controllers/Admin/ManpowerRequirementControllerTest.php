<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ManpowerRequirementController;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ManpowerRequirementControllerTest extends TestCase
{
    use RefreshDatabase;

    private ManpowerRequirementController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ManpowerRequirementController();
    }

    public function test_index_returns_view(): void
    {
        Job::factory()->count(3)->create();

        $request = Request::create('/admin/manpower', 'GET');
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_searches_by_code(): void
    {
        Job::factory()->create(['code' => 'JOB-001']);
        Job::factory()->create(['code' => 'JOB-002']);

        $request = Request::create('/admin/manpower?q=JOB-001', 'GET', ['q' => 'JOB-001']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_searches_by_title(): void
    {
        Job::factory()->create(['title' => 'Software Engineer']);
        Job::factory()->create(['title' => 'Data Analyst']);

        $request = Request::create('/admin/manpower?q=Software', 'GET', ['q' => 'Software']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_returns_json_when_wants_json(): void
    {
        Job::factory()->create();

        $request = Request::create('/admin/manpower', 'GET');
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_edit_returns_view(): void
    {
        $job = Job::factory()->create();

        $request = Request::create("/admin/manpower/{$job->id}/edit", 'GET');

        $response = $this->controller->edit($job);

        $this->assertNotNull($response);
    }

    public function test_edit_returns_json_when_wants_json(): void
    {
        $job = Job::factory()->create();

        request()->headers->set('Accept', 'application/json');

        $response = $this->controller->edit($job);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_preview_calculates_budget_headcount(): void
    {
        $request = Request::create('/admin/manpower/preview', 'POST', [
            'assets_count' => 10,
            'ratio_per_asset' => 2.5,
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->preview($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals(25, $data['budget_headcount']);
    }

    public function test_preview_rounds_up(): void
    {
        $request = Request::create('/admin/manpower/preview', 'POST', [
            'assets_count' => 3,
            'ratio_per_asset' => 1.3,
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->preview($request);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(4, $data['budget_headcount']);
    }

    public function test_preview_validates_required_fields(): void
    {
        $request = Request::create('/admin/manpower/preview', 'POST', []);

        try {
            $this->controller->preview($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('assets_count', $e->errors());
            $this->assertArrayHasKey('ratio_per_asset', $e->errors());
        }
    }

    public function test_update_validates_required_fields(): void
    {
        $job = Job::factory()->create();

        $request = Request::create("/admin/manpower/{$job->id}", 'POST', []);

        try {
            $this->controller->update($request, $job);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('assets_count', $e->errors());
            $this->assertArrayHasKey('ratio_per_asset', $e->errors());
        }
    }

    public function test_update_validates_assets_count_integer(): void
    {
        $job = Job::factory()->create();

        $request = Request::create("/admin/manpower/{$job->id}", 'POST', [
            'assets_count' => 'not-a-number',
            'ratio_per_asset' => 2,
        ]);

        try {
            $this->controller->update($request, $job);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('assets_count', $e->errors());
        }
    }

    public function test_update_validates_ratio_per_asset_numeric(): void
    {
        $job = Job::factory()->create();

        $request = Request::create("/admin/manpower/{$job->id}", 'POST', [
            'assets_count' => 5,
            'ratio_per_asset' => 'not-numeric',
        ]);

        try {
            $this->controller->update($request, $job);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('ratio_per_asset', $e->errors());
        }
    }

    public function test_preview_zero_assets_returns_zero(): void
    {
        $request = Request::create('/admin/manpower/preview', 'POST', [
            'assets_count' => 0,
            'ratio_per_asset' => 5,
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->preview($request);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(0, $data['budget_headcount']);
    }

    public function test_preview_large_numbers(): void
    {
        $request = Request::create('/admin/manpower/preview', 'POST', [
            'assets_count' => 1000000,
            'ratio_per_asset' => 3,
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->preview($request);

        $data = json_decode($response->getContent(), true);
        $this->assertEquals(3000000, $data['budget_headcount']);
    }
}
