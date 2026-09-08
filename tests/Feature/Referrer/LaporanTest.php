<?php

namespace Tests\Feature\Referrer;

use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\Referrer;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function buatData(Referrer $referrer, Jalur $jalur, TahunPenerimaan $tahun, string $nomor, string $namaMahasiswa): void
    {
        Pendaftaran::forceCreate([
            'no_urut' => random_int(1, 999999),
            'user_id' => User::factory()->create(['name' => $namaMahasiswa])->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'referrer_id' => $referrer->id,
            'nomor_pendaftaran' => $nomor,
            'status' => 'lolos',
            'status_pembayaran' => 'lunas',
        ]);
    }

    public function test_laporan_shows_all_years_by_default(): void
    {
        $referrerUser = User::factory()->create();
        $referrerUser->assignRole('mitra');
        $referrer = Referrer::factory()->mitra()->create(['user_id' => $referrerUser->id]);

        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);
        $tahunBaru = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027', 'status' => 'aktif']);
        $tahunLama = TahunPenerimaan::create(['kode' => '2025/2026', 'nama' => 'Tahun 2025/2026', 'status' => 'nonaktif']);

        $this->buatData($referrer, $jalur, $tahunBaru, 'PMB-BARU-00001', 'Rian Terkini');
        $this->buatData($referrer, $jalur, $tahunLama, 'PMB-LAMA-00001', 'Mahasiswa Lama');

        $response = $this->actingAs($referrerUser)->get(route('referrer.laporan.index'));

        $response->assertOk();
        $response->assertSee('Rian Terkini');
        $response->assertSee('Mahasiswa Lama');
    }

    public function test_laporan_can_be_filtered_by_tahun_penerimaan(): void
    {
        $referrerUser = User::factory()->create();
        $referrerUser->assignRole('mitra');
        $referrer = Referrer::factory()->mitra()->create(['user_id' => $referrerUser->id]);

        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);
        $tahunBaru = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027', 'status' => 'aktif']);
        $tahunLama = TahunPenerimaan::create(['kode' => '2025/2026', 'nama' => 'Tahun 2025/2026', 'status' => 'nonaktif']);

        $this->buatData($referrer, $jalur, $tahunBaru, 'PMB-BARU-00001', 'Rian Terkini');
        $this->buatData($referrer, $jalur, $tahunLama, 'PMB-LAMA-00001', 'Mahasiswa Lama');

        $response = $this->actingAs($referrerUser)->get(route('referrer.laporan.index', ['tahun_id' => $tahunLama->id]));

        $response->assertOk();
        $response->assertSee('Mahasiswa Lama');
        $response->assertDontSee('Rian Terkini');
    }

    public function test_mahasiswa_cannot_access_referrer_laporan(): void
    {
        $mahasiswa = User::factory()->create();
        $mahasiswa->assignRole('mahasiswa');

        $response = $this->actingAs($mahasiswa)->get(route('referrer.laporan.index'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('referrer.laporan.index'));

        $response->assertRedirect(route('login'));
    }
}
