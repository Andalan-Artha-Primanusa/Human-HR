<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CandidateTraining;

class CandidateTrainingTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $training = new CandidateTraining();
        $this->assertContains('candidate_profile_id', $training->getFillable());
        $this->assertContains('title', $training->getFillable());
        $this->assertContains('institution', $training->getFillable());
        $this->assertContains('period_start', $training->getFillable());
        $this->assertContains('period_end', $training->getFillable());
        $this->assertContains('certificate_path', $training->getFillable());
        $this->assertContains('order_no', $training->getFillable());
    }

    public function test_casts(): void
    {
        $training = new CandidateTraining();
        $casts = $training->getCasts();
        $this->assertEquals('date', $casts['period_start']);
        $this->assertEquals('date', $casts['period_end']);
    }

    public function test_uses_uuid_primary_key(): void
    {
        $training = new CandidateTraining();
        $this->assertFalse($training->incrementing);
        $this->assertEquals('string', $training->getKeyType());
    }

    public function test_uses_has_uuids_trait(): void
    {
        $training = new CandidateTraining();
        $this->assertContains(
            \Illuminate\Database\Eloquent\Concerns\HasUuids::class,
            class_uses_recursive($training)
        );
    }

    public function test_has_profile_relationship(): void
    {
        $training = new CandidateTraining();
        $relation = $training->profile();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\CandidateProfile::class, get_class($relation->getRelated()));
    }
}
