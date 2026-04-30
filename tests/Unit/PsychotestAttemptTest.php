<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PsychotestAttempt;

class PsychotestAttemptTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $attempt = new PsychotestAttempt();
        $this->assertContains('application_id', $attempt->getFillable());
        $this->assertContains('test_id', $attempt->getFillable());
        $this->assertContains('user_id', $attempt->getFillable());
        $this->assertContains('attempt_no', $attempt->getFillable());
        $this->assertContains('status', $attempt->getFillable());
        $this->assertContains('started_at', $attempt->getFillable());
        $this->assertContains('finished_at', $attempt->getFillable());
        $this->assertContains('submitted_at', $attempt->getFillable());
        $this->assertContains('expires_at', $attempt->getFillable());
        $this->assertContains('score', $attempt->getFillable());
        $this->assertContains('is_active', $attempt->getFillable());
        $this->assertContains('meta', $attempt->getFillable());
    }

    public function test_casts(): void
    {
        $attempt = new PsychotestAttempt();
        $casts = $attempt->getCasts();
        $this->assertEquals('integer', $casts['attempt_no']);
        $this->assertEquals('datetime', $casts['started_at']);
        $this->assertEquals('datetime', $casts['finished_at']);
        $this->assertEquals('datetime', $casts['submitted_at']);
        $this->assertEquals('datetime', $casts['expires_at']);
        $this->assertEquals('decimal:2', $casts['score']);
        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('array', $casts['meta']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $attempt = new PsychotestAttempt();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($attempt)
        );
    }

    public function test_has_application_relationship(): void
    {
        $attempt = new PsychotestAttempt();
        $relation = $attempt->application();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\JobApplication::class, get_class($relation->getRelated()));
    }

    public function test_has_test_relationship(): void
    {
        $attempt = new PsychotestAttempt();
        $relation = $attempt->test();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\PsychotestTest::class, get_class($relation->getRelated()));
    }

    public function test_has_user_relationship(): void
    {
        $attempt = new PsychotestAttempt();
        $relation = $attempt->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\User::class, get_class($relation->getRelated()));
    }

    public function test_has_answers_relationship(): void
    {
        $attempt = new PsychotestAttempt();
        $relation = $attempt->answers();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\PsychotestAnswer::class, get_class($relation->getRelated()));
    }
}
