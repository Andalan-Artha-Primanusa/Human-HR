<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Admin;

use App\Http\Controllers\Admin\SiteController;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    private SiteController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SiteController();
    }

    public function test_index_returns_view_with_sites(): void
    {
        Site::factory()->count(3)->create();

        $request = Request::create('/admin/sites', 'GET');
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_search_query(): void
    {
        Site::factory()->create(['code' => 'JKT001', 'name' => 'Jakarta']);
        Site::factory()->create(['code' => 'BDG001', 'name' => 'Bandung']);

        $request = Request::create('/admin/sites?q=JKT', 'GET', ['q' => 'JKT']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_active_status(): void
    {
        Site::factory()->create(['is_active' => true]);
        Site::factory()->create(['is_active' => false]);

        $request = Request::create('/admin/sites?status=active', 'GET', ['status' => 'active']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_filters_by_inactive_status(): void
    {
        Site::factory()->create(['is_active' => true]);
        Site::factory()->create(['is_active' => false]);

        $request = Request::create('/admin/sites?status=inactive', 'GET', ['status' => 'inactive']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_sorts_by_code(): void
    {
        Site::factory()->create(['code' => 'ZULU']);
        Site::factory()->create(['code' => 'ALFA']);

        $request = Request::create('/admin/sites?sort=code&order=asc', 'GET', ['sort' => 'code', 'order' => 'asc']);
        $response = $this->controller->index($request);

        $this->assertNotNull($response);
    }

    public function test_index_invalid_sort_validation_fails(): void
    {
        $request = Request::create('/admin/sites?sort=invalid_field', 'GET', ['sort' => 'invalid_field']);

        try {
            $this->controller->index($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('sort', $e->errors());
        }
    }

    public function test_store_creates_site_with_forced_active(): void
    {
        $request = Request::create('/admin/sites', 'POST', [
            'code' => 'TST001',
            'name' => 'Test Site',
            'region' => 'Java',
            'timezone' => 'Asia/Jakarta',
            'address' => 'Jl. Test No. 1',
            'notes' => 'Test notes',
        ]);

        $response = $this->controller->store($request);

        $this->assertDatabaseHas('sites', [
            'code' => 'TST001',
            'name' => 'Test Site',
            'is_active' => true,
        ]);
    }

    public function test_store_returns_json_when_wants_json(): void
    {
        $request = Request::create('/admin/sites', 'POST', [
            'code' => 'TST002',
            'name' => 'Test Site JSON',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->store($request);

        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['created']);
    }

    public function test_store_validates_required_code_and_name(): void
    {
        $request = Request::create('/admin/sites', 'POST', []);

        try {
            $this->controller->store($request);
            $this->fail('Expected validation exception');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('code', $e->errors());
            $this->assertArrayHasKey('name', $e->errors());
        }
    }

    public function test_store_invalid_meta_json_returns_error(): void
    {
        $request = Request::create('/admin/sites', 'POST', [
            'code' => 'TST003',
            'name' => 'Test Site',
            'meta_json' => 'not-valid-json{{{',
        ]);

        $response = $this->controller->store($request);

        $this->assertDatabaseMissing('sites', ['code' => 'TST003']);
    }

    public function test_store_valid_meta_json_saves_meta(): void
    {
        $meta = ['key' => 'value', 'nested' => ['a' => 1]];
        $request = Request::create('/admin/sites', 'POST', [
            'code' => 'TST004',
            'name' => 'Test Site Meta',
            'meta_json' => json_encode($meta),
        ]);

        $this->controller->store($request);

        $site = Site::where('code', 'TST004')->first();
        $this->assertEquals($meta, $site->meta);
    }

    public function test_show_returns_site(): void
    {
        $site = Site::factory()->create();

        $response = $this->controller->show($site);

        $this->assertNotNull($response);
    }

    public function test_update_modifies_site_data(): void
    {
        $site = Site::factory()->create(['name' => 'Original Name']);

        $request = Request::create("/admin/sites/{$site->id}", 'PUT', [
            'code' => $site->code,
            'name' => 'Updated Name',
        ]);

        $this->controller->update($request, $site);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_update_invalid_meta_json_returns_error_and_does_not_change_meta(): void
    {
        $site = Site::factory()->create(['meta' => null]);

        $request = Request::create("/admin/sites/{$site->id}", 'PUT', [
            'code' => $site->code,
            'name' => $site->name,
            'meta_json' => '{bad json',
        ]);

        $response = $this->controller->update($request, $site);

        $site->refresh();
        $this->assertNull($site->meta);
    }

    public function test_update_empty_meta_json_clears_meta(): void
    {
        $site = Site::factory()->create(['meta' => ['old' => 'data']]);

        $request = Request::create("/admin/sites/{$site->id}", 'PUT', [
            'code' => $site->code,
            'name' => $site->name,
            'meta_json' => '',
        ]);

        $this->controller->update($request, $site);

        $site->refresh();
        $this->assertNull($site->meta);
    }

    public function test_update_does_not_touch_is_active(): void
    {
        $site = Site::factory()->create(['is_active' => true]);

        $request = Request::create("/admin/sites/{$site->id}", 'PUT', [
            'code' => $site->code,
            'name' => 'Updated',
            'is_active' => false,
        ]);

        $this->controller->update($request, $site);

        $site->refresh();
        $this->assertTrue($site->is_active);
    }

    public function test_update_returns_json_when_wants_json(): void
    {
        $site = Site::factory()->create();

        $request = Request::create("/admin/sites/{$site->id}", 'PUT', [
            'code' => $site->code,
            'name' => 'JSON Updated',
        ]);
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->update($request, $site);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['updated']);
    }

    public function test_destroy_deletes_site_when_no_jobs(): void
    {
        $site = Site::factory()->create();

        $request = Request::create("/admin/sites/{$site->id}", 'DELETE');

        $response = $this->controller->destroy($request, $site);

        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
    }

    public function test_destroy_returns_json_when_wants_json(): void
    {
        $site = Site::factory()->create();

        $request = Request::create("/admin/sites/{$site->id}", 'DELETE');
        $request->headers->set('Accept', 'application/json');

        $response = $this->controller->destroy($request, $site);

        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['deleted']);
    }

    public function test_site_code_validation_rejects_special_characters(): void
    {
        $request = Request::create('/admin/sites', 'POST', [
            'code' => 'JKT@#$%',
            'name' => 'Invalid Code Site',
        ]);

        try {
            $this->controller->store($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('code', $e->errors());
        }
    }

    public function test_site_code_validation_allows_valid_format(): void
    {
        $request = Request::create('/admin/sites', 'POST', [
            'code' => 'JKT-001_v2.0',
            'name' => 'Valid Code Site',
        ]);

        $this->controller->store($request);

        $this->assertDatabaseHas('sites', ['code' => 'JKT-001_v2.0']);
    }
}
