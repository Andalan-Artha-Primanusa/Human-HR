<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class JobTest extends TestCase
{
    use RefreshDatabase;
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

    public function test_relationships(): void
    {
        $job = new Job();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $job->site());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $job->company());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $job->creator());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $job->updater());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $job->applications());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $job->manpowerRequirements());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class, $job->manpowerRequirement());
    }

    public function test_scopes(): void
    {
        $this->assertStringContainsString('where "status" = ?', Job::open()->toSql());
        $this->assertStringContainsString('where "site_id" = ?', Job::atSite('site-1')->toSql());
        $this->assertStringContainsString('where "company_id" is null', Job::atCompany(null)->toSql());
        $this->assertStringContainsString('where "company_id" = ?', Job::atCompany('comp-1')->toSql());
        
        $divSql = Job::inDivision('hr')->toSql();
        $this->assertStringContainsString('where "division" = ?', $divSql);
        
        $this->assertStringContainsString('select * from "sites" where "job_listings"."site_id" = "sites"."id" and "code" = ?', Job::atSiteCode('S1')->toSql());
        $this->assertStringContainsString('select * from "companies" where "job_listings"."company_id" = "companies"."id" and "code" = ?', Job::atCompanyCode('C1')->toSql());

        $searchSql = Job::search('test')->toSql();
        $this->assertStringContainsString('code" like ?', $searchSql);
    }

    public function test_keywords_attribute(): void
    {
        $job = new Job();
        $job->keywords = 'test1, test2';
        $this->assertEquals('test1, test2', $job->keywords_text);
        
        $job->keywords = ['test3', 'test4'];
        $this->assertEquals('test3, test4', $job->keywords_text);

        $job->keywords = json_encode(['test5']);
        $this->assertEquals('test5', $job->keywords_text);

        $job->keywords = [['name' => 'test6']];
        $this->assertEquals('test6', $job->keywords_text);
    }

    public function test_set_site_code_attribute(): void
    {
        $job = new Job();
        $job->site_code = null;
        $this->assertNull($job->site_id);

        DB::table('sites')->insert(['id' => 'site-uuid-1', 'code' => 'SITE1', 'name' => 'Site 1', 'created_at' => now(), 'updated_at' => now()]);
        $job->site_code = 'SITE1';
        $this->assertEquals('site-uuid-1', $job->site_id);
    }

    public function test_set_company_code_attribute(): void
    {
        $job = new Job();
        $job->company_code = null;
        $this->assertNull($job->company_id);

        DB::table('companies')->insert(['id' => 'comp-uuid-1', 'code' => 'COMP1', 'name' => 'Comp 1', 'created_at' => now(), 'updated_at' => now()]);
        $job->company_code = 'COMP1';
        $this->assertEquals('comp-uuid-1', $job->company_id);
    }
}
