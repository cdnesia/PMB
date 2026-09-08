<?php

namespace Tests\Feature\Admin;

use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\Prodi;
use App\Models\ProdiKelasJalur;
use App\Models\Promo;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoAjaxCrudTest extends TestCase
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

    private function matriks(): array
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);
        $prodi = Prodi::create(['kode' => 'TI', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'is_active' => true]);
        $kelas = KelasPerkuliahan::create(['kode' => 'REG-A', 'nama' => 'Reguler A', 'is_active' => true]);
        ProdiKelasJalur::create(['jalur_id' => $jalur->id, 'prodi_id' => $prodi->id, 'kelas_id' => $kelas->id]);

        return compact('jalur', 'prodi', 'kelas');
    }

    public function test_store_global_promo_via_ajax(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.promo.store'), [
            'kode' => 'GLOBAL50',
            'nama' => 'Promo Global',
            'jenis' => 'pendaftaran',
            'tipe' => 'persen',
            'nilai' => 50,
            'is_global' => '1',
            'is_active' => '1',
        ]);

        $response->assertCreated();
        $promo = Promo::where('kode', 'GLOBAL50')->firstOrFail();
        $this->assertTrue($promo->is_global);
        $this->assertCount(0, $promo->ketentuan);
    }

    public function test_store_non_global_promo_with_ketentuan_via_ajax(): void
    {
        ['jalur' => $jalur, 'prodi' => $prodi, 'kelas' => $kelas] = $this->matriks();

        $response = $this->actingAs($this->admin())->postJson(route('admin.promo.store'), [
            'kode' => 'SPESIFIK',
            'nama' => 'Promo Spesifik',
            'jenis' => 'pendaftaran',
            'tipe' => 'nominal',
            'nilai' => 100000,
            'ketentuan' => [
                ['jalur_id' => $jalur->id, 'prodi_id' => $prodi->id, 'kelas_id' => $kelas->id],
            ],
        ]);

        $response->assertCreated();
        $promo = Promo::where('kode', 'SPESIFIK')->firstOrFail();
        $this->assertCount(1, $promo->ketentuan);
    }

    public function test_store_non_global_without_ketentuan_returns_422(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.promo.store'), [
            'kode' => 'GAGAL',
            'nama' => 'Promo Gagal',
            'jenis' => 'pendaftaran',
            'tipe' => 'persen',
            'nilai' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ketentuan']);
    }

    public function test_store_with_invalid_kombinasi_returns_422(): void
    {
        ['jalur' => $jalur, 'prodi' => $prodi] = $this->matriks();
        $kelasLain = KelasPerkuliahan::create(['kode' => 'REG-B', 'nama' => 'Reguler B', 'is_active' => true]);

        $response = $this->actingAs($this->admin())->postJson(route('admin.promo.store'), [
            'kode' => 'INVALID',
            'nama' => 'Promo Invalid',
            'jenis' => 'pendaftaran',
            'tipe' => 'persen',
            'nilai' => 10,
            'ketentuan' => [
                ['jalur_id' => $jalur->id, 'prodi_id' => $prodi->id, 'kelas_id' => $kelasLain->id],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['ketentuan']);
    }
}
