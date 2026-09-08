<?php

namespace Tests\Feature\Admin;

use App\Models\Gelombang;
use App\Models\Jalur;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GelombangAjaxCrudTest extends TestCase
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

    public function test_store_with_jalur_selection_via_ajax(): void
    {
        $tahun = TahunPenerimaan::create(['kode' => '2027/2028', 'nama' => 'Tahun 2027/2028', 'status' => 'aktif']);
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'is_active' => true]);

        $response = $this->actingAs($this->admin())->postJson(route('admin.gelombang.store'), [
            'tahun_id' => $tahun->id,
            'nama' => 'Gelombang 1',
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-02-01',
            'jalur' => [$jalur->id],
            'is_active' => '1',
        ]);

        $response->assertCreated();
        $gelombang = Gelombang::where('nama', 'Gelombang 1')->firstOrFail();
        $this->assertTrue($gelombang->jalur->contains($jalur));
    }

    public function test_store_with_overlapping_dates_returns_422(): void
    {
        $tahun = TahunPenerimaan::create(['kode' => '2027/2028', 'nama' => 'Tahun 2027/2028', 'status' => 'aktif']);
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'is_active' => true]);

        $existing = Gelombang::create([
            'tahun_id' => $tahun->id,
            'nama' => 'Gelombang 1',
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-02-01',
            'is_active' => true,
        ]);
        $existing->jalur()->sync([$jalur->id]);

        $response = $this->actingAs($this->admin())->postJson(route('admin.gelombang.store'), [
            'tahun_id' => $tahun->id,
            'nama' => 'Gelombang 2 Bentrok',
            'tanggal_mulai' => '2027-01-15',
            'tanggal_selesai' => '2027-03-01',
            'jalur' => [$jalur->id],
            'is_active' => '1',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal_mulai']);
    }
}
