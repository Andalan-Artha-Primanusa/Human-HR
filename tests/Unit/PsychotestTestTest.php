<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PsychotestTest;

class PsychotestTestTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $test = new PsychotestTest();
        $this->assertContains('name', $test->getFillable());
        $this->assertContains('duration_minutes', $test->getFillable());
        $this->assertContains('scoring', $test->getFillable());
    }

    public function test_casts(): void
    {
        $test = new PsychotestTest();
        $casts = $test->getCasts();
        $this->assertEquals('integer', $casts['duration_minutes']);
        $this->assertEquals('array', $casts['scoring']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $test = new PsychotestTest();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($test)
        );
    }

    public function test_has_questions_relationship(): void
    {
        $test = new PsychotestTest();
        $relation = $test->questions();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\PsychotestQuestion::class, get_class($relation->getRelated()));
    }
}
