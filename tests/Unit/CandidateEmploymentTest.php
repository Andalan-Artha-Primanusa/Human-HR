<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CandidateEmployment;
use Carbon\Carbon;

class CandidateEmploymentTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $employment = new CandidateEmployment();
        $this->assertContains('candidate_profile_id', $employment->getFillable());
        $this->assertContains('company', $employment->getFillable());
        $this->assertContains('position_start', $employment->getFillable());
        $this->assertContains('position_end', $employment->getFillable());
        $this->assertContains('period_start', $employment->getFillable());
        $this->assertContains('period_end', $employment->getFillable());
        $this->assertContains('reason_for_leaving', $employment->getFillable());
        $this->assertContains('job_description', $employment->getFillable());
        $this->assertContains('order_no', $employment->getFillable());
    }

    public function test_casts(): void
    {
        $employment = new CandidateEmployment();
        $casts = $employment->getCasts();
        $this->assertEquals('date', $casts['period_start']);
        $this->assertEquals('date', $casts['period_end']);
    }

    public function test_uses_uuid_primary_key(): void
    {
        $employment = new CandidateEmployment();
        $this->assertFalse($employment->incrementing);
        $this->assertEquals('string', $employment->getKeyType());
    }

    public function test_uses_has_uuids_trait(): void
    {
        $employment = new CandidateEmployment();
        $this->assertContains(
            \Illuminate\Database\Eloquent\Concerns\HasUuids::class,
            class_uses_recursive($employment)
        );
    }

    public function test_has_profile_relationship(): void
    {
        $employment = new CandidateEmployment();
        $relation = $employment->profile();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\CandidateProfile::class, get_class($relation->getRelated()));
    }

    public function test_get_period_label_with_both_dates(): void
    {
        $employment = new CandidateEmployment();
        $employment->period_start = Carbon::create(2020, 1, 15);
        $employment->period_end = Carbon::create(2023, 6, 30);

        $this->assertEquals('Jan 2020 - Jun 2023', $employment->period_label);
    }

    public function test_get_period_label_without_end_date(): void
    {
        $employment = new CandidateEmployment();
        $employment->period_start = Carbon::create(2020, 1, 15);
        $employment->period_end = null;

        $this->assertEquals('Jan 2020 - Sekarang', $employment->period_label);
    }

    public function test_get_period_label_with_null_start_date(): void
    {
        $employment = new CandidateEmployment();
        $employment->period_start = null;
        $employment->period_end = Carbon::create(2023, 6, 30);

        $this->assertEquals('- - Jun 2023', $employment->period_label);
    }
}
