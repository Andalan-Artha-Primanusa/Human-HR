<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\JobApplication;

class JobApplicationTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $application = new JobApplication();
        $this->assertContains('job_id', $application->getFillable());
        $this->assertContains('user_id', $application->getFillable());
        $this->assertContains('poh_id', $application->getFillable());
        $this->assertContains('current_stage', $application->getFillable());
        $this->assertContains('overall_status', $application->getFillable());
        $this->assertContains('feedback_hr', $application->getFillable());
        $this->assertContains('approve_hr', $application->getFillable());
        $this->assertContains('mcu_meta', $application->getFillable());
    }

    public function test_mcu_meta_is_cast_to_array(): void
    {
        $application = new JobApplication();
        $casts = $application->getCasts();
        $this->assertEquals('array', $casts['mcu_meta']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $application = new JobApplication();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($application)
        );
    }

    public function test_has_job_relationship(): void
    {
        $application = new JobApplication();
        $relation = $application->job();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\Job::class, get_class($relation->getRelated()));
    }

    public function test_has_user_relationship(): void
    {
        $application = new JobApplication();
        $relation = $application->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\User::class, get_class($relation->getRelated()));
    }

    public function test_has_poh_relationship(): void
    {
        $application = new JobApplication();
        $relation = $application->poh();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\Poh::class, get_class($relation->getRelated()));
    }

    public function test_has_stages_relationship(): void
    {
        $application = new JobApplication();
        $relation = $application->stages();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\ApplicationStage::class, get_class($relation->getRelated()));
    }

    public function test_has_interviews_relationship(): void
    {
        $application = new JobApplication();
        $relation = $application->interviews();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\Interview::class, get_class($relation->getRelated()));
    }

    public function test_has_psychotest_attempts_relationship(): void
    {
        $application = new JobApplication();
        $relation = $application->psychotestAttempts();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\PsychotestAttempt::class, get_class($relation->getRelated()));
    }

    public function test_has_offer_relationship(): void
    {
        $application = new JobApplication();
        $relation = $application->offer();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $relation);
        $this->assertEquals(\App\Models\Offer::class, get_class($relation->getRelated()));
    }

    public function test_has_feedbacks_relationship(): void
    {
        $application = new JobApplication();
        $relation = $application->feedbacks();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\ApplicationFeedback::class, get_class($relation->getRelated()));
    }
}
