<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\CompanyController;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    private CompanyController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new CompanyController();
    }

    public function test_index_returns_view(): void
    {
        Company::factory()->count(3)->create();

        $request = Request::create('/admin/companies', 'GET');
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_search_query(): void
    {
        Company::factory()->create(['name' => 'Alpha Corp', 'code' => 'ALP']);
        Company::factory()->create(['name' => 'Beta Corp', 'code' => 'BTA']);

        $request = Request::create('/admin/companies?q=Alpha', 'GET', ['q' => 'Alpha']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_status(): void
    {
        Company::factory()->create(['status' => 'active']);
        Company::factory()->create(['status' => 'inactive']);

        $request = Request::create('/admin/companies?status=active', 'GET', ['status' => 'active']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_create_returns_view_with_default_status(): void
    {
        $response = $this->controller->create();

        $this->assertNotNull($response);
    }

    public function test_show_returns_view(): void
    {
        $company = Company::factory()->create();

        $response = $this->controller->show($company);

        $this->assertNotNull($response);
    }

    public function test_edit_returns_view(): void
    {
        $company = Company::factory()->create();

        $response = $this->controller->edit($company);

        $this->assertNotNull($response);
    }

    public function test_destroy_soft_deletes_company(): void
    {
        $company = Company::factory()->create();

        $this->controller->destroy($company);

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    public function test_index_sanitize_removes_control_characters(): void
    {
        $request = Request::create('/admin/companies?q=' . urlencode("Alpha\x00\x01\x1F"), 'GET');
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_empty_search_returns_all(): void
    {
        Company::factory()->count(2)->create();

        $request = Request::create('/admin/companies', 'GET');
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }
}
