<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Offer;

class OfferTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $offer = new Offer();
        $this->assertContains('application_id', $offer->getFillable());
        $this->assertContains('status', $offer->getFillable());
        $this->assertContains('salary', $offer->getFillable());
        $this->assertContains('body_template', $offer->getFillable());
        $this->assertContains('signed_path', $offer->getFillable());
        $this->assertContains('meta', $offer->getFillable());
    }

    public function test_casts(): void
    {
        $offer = new Offer();
        $casts = $offer->getCasts();
        $this->assertEquals('array', $casts['salary']);
        $this->assertEquals('array', $casts['meta']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $offer = new Offer();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($offer)
        );
    }

    public function test_has_application_relationship(): void
    {
        $offer = new Offer();
        $relation = $offer->application();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\JobApplication::class, get_class($relation->getRelated()));
    }
}
