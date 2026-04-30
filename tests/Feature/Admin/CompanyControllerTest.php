<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'hr', 'email_verified_at' => now()]);
    }

    public function test_index_displays_companies()
    {
        Company::factory()->create(['name' => 'Acme Corp']);

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.companies.index'));

        $response->assertStatus(200);
        $response->assertSee('Acme Corp');
    }

    public function test_create_renders_form()
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('admin.companies.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.companies.create');
    }

    public function test_store_creates_company()
    {
        Storage::fake('public');
        $this->actingAs($this->admin);

        $data = [
            'name' => 'New Company',
            'code' => 'NC01',
            'status' => 'active',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ];

        $response = $this->post(route('admin.companies.store'), $data);

        $response->assertRedirect(route('admin.companies.index'));
        $this->assertDatabaseHas('companies', ['name' => 'New Company', 'code' => 'NC01']);
        
        $company = Company::where('code', 'NC01')->first();
        $this->assertNotNull($company->logo_path);
    }

    public function test_show_displays_details()
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.companies.show', $company));

        $response->assertStatus(200);
        $response->assertSee($company->name);
    }

    public function test_edit_renders_form()
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->get(route('admin.companies.edit', $company));

        $response->assertStatus(200);
        $response->assertViewIs('admin.companies.edit');
    }

    public function test_update_modifies_company()
    {
        $company = Company::factory()->create(['name' => 'Old Name']);

        $this->actingAs($this->admin);
        $response = $this->put(route('admin.companies.update', $company), [
            'name' => 'Updated Name',
            'code' => $company->code,
            'status' => 'active'
        ]);

        $response->assertRedirect(route('admin.companies.index'));
        $this->assertDatabaseHas('companies', ['id' => $company->id, 'name' => 'Updated Name']);
    }

    public function test_destroy_deletes_company()
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin);
        $response = $this->delete(route('admin.companies.destroy', $company));

        $response->assertRedirect(route('admin.companies.index'));
        $this->assertSoftDeleted($company);
    }
}
