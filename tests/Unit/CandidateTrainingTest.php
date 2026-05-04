<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CandidateProfile;
use App\Models\CandidateTraining;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CandidateTrainingTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $training = new CandidateTraining();
        $this->assertContains('candidate_profile_id', $training->getFillable());
        $this->assertContains('title', $training->getFillable());
        $this->assertContains('institution', $training->getFillable());
        $this->assertContains('period_start', $training->getFillable());
        $this->assertContains('period_end', $training->getFillable());
        $this->assertContains('certificate_path', $training->getFillable());
        $this->assertContains('order_no', $training->getFillable());
    }

    public function test_casts(): void
    {
        $training = new CandidateTraining();
        $casts = $training->getCasts();
        $this->assertEquals('date', $casts['period_start']);
        $this->assertEquals('date', $casts['period_end']);
    }

    public function test_uses_uuid_primary_key(): void
    {
        $training = new CandidateTraining();
        $this->assertFalse($training->incrementing);
        $this->assertEquals('string', $training->getKeyType());
    }

    public function test_uses_has_uuids_trait(): void
    {
        $training = new CandidateTraining();
        $this->assertContains(
            \Illuminate\Database\Eloquent\Concerns\HasUuids::class,
            class_uses_recursive($training)
        );
    }

    public function test_has_profile_relationship(): void
    {
        $training = new CandidateTraining();
        $relation = $training->profile();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals(\App\Models\CandidateProfile::class, get_class($relation->getRelated()));
    }

    public function test_booted_auto_generates_uuid_when_not_provided(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        $training = CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'PHP Fundamentals',
            'institution' => 'Tech Academy',
            'period_start' => '2024-01-01',
        ]);

        $this->assertNotNull($training->id);
        $this->assertIsString($training->id);
        $this->assertTrue(strlen($training->id) === 36);
    }

    public function test_booted_auto_sets_order_no_when_null(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Training 1',
            'institution' => 'School',
            'period_start' => '2024-01-01',
        ]);

        $second = CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Training 2',
            'institution' => 'School',
            'period_start' => '2024-02-01',
        ]);

        $this->assertEquals(2, $second->order_no);
    }

    public function test_booted_respects_explicit_order_no(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        $training = CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Explicit Order',
            'institution' => 'School',
            'period_start' => '2024-01-01',
            'order_no' => 5,
        ]);

        $this->assertEquals(5, $training->order_no);
    }

    public function test_ordered_scope_sorts_correctly(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Last Training',
            'institution' => 'School',
            'period_start' => '2024-01-01',
            'order_no' => 3,
        ]);
        CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'First Training',
            'institution' => 'School',
            'period_start' => '2024-01-01',
            'order_no' => 1,
        ]);
        CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Second Training',
            'institution' => 'School',
            'period_start' => '2024-01-01',
            'order_no' => 2,
        ]);

        $ordered = CandidateTraining::ordered()->get();

        $this->assertEquals('First Training', $ordered->first()->title);
        $this->assertEquals('Last Training', $ordered->last()->title);
    }

    public function test_can_create_with_all_attributes(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        $training = CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Advanced Laravel',
            'institution' => 'Laravel Academy',
            'period_start' => '2023-06-01',
            'period_end' => '2023-12-01',
            'certificate_path' => 'certs/test.pdf',
            'order_no' => 1,
        ]);

        $this->assertDatabaseHas('candidate_trainings', ['title' => 'Advanced Laravel']);

        $retrieved = CandidateTraining::find($training->id);
        $this->assertEquals('Advanced Laravel', $retrieved->title);
        $this->assertEquals('certs/test.pdf', $retrieved->certificate_path);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $retrieved->period_start);
    }

    public function test_profile_relationship_returns_correct_profile(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        $training = CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Test',
            'institution' => 'School',
            'period_start' => '2024-01-01',
        ]);

        $this->assertEquals($profile->id, $training->profile->id);
    }

    public function test_period_dates_are_cast_to_carbon(): void
    {
        $profile = CandidateProfile::create(['user_id' => User::factory()->create()->id, 'full_name' => 'Test']);

        $training = CandidateTraining::create([
            'candidate_profile_id' => $profile->id,
            'title' => 'Test',
            'institution' => 'School',
            'period_start' => '2024-06-15',
            'period_end' => '2024-12-31',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $training->period_start);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $training->period_end);
        $this->assertEquals('2024-06-15', $training->period_start->toDateString());
    }
}
