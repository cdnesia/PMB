<?php

namespace Tests\Feature;

use App\Models\Jalur;
use App\Models\PembayaranPendaftaran;
use App\Models\Pendaftaran;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DokumenControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    private function buatPembayaran(): PembayaranPendaftaran
    {
        $pemilik = User::factory()->create();
        $pemilik->assignRole('mahasiswa');

        $tahun = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027', 'status' => 'aktif']);
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler']);

        $pendaftaran = Pendaftaran::forceCreate([
            'no_urut' => random_int(1, 999999),
            'user_id' => $pemilik->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'nomor_pendaftaran' => 'PMB-DOK-'.random_int(1, 999999),
        ]);

        Storage::disk('local')->put('pembayaran_pendaftaran/bukti-rahasia.jpg', 'isi-file-rahasia');

        return PembayaranPendaftaran::create([
            'pendaftaran_id' => $pendaftaran->id,
            'nominal' => 100000,
            'bukti_bayar' => 'pembayaran_pendaftaran/bukti-rahasia.jpg',
            'file_name' => 'bukti.jpg',
        ]);
    }

    public function test_guest_cannot_access_document(): void
    {
        $pembayaran = $this->buatPembayaran();

        $response = $this->get(route('dokumen.pembayaran', $pembayaran));

        $response->assertRedirect(route('login'));
    }

    public function test_owner_can_view_their_own_document(): void
    {
        $pembayaran = $this->buatPembayaran();
        $pemilik = $pembayaran->pendaftaran->user;

        $response = $this->actingAs($pemilik)->get(route('dokumen.pembayaran', $pembayaran));

        $response->assertOk();
    }

    public function test_other_mahasiswa_cannot_view_someone_elses_document(): void
    {
        $pembayaran = $this->buatPembayaran();

        $lain = User::factory()->create();
        $lain->assignRole('mahasiswa');

        $response = $this->actingAs($lain)->get(route('dokumen.pembayaran', $pembayaran));

        $response->assertForbidden();
    }

    public function test_admin_can_view_any_document(): void
    {
        $pembayaran = $this->buatPembayaran();

        $admin = User::where('email', 'admin@pmb.test')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('dokumen.pembayaran', $pembayaran));

        $response->assertOk();
    }

    public function test_missing_file_returns_404(): void
    {
        $pembayaran = $this->buatPembayaran();
        Storage::disk('local')->delete($pembayaran->bukti_bayar);

        $response = $this->actingAs($pembayaran->pendaftaran->user)->get(route('dokumen.pembayaran', $pembayaran));

        $response->assertNotFound();
    }
}
