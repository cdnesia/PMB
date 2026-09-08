<?php

namespace Tests\Feature\Admin;

use App\Models\CbtJadwal;
use App\Models\Jalur;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtJadwalAjaxCrudTest extends TestCase
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

    public function test_store_with_komposisi_via_ajax(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);

        $response = $this->actingAs($this->admin())->postJson(route('admin.cbt-jadwal.store'), [
            'jalur_id' => $jalur->id,
            'nama' => 'Tes CBT Gelombang 1',
            'durasi_menit' => 60,
            'waktu_mulai' => '2027-01-01 08:00:00',
            'waktu_selesai' => '2027-01-01 17:00:00',
            'komposisi' => [
                ['kategori' => 'Akademik', 'jumlah' => 10, 'jumlah_prodi' => 0],
                ['kategori' => 'Sosial', 'jumlah' => 5, 'jumlah_prodi' => 0],
            ],
            'is_active' => '1',
        ]);

        $response->assertCreated();
        $jadwal = CbtJadwal::where('nama', 'Tes CBT Gelombang 1')->firstOrFail();
        $this->assertCount(2, $jadwal->komposisi);
    }

    public function test_store_with_duplicate_kategori_returns_422(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);

        $response = $this->actingAs($this->admin())->postJson(route('admin.cbt-jadwal.store'), [
            'jalur_id' => $jalur->id,
            'nama' => 'Tes CBT Duplikat',
            'durasi_menit' => 60,
            'waktu_mulai' => '2027-01-01 08:00:00',
            'waktu_selesai' => '2027-01-01 17:00:00',
            'komposisi' => [
                ['kategori' => 'Akademik', 'jumlah' => 10, 'jumlah_prodi' => 0],
                ['kategori' => 'akademik', 'jumlah' => 5, 'jumlah_prodi' => 0],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['komposisi']);
    }
}
