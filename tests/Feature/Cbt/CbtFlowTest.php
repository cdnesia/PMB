<?php

namespace Tests\Feature\Cbt;

use App\Models\CbtJadwal;
use App\Models\CbtSesi;
use App\Models\CbtSoal;
use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\Pendaftaran;
use App\Models\PendaftaranProdi;
use App\Models\Prodi;
use App\Models\TahunPenerimaan;
use App\Models\User;
use App\Services\CbtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CbtFlowTest extends TestCase
{
    use RefreshDatabase;

    private function buatPendaftaran(Jalur $jalur, int $noUrut = 1): Pendaftaran
    {
        $tahun = TahunPenerimaan::firstOrCreate(['kode' => '2026/2027'], ['nama' => 'Tahun 2026/2027']);

        return Pendaftaran::forceCreate([
            'no_urut' => $noUrut,
            'user_id' => User::factory()->create()->id,
            'tahun_id' => $tahun->id,
            'jalur_id' => $jalur->id,
            'status' => 'lunas',
            'status_pembayaran' => 'lunas',
        ]);
    }

    private function buatSoal(Jalur $jalur, int $jumlah, string $kunci = 'a', string $kategori = 'Akademik', ?string $prodiId = null): void
    {
        for ($i = 0; $i < $jumlah; $i++) {
            CbtSoal::create([
                'jalur_id' => $jalur->id,
                'prodi_id' => $prodiId,
                'kategori' => $kategori,
                'pertanyaan' => "Soal ke-{$i}",
                'pilihan_a' => 'A', 'pilihan_b' => 'B', 'pilihan_c' => 'C', 'pilihan_d' => 'D',
                'kunci_jawaban' => $kunci,
                'bobot' => 1,
                'is_active' => true,
            ]);
        }
    }

    private function buatJadwal(Jalur $jalur, array $komposisi, ?string $prodiId = null, int $durasiMenit = 30): CbtJadwal
    {
        $jadwal = CbtJadwal::create([
            'jalur_id' => $jalur->id,
            'prodi_id' => $prodiId,
            'nama' => 'Tes Demo',
            'durasi_menit' => $durasiMenit,
            'waktu_mulai' => now()->subHour(),
            'waktu_selesai' => now()->addHour(),
            'is_active' => true,
        ]);

        $jadwal->komposisi()->createMany($komposisi);

        return $jadwal;
    }

    public function test_mulai_jawab_dan_submit_menghasilkan_skor_yang_benar(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $this->buatSoal($jalur, 4, 'a');
        $pendaftaran = $this->buatPendaftaran($jalur);

        $jadwal = $this->buatJadwal($jalur, [['kategori' => 'Akademik', 'jumlah' => 4]]);

        $cbt = app(CbtService::class);
        $sesi = $cbt->mulai($pendaftaran, $jadwal);

        $this->assertCount(4, $sesi->soal_urutan);

        $soalList = CbtSoal::whereIn('id', $sesi->soal_urutan)->get();

        // Jawab 3 dari 4 soal dengan benar, 1 dibiarkan kosong.
        foreach ($soalList->take(3) as $soal) {
            $cbt->simpanJawaban($sesi, $soal, 'a', false);
        }

        $sesi = $cbt->finalisasi($sesi->fresh(), 'submit');

        $this->assertSame('selesai', $sesi->status);
        $this->assertSame(3, $sesi->jumlah_benar);
        $this->assertSame(0, $sesi->jumlah_salah);
        $this->assertSame(1, $sesi->jumlah_kosong);
        $this->assertEqualsWithDelta(75.0, (float) $sesi->skor, 0.01);

        $pendaftaran->refresh();
        $this->assertEqualsWithDelta(75.0, (float) $pendaftaran->nilai_seleksi, 0.01);
    }

    public function test_hanya_satu_kesempatan_per_jadwal(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $this->buatSoal($jalur, 2);
        $pendaftaran = $this->buatPendaftaran($jalur);

        $jadwal = $this->buatJadwal($jalur, [['kategori' => 'Akademik', 'jumlah' => 2]]);

        $cbt = app(CbtService::class);
        $cbt->mulai($pendaftaran, $jadwal);

        $this->expectException(ValidationException::class);
        $cbt->mulai($pendaftaran, $jadwal);
    }

    public function test_tutup_sesi_kedaluwarsa_menilai_otomatis_sesi_yang_lewat_deadline(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $this->buatSoal($jalur, 2, 'a');
        $pendaftaranTelat = $this->buatPendaftaran($jalur, 1);
        $pendaftaranMasihJalan = $this->buatPendaftaran($jalur, 2);

        $jadwal = $this->buatJadwal($jalur, [['kategori' => 'Akademik', 'jumlah' => 2]]);

        $sesiTelat = CbtSesi::create([
            'cbt_jadwal_id' => $jadwal->id,
            'pendaftaran_id' => $pendaftaranTelat->id,
            'status' => 'berlangsung',
            'soal_urutan' => CbtSoal::pluck('id')->all(),
            'started_at' => now()->subHours(2),
            'deadline_at' => now()->subMinute(),
        ]);

        $sesiMasihJalan = CbtSesi::create([
            'cbt_jadwal_id' => $jadwal->id,
            'pendaftaran_id' => $pendaftaranMasihJalan->id,
            'status' => 'berlangsung',
            'soal_urutan' => CbtSoal::pluck('id')->all(),
            'started_at' => now(),
            'deadline_at' => now()->addMinutes(20),
        ]);

        $total = app(CbtService::class)->tutupSesiKedaluwarsa();

        $this->assertSame(1, $total);
        $this->assertSame('selesai', $sesiTelat->fresh()->status);
        $this->assertSame('auto_timeout', $sesiTelat->fresh()->finish_reason);
        $this->assertSame('berlangsung', $sesiMasihJalan->fresh()->status);
    }

    public function test_soal_diambil_sesuai_jumlah_per_kategori_pada_komposisi(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $this->buatSoal($jalur, 6, 'a', 'Akademik');
        $this->buatSoal($jalur, 6, 'a', 'Sosial');
        $pendaftaran = $this->buatPendaftaran($jalur);

        $jadwal = $this->buatJadwal($jalur, [
            ['kategori' => 'Akademik', 'jumlah' => 4],
            ['kategori' => 'Sosial', 'jumlah' => 3],
        ]);

        $sesi = app(CbtService::class)->mulai($pendaftaran, $jadwal);

        $this->assertCount(7, $sesi->soal_urutan);

        $kategoriTerpilih = CbtSoal::whereIn('id', $sesi->soal_urutan)->pluck('kategori');
        $this->assertSame(4, $kategoriTerpilih->filter(fn ($k) => $k === 'Akademik')->count());
        $this->assertSame(3, $kategoriTerpilih->filter(fn ($k) => $k === 'Sosial')->count());
    }

    public function test_jadwal_khusus_prodi_mengambil_soal_umum_ditambah_soal_khusus_prodi(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $prodiAnestesi = Prodi::create(['kode' => 'ANES', 'nama' => 'Anestesi']);

        // 4 soal umum Akademik + 2 soal khusus prodi Anestesi.
        $this->buatSoal($jalur, 4, 'a', 'Akademik');
        $this->buatSoal($jalur, 2, 'a', 'Akademik', $prodiAnestesi->id);

        // Jadwal menargetkan prodi Anestesi secara eksplisit: 4 umum + 2 khusus prodi = 6 soal.
        $jadwal = $this->buatJadwal($jalur, [['kategori' => 'Akademik', 'jumlah' => 4, 'jumlah_prodi' => 2]], $prodiAnestesi->id);
        $pendaftaran = $this->buatPendaftaran($jalur);

        $sesi = app(CbtService::class)->mulai($pendaftaran, $jadwal);

        $this->assertCount(6, $sesi->soal_urutan);
        $jumlahSoalProdi = CbtSoal::whereIn('id', $sesi->soal_urutan)->whereNotNull('prodi_id')->count();
        $this->assertSame(2, $jumlahSoalProdi, 'Kedua soal khusus Anestesi harus ikut, bukan diacak/opsional.');
    }

    public function test_jadwal_umum_tidak_pernah_mengambil_soal_khusus_prodi(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $prodiAnestesi = Prodi::create(['kode' => 'ANES', 'nama' => 'Anestesi']);

        $this->buatSoal($jalur, 4, 'a', 'Akademik');
        $this->buatSoal($jalur, 2, 'a', 'Akademik', $prodiAnestesi->id);

        // Jadwal umum (prodi_id null) — jumlah_prodi diabaikan sepenuhnya, bukan cuma dilewati.
        $jadwal = $this->buatJadwal($jalur, [['kategori' => 'Akademik', 'jumlah' => 4]]);
        $pendaftaran = $this->buatPendaftaran($jalur);

        $sesi = app(CbtService::class)->mulai($pendaftaran, $jadwal);

        $this->assertCount(4, $sesi->soal_urutan);
        $jumlahSoalProdi = CbtSoal::whereIn('id', $sesi->soal_urutan)->whereNotNull('prodi_id')->count();
        $this->assertSame(0, $jumlahSoalProdi);
    }

    public function test_jadwal_khusus_prodi_dengan_bank_kurang_menolak_dengan_error(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $prodiTanpaBank = Prodi::create(['kode' => 'TI', 'nama' => 'Informatika']);

        $this->buatSoal($jalur, 4, 'a', 'Akademik');
        // Prodi ini sama sekali tidak punya bank soal khusus, tapi admin salah mengatur jumlah_prodi=2.

        $jadwal = $this->buatJadwal($jalur, [['kategori' => 'Akademik', 'jumlah' => 4, 'jumlah_prodi' => 2]], $prodiTanpaBank->id);
        $pendaftaran = $this->buatPendaftaran($jalur);

        $this->expectException(ValidationException::class);
        app(CbtService::class)->mulai($pendaftaran, $jadwal);
    }

    public function test_jadwal_berlaku_memprioritaskan_jadwal_khusus_prodi_lalu_jatuh_ke_jadwal_umum(): void
    {
        $jalur = Jalur::create(['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'requires_cbt' => true]);
        $prodiAnestesi = Prodi::create(['kode' => 'ANES', 'nama' => 'Anestesi']);
        $prodiLain = Prodi::create(['kode' => 'TI', 'nama' => 'Informatika']);
        $kelas = KelasPerkuliahan::create(['kode' => 'REGA', 'nama' => 'Reguler A']);

        $jadwalUmum = $this->buatJadwal($jalur, [['kategori' => 'Akademik', 'jumlah' => 1]]);
        $jadwalAnestesi = $this->buatJadwal($jalur, [['kategori' => 'Akademik', 'jumlah' => 1]], $prodiAnestesi->id);

        $cbt = app(CbtService::class);

        $pendaftaranAnestesi = $this->buatPendaftaran($jalur, 1);
        PendaftaranProdi::create(['pendaftaran_id' => $pendaftaranAnestesi->id, 'urutan' => 1, 'prodi_id' => $prodiAnestesi->id, 'kelas_id' => $kelas->id]);
        $this->assertSame($jadwalAnestesi->id, $cbt->jadwalBerlaku($pendaftaranAnestesi)?->id);

        $pendaftaranLain = $this->buatPendaftaran($jalur, 2);
        PendaftaranProdi::create(['pendaftaran_id' => $pendaftaranLain->id, 'urutan' => 1, 'prodi_id' => $prodiLain->id, 'kelas_id' => $kelas->id]);
        $this->assertSame($jadwalUmum->id, $cbt->jadwalBerlaku($pendaftaranLain)?->id);
    }
}
