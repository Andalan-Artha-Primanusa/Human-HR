<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ManpowerRequirement;

class ManpowerRequirementTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $manpower = new ManpowerRequirement();
        $this->assertContains('job_id', $manpower->getFillable());
        $this->assertContains('asset_name', $manpower->getFillable());
        $this->assertContains('assets_count', $manpower->getFillable());
        $this->assertContains('ratio_per_asset', $manpower->getFillable());
        $this->assertContains('budget_headcount', $manpower->getFillable());
        $this->assertContains('filled_headcount', $manpower->getFillable());
    }

    public function test_casts(): void
    {
        $manpower = new ManpowerRequirement();
        $casts = $manpower->getCasts();
        $this->assertEquals('integer', $casts['assets_count']);
        $this->assertEquals('float', $casts['ratio_per_asset']);
        $this->assertEquals('integer', $casts['budget_headcount']);
        $this->assertEquals('integer', $casts['filled_headcount']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $manpower = new ManpowerRequirement();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($manpower)
        );
    }

    public function test_has_job_relationship(): void
    {
        $manpower = new ManpowerRequirement();
        $relation = $manpower->job();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\Job::class, get_class($relation->getRelated()));
    }

    public function test_get_computed_budget_with_assets_and_ratio(): void
    {
        $manpower = new ManpowerRequirement();
        $manpower->assets_count = 5;
        $manpower->ratio_per_asset = 2.5;

        $this->assertEquals(13, $manpower->computed_budget);
    }

    public function test_get_computed_budget_with_zero_assets(): void
    {
        $manpower = new ManpowerRequirement();
        $manpower->assets_count = 0;
        $manpower->ratio_per_asset = 2.5;

        $this->assertEquals(0, $manpower->computed_budget);
    }

    public function test_get_computed_budget_with_null_values(): void
    {
        $manpower = new ManpowerRequirement();
        $manpower->assets_count = null;
        $manpower->ratio_per_asset = null;

        $this->assertEquals(0, $manpower->computed_budget);
    }

    public function test_get_computed_budget_with_negative_ratio(): void
    {
        $manpower = new ManpowerRequirement();
        $manpower->assets_count = 5;
        $manpower->ratio_per_asset = -1.0;

        $this->assertEquals(0, $manpower->computed_budget);
    }

    public function test_get_computed_budget_rounds_up(): void
    {
        $manpower = new ManpowerRequirement();
        $manpower->assets_count = 3;
        $manpower->ratio_per_asset = 1.1;

        $this->assertEquals(4, $manpower->computed_budget);
    }
}
