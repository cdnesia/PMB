<?php

namespace Tests\Feature\Admin;

use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JalurAjaxCrudTest extends TestCase
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

    public function test_store_with_biaya_kelas_and_syarat_via_ajax(): void
    {
        $kelas = KelasPerkuliahan::create(['kode' => 'REG-A', 'nama' => 'Reguler A', 'is_active' => true]);

        $response = $this->actingAs($this->admin())->postJson(route('admin.jalur.store'), [
            'kode' => 'REGULER',
            'nama' => 'Jalur Reguler',
            'kategori' => 'nasional',
            'urutan' => 1,
            'biaya_kelas' => [$kelas->id => 150000],
            'requires_cbt' => '1',
            'is_active' => '1',
            'syarat' => [
                ['tipe' => 'field', 'nama' => 'Nomor KIPK', 'kode' => 'nomor_kipk', 'wajib' => '1'],
            ],
        ]);

        $response->assertCreated();
        $jalur = Jalur::where('kode', 'REGULER')->firstOrFail();
        $this->assertDatabaseHas('jalur_kelas', ['jalur_id' => $jalur->id, 'kelas_id' => $kelas->id, 'biaya_pendaftaran' => 150000]);
        $this->assertDatabaseHas('syarat_jalur', ['jalur_id' => $jalur->id, 'kode' => 'nomor_kipk', 'wajib' => true]);
    }

    public function test_store_invalid_returns_422(): void
    {
        $response = $this->actingAs($this->admin())->postJson(route('admin.jalur.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kode', 'nama', 'kategori']);
    }

    public function test_update_replaces_syarat_via_ajax(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kategori' => 'nasional']);
        $jalur->syarat()->create(['tipe' => 'field', 'kode' => 'lama', 'nama' => 'Syarat Lama', 'wajib' => true, 'is_active' => true]);

        $response = $this->actingAs($this->admin())->putJson(route('admin.jalur.update', $jalur), [
            'kode' => 'REGULER',
            'nama' => 'Jalur Reguler',
            'kategori' => 'nasional',
            'syarat' => [
                ['tipe' => 'field', 'nama' => 'Syarat Baru', 'kode' => 'baru', 'wajib' => '0'],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('syarat_jalur', ['jalur_id' => $jalur->id, 'kode' => 'lama']);
        $this->assertDatabaseHas('syarat_jalur', ['jalur_id' => $jalur->id, 'kode' => 'baru']);
    }
}
