<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Poh;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplicationControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $hr;
    protected $pelamar;
    protected $site;
    protected $job;
    protected $application;

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
            'current_stage' => 'applied',
            'overall_status' => 'active',
        ]);
    }

    public function test_pelamar_index_requires_auth()
    {
        $response = $this->get(route('applications.mine'));
        $response->assertRedirect(route('login'));
    }

    public function test_pelamar_index_renders_own_applications()
    {
        $this->actingAs($this->pelamar);
        $response = $this->get(route('applications.mine'));
        $response->assertStatus(200);
        $response->assertViewHas('apps');
    }

    public function test_pelamar_store_creates_application()
    {
        $this->actingAs($this->pelamar);

        $response = $this->post(route('applications.store', $this->job));

        $response->assertRedirect(route('candidate.profiles.edit', ['job' => $this->job->id]));
        $this->assertDatabaseHas('job_applications', [
            'job_id' => $this->job->id,
            'user_id' => $this->pelamar->id,
            'current_stage' => 'applied',
        ]);
    }

    public function test_pelamar_store_with_poh()
    {
        $poh = Poh::create([
            'name' => 'Test POH',
            'code' => 'TPOH-01',
            'address' => 'Test Address',
            'is_active' => true,
        ]);

        $this->actingAs($this->pelamar);

        $this->post(route('applications.store', $this->job), [
            'poh_id' => $poh->id,
        ]);

        $this->assertDatabaseHas('job_applications', [
            'job_id' => $this->job->id,
            'user_id' => $this->pelamar->id,
            'poh_id' => $poh->id,
        ]);
    }

    public function test_pelamar_store_creates_application_stage()
    {
        $this->actingAs($this->pelamar);

        $this->post(route('applications.store', $this->job));

        $app = JobApplication::where('user_id', $this->pelamar->id)->first();
        $this->assertDatabaseHas('application_stages', [
            'application_id' => $app->id,
            'stage_key' => 'applied',
            'status' => 'pending',
        ]);
    }

    public function test_pelamar_store_redirects_to_profile_wizard()
    {
        $this->actingAs($this->pelamar);

        $response = $this->post(route('applications.store', $this->job));

        $response->assertRedirect(route('candidate.profiles.edit', ['job' => $this->job->id]));
    }

    public function test_pelamar_store_cannot_apply_to_closed_job()
    {
        $closedJob = Job::create([
            'title' => 'Closed Job',
            'slug' => 'closed-job',
            'code' => 'CJ-01',
            'description' => 'Test',
            'status' => 'closed',
            'level' => 1,
            'site_id' => $this->site->id,
        ]);

        $this->actingAs($this->pelamar);

        $response = $this->post(route('applications.store', $closedJob));

        $response->assertForbidden();
    }

    public function test_pelamar_store_redirects_if_already_applied()
    {
        $this->actingAs($this->pelamar);

        $response = $this->post(route('applications.store', $this->job));
        $response = $this->post(route('applications.store', $this->job));

        $response->assertSessionHas('info');
    }

    public function test_admin_index_requires_auth()
    {
        $response = $this->get(route('admin.applications.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_index_renders_for_hr()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.index'));
        $response->assertStatus(200);
    }

    public function test_admin_index_filters_by_stage()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.index', ['stage' => 'applied']));
        $response->assertStatus(200);
    }

    public function test_admin_index_filters_by_search()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.index', ['q' => 'Software']));
        $response->assertStatus(200);
    }

    public function test_admin_board_requires_auth()
    {
        $response = $this->get(route('admin.applications.board'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_board_renders_for_hr()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.board'));
        $response->assertStatus(200);
        $response->assertViewHas('stages');
        $response->assertViewHas('grouped');
    }

    public function test_admin_board_shows_all_stages()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.board'));
        $response->assertStatus(200);

        $stages = $response->viewData('stages');
        $this->assertArrayHasKey('applied', $stages);
        $this->assertArrayHasKey('screening', $stages);
        $this->assertArrayHasKey('psychotest', $stages);
        $this->assertArrayHasKey('hired', $stages);
        $this->assertArrayHasKey('not_qualified', $stages);
    }

    public function test_admin_board_groups_applications_by_stage()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.board'));
        $response->assertStatus(200);

        $grouped = $response->viewData('grouped');
        $this->assertTrue($grouped->has('applied'));
        $this->assertEquals(1, $grouped['applied']->count());
    }

    public function test_admin_board_filters_by_job_id()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.board', ['job_id' => $this->job->id]));
        $response->assertStatus(200);
    }

    public function test_admin_board_filters_by_stage()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.board', ['only' => 'applied']));
        $response->assertStatus(200);
    }

    public function test_admin_board_filters_by_search()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.applications.board', ['q' => $this->pelamar->name]));
        $response->assertStatus(200);
    }

    public function test_move_stage_to_screening()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.move', $this->application), [
            'to' => 'screening',
        ]);

        $response->assertRedirect();
        $this->application->refresh();
        $this->assertEquals('screening', $this->application->current_stage);
    }

    public function test_move_stage_to_not_qualified()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.move', $this->application), [
            'to' => 'not_qualified',
        ]);

        $response->assertRedirect();
        $this->application->refresh();
        $this->assertEquals('not_qualified', $this->application->current_stage);
        $this->assertEquals('not_qualified', $this->application->overall_status);
    }

    public function test_move_stage_to_hired()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.move', $this->application), [
            'to' => 'hired',
        ]);

        $response->assertRedirect();
        $this->application->refresh();
        $this->assertEquals('hired', $this->application->current_stage);
        $this->assertEquals('hired', $this->application->overall_status);
    }

    public function test_move_stage_creates_application_stage_entry()
    {
        $this->actingAs($this->hr);

        $this->post(route('admin.applications.move', $this->application), [
            'to' => 'screening',
        ]);

        $app = JobApplication::find($this->application->id);
        $this->assertDatabaseHas('application_stages', [
            'application_id' => $app->id,
            'stage_key' => 'screening',
        ]);
    }

    public function test_move_stage_sends_notification_to_user()
    {
        $this->actingAs($this->hr);

        $this->post(route('admin.applications.move', $this->application), [
            'to' => 'screening',
        ]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->pelamar->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_move_stage_ajax_moves_application()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.board.move'), [
            'id' => $this->application->id,
            'to' => 'screening',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true, 'moved_to' => 'screening']);

        $this->application->refresh();
        $this->assertEquals('screening', $this->application->current_stage);
    }

    public function test_move_stage_ajax_invalid_id()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.board.move'), [
            'id' => '00000000-0000-0000-0000-000000000000',
            'to' => 'screening',
        ]);

        $response->assertSessionHasErrors('id');
    }

    public function test_store_feedback_hr()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.feedback.store'), [
            'application_id' => $this->application->id,
            'stage_key' => 'hr_iv',
            'role' => 'hr',
            'feedback' => 'Candidate looks promising',
            'approve' => 'yes',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseHas('application_feedbacks', [
            'application_id' => $this->application->id,
            'role' => 'hr',
            'feedback' => 'Candidate looks promising',
            'approve' => 'yes',
        ]);
    }

    public function test_store_feedback_requires_application_id()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.feedback.store'), [
            'stage_key' => 'hr_iv',
            'role' => 'hr',
            'feedback' => 'Test feedback',
            'approve' => 'yes',
        ]);

        $response->assertSessionHasErrors('application_id');
    }

    public function test_store_feedback_requires_feedback_text()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.feedback.store'), [
            'application_id' => $this->application->id,
            'stage_key' => 'hr_iv',
            'role' => 'hr',
            'approve' => 'yes',
        ]);

        $response->assertSessionHasErrors('feedback');
    }

    public function test_delete_feedback_removes_feedback()
    {
        $this->actingAs($this->hr);

        $this->post(route('admin.applications.feedback.store'), [
            'application_id' => $this->application->id,
            'stage_key' => 'hr_iv',
            'role' => 'hr',
            'feedback' => 'Test feedback',
            'approve' => 'yes',
        ]);

        $response = $this->delete(route('admin.applications.feedback.delete'), [
            'application_id' => $this->application->id,
            'role' => 'hr',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('application_feedbacks', [
            'application_id' => $this->application->id,
            'role' => 'hr',
        ]);
    }

    public function test_store_feedback_updates_application_columns()
    {
        $this->actingAs($this->hr);

        $this->post(route('admin.applications.feedback.store'), [
            'application_id' => $this->application->id,
            'stage_key' => 'hr_iv',
            'role' => 'hr',
            'feedback' => 'Good candidate',
            'approve' => 'yes',
        ]);

        $this->application->refresh();
        $this->assertEquals('Good candidate', $this->application->feedback_hr);
        $this->assertEquals('yes', $this->application->approve_hr);
    }

    public function test_send_offer_email_requires_auth()
    {
        $response = $this->post(route('admin.applications.send-offer', $this->application));
        $response->assertRedirect(route('login'));
    }

    public function test_send_mcu_email_requires_auth()
    {
        $response = $this->post(route('admin.applications.send-mcu', $this->application));
        $response->assertRedirect(route('login'));
    }

    public function test_move_stage_ajax_with_invalid_to_stage()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.applications.board.move'), [
            'id' => $this->application->id,
            'to' => 'invalid_stage_name',
        ]);

        $response->assertSessionHasErrors('to');
    }
}
