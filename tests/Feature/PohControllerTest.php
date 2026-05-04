<?php

namespace Tests\Feature;

use App\Models\Poh;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PohControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $hr;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hr = User::factory()->create([
            'role' => 'hr',
            'email_verified_at' => now(),
        ]);
    }

    public function test_admin_index_requires_auth()
    {
        $response = $this->get(route('admin.pohs.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_index_renders_for_hr()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.pohs.index'));
        $response->assertStatus(200);
        $response->assertViewHas('pohs');
    }

    public function test_admin_index_shows_all_pohs()
    {
        Poh::create([
            'name' => 'POH Alpha',
            'code' => 'PA-01',
            'address' => 'Address A',
            'description' => 'Description A',
            'is_active' => true,
        ]);
        Poh::create([
            'name' => 'POH Beta',
            'code' => 'PB-02',
            'address' => 'Address B',
            'description' => 'Description B',
            'is_active' => false,
        ]);

        $this->actingAs($this->hr);
        $response = $this->get(route('admin.pohs.index'));
        $response->assertStatus(200);

        $pohs = $response->viewData('pohs');
        $this->assertGreaterThanOrEqual(2, $pohs->count());
    }

    public function test_admin_index_filters_by_search_query()
    {
        Poh::create([
            'name' => 'Jakarta Office',
            'code' => 'JKT-01',
            'address' => 'Jakarta Pusat',
            'is_active' => true,
        ]);
        Poh::create([
            'name' => 'Surabaya Office',
            'code' => 'SBY-01',
            'address' => 'Surabaya',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);
        $response = $this->get(route('admin.pohs.index', ['q' => 'Jakarta']));
        $response->assertStatus(200);

        $pohs = $response->viewData('pohs');
        foreach ($pohs as $poh) {
            $this->assertTrue(
                str_contains(strtolower($poh->name), 'jakarta') ||
                str_contains(strtolower($poh->code), 'jakarta') ||
                str_contains(strtolower($poh->address), 'jakarta')
            );
        }
    }

    public function test_admin_create_renders_form()
    {
        $this->actingAs($this->hr);
        $response = $this->get(route('admin.pohs.create'));
        $response->assertStatus(200);
    }

    public function test_admin_store_creates_poh()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.pohs.store'), [
            'name' => 'New POH',
            'code' => 'NPOH-01',
            'address' => 'New Address',
            'description' => 'New Description',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseHas('pohs', [
            'name' => 'New POH',
            'code' => 'NPOH-01',
        ]);
    }

    public function test_admin_store_requires_name()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.pohs.store'), [
            'code' => 'NN-01',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_store_requires_code()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.pohs.store'), [
            'name' => 'No Code POH',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_admin_store_requires_unique_code()
    {
        Poh::create([
            'name' => 'Existing POH',
            'code' => 'UNIQUE-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);

        $response = $this->post(route('admin.pohs.store'), [
            'name' => 'Duplicate POH',
            'code' => 'UNIQUE-01',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_admin_store_accepts_optional_fields()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.pohs.store'), [
            'name' => 'Minimal POH',
            'code' => 'MIN-01',
        ]);

        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseHas('pohs', ['name' => 'Minimal POH']);
    }

    public function test_admin_edit_renders_form()
    {
        $poh = Poh::create([
            'name' => 'Edit POH',
            'code' => 'EDIT-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);
        $response = $this->get(route('admin.pohs.edit', $poh));
        $response->assertStatus(200);
        $response->assertViewHas('poh');
    }

    public function test_admin_update_modifies_poh()
    {
        $poh = Poh::create([
            'name' => 'Original POH',
            'code' => 'ORIG-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);

        $response = $this->put(route('admin.pohs.update', $poh), [
            'name' => 'Updated POH',
            'code' => 'ORIG-01',
            'address' => 'Updated Address',
            'is_active' => false,
        ]);

        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseHas('pohs', [
            'id' => $poh->id,
            'name' => 'Updated POH',
            'address' => 'Updated Address',
        ]);
    }

    public function test_admin_update_keeps_code_unique_allowing_same_code()
    {
        $poh = Poh::create([
            'name' => 'Same Code POH',
            'code' => 'SAME-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);

        $response = $this->put(route('admin.pohs.update', $poh), [
            'name' => 'Updated Name',
            'code' => 'SAME-01',
        ]);

        $response->assertRedirect(route('admin.pohs.index'));
    }

    public function test_admin_update_rejects_duplicate_code()
    {
        Poh::create([
            'name' => 'POH A',
            'code' => 'DUP-01',
            'is_active' => true,
        ]);
        $pohB = Poh::create([
            'name' => 'POH B',
            'code' => 'DUP-02',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);

        $response = $this->put(route('admin.pohs.update', $pohB), [
            'name' => 'Updated POH B',
            'code' => 'DUP-01',
        ]);

        $response->assertSessionHasErrors('code');
    }

    public function test_admin_destroy_deletes_poh()
    {
        $poh = Poh::create([
            'name' => 'Delete POH',
            'code' => 'DEL-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);

        $response = $this->delete(route('admin.pohs.destroy', $poh));

        $response->assertRedirect(route('admin.pohs.index'));
        $this->assertDatabaseMissing('pohs', ['id' => $poh->id]);
    }

    public function test_admin_store_shows_success_message()
    {
        $this->actingAs($this->hr);

        $response = $this->post(route('admin.pohs.store'), [
            'name' => 'Success POH',
            'code' => 'SUC-01',
        ]);

        $response->assertSessionHas('success', 'POH berhasil ditambahkan.');
    }

    public function test_admin_update_shows_success_message()
    {
        $poh = Poh::create([
            'name' => 'Update Msg POH',
            'code' => 'UMSG-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);

        $response = $this->put(route('admin.pohs.update', $poh), [
            'name' => 'Updated Msg POH',
            'code' => 'UMSG-01',
        ]);

        $response->assertSessionHas('success', 'POH berhasil diupdate.');
    }

    public function test_admin_destroy_shows_success_message()
    {
        $poh = Poh::create([
            'name' => 'Delete Msg POH',
            'code' => 'DMSG-01',
            'is_active' => true,
        ]);

        $this->actingAs($this->hr);

        $response = $this->delete(route('admin.pohs.destroy', $poh));

        $response->assertSessionHas('success', 'POH berhasil dihapus.');
    }
}
