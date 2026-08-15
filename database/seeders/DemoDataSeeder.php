<?php

namespace Database\Seeders;

use App\Models\Jalur;
use App\Models\KelasPerkuliahan;
use App\Models\Kuota;
use App\Models\Prodi;
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
            ['kode' => 'REGULER', 'nama' => 'Jalur Reguler', 'kategori' => 'mandiri', 'urutan' => 1, 'biaya_pendaftaran' => 350000],
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
                    \App\Models\ProdiKelasJalur::firstOrCreate([
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

                \App\Models\JalurKelas::firstOrCreate(
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
