<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\PsychotestAttempt;
use App\Models\PsychotestQuestion;
use App\Models\PsychotestTest;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PsychotestControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $hr;
    protected $pelamar;
    protected $otherPelamar;
    protected $site;
    protected $job;
    protected $application;
    protected $psychotest;
    protected $attempt;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = User::factory()->create([
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);

        $this->pelamar = User::factory()->create([
            'role' => 'pelamar',
            'email_verified_at' => now(),
        ]);

        $this->otherPelamar = User::factory()->create([
            'role' => 'pelamar',
            'email_verified_at' => now(),
        ]);

        $this->site = Site::factory()->create();

        $this->job = Job::create([
            'title' => 'Software Engineer',
            'slug' => 'software-engineer',
            'code' => 'SE-01',
            'description' => 'Test',
            'requirements' => 'Test',
            'status' => 'open',
            'level' => 1,
            'employment_type' => 'fulltime',
            'site_id' => $this->site->id,
        ]);

        $this->application = JobApplication::create([
            'user_id' => $this->pelamar->id,
            'job_id' => $this->job->id,
            'current_stage' => 'psychotest',
            'overall_status' => 'active',
        ]);

        $this->psychotest = PsychotestTest::create([
            'name' => 'General Ability Test',
            'duration_minutes' => 60,
            'scoring' => ['pass_ratio' => 0.6],
        ]);

        PsychotestQuestion::create([
            'test_id' => $this->psychotest->id,
            'type' => 'mcq',
            'question' => 'What is 2 + 2?',
            'options' => ['3', '4', '5', '6'],
            'answer_key' => '4',
            'weight' => 1,
            'order_no' => 1,
        ]);

        PsychotestQuestion::create([
            'test_id' => $this->psychotest->id,
            'type' => 'truefalse',
            'question' => 'The sky is blue.',
            'options' => ['true', 'false'],
            'answer_key' => 'true',
            'weight' => 1,
            'order_no' => 2,
        ]);

        $this->attempt = PsychotestAttempt::create([
            'application_id' => $this->application->id,
            'test_id' => $this->psychotest->id,
            'user_id' => $this->pelamar->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_index_requires_auth()
    {
        $response = $this->get(route('admin.psychotests.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_index_renders_for_hr()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.psychotests.index'));
        $response->assertStatus(200);
    }

    public function test_admin_index_with_search_query()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.psychotests.index', ['q' => 'Software']));
        $response->assertStatus(200);
    }

    public function test_admin_index_filters_by_active_status()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.psychotests.index', ['status' => 'active']));
        $response->assertStatus(200);
    }

    public function test_admin_index_filters_by_finished_status()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.psychotests.index', ['status' => 'finished']));
        $response->assertStatus(200);
    }

    public function test_user_show_requires_auth()
    {
        $response = $this->get(route('psychotest.show', $this->attempt));
        $response->assertRedirect(route('login'));
    }

    public function test_user_show_renders_for_owner()
    {
        $this->actingAs($this->pelamar);
        $response = $this->get(route('psychotest.show', $this->attempt));
        $response->assertStatus(200);
    }

    public function test_user_show_is_forbidden_for_other_user()
    {
        $otherApplication = JobApplication::create([
            'user_id' => $this->otherPelamar->id,
            'job_id' => $this->job->id,
            'current_stage' => 'psychotest',
            'overall_status' => 'active',
        ]);

        $otherAttempt = PsychotestAttempt::create([
            'application_id' => $otherApplication->id,
            'test_id' => $this->psychotest->id,
            'user_id' => $this->otherPelamar->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->pelamar);
        $response = $this->get(route('psychotest.show', $otherAttempt));
        $response->assertForbidden();
    }

    public function test_user_show_redirects_when_already_submitted()
    {
        $this->attempt->update(['status' => 'submitted']);

        $this->actingAs($this->pelamar);
        $response = $this->get(route('psychotest.show', $this->attempt));
        $response->assertRedirect(route('applications.mine'));
        $response->assertSessionHas('warn');
    }

    public function test_user_show_redirects_when_already_scored()
    {
        $this->attempt->update(['status' => 'scored']);

        $this->actingAs($this->pelamar);
        $response = $this->get(route('psychotest.show', $this->attempt));
        $response->assertRedirect(route('applications.mine'));
    }

    public function test_user_show_redirects_when_expired()
    {
        $this->attempt->update([
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($this->pelamar);
        $response = $this->get(route('psychotest.show', $this->attempt));
        $response->assertRedirect(route('applications.mine'));
        $response->assertSessionHas('warn');
    }

    public function test_user_show_sets_started_at_on_first_access()
    {
        $this->assertNull($this->attempt->started_at);

        $this->actingAs($this->pelamar);
        $this->get(route('psychotest.show', $this->attempt));

        $this->attempt->refresh();
        $this->assertNotNull($this->attempt->started_at);
        $this->assertEquals('in_progress', $this->attempt->status);
    }

    public function test_user_show_does_not_reset_started_at_on_subsequent_access()
    {
        $this->attempt->update([
            'started_at' => now()->subMinutes(10),
            'status' => 'in_progress',
        ]);

        $originalStartedAt = $this->attempt->started_at;

        $this->actingAs($this->pelamar);
        $this->get(route('psychotest.show', $this->attempt));

        $this->attempt->refresh();
        $this->assertEquals($originalStartedAt->timestamp, $this->attempt->started_at->timestamp);
    }

    public function test_user_submit_requires_auth()
    {
        $response = $this->post(route('psychotest.submit', $this->attempt), []);
        $response->assertRedirect(route('login'));
    }

    public function test_user_submit_is_forbidden_for_other_user()
    {
        $otherApplication = JobApplication::create([
            'user_id' => $this->otherPelamar->id,
            'job_id' => $this->job->id,
            'current_stage' => 'psychotest',
            'overall_status' => 'active',
        ]);

        $otherAttempt = PsychotestAttempt::create([
            'application_id' => $otherApplication->id,
            'test_id' => $this->psychotest->id,
            'user_id' => $this->otherPelamar->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->pelamar);
        $response = $this->post(route('psychotest.submit', $otherAttempt), [
            'answers' => [],
        ]);
        $response->assertForbidden();
    }

    public function test_user_submit_is_blocked_when_already_submitted()
    {
        $this->attempt->update(['status' => 'submitted']);

        $this->actingAs($this->pelamar);
        $response = $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [],
        ]);
        $response->assertRedirect(route('applications.mine'));
        $response->assertSessionHas('warn');
    }

    public function test_user_submit_is_blocked_when_expired()
    {
        $this->attempt->update([
            'status' => 'pending',
            'expires_at' => now()->subDay(),
        ]);

        $this->actingAs($this->pelamar);
        $response = $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [],
        ]);
        $response->assertRedirect(route('applications.mine'));
        $response->assertSessionHas('warn');
    }

    public function test_user_submit_requires_answers_array()
    {
        $this->actingAs($this->pelamar);
        $response = $this->post(route('psychotest.submit', $this->attempt), []);
        $response->assertSessionHasErrors('answers');
    }

    public function test_user_submit_correct_answers()
    {
        $questions = $this->psychotest->questions()->orderBy('order_no')->get();

        $this->actingAs($this->pelamar);
        $response = $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                $questions[0]->id => '4',
                $questions[1]->id => 'true',
            ],
        ]);

        $response->assertRedirect(route('applications.mine'));
        $response->assertSessionHas('ok');

        $this->attempt->refresh();
        $this->assertEquals('scored', $this->attempt->status);
        $this->assertNotNull($this->attempt->finished_at);
        $this->assertNotNull($this->attempt->submitted_at);
        $this->assertEquals(2.0, (float) $this->attempt->score);
    }

    public function test_user_submit_wrong_answers()
    {
        $questions = $this->psychotest->questions()->orderBy('order_no')->get();

        $this->actingAs($this->pelamar);
        $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                $questions[0]->id => '5',
                $questions[1]->id => 'false',
            ],
        ]);

        $this->attempt->refresh();
        $this->assertEquals(0.0, (float) $this->attempt->score);
    }

    public function test_user_submit_mixed_answers()
    {
        $questions = $this->psychotest->questions()->orderBy('order_no')->get();

        $this->actingAs($this->pelamar);
        $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                $questions[0]->id => '4',
                $questions[1]->id => 'false',
            ],
        ]);

        $this->attempt->refresh();
        $this->assertEquals(1.0, (float) $this->attempt->score);
    }

    public function test_user_submit_saves_answers()
    {
        $questions = $this->psychotest->questions()->orderBy('order_no')->get();

        $this->actingAs($this->pelamar);
        $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                $questions[0]->id => '4',
                $questions[1]->id => 'true',
            ],
        ]);

        $this->assertDatabaseHas('psychotest_answers', [
            'attempt_id' => $this->attempt->id,
            'question_id' => $questions[0]->id,
            'answer' => '4',
            'is_correct' => 1,
        ]);

        $this->assertDatabaseHas('psychotest_answers', [
            'attempt_id' => $this->attempt->id,
            'question_id' => $questions[1]->id,
            'answer' => 'true',
            'is_correct' => 1,
        ]);
    }

    public function test_user_submit_creates_application_stage()
    {
        $questions = $this->psychotest->questions()->orderBy('order_no')->get();

        $this->actingAs($this->pelamar);
        $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                $questions[0]->id => '4',
                $questions[1]->id => 'true',
            ],
        ]);

        $this->assertDatabaseHas('application_stages', [
            'application_id' => $this->application->id,
            'stage_key' => 'psychotest',
            'status' => 'passed',
        ]);
    }

    public function test_user_submit_failed_moves_stage()
    {
        $this->psychotest->update(['scoring' => ['pass_ratio' => 1.0]]);
        $questions = $this->psychotest->questions()->orderBy('order_no')->get();

        $this->actingAs($this->pelamar);
        $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                $questions[0]->id => 'wrong',
                $questions[1]->id => 'wrong',
            ],
        ]);

        $this->application->refresh();
        $this->assertEquals('psychotest', $this->application->current_stage);
    }

    public function test_user_submit_passed_moves_to_hr_iv()
    {
        $questions = $this->psychotest->questions()->orderBy('order_no')->get();

        $this->actingAs($this->pelamar);
        $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                $questions[0]->id => '4',
                $questions[1]->id => 'true',
            ],
        ]);

        $this->application->refresh();
        $this->assertEquals('hr_iv', $this->application->current_stage);
    }

    public function test_user_submit_partial_answers()
    {
        $questions = $this->psychotest->questions()->orderBy('order_no')->get();

        $this->actingAs($this->pelamar);
        $response = $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                $questions[0]->id => '4',
            ],
        ]);

        $response->assertRedirect(route('applications.mine'));

        $this->attempt->refresh();
        $this->assertEquals('scored', $this->attempt->status);
    }

    public function test_user_submit_with_null_answers()
    {
        $this->actingAs($this->pelamar);
        $response = $this->post(route('psychotest.submit', $this->attempt), [
            'answers' => [
                'non-existent-id' => 'answer',
            ],
        ]);

        $response->assertRedirect(route('applications.mine'));

        $this->attempt->refresh();
        $this->assertEquals('scored', $this->attempt->status);
        $this->assertEquals(0.0, (float) $this->attempt->score);
    }
}
