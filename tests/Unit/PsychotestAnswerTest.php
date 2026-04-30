<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PsychotestAnswer;

class PsychotestAnswerTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $answer = new PsychotestAnswer();
        $this->assertContains('attempt_id', $answer->getFillable());
        $this->assertContains('question_id', $answer->getFillable());
        $this->assertContains('answer', $answer->getFillable());
        $this->assertContains('is_correct', $answer->getFillable());
    }

    public function test_casts(): void
    {
        $answer = new PsychotestAnswer();
        $casts = $answer->getCasts();
        $this->assertEquals('boolean', $casts['is_correct']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $answer = new PsychotestAnswer();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($answer)
        );
    }

    public function test_has_attempt_relationship(): void
    {
        $answer = new PsychotestAnswer();
        $relation = $answer->attempt();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\PsychotestAttempt::class, get_class($relation->getRelated()));
    }

    public function test_has_question_relationship(): void
    {
        $answer = new PsychotestAnswer();
        $relation = $answer->question();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\PsychotestQuestion::class, get_class($relation->getRelated()));
    }
}
