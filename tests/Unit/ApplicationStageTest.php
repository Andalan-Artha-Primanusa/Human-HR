<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ApplicationStage;
use App\Models\User;

class ApplicationStageTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $stage = new ApplicationStage();
        $this->assertContains('application_id', $stage->getFillable());
        $this->assertContains('stage_key', $stage->getFillable());
        $this->assertContains('status', $stage->getFillable());
        $this->assertContains('score', $stage->getFillable());
        $this->assertContains('payload', $stage->getFillable());
        $this->assertContains('acted_by', $stage->getFillable());
        $this->assertContains('user_id', $stage->getFillable());
        $this->assertContains('notes', $stage->getFillable());
    }

    public function test_casts(): void
    {
        $stage = new ApplicationStage();
        $casts = $stage->getCasts();
        $this->assertEquals('decimal:2', $casts['score']);
        $this->assertEquals('array', $casts['payload']);
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $stage = new ApplicationStage();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($stage)
        );
    }

    public function test_has_application_relationship(): void
    {
        $stage = new ApplicationStage();
        $relation = $stage->application();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\JobApplication::class, get_class($relation->getRelated()));
    }

    public function test_has_actor_relationship(): void
    {
        $stage = new ApplicationStage();
        $relation = $stage->actor();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\User::class, get_class($relation->getRelated()));
    }

    public function test_has_user_relationship(): void
    {
        $stage = new ApplicationStage();
        $relation = $stage->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\User::class, get_class($relation->getRelated()));
    }

    public function test_has_feedbacks_relationship(): void
    {
        $stage = new ApplicationStage();
        $stage->stage_key = 'interview';
        $relation = $stage->feedbacks();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
    }

    public function test_get_actor_name_returns_system_unknown_when_no_actor(): void
    {
        $stage = new ApplicationStage();
        $this->assertEquals('Sistem/Unknown', $stage->actor_name);
    }

    public function test_get_actor_name_with_loaded_actor_relation(): void
    {
        $stage = new ApplicationStage();
        $user = new User();
        $user->name = 'John Doe';
        $stage->setRelation('actor', $user);
        $this->assertEquals('John Doe', $stage->actor_name);
    }

    public function test_get_actor_name_with_loaded_user_relation(): void
    {
        $stage = new ApplicationStage();
        $user = new User();
        $user->name = 'Jane Smith';
        $stage->setRelation('user', $user);
        $this->assertEquals('Jane Smith', $stage->actor_name);
    }

    public function test_get_actor_name_with_null_user_name(): void
    {
        $stage = new ApplicationStage();
        $user = new User();
        $user->name = null;
        $stage->setRelation('actor', $user);
        $this->assertEquals('Sistem/Unknown', $stage->actor_name);
    }
}
