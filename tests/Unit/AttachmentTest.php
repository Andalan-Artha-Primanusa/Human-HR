<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Attachment;

class AttachmentTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $attachment = new Attachment();
        $this->assertContains('label', $attachment->getFillable());
        $this->assertContains('path', $attachment->getFillable());
        $this->assertContains('mime', $attachment->getFillable());
        $this->assertContains('size_bytes', $attachment->getFillable());
    }

    public function test_uses_has_uuid_primary_key_trait(): void
    {
        $attachment = new Attachment();
        $this->assertContains(
            \App\Models\Concerns\HasUuidPrimaryKey::class,
            class_uses_recursive($attachment)
        );
    }

    public function test_has_attachable_morph_to_relationship(): void
    {
        $attachment = new Attachment();
        $relation = $attachment->attachable();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class, $relation);
    }
}
