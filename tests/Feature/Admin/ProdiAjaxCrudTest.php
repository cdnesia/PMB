<?php

namespace Tests\Feature\Admin;

use App\Models\Prodi;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProdiAjaxCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_store_via_ajax_returns_json(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->postJson(route('admin.prodi.store'), [
            'kode' => 'TI',
            'nama' => 'Teknik Informatika',
            'jenjang' => 'S1',
            'fakultas' => 'Teknik',
            'is_active' => '1',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['message']);
        $this->assertDatabaseHas('prodi', ['kode' => 'TI']);
    }

    public function test_store_via_ajax_with_invalid_data_returns_422_json(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->postJson(route('admin.prodi.store'), [
            'nama' => 'Tanpa kode',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kode', 'jenjang']);
    }

    public function test_update_via_ajax_returns_json(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $prodi = Prodi::create(['kode' => 'TI', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'is_active' => true]);

        $response = $this->actingAs($admin)->putJson(route('admin.prodi.update', $prodi), [
            'kode' => 'TI2',
            'nama' => 'Teknik Informatika Baru',
            'jenjang' => 'S2',
            'is_active' => '0',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('prodi', ['id' => $prodi->id, 'kode' => 'TI2', 'is_active' => false]);
    }

    public function test_destroy_via_ajax_returns_json(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();
        $prodi = Prodi::create(['kode' => 'TI', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'is_active' => true]);

        $response = $this->actingAs($admin)->deleteJson(route('admin.prodi.destroy', $prodi));

        $response->assertOk();
        $this->assertDatabaseMissing('prodi', ['id' => $prodi->id]);
    }

    public function test_store_without_ajax_still_redirects(): void
    {
        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.prodi.store'), [
            'kode' => 'TI',
            'nama' => 'Teknik Informatika',
            'jenjang' => 'S1',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.prodi.index'));
        $this->assertDatabaseHas('prodi', ['kode' => 'TI']);
    }
}
