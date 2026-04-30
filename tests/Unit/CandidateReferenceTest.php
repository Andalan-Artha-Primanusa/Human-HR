<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CandidateReference;

class CandidateReferenceTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $reference = new CandidateReference();
        $this->assertContains('candidate_profile_id', $reference->getFillable());
        $this->assertContains('name', $reference->getFillable());
        $this->assertContains('job_title', $reference->getFillable());
        $this->assertContains('company', $reference->getFillable());
        $this->assertContains('contact', $reference->getFillable());
        $this->assertContains('order_no', $reference->getFillable());
    }

    public function test_uses_uuid_primary_key(): void
    {
        $reference = new CandidateReference();
        $this->assertFalse($reference->incrementing);
        $this->assertEquals('string', $reference->getKeyType());
    }

    public function test_uses_has_uuids_trait(): void
    {
        $reference = new CandidateReference();
        $this->assertContains(
            \Illuminate\Database\Eloquent\Concerns\HasUuids::class,
            class_uses_recursive($reference)
        );
    }

    public function test_has_profile_relationship(): void
    {
        $reference = new CandidateReference();
        $relation = $reference->profile();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\CandidateProfile::class, get_class($relation->getRelated()));
    }
}
