<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Poh;
use App\Models\Site;
use App\Models\User;
use App\Models\ApplicationFeedback;
use App\Models\PsychotestAttempt;
use App\Models\Offer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\OfferLetterMail;
use App\Mail\McuMail;
use Tests\TestCase;

class ApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $job;
    protected $poh;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'pelamar', 'email_verified_at' => now()]);
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
        $this->poh = Poh::factory()->create(['is_active' => true]);
        $site = Site::factory()->create();
        $this->job = Job::factory()->create(['site_id' => $site->id, 'status' => 'open']);
    }

    public function test_index_displays_user_applications()
    {
        JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active'
        ]);

        $this->actingAs($this->user);

        $response = $this->get(route('applications.mine'));

        $response->assertStatus(200);
        $response->assertSee($this->job->title);
    }

    public function test_store_creates_application_and_redirects()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('applications.store', $this->job), [
            'poh_id' => $this->poh->id
        ]);

        $response->assertRedirect(route('candidate.profiles.edit', $this->job));
        $this->assertDatabaseHas('job_applications', [
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'applied'
        ]);
    }

    public function test_admin_index_displays_all_applications()
    {
        JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active'
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.applications.index'));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($this->job->title);
    }

    public function test_board_renders_kanban_view()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.applications.board'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.applications.board');
    }

    public function test_store_feedback_via_ajax()
    {
        $app = JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'hr_iv',
            'overall_status' => 'active'
        ]);

        $this->actingAs($this->admin);

        $response = $this->postJson(route('admin.applications.feedback.store'), [
            'application_id' => $app->id,
            'stage_key' => 'hr_iv',
            'role' => 'hr',
            'feedback' => 'Good candidate',
            'approve' => 'yes'
        ]);

        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
        $this->assertDatabaseHas('application_feedbacks', [
            'application_id' => $app->id,
            'role' => 'hr',
            'feedback' => 'Good candidate'
        ]);
    }

    public function test_move_stage_updates_application()
    {
        $app = JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'applied',
            'overall_status' => 'active'
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.applications.move', $app), [
            'to' => 'screening',
            'status' => 'passed',
            'note' => 'Looks good'
        ]);

        $response->assertRedirect();
        $app->refresh();
        $this->assertEquals('screening', $app->current_stage);
        $this->assertDatabaseHas('application_stages', [
            'application_id' => $app->id,
            'stage_key' => 'screening'
        ]);
    }

    public function test_move_to_psychotest_creates_attempt()
    {
        $app = JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'screening',
            'overall_status' => 'active'
        ]);

        $this->actingAs($this->admin);

        $this->post(route('admin.applications.move', $app), [
            'to' => 'psychotest'
        ]);

        $app->refresh();
        $this->assertEquals('psychotest', $app->current_stage);
        $this->assertDatabaseHas('psychotest_attempts', [
            'application_id' => $app->id,
            'user_id' => $this->user->id
        ]);
    }

    public function test_send_offer_email()
    {
        Mail::fake();
        $app = JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'offer',
            'overall_status' => 'active'
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.applications.send-offer', $app), [
            'gross' => 5000000,
            'allowance' => 1000000,
            'email_body' => 'Welcome aboard!'
        ]);

        $response->assertSessionHas('ok');
        Mail::assertQueued(OfferLetterMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
        $this->assertDatabaseHas('offers', [
            'application_id' => $app->id,
            'status' => 'sent'
        ]);
    }

    public function test_send_mcu_email()
    {
        Mail::fake();
        $app = JobApplication::create([
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'current_stage' => 'mcu',
            'overall_status' => 'active'
        ]);

        $this->actingAs($this->admin);

        $response = $this->post(route('admin.applications.send-mcu', $app), [
            'email_body' => 'Please do MCU',
            'clinic_name' => 'Clinic A',
            'clinic_address' => 'Street A',
            'mcu_date' => now()->addDays(7)->format('Y-m-d'),
            'mcu_time' => '08:00'
        ]);

        $response->assertSessionHas('ok');
        Mail::assertQueued(McuMail::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
        $app->refresh();
        $this->assertNotNull($app->mcu_meta);
    }
}
