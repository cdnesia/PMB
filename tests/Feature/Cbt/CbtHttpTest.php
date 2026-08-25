<?php

namespace Tests\Feature\Cbt;

use App\Models\CbtJadwal;
use App\Models\CbtSoal;
use App\Models\Jalur;
use App\Models\Pendaftaran;
use App\Models\Prodi;
use App\Models\TahunPenerimaan;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CbtHttpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function siapkanJadwal(): CbtJadwal
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);

        for ($i = 0; $i < 3; $i++) {
            CbtSoal::create([
                'jalur_id' => $jalur->id,
                'kategori' => 'Akademik',
                'pertanyaan' => "Soal ke-{$i}",
                'pilihan_a' => 'A', 'pilihan_b' => 'B', 'pilihan_c' => 'C', 'pilihan_d' => 'D',
                'kunci_jawaban' => 'a',
                'bobot' => 1,
                'is_active' => true,
            ]);
        }

        $jadwal = CbtJadwal::create([
            'jalur_id' => $jalur->id,
            'nama' => 'Tes Demo',
            'durasi_menit' => 30,
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->addHour(),
            'is_active' => true,
        ]);

        $jadwal->komposisi()->create(['kategori' => 'Akademik', 'jumlah' => 3]);

        return $jadwal;
    }

    public function test_admin_bisa_membuka_halaman_bank_soal_dan_jadwal_cbt(): void
    {
        $admin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        $this->siapkanJadwal();

        $this->actingAs($admin)->get(route('admin.cbt-soal.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.cbt-soal.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.cbt-jadwal.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.cbt-jadwal.create'))->assertOk();
    }

    public function test_menu_jadwal_cbt_di_sidebar_aktif_di_halaman_jadwal_maupun_peserta(): void
    {
        $admin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        $jadwal = $this->siapkanJadwal();

        foreach ([
            route('admin.cbt-jadwal.index'),
            route('admin.cbt-jadwal.create'),
            route('admin.cbt-jadwal.peserta', $jadwal),
        ] as $url) {
            $html = $this->actingAs($admin)->get($url)->getContent();

            preg_match('/<a[^>]*href="'.preg_quote(route('admin.cbt-jadwal.index'), '/').'"[^>]*class="([^"]*)"/', $html, $matches);

            $this->assertNotEmpty($matches, "Link menu Jadwal CBT tidak ditemukan di halaman {$url}");
            $this->assertStringContainsString('bg-indigo-600', $matches[1], "Menu Jadwal CBT harus tersorot aktif di halaman {$url}");
        }
    }

    public function test_mahasiswa_bisa_mulai_mengerjakan_dan_mengumpulkan_ujian_lewat_http(): void
    {
        $jadwal = $this->siapkanJadwal();

        $mahasiswa = User::factory()->create();
        $mahasiswa->assignRole('mahasiswa');

        $tahun = TahunPenerimaan::create(['kode' => '2026/2027', 'nama' => 'Tahun 2026/2027']);
        $pendaftaran = Pendaftaran::forceCreate([
            'no_urut' => 1,
            'user_id' => $mahasiswa->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jadwal->jalur_id,
            'status' => 'lunas',
            'status_pembayaran' => 'lunas',
        ]);

        $this->actingAs($mahasiswa)->get(route('mahasiswa.cbt.index'))->assertOk();

        $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.cbt.mulai', $pendaftaran))
            ->assertRedirect();

        $sesi = $pendaftaran->cbtSesi()->firstOrFail();

        $this->actingAs($mahasiswa)->get(route('mahasiswa.cbt.ujian', $sesi))->assertOk();

        $soal = CbtSoal::where('jalur_id', $jadwal->jalur_id)->first();
        $this->actingAs($mahasiswa)
            ->postJson(route('mahasiswa.cbt.jawab', $sesi), [
                'cbt_soal_id' => $soal->id,
                'jawaban' => 'a',
                'ragu_ragu' => false,
            ])
            ->assertOk();

        $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.cbt.submit', $sesi))
            ->assertRedirect(route('mahasiswa.cbt.index'));

        $this->assertSame('selesai', $sesi->fresh()->status);
        $this->assertNotNull($pendaftaran->fresh()->nilai_seleksi);
    }

    public function test_form_jadwal_komposisi_kategori_berupa_dropdown_pilihan_bukan_teks_bebas(): void
    {
        $admin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        $this->siapkanJadwal(); // menyiapkan 1 soal berkategori "Akademik"

        $html = $this->actingAs($admin)->get(route('admin.cbt-jadwal.create'))->getContent();

        $this->assertMatchesRegularExpression('/<select[^>]*x-model="row\.kategori"/', $html, 'Kategori komposisi harus berupa <select>, bukan input teks bebas.');
        $this->assertStringContainsString('>Akademik</option>', $html, 'Kategori yang sudah ada di bank soal harus muncul sebagai pilihan.');
    }

    public function test_x_data_komposisi_jadwal_berisi_json_valid_bukan_direktif_blade_mentah(): void
    {
        $admin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        $this->siapkanJadwal();

        $html = $this->actingAs($admin)->get(route('admin.cbt-jadwal.create'))->getContent();

        $this->assertStringNotContainsString('@js(', $html, 'Direktif @js tidak boleh muncul mentah di HTML — berarti x-data gagal dikompilasi dan Alpine tidak akan berjalan (mis. tombol Tambah Kategori tidak berfungsi).');
    }

    public function test_admin_bisa_membuat_jadwal_dengan_komposisi_beberapa_kategori(): void
    {
        $admin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);

        $response = $this->actingAs($admin)->post(route('admin.cbt-jadwal.store'), [
            'jalur_id' => $jalur->id,
            'nama' => 'Tes CBT Gelombang 1',
            'durasi_menit' => 60,
            'waktu_mulai' => now()->addDay()->format('Y-m-d\TH:i'),
            'waktu_selesai' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'is_active' => '1',
            'komposisi' => [
                ['kategori' => 'Akademik', 'jumlah' => 10],
                ['kategori' => 'Sosial', 'jumlah' => 10],
            ],
        ]);

        $response->assertRedirect(route('admin.cbt-jadwal.index'));

        $jadwal = CbtJadwal::where('nama', 'Tes CBT Gelombang 1')->firstOrFail();
        $this->assertSame(20, $jadwal->totalSoal());
        $this->assertSame(10, $jadwal->komposisi()->where('kategori', 'Akademik')->value('jumlah'));
        $this->assertSame(10, $jadwal->komposisi()->where('kategori', 'Sosial')->value('jumlah'));
    }

    public function test_admin_bisa_membuat_jadwal_khusus_prodi_dengan_kuota_tambahan(): void
    {
        $admin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $prodi = Prodi::create(['kode' => 'ANES', 'nama' => 'Anestesi']);

        $response = $this->actingAs($admin)->post(route('admin.cbt-jadwal.store'), [
            'jalur_id' => $jalur->id,
            'prodi_id' => $prodi->id,
            'nama' => 'Tes CBT Anestesi',
            'durasi_menit' => 60,
            'waktu_mulai' => now()->addDay()->format('Y-m-d\TH:i'),
            'waktu_selesai' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'is_active' => '1',
            'komposisi' => [
                ['kategori' => 'Akademik', 'jumlah' => 4, 'jumlah_prodi' => 2],
            ],
        ]);

        $response->assertRedirect(route('admin.cbt-jadwal.index'));

        $jadwal = CbtJadwal::where('nama', 'Tes CBT Anestesi')->firstOrFail();
        $this->assertSame($prodi->id, $jadwal->prodi_id);
        $this->assertSame(2, (int) $jadwal->komposisi()->value('jumlah_prodi'));
    }

    public function test_kuota_khusus_prodi_ditolak_jika_jadwal_tidak_menargetkan_prodi(): void
    {
        $admin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);

        $response = $this->actingAs($admin)->post(route('admin.cbt-jadwal.store'), [
            'jalur_id' => $jalur->id,
            'nama' => 'Tes CBT Salah Konfigurasi',
            'durasi_menit' => 60,
            'waktu_mulai' => now()->addDay()->format('Y-m-d\TH:i'),
            'waktu_selesai' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'is_active' => '1',
            'komposisi' => [
                ['kategori' => 'Akademik', 'jumlah' => 4, 'jumlah_prodi' => 2],
            ],
        ]);

        $response->assertSessionHasErrors('prodi_id');
        $this->assertDatabaseMissing('cbt_jadwal', ['nama' => 'Tes CBT Salah Konfigurasi']);
    }

    public function test_admin_bisa_membuat_soal_khusus_prodi(): void
    {
        $admin = User::where('email', 'adminpmb@pmb.test')->firstOrFail();
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $prodi = Prodi::create(['kode' => 'ANES', 'nama' => 'Anestesi']);

        $response = $this->actingAs($admin)->post(route('admin.cbt-soal.store'), [
            'jalur_id' => $jalur->id,
            'prodi_id' => $prodi->id,
            'kategori' => 'Akademik',
            'pertanyaan' => 'Contoh soal khusus Anestesi?',
            'pilihan_a' => 'A', 'pilihan_b' => 'B', 'pilihan_c' => 'C', 'pilihan_d' => 'D',
            'kunci_jawaban' => 'a',
            'bobot' => 1,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.cbt-soal.index'));

        $this->assertDatabaseHas('cbt_soal', [
            'prodi_id' => $prodi->id,
            'kategori' => 'Akademik',
        ]);
    }
}
