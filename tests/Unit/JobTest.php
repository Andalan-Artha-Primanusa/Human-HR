<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Job;

class JobTest extends TestCase
{
    public function test_normalize_level_with_valid_levels(): void
    {
        $this->assertEquals('foreman', Job::normalizeLevel('foreman'));
        $this->assertEquals('supervisor', Job::normalizeLevel('Supervisor'));
        $this->assertEquals('manager', Job::normalizeLevel('  MANAGER  '));
        $this->assertEquals('section_head', Job::normalizeLevel('section head'));
        $this->assertEquals('dept_head', Job::normalizeLevel('dept_head'));
        $this->assertEquals('project_manager', Job::normalizeLevel('Project Manager'));
    }

    public function test_normalize_level_with_aliases(): void
    {
        $this->assertNull(Job::normalizeLevel('non-staff'));
        $this->assertNull(Job::normalizeLevel('nonstaff'));
    }

    public function test_normalize_level_with_null(): void
    {
        $this->assertNull(Job::normalizeLevel(null));
        $this->assertNull(Job::normalizeLevel(''));
        $this->assertNull(Job::normalizeLevel('invalid_level'));
    }

    public function test_set_level_attribute(): void
    {
        $job = new Job();
        $job->level = '  SUPERVISOR  ';
        $this->assertEquals('supervisor', $job->level);
    }

    public function test_set_level_attribute_with_invalid_value(): void
    {
        $job = new Job();
        $job->level = 'invalid_level';
        $this->assertNull($job->level);
    }

    public function test_get_level_label_attribute(): void
    {
        $job = new Job();
        $job->level = 'manager';
        $this->assertEquals('Manager', $job->level_label);

        $job2 = new Job();
        $job2->level = 'pjo';
        $this->assertEquals('PJO', $job2->level_label);
    }

    public function test_get_level_label_returns_null_when_no_level(): void
    {
        $job = new Job();
        $this->assertNull($job->level_label);
    }

    public function test_normalize_division_with_valid_divisions(): void
    {
        $this->assertEquals('engineering', Job::normalizeDivision('engineering'));
        $this->assertEquals('hr', Job::normalizeDivision('HR'));
        $this->assertEquals('it', Job::normalizeDivision('IT'));
        $this->assertEquals('operations', Job::normalizeDivision('  Operations  '));
    }

    public function test_normalize_division_with_aliases(): void
    {
        $this->assertEquals('hr', Job::normalizeDivision('human_resources'));
        $this->assertEquals('hr', Job::normalizeDivision('people'));
        $this->assertEquals('it', Job::normalizeDivision('information_technology'));
        $this->assertEquals('operations', Job::normalizeDivision('ops'));
    }

    public function test_normalize_division_with_null(): void
    {
        $this->assertNull(Job::normalizeDivision(null));
        $this->assertNull(Job::normalizeDivision(''));
        $this->assertNull(Job::normalizeDivision('invalid_division'));
    }

    public function test_set_division_attribute(): void
    {
        $job = new Job();
        $job->division = '  HUMAN RESOURCES  ';
        $this->assertEquals('hr', $job->division);
    }

    public function test_get_division_label_attribute(): void
    {
        $job = new Job();
        $job->division = 'engineering';
        $this->assertEquals('Engineering', $job->division_label);

        $job2 = new Job();
        $job2->division = 'finance';
        $this->assertEquals('Finance', $job2->division_label);
    }

    public function test_set_skills_attribute_with_array(): void
    {
        $job = new Job();
        $job->skills = ['PHP', 'Laravel', 'MySQL'];
        $skills = json_decode($job->getAttributes()['skills'], true);
        $this->assertEquals(['PHP', 'Laravel', 'MySQL'], $skills);
    }

    public function test_set_skills_attribute_with_csv_string(): void
    {
        $job = new Job();
        $job->skills = 'PHP, Laravel, MySQL';
        $skills = json_decode($job->getAttributes()['skills'], true);
        $this->assertEquals(['PHP', 'Laravel', 'MySQL'], $skills);
    }

    public function test_set_skills_attribute_with_semicolon_separated(): void
    {
        $job = new Job();
        $job->skills = 'PHP;Laravel;MySQL';
        $skills = json_decode($job->getAttributes()['skills'], true);
        $this->assertEquals(['PHP', 'Laravel', 'MySQL'], $skills);
    }

    public function test_set_skills_attribute_removes_duplicates_and_empty(): void
    {
        $job = new Job();
        $job->skills = ['PHP', '  ', 'Laravel', '', 'PHP', 'MySQL'];
        $skills = json_decode($job->getAttributes()['skills'], true);
        $this->assertEquals(['PHP', 'Laravel', 'MySQL'], $skills);
    }

    public function test_fillable_attributes(): void
    {
        $job = new Job();
        $this->assertContains('title', $job->getFillable());
        $this->assertContains('code', $job->getFillable());
        $this->assertContains('description', $job->getFillable());
        $this->assertContains('level', $job->getFillable());
        $this->assertContains('division', $job->getFillable());
    }

    public function test_uses_correct_table_name(): void
    {
        $job = new Job();
        $this->assertEquals('job_listings', $job->getTable());
    }

    public function test_level_constants(): void
    {
        $this->assertContains('manager', Job::LEVELS);
        $this->assertContains('supervisor', Job::LEVELS);
        $this->assertEquals('Manager', Job::LEVEL_LABELS['manager']);
    }

    public function test_division_constants(): void
    {
        $this->assertArrayHasKey('engineering', Job::DIVISIONS);
        $this->assertArrayHasKey('hr', Job::DIVISIONS);
        $this->assertEquals('Engineering', Job::DIVISIONS['engineering']);
    }
}
