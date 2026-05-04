<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CandidateProfile;
use App\Models\CandidateReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidateReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $reference = new CandidateReference();
        $this->assertContains('candidate_profile_id', $reference->getFillable());
        $this->assertContains('name', $reference->getFillable());
        $this->assertContains('job_title', $reference->getFillable());
        $this->assertContains('company', $reference->getFillable());
        $this->assertContains('contact', $reference->getFillable());
        $this->assertContains('order_no', $reference->getFillable());
    }

    public function test_uses_uuid_primary_key(): void
    {
        $reference = new CandidateReference();
        $this->assertFalse($reference->incrementing);
        $this->assertEquals('string', $reference->getKeyType());
    }

    public function test_uses_has_uuids_trait(): void
    {
        $reference = new CandidateReference();
        $this->assertContains(
            \Illuminate\Database\Eloquent\Concerns\HasUuids::class,
            class_uses_recursive($reference)
        );
    }

    public function test_has_profile_relationship(): void
    {
        $reference = new CandidateReference();
        $relation = $reference->profile();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\CandidateProfile::class, get_class($relation->getRelated()));
    }

    public function test_ordered_scope_sorts_by_order_no(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        CandidateReference::create(['candidate_profile_id' => $profile->id, 'name' => 'Third', 'job_title' => 'Job', 'company' => 'Co', 'contact' => 'C', 'order_no' => 3]);
        CandidateReference::create(['candidate_profile_id' => $profile->id, 'name' => 'First', 'job_title' => 'Job', 'company' => 'Co', 'contact' => 'C', 'order_no' => 1]);
        CandidateReference::create(['candidate_profile_id' => $profile->id, 'name' => 'Second', 'job_title' => 'Job', 'company' => 'Co', 'contact' => 'C', 'order_no' => 2]);

        $ordered = CandidateReference::ordered()->get();

        $this->assertEquals('First', $ordered->first()->name);
        $this->assertEquals('Third', $ordered->last()->name);
    }

    public function test_can_create_and_retrieve(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        $reference = CandidateReference::create([
            'candidate_profile_id' => $profile->id,
            'name' => 'John Doe',
            'job_title' => 'Manager',
            'company' => 'Test Corp',
            'contact' => 'john@example.com',
            'order_no' => 1,
        ]);

        $this->assertNotNull($reference->id);
        $this->assertDatabaseHas('candidate_references', ['name' => 'John Doe']);

        $retrieved = CandidateReference::find($reference->id);
        $this->assertEquals('John Doe', $retrieved->name);
        $this->assertEquals('Manager', $retrieved->job_title);
    }

    public function test_profile_relationship_returns_correct_profile(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        $reference = CandidateReference::create([
            'candidate_profile_id' => $profile->id,
            'name' => 'Jane',
            'job_title' => 'Job',
            'company' => 'Co',
            'contact' => 'C',
        ]);

        $this->assertEquals($profile->id, $reference->profile->id);
    }
}
