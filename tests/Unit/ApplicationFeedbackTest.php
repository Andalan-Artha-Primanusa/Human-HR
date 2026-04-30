<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ApplicationFeedback;

class ApplicationFeedbackTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $feedback = new ApplicationFeedback();
        $this->assertContains('application_id', $feedback->getFillable());
        $this->assertContains('stage_key', $feedback->getFillable());
        $this->assertContains('role', $feedback->getFillable());
        $this->assertContains('feedback', $feedback->getFillable());
        $this->assertContains('approve', $feedback->getFillable());
        $this->assertContains('user_id', $feedback->getFillable());
    }

    public function test_uses_correct_table_name(): void
    {
        $feedback = new ApplicationFeedback();
        $this->assertEquals('application_feedbacks', $feedback->getTable());
    }

    public function test_uses_has_uuids_trait(): void
    {
        $feedback = new ApplicationFeedback();
        $this->assertContains(
            \Illuminate\Database\Eloquent\Concerns\HasUuids::class,
            class_uses_recursive($feedback)
        );
    }

    public function test_has_user_relationship(): void
    {
        $feedback = new ApplicationFeedback();
        $relation = $feedback->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\User::class, get_class($relation->getRelated()));
    }

    public function test_has_application_relationship(): void
    {
        $feedback = new ApplicationFeedback();
        $relation = $feedback->application();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\JobApplication::class, get_class($relation->getRelated()));
    }
}
