<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\AuditLog;

class AuditLogTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $log = new AuditLog();
        $this->assertContains('user_id', $log->getFillable());
        $this->assertContains('event', $log->getFillable());
        $this->assertContains('target_type', $log->getFillable());
        $this->assertContains('target_id', $log->getFillable());
        $this->assertContains('ip', $log->getFillable());
        $this->assertContains('user_agent', $log->getFillable());
        $this->assertContains('before', $log->getFillable());
        $this->assertContains('after', $log->getFillable());
    }

    public function test_uses_correct_table_name(): void
    {
        $log = new AuditLog();
        $this->assertEquals('audit_logs', $log->getTable());
    }

    public function test_casts(): void
    {
        $log = new AuditLog();
        $casts = $log->getCasts();
        $this->assertEquals('array', $casts['before']);
        $this->assertEquals('array', $casts['after']);
    }

    public function test_uses_has_uuids_trait(): void
    {
        $log = new AuditLog();
        $this->assertContains(
            \Illuminate\Database\Eloquent\Concerns\HasUuids::class,
            class_uses_recursive($log)
        );
    }

    public function test_has_user_relationship(): void
    {
        $log = new AuditLog();
        $relation = $log->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\User::class, get_class($relation->getRelated()));
    }
}
