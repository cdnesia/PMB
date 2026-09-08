<?php

namespace Tests\Feature\Admin;

use App\Models\DokumenPersyaratan;
use App\Models\Jalur;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DokumenAjaxCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@pmb.test')->firstOrFail();
    }

    public function test_store_multiple_rows_at_once_via_ajax(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);

        $response = $this->actingAs($this->admin())->postJson(route('admin.dokumen.store'), [
            'jalur_id' => $jalur->id,
            'dokumen' => [
                ['nama' => 'Kartu Keluarga', 'wajib' => '1'],
                ['nama' => 'Ijazah', 'wajib' => '1'],
                ['nama' => 'Foto', 'wajib' => '0'],
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseCount('dokumen_persyaratan', 3);
        $this->assertDatabaseHas('dokumen_persyaratan', ['nama' => 'Kartu Keluarga', 'wajib' => true]);
        $this->assertDatabaseHas('dokumen_persyaratan', ['nama' => 'Foto', 'wajib' => false]);
    }

    public function test_store_without_jalur_or_prodi_returns_422(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.dokumen.store'), [
            'dokumen' => [['nama' => 'Kartu Keluarga', 'wajib' => '1']],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jalur_id']);
    }

    public function test_update_single_row_via_ajax(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);
        $dokumen = DokumenPersyaratan::create([
            'jalur_id' => $jalur->id,
            'nama' => 'Kartu Keluarga',
            'wajib' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())->putJson(route('admin.dokumen.update', $dokumen), [
            'jalur_id' => $jalur->id,
            'nama' => 'Kartu Keluarga (scan berwarna)',
            'wajib' => '0',
            'is_active' => '1',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('dokumen_persyaratan', [
            'id' => $dokumen->id,
            'nama' => 'Kartu Keluarga (scan berwarna)',
            'wajib' => false,
        ]);
    }
}
