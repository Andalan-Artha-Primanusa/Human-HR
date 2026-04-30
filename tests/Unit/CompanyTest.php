<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Company;

class CompanyTest extends TestCase
{
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
}
