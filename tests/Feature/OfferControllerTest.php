<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\Offer;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferControllerTest extends TestCase
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
            'current_stage' => 'offer',
            'overall_status' => 'active',
        ]);
    }

    public function test_admin_index_requires_auth()
    {
        $response = $this->get(route('admin.offers.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_index_renders_for_hr()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.offers.index'));
        $response->assertStatus(200);
    }

    public function test_admin_index_is_forbidden_for_pelamar()
    {
        $this->actingAs($this->pelamar);
        $response = $this->get(route('admin.offers.index'));
        $response->assertForbidden();
    }

    public function test_admin_index_filters_by_status()
    {
        Offer::create([
            'application_id' => $this->application->id,
            'status' => 'draft',
            'salary' => ['gross' => 5000000, 'allowance' => 1000000],
        ]);

        $this->actingAs($this->hr);
        $response = $this->get(route('admin.offers.index', ['status' => 'draft']));
        $response->assertStatus(200);
    }

    public function test_admin_index_with_search_query()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.offers.index', ['q' => 'Software']));
        $response->assertStatus(200);
    }

    public function test_admin_store_creates_offer()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.offers.store', $this->application), [
            'gross_salary' => 8000000,
            'allowance' => 2000000,
            'notes' => 'Penawaran awal',
        ]);

        $response->assertRedirect(route('admin.applications.index'));
        $this->assertDatabaseHas('offers', [
            'application_id' => $this->application->id,
            'status' => 'draft',
        ]);
    }

    public function test_admin_store_updates_application_stage_to_offer()
    {
        $this->actingAs($this->hr);

        $this->post(route('admin.offers.store', $this->application), [
            'gross_salary' => 7000000,
            'allowance' => 1500000,
        ]);

        $this->application->refresh();
        $this->assertEquals('offer', $this->application->current_stage);
    }

    public function test_admin_store_requires_gross_salary()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.offers.store', $this->application), [
            'allowance' => 1000000,
        ]);

        $response->assertSessionHasErrors('gross_salary');
    }

    public function test_admin_store_requires_numeric_gross_salary()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.offers.store', $this->application), [
            'gross_salary' => 'not_a_number',
            'allowance' => 1000000,
        ]);

        $response->assertSessionHasErrors('gross_salary');
    }

    public function test_admin_store_requires_positive_gross_salary()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.offers.store', $this->application), [
            'gross_salary' => -100,
        ]);

        $response->assertSessionHasErrors('gross_salary');
    }

    public function test_admin_store_is_forbidden_for_pelamar()
    {
        $this->actingAs($this->pelamar);

        $response = $this->post(route('admin.offers.store', $this->application), [
            'gross_salary' => 5000000,
        ]);

        $response->assertForbidden();
    }

    public function test_admin_store_with_meta_data()
    {
        $this->actingAs($this->hr);

        $this->post(route('admin.offers.store', $this->application), [
            'gross_salary' => 9000000,
            'meta' => [
                'doc_no' => 'OFF-2024-001',
                'join_date' => '2024-06-01',
            ],
        ]);

        $offer = Offer::where('application_id', $this->application->id)->first();
        $this->assertNotNull($offer);
        $this->assertArrayHasKey('doc_no', $offer->meta);
    }

    public function test_admin_update_modifies_offer()
    {
        $offer = Offer::create([
            'application_id' => $this->application->id,
            'status' => 'draft',
            'salary' => ['gross' => 5000000, 'allowance' => 1000000],
            'body_template' => 'Original body',
        ]);

        $this->actingAs($this->hr);

        $response = $this->patch(route('admin.offers.update', $offer), [
            'gross' => 7500000,
            'allowance' => 2500000,
            'body' => 'Updated body template',
            'status' => 'sent',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('offers', [
            'id' => $offer->id,
            'status' => 'sent',
        ]);

        $offer->refresh();
        $this->assertEquals(7500000, $offer->salary['gross']);
        $this->assertEquals(2500000, $offer->salary['allowance']);
        $this->assertEquals('Updated body template', $offer->body_template);
    }

    public function test_admin_update_with_meta_fields()
    {
        $offer = Offer::create([
            'application_id' => $this->application->id,
            'status' => 'draft',
            'salary' => ['gross' => 5000000, 'allowance' => 1000000],
            'body_template' => 'Body',
        ]);

        $this->actingAs($this->hr);

        $this->patch(route('admin.offers.update', $offer), [
            'gross' => 6000000,
            'allowance' => 1500000,
            'body' => 'Body',
            'doc_no' => 'OFF-2024-002',
            'grade_level' => 'L3',
            'join_date' => '2024-07-01',
        ]);

        $offer->refresh();
        $this->assertEquals('OFF-2024-002', $offer->meta['doc_no']);
        $this->assertEquals('L3', $offer->meta['grade_level']);
        $this->assertEquals('2024-07-01', $offer->meta['join_date']);
    }

    public function test_admin_update_is_forbidden_for_pelamar()
    {
        $offer = Offer::create([
            'application_id' => $this->application->id,
            'status' => 'draft',
            'salary' => ['gross' => 5000000, 'allowance' => 1000000],
            'body_template' => 'Body',
        ]);

        $this->actingAs($this->pelamar);

        $response = $this->patch(route('admin.offers.update', $offer), [
            'gross' => 9999999,
            'allowance' => 999999,
            'body' => 'Hacked body',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_update_requires_gross()
    {
        $offer = Offer::create([
            'application_id' => $this->application->id,
            'status' => 'draft',
            'salary' => ['gross' => 5000000, 'allowance' => 1000000],
            'body_template' => 'Body',
        ]);

        $this->actingAs($this->hr);

        $response = $this->patch(route('admin.offers.update', $offer), [
            'allowance' => 1000000,
            'body' => 'Body',
        ]);

        $response->assertSessionHasErrors('gross');
    }

    public function test_admin_pdf_renders_for_hr()
    {
        $offer = Offer::create([
            'application_id' => $this->application->id,
            'status' => 'sent',
            'salary' => ['gross' => 8000000, 'allowance' => 2000000],
            'body_template' => 'Offer Letter Body',
        ]);

        $this->actingAs($this->hr);

        $response = $this->get(route('admin.offers.pdf', $offer));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type');
    }

    public function test_admin_pdf_is_forbidden_for_pelamar()
    {
        $offer = Offer::create([
            'application_id' => $this->application->id,
            'status' => 'sent',
            'salary' => ['gross' => 8000000, 'allowance' => 2000000],
        ]);

        $this->actingAs($this->pelamar);

        $response = $this->get(route('admin.offers.pdf', $offer));
        $response->assertForbidden();
    }

    public function test_admin_pdf_with_download_flag()
    {
        $offer = Offer::create([
            'application_id' => $this->application->id,
            'status' => 'sent',
            'salary' => ['gross' => 8000000, 'allowance' => 2000000],
            'body_template' => 'Offer Letter',
        ]);

        $this->actingAs($this->hr);

        $response = $this->get(route('admin.offers.pdf', $offer) . '?dl=1');
        $response->assertStatus(200);
    }
}
