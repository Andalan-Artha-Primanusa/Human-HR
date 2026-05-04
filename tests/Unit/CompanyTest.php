<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Company;
use App\Models\Job;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $company = new Company();
        $this->assertContains('code', $company->getFillable());
        $this->assertContains('name', $company->getFillable());
        $this->assertContains('legal_name', $company->getFillable());
        $this->assertContains('email', $company->getFillable());
        $this->assertContains('status', $company->getFillable());
    }

    public function test_uses_soft_deletes(): void
    {
        $company = new Company();
        $this->assertContains(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive($company)
        );
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $company = new Company();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($company)
        );
    }

    public function test_has_jobs_relationship(): void
    {
        $company = new Company();
        $relation = $company->jobs();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\Job::class, get_class($relation->getRelated()));
    }

    public function test_meta_is_cast_to_array(): void
    {
        $company = new Company();
        $casts = $company->getCasts();
        $this->assertEquals('array', $casts['meta']);
    }

    public function test_can_create_company(): void
    {
        $company = Company::create([
            'code' => 'COMP-001',
            'name' => 'Test Company',
            'legal_name' => 'PT Test Company',
            'email' => 'info@testcompany.com',
            'phone' => '021-12345678',
            'website' => 'https://testcompany.com',
            'address' => 'Jakarta',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'country' => 'Indonesia',
            'status' => 'active',
        ]);

        $this->assertNotNull($company->id);
        $this->assertDatabaseHas('companies', ['name' => 'Test Company']);

        $retrieved = Company::find($company->id);
        $this->assertEquals('PT Test Company', $retrieved->legal_name);
        $this->assertEquals('active', $retrieved->status);
    }

    public function test_meta_attribute_stores_and_retrieves_array(): void
    {
        $company = Company::create([
            'code' => 'COMP-META',
            'name' => 'Meta Company',
            'meta' => ['industry' => 'Technology', 'founded' => 2020],
        ]);

        $retrieved = Company::find($company->id);
        $this->assertIsArray($retrieved->meta);
        $this->assertEquals('Technology', $retrieved->meta['industry']);
        $this->assertEquals(2020, $retrieved->meta['founded']);
    }

    public function test_search_scope_finds_by_name(): void
    {
        Company::create(['code' => 'AAA', 'name' => 'Alpha Company', 'status' => 'active']);
        Company::create(['code' => 'BBB', 'name' => 'Beta Corporation', 'status' => 'active']);

        $results = Company::search('Alpha')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Alpha Company', $results->first()->name);
    }

    public function test_search_scope_finds_by_code(): void
    {
        Company::create(['code' => 'SEARCH-001', 'name' => 'Search Company', 'status' => 'active']);
        Company::create(['code' => 'OTHER-002', 'name' => 'Other Company', 'status' => 'active']);

        $results = Company::search('SEARCH')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('SEARCH-001', $results->first()->code);
    }

    public function test_search_scope_finds_by_legal_name(): void
    {
        Company::create(['code' => 'LGL', 'name' => 'Legal Name Co', 'legal_name' => 'PT Legal Name Corp', 'status' => 'active']);
        Company::create(['code' => 'OTH', 'name' => 'Other Co', 'status' => 'active']);

        $results = Company::search('Legal Name')->get();

        $this->assertCount(1, $results);
    }

    public function test_search_scope_returns_all_when_term_empty(): void
    {
        Company::create(['code' => 'X1', 'name' => 'Company One', 'status' => 'active']);
        Company::create(['code' => 'X2', 'name' => 'Company Two', 'status' => 'active']);

        $results = Company::search('')->get();

        $this->assertGreaterThanOrEqual(2, $results->count());
    }

    public function test_active_scope_filters_by_status(): void
    {
        Company::create(['code' => 'ACT', 'name' => 'Active Company', 'status' => 'active']);
        Company::create(['code' => 'INA', 'name' => 'Inactive Company', 'status' => 'inactive']);

        $results = Company::active()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('active', $results->first()->status);
    }

    public function test_jobs_relationship_returns_correct_jobs(): void
    {
        $site = Site::factory()->create();
        $company = Company::create(['code' => 'JTEST', 'name' => 'Job Company', 'status' => 'active']);

        Job::create([
            'title' => 'Job 1',
            'slug' => 'job-1',
            'code' => 'J1',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'site_id' => $site->id,
            'company_id' => $company->id,
        ]);
        Job::create([
            'title' => 'Job 2',
            'slug' => 'job-2',
            'code' => 'J2',
            'description' => 'Test',
            'status' => 'open',
            'level' => 1,
            'site_id' => $site->id,
            'company_id' => $company->id,
        ]);

        $this->assertEquals(2, $company->jobs()->count());
    }

    public function test_soft_delete_marks_company_as_deleted(): void
    {
        $company = Company::create(['code' => 'DEL', 'name' => 'Delete Me', 'status' => 'active']);

        $this->assertDatabaseHas('companies', ['id' => $company->id]);

        $company->delete();

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
        $this->assertNull(Company::find($company->id));
    }

    public function test_trashed_company_not_returned_by_default_query(): void
    {
        $company = Company::create(['code' => 'TRASH', 'name' => 'Trashed Co', 'status' => 'active']);
        $company->delete();

        $results = Company::where('code', 'TRASH')->get();
        $this->assertCount(0, $results);
    }

    public function test_can_restore_soft_deleted_company(): void
    {
        $company = Company::create(['code' => 'REST', 'name' => 'Restore Me', 'status' => 'active']);
        $company->delete();

        $company->restore();

        $this->assertNotSoftDeleted('companies', ['id' => $company->id]);
    }
}
