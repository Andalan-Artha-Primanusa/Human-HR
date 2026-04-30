<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PsychotestQuestion;

class PsychotestQuestionTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $question = new PsychotestQuestion();
        $this->assertContains('test_id', $question->getFillable());
        $this->assertContains('type', $question->getFillable());
        $this->assertContains('question', $question->getFillable());
        $this->assertContains('options', $question->getFillable());
        $this->assertContains('answer_key', $question->getFillable());
        $this->assertContains('weight', $question->getFillable());
        $this->assertContains('order_no', $question->getFillable());
    }

    public function test_casts(): void
    {
        $question = new PsychotestQuestion();
        $casts = $question->getCasts();
        $this->assertEquals('array', $casts['options']);
        $this->assertEquals('decimal:2', $casts['weight']);
        $this->assertEquals('integer', $casts['order_no']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $question = new PsychotestQuestion();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($question)
        );
    }

    public function test_has_test_relationship(): void
    {
        $question = new PsychotestQuestion();
        $relation = $question->test();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\PsychotestTest::class, get_class($relation->getRelated()));
    }
}
