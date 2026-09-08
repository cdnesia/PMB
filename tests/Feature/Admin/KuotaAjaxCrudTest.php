<?php

namespace Tests\Feature\Admin;

use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\Kuota;
use App\Models\Prodi;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KuotaAjaxCrudTest extends TestCase
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

    private function fixture(): array
    {
        return [
            'tahun' => TahunPenerimaan::create(['kode' => '2027/2028', 'nama' => 'Tahun 2027/2028', 'status' => 'aktif']),
            'jalur' => Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']),
            'prodi' => Prodi::create(['kode' => 'TI', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'is_active' => true]),
            'kelas' => KelasPerkuliahan::create(['kode' => 'REG-A', 'nama' => 'Reguler A', 'is_active' => true]),
        ];
    }

    public function test_store_via_ajax(): void
    {
        ['tahun' => $tahun, 'jalur' => $jalur, 'prodi' => $prodi, 'kelas' => $kelas] = $this->fixture();

        $response = $this->actingAs($this->admin())->postJson(route('admin.kuota.store'), [
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'jumlah' => 40,
            'is_active' => '1',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('kuota', ['tahun_id' => $tahun->id, 'prodi_id' => $prodi->id, 'jumlah' => 40, 'terpakai' => 0]);
    }

    public function test_update_with_jumlah_below_terpakai_returns_422(): void
    {
        ['tahun' => $tahun, 'jalur' => $jalur, 'prodi' => $prodi, 'kelas' => $kelas] = $this->fixture();

        $kuota = Kuota::create([
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'jumlah' => 40,
            'terpakai' => 10,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())->putJson(route('admin.kuota.update', $kuota), [
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'jumlah' => 5,
            'is_active' => '1',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['jumlah']);
        $this->assertDatabaseHas('kuota', ['id' => $kuota->id, 'jumlah' => 40]);
    }

    public function test_destroy_via_ajax(): void
    {
        ['tahun' => $tahun, 'jalur' => $jalur, 'prodi' => $prodi, 'kelas' => $kelas] = $this->fixture();

        $kuota = Kuota::create([
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'prodi_id' => $prodi->id,
            'kelas_id' => $kelas->id,
            'jumlah' => 40,
            'terpakai' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())->deleteJson(route('admin.kuota.destroy', $kuota));

        $response->assertOk();
        $this->assertDatabaseMissing('kuota', ['id' => $kuota->id]);
    }
}
