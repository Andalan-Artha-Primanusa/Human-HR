<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CandidateProfile;

class CandidateProfileTest extends TestCase
{
    public function test_fillable_attributes(): void
    {
        $profile = new CandidateProfile();
        $this->assertContains('user_id', $profile->getFillable());
        $this->assertContains('full_name', $profile->getFillable());
        $this->assertContains('gender', $profile->getFillable());
        $this->assertContains('nik', $profile->getFillable());
        $this->assertContains('phone', $profile->getFillable());
        $this->assertContains('last_education', $profile->getFillable());
        $this->assertContains('expected_salary', $profile->getFillable());
        $this->assertContains('work_motivation', $profile->getFillable());
    }

    public function test_casts(): void
    {
        $profile = new CandidateProfile();
        $casts = $profile->getCasts();
        $this->assertEquals('date', $casts['birthdate']);
        $this->assertEquals('decimal:2', $casts['current_salary']);
        $this->assertEquals('decimal:2', $casts['expected_salary']);
        $this->assertEquals('date', $casts['available_start_date']);
        $this->assertEquals('boolean', $casts['has_relatives']);
        $this->assertEquals('boolean', $casts['worked_before']);
        $this->assertEquals('boolean', $casts['applied_before']);
        $this->assertEquals('boolean', $casts['willing_out_of_town']);
        $this->assertEquals('array', $casts['documents']);
        $this->assertEquals('array', $casts['extras']);
    }

    public function test_uses_has_uuids_trait(): void
    {
        $profile = new CandidateProfile();
        $this->assertContains(
            \Illuminate\Database\Eloquent\Concerns\HasUuids::class,
            class_uses_recursive($profile)
        );
    }

    public function test_gender_is_lowercased_and_trimmed(): void
    {
        $profile = new CandidateProfile();
        $profile->gender = '  MALE  ';
        $this->assertEquals('male', $profile->gender);
    }

    public function test_gender_can_be_null(): void
    {
        $profile = new CandidateProfile();
        $profile->gender = null;
        $this->assertNull($profile->gender);
    }

    public function test_has_user_relationship(): void
    {
        $profile = new CandidateProfile();
        $relation = $profile->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\User::class, get_class($relation->getRelated()));
    }

    public function test_has_trainings_relationship(): void
    {
        $profile = new CandidateProfile();
        $relation = $profile->trainings();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\CandidateTraining::class, get_class($relation->getRelated()));
    }

    public function test_has_employments_relationship(): void
    {
        $profile = new CandidateProfile();
        $relation = $profile->employments();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\CandidateEmployment::class, get_class($relation->getRelated()));
    }

    public function test_has_references_relationship(): void
    {
        $profile = new CandidateProfile();
        $relation = $profile->references();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relation);
        $this->assertEquals(\App\Models\CandidateReference::class, get_class($relation->getRelated()));
    }
}
