<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Poh;

class PohTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $poh = new Poh();
        $this->assertContains('name', $poh->getFillable());
        $this->assertContains('code', $poh->getFillable());
        $this->assertContains('address', $poh->getFillable());
        $this->assertContains('description', $poh->getFillable());
        $this->assertContains('is_active', $poh->getFillable());
    }

    public function test_casts(): void
    {
        $poh = new Poh();
        $casts = $poh->getCasts();
        $this->assertEquals('boolean', $casts['is_active']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $poh = new Poh();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($poh)
        );
    }
}
