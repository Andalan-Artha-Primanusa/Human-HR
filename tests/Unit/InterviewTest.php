<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Interview;

class InterviewTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $interview = new Interview();
        $this->assertContains('application_id', $interview->getFillable());
        $this->assertContains('title', $interview->getFillable());
        $this->assertContains('mode', $interview->getFillable());
        $this->assertContains('location', $interview->getFillable());
        $this->assertContains('meeting_link', $interview->getFillable());
        $this->assertContains('start_at', $interview->getFillable());
        $this->assertContains('end_at', $interview->getFillable());
        $this->assertContains('panel', $interview->getFillable());
        $this->assertContains('notes', $interview->getFillable());
    }

    public function test_casts(): void
    {
        $interview = new Interview();
        $casts = $interview->getCasts();
        $this->assertEquals('datetime', $casts['start_at']);
        $this->assertEquals('datetime', $casts['end_at']);
        $this->assertEquals('array', $casts['panel']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $interview = new Interview();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($interview)
        );
    }

    public function test_has_application_relationship(): void
    {
        $interview = new Interview();
        $relation = $interview->application();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\JobApplication::class, get_class($relation->getRelated()));
    }
}
