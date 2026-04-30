<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Site;

class SiteTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $site = new Site();
        $this->assertContains('code', $site->getFillable());
        $this->assertContains('name', $site->getFillable());
        $this->assertContains('region', $site->getFillable());
        $this->assertContains('timezone', $site->getFillable());
        $this->assertContains('address', $site->getFillable());
        $this->assertContains('is_active', $site->getFillable());
    }

    public function test_uses_uuid_as_primary_key(): void
    {
        $site = new Site();
        $this->assertFalse($site->incrementing);
        $this->assertEquals('string', $site->getKeyType());
    }

    public function test_casts(): void
    {
        $site = new Site();
        $casts = $site->getCasts();
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('array', $casts['meta']);
        $this->assertEquals('float', $casts['latitude']);
        $this->assertEquals('float', $casts['longitude']);
    }

    public function test_has_jobs_relationship(): void
    {
        $site = new Site();
        $relation = $site->jobs();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\Job::class, get_class($relation->getRelated()));
    }
}
