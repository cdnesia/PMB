<?php

namespace Database\Seeders;

use App\Models\CbtJadwal;
use App\Models\CbtSoal;
use App\Models\Jalur;
use App\Models\JalurKelas;
use App\Models\KelasPerkuliahan;
use App\Models\Kuota;
use App\Models\Prodi;
use App\Models\ProdiKelasJalur;
use App\Models\TahunPenerimaan;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tahun = TahunPenerimaan::firstOrCreate(
            ['kode' => '2026/2027'],
            [
                'nama' => 'Tahun Penerimaan 2026/2027',
                'status' => 'aktif',
                'tanggal_mulai' => '2026-06-01',
                'tanggal_selesai' => '2026-09-30',
            ]
        );

        $jalurData = [
            ['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kategori' => 'mandiri', 'urutan' => 1, 'biaya_pendaftaran' => 350000, 'requires_cbt' => true],
            ['kode' => 'RPL', 'nama' => 'Jalur RPL', 'kategori' => 'mandiri', 'urutan' => 2, 'biaya_pendaftaran' => 350000],
        ];
        foreach ($jalurData as $j) {
            Jalur::firstOrCreate(['kode' => $j['kode']], $j);
        }

        $kelasData = [
            ['kode' => 'REGA', 'nama' => 'Reguler A'],
            ['kode' => 'REGB', 'nama' => 'Reguler B'],
            ['kode' => 'KARY', 'nama' => 'Kelas Karyawan'],
        ];
        foreach ($kelasData as $k) {
            KelasPerkuliahan::firstOrCreate(['kode' => $k['kode']], $k);
        }

        // Matriks setting prodi: prodi x kelas x jalur (dibuat via Seeder Setting)
        $this->seedMatriks();
        $this->seedJalurKelas();
        $this->seedKuota($tahun);
        $this->seedCbt();
    }

    /**
     * Contoh bank soal (dikategorikan Akademik & Sosial, plus contoh soal khusus prodi)
     * & jadwal CBT untuk Jalur Reguler agar alur tes CBT bisa langsung dicoba.
     */
    private function seedCbt(): void
    {
        $jalur = Jalur::where('kode', 'REGULER')->first();

        if (! $jalur || CbtSoal::where('jalur_id', $jalur->id)->exists()) {
            return;
        }

        $akademik = [
            ['pertanyaan' => 'Hasil dari 12 x 8 adalah?', 'pilihan_a' => '86', 'pilihan_b' => '96', 'pilihan_c' => '106', 'pilihan_d' => '116', 'kunci_jawaban' => 'b'],
            ['pertanyaan' => 'Jika x + 5 = 12, maka nilai x adalah?', 'pilihan_a' => '5', 'pilihan_b' => '6', 'pilihan_c' => '7', 'pilihan_d' => '8', 'kunci_jawaban' => 'c'],
            ['pertanyaan' => 'Manakah yang merupakan bilangan prima?', 'pilihan_a' => '9', 'pilihan_b' => '15', 'pilihan_c' => '17', 'pilihan_d' => '21', 'kunci_jawaban' => 'c'],
            ['pertanyaan' => 'Jika sebuah persegi memiliki sisi 6 cm, berapa luasnya?', 'pilihan_a' => '24 cm²', 'pilihan_b' => '30 cm²', 'pilihan_c' => '36 cm²', 'pilihan_d' => '42 cm²', 'kunci_jawaban' => 'c'],
            ['pertanyaan' => 'Akar kuadrat dari 144 adalah?', 'pilihan_a' => '10', 'pilihan_b' => '11', 'pilihan_c' => '12', 'pilihan_d' => '13', 'kunci_jawaban' => 'c'],
            ['pertanyaan' => '3/4 jika diubah ke persen menjadi?', 'pilihan_a' => '65%', 'pilihan_b' => '70%', 'pilihan_c' => '75%', 'pilihan_d' => '80%', 'kunci_jawaban' => 'c'],
        ];

        $sosial = [
            ['pertanyaan' => 'Ibu kota Provinsi Jambi adalah?', 'pilihan_a' => 'Jambi', 'pilihan_b' => 'Palembang', 'pilihan_c' => 'Bengkulu', 'pilihan_d' => 'Pekanbaru', 'kunci_jawaban' => 'a'],
            ['pertanyaan' => 'Antonim dari kata "maju" adalah?', 'pilihan_a' => 'Cepat', 'pilihan_b' => 'Mundur', 'pilihan_c' => 'Lambat', 'pilihan_d' => 'Naik', 'kunci_jawaban' => 'b'],
            ['pertanyaan' => 'Proklamasi Kemerdekaan Indonesia dibacakan pada tanggal?', 'pilihan_a' => '17 Agustus 1945', 'pilihan_b' => '17 Agustus 1949', 'pilihan_c' => '20 Mei 1945', 'pilihan_d' => '10 November 1945', 'kunci_jawaban' => 'a'],
            ['pertanyaan' => 'Sinonim dari kata "cerdas" adalah?', 'pilihan_a' => 'Bodoh', 'pilihan_b' => 'Pintar', 'pilihan_c' => 'Malas', 'pilihan_d' => 'Rajin', 'kunci_jawaban' => 'b'],
            ['pertanyaan' => 'Pancasila sebagai dasar negara disahkan pada?', 'pilihan_a' => '1 Juni 1945', 'pilihan_b' => '17 Agustus 1945', 'pilihan_c' => '18 Agustus 1945', 'pilihan_d' => '28 Oktober 1928', 'kunci_jawaban' => 'c'],
            ['pertanyaan' => 'Lembaga yang bertugas membuat undang-undang di Indonesia adalah?', 'pilihan_a' => 'MA', 'pilihan_b' => 'DPR', 'pilihan_c' => 'BPK', 'pilihan_d' => 'KPK', 'kunci_jawaban' => 'b'],
        ];

        foreach ($akademik as $s) {
            CbtSoal::create($s + ['jalur_id' => $jalur->id, 'kategori' => 'Akademik', 'bobot' => 1, 'is_active' => true]);
        }
        foreach ($sosial as $s) {
            CbtSoal::create($s + ['jalur_id' => $jalur->id, 'kategori' => 'Sosial', 'bobot' => 1, 'is_active' => true]);
        }

        // Jadwal umum: berlaku untuk semua prodi di jalur ini, hanya mengambil soal umum.
        $jadwalUmum = CbtJadwal::create([
            'jalur_id' => $jalur->id,
            'nama' => 'Tes CBT Jalur Reguler — Umum',
            'durasi_menit' => 30,
            'waktu_mulai' => now()->subDay(),
            'waktu_selesai' => now()->addMonths(2),
            'is_active' => true,
        ]);
        $jadwalUmum->komposisi()->createMany([
            ['kategori' => 'Akademik', 'jumlah' => 4],
            ['kategori' => 'Sosial', 'jumlah' => 4],
        ]);

        // Contoh jadwal khusus prodi (mis. Anestesi), sesuai kode program studi NEO Feeder
        // bila sudah tersinkron: dipilih eksplisit saat membuat jadwal, bukan ditebak dari
        // pilihan prodi pendaftar.
        $prodiAnestesi = Prodi::where('nama', 'like', '%anestesi%')->first();
        if ($prodiAnestesi) {
            $soalAnestesi = [
                ['pertanyaan' => 'Obat anestesi lokal yang paling umum digunakan untuk prosedur minor adalah?', 'pilihan_a' => 'Lidokain', 'pilihan_b' => 'Parasetamol', 'pilihan_c' => 'Amoksisilin', 'pilihan_d' => 'Ibuprofen', 'kunci_jawaban' => 'a'],
                ['pertanyaan' => 'Skor yang digunakan untuk menilai kesiapan pasien sebelum anestesi umum adalah?', 'pilihan_a' => 'APGAR', 'pilihan_b' => 'ASA Physical Status', 'pilihan_c' => 'GCS', 'pilihan_d' => 'NIHSS', 'kunci_jawaban' => 'b'],
            ];
            foreach ($soalAnestesi as $s) {
                CbtSoal::create($s + ['jalur_id' => $jalur->id, 'prodi_id' => $prodiAnestesi->id, 'kategori' => 'Akademik', 'bobot' => 1, 'is_active' => true]);
            }

            $jadwalAnestesi = CbtJadwal::create([
                'jalur_id' => $jalur->id,
                'prodi_id' => $prodiAnestesi->id,
                'nama' => 'Tes CBT Jalur Reguler — Anestesi',
                'durasi_menit' => 30,
                'waktu_mulai' => now()->subDay(),
                'waktu_selesai' => now()->addMonths(2),
                'is_active' => true,
            ]);
            // Akademik: 4 soal umum + 2 soal khusus prodi (sesuai jumlah bank Anestesi di atas).
            // Sosial: 4 soal umum saja, tidak ada kuota khusus prodi.
            $jadwalAnestesi->komposisi()->createMany([
                ['kategori' => 'Akademik', 'jumlah' => 4, 'jumlah_prodi' => 2],
                ['kategori' => 'Sosial', 'jumlah' => 4, 'jumlah_prodi' => 0],
            ]);
        }
    }

    /**
     * Resolve prodi dari kode NEO Feeder (kode_program_studi); fallback ke
     * kode demo bila NEO Feeder offline.
     *
     * @param  array<int, string>  $codes
     */
    private function resolveProdi(array $codes): ?Prodi
    {
        foreach ($codes as $code) {
            $prodi = Prodi::where('kode', $code)->first();
            if ($prodi) {
                return $prodi;
            }
        }

        return null;
    }

    private function seedMatriks(): void
    {
        $kelas = KelasPerkuliahan::all()->keyBy('kode');
        $jalur = Jalur::all()->keyBy('kode');

        // Kode prodi mengikuti NEO Feeder; fallback ke kode demo bila offline.
        $prodi = [
            'TI' => $this->resolveProdi(['55201', 'TI']), // Informatika
            'SI' => $this->resolveProdi(['57201', 'SI']), // Sistem Informasi
            'MJ' => $this->resolveProdi(['61201', 'MJ']), // Manajemen
            'AK' => $this->resolveProdi(['60201', 'AK']), // Ekonomi Pembangunan
            'HK' => $this->resolveProdi(['74202', 'HK']), // Hukum Bisnis
        ];

        // Reguler A & B untuk Jalur Reguler; Kelas Karyawan untuk Jalur RPL.
        $kombinasi = [
            'TI' => [
                'REGA' => ['REGULER'],
                'REGB' => ['REGULER'],
                'KARY' => ['RPL'],
            ],
            'SI' => [
                'REGA' => ['REGULER'],
                'REGB' => ['REGULER'],
                'KARY' => ['RPL'],
            ],
            'MJ' => [
                'REGA' => ['REGULER'],
                'REGB' => ['REGULER'],
                'KARY' => ['RPL'],
            ],
            'AK' => [
                'REGA' => ['REGULER'],
                'KARY' => ['RPL'],
            ],
            'HK' => [
                'REGA' => ['REGULER'],
                'REGB' => ['REGULER'],
            ],
        ];

        foreach ($kombinasi as $slot => $kelasJalur) {
            if (! ($prodi[$slot] ?? null)) {
                continue;
            }

            foreach ($kelasJalur as $kodeKelas => $kodeJalurList) {
                foreach ($kodeJalurList as $kodeJalur) {
                    ProdiKelasJalur::firstOrCreate([
                        'prodi_id' => $prodi[$slot]->id,
                        'kelas_id' => $kelas[$kodeKelas]->id,
                        'jalur_id' => $jalur[$kodeJalur]->id,
                    ]);
                }
            }
        }
    }

    private function seedJalurKelas(): void
    {
        $kelas = KelasPerkuliahan::all()->keyBy('kode');
        $jalur = Jalur::all()->keyBy('kode');

        // Faktor biaya per kelas relatif terhadap biaya default jalur.
        $faktorKelas = [
            'REGA' => 1.0,
            'REGB' => 1.0,
            'KARY' => 1.5,
        ];

        foreach ($jalur as $j) {
            foreach ($kelas as $k) {
                $faktor = $faktorKelas[$k->kode] ?? 1.0;
                $biaya = (int) round((float) $j->biaya_pendaftaran * $faktor);

                JalurKelas::firstOrCreate(
                    ['jalur_id' => $j->id, 'kelas_id' => $k->id],
                    ['biaya_pendaftaran' => $biaya]
                );
            }
        }
    }

    private function seedKuota(TahunPenerimaan $tahun): void
    {
        $kelas = KelasPerkuliahan::all()->keyBy('kode');
        $jalur = Jalur::all()->keyBy('kode');

        $prodi = [
            'TI' => $this->resolveProdi(['55201', 'TI']),
            'SI' => $this->resolveProdi(['57201', 'SI']),
            'MJ' => $this->resolveProdi(['61201', 'MJ']),
            'AK' => $this->resolveProdi(['60201', 'AK']),
            'HK' => $this->resolveProdi(['74202', 'HK']),
        ];

        $data = [
            ['TI', 'REGA', 'REGULER', 40],
            ['TI', 'REGB', 'REGULER', 40],
            ['TI', 'KARY', 'RPL', 40],
            ['SI', 'REGA', 'REGULER', 40],
            ['SI', 'REGB', 'REGULER', 40],
            ['SI', 'KARY', 'RPL', 40],
            ['MJ', 'REGA', 'REGULER', 40],
            ['MJ', 'KARY', 'RPL', 40],
            ['AK', 'REGA', 'REGULER', 40],
            ['AK', 'KARY', 'RPL', 40],
            ['HK', 'REGA', 'REGULER', 40],
        ];

        foreach ($data as [$slot, $kodeKelas, $kodeJalur, $jumlah]) {
            if (! ($prodi[$slot] ?? null)) {
                continue;
            }

            Kuota::firstOrCreate([
                'tahun_id' => $tahun->id,
                'jalur_id' => $jalur[$kodeJalur]->id,
                'prodi_id' => $prodi[$slot]->id,
                'kelas_id' => $kelas[$kodeKelas]->id,
            ], [
                'jumlah' => $jumlah,
                'terpakai' => 0,
                'is_active' => true,
            ]);
        }
    }
}
