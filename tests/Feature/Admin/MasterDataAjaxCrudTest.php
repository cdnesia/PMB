<?php

namespace Tests\Feature\Admin;

use App\Models\KelasPerkuliahan;
use App\Models\SumberInformasi;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cakupan AJAX CRUD untuk resource master data yang bentuk formnya flat
 * (tanpa relasi bersarang): Tahun Penerimaan, Kelas Perkuliahan, Sumber
 * Informasi. Pola sama seperti ProdiAjaxCrudTest, jadi di sini cukup satu
 * kasus sukses + satu kasus validasi gagal per resource.
 */
class MasterDataAjaxCrudTest extends TestCase
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

    public function test_tahun_store_and_update_via_ajax(): void
    {
        $admin = $this->admin();

        $store = $this->actingAs($admin)->postJson(route('admin.tahun.store'), [
            'kode' => '2027/2028',
            'nama' => 'Tahun 2027/2028',
            'status' => 'draft',
        ]);
        $store->assertCreated();
        $tahun = TahunPenerimaan::where('kode', '2027/2028')->firstOrFail();

        $update = $this->actingAs($admin)->putJson(route('admin.tahun.update', $tahun), [
            'kode' => '2027/2028',
            'nama' => 'Tahun 2027/2028 Diperbarui',
            'status' => 'aktif',
        ]);
        $update->assertOk();
        $this->assertDatabaseHas('tahun_penerimaan', ['id' => $tahun->id, 'status' => 'aktif']);
    }

    public function test_tahun_store_invalid_returns_422(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.tahun.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kode', 'nama', 'status']);
    }

    public function test_kelas_store_and_destroy_via_ajax(): void
    {
        $admin = $this->admin();

        $store = $this->actingAs($admin)->postJson(route('admin.kelas.store'), [
            'kode' => 'REG-C',
            'nama' => 'Reguler C',
            'is_active' => '1',
        ]);
        $store->assertCreated();
        $kelas = KelasPerkuliahan::where('kode', 'REG-C')->firstOrFail();

        $destroy = $this->actingAs($admin)->deleteJson(route('admin.kelas.destroy', $kelas));
        $destroy->assertOk();
        $this->assertDatabaseMissing('kelas_perkuliahan', ['id' => $kelas->id]);
    }

    public function test_kelas_store_invalid_returns_422(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.kelas.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kode', 'nama']);
    }

    public function test_sumber_informasi_store_and_update_via_ajax(): void
    {
        $admin = $this->admin();

        $store = $this->actingAs($admin)->postJson(route('admin.sumber-informasi.store'), [
            'kode' => 'TIKTOK',
            'nama' => 'TikTok',
            'urutan' => 5,
        ]);
        $store->assertCreated();
        $sumber = SumberInformasi::where('kode', 'TIKTOK')->firstOrFail();

        $update = $this->actingAs($admin)->putJson(route('admin.sumber-informasi.update', $sumber), [
            'kode' => 'TIKTOK',
            'nama' => 'TikTok Ads',
            'urutan' => 1,
            'is_active' => '0',
        ]);
        $update->assertOk();
        $this->assertDatabaseHas('sumber_informasi', ['id' => $sumber->id, 'nama' => 'TikTok Ads', 'is_active' => false]);
    }

    public function test_sumber_informasi_store_invalid_returns_422(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.sumber-informasi.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kode', 'nama']);
    }
}
