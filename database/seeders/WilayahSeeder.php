<?php

namespace Database\Seeders;

use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        // Sumber utama wilayah adalah NEO Feeder. Bila sinkronisasi gagal
        // (mis. offline saat development), fallback ke data demo Kemendagri.
        try {
            if (config('neofeeder.url')) {
                $exit = Artisan::call('neofeeder:sync-wilayah', ['--fresh' => true]);
                $this->command?->info(trim(Artisan::output()));

                if ($exit === 0) {
                    return;
                }
            }
        } catch (Throwable $e) {
            $this->command?->warn('Gagal sinkronisasi wilayah NEO Feeder ('.$e->getMessage().'). Memakai data demo.');
        }

        $this->seedDemo();
    }

    /**
     * Data demo wilayah (kode Kemendagri) — hanya dipakai bila NEO Feeder
     * tidak dapat dijangkau.
     */
    private function seedDemo(): void
    {
        // 34 Provinsi (kode Kemendagri, sumber referensi NEO Feeder / PDDikti)
        $provinsi = [
            '11' => 'Aceh',
            '12' => 'Sumatera Utara',
            '13' => 'Sumatera Barat',
            '14' => 'Riau',
            '15' => 'Jambi',
            '16' => 'Sumatera Selatan',
            '17' => 'Bengkulu',
            '18' => 'Lampung',
            '19' => 'Kepulauan Bangka Belitung',
            '21' => 'Kepulauan Riau',
            '31' => 'DKI Jakarta',
            '32' => 'Jawa Barat',
            '33' => 'Jawa Tengah',
            '34' => 'DI Yogyakarta',
            '35' => 'Jawa Timur',
            '36' => 'Banten',
            '51' => 'Bali',
            '52' => 'Nusa Tenggara Barat',
            '53' => 'Nusa Tenggara Timur',
            '61' => 'Kalimantan Barat',
            '62' => 'Kalimantan Tengah',
            '63' => 'Kalimantan Selatan',
            '64' => 'Kalimantan Timur',
            '65' => 'Kalimantan Utara',
            '71' => 'Sulawesi Utara',
            '72' => 'Sulawesi Tengah',
            '73' => 'Sulawesi Selatan',
            '74' => 'Sulawesi Tenggara',
            '75' => 'Gorontalo',
            '76' => 'Sulawesi Barat',
            '81' => 'Maluku',
            '82' => 'Maluku Utara',
            '91' => 'Papua',
            '92' => 'Papua Barat',
        ];

        $provinsiIds = [];
        foreach ($provinsi as $kode => $nama) {
            $p = Wilayah::firstOrCreate(
                ['kode' => $kode],
                ['nama' => $nama, 'level' => Wilayah::LEVEL_PROVINSI, 'parent_id' => null]
            );
            $provinsiIds[$kode] = $p->id;
        }

        // Sample kab/kota → kecamatan → kelurahan (untuk demo; data lengkap diimpor dari referensi NEO Feeder)
        $this->seedKota('32.73', 'Kota Bandung', $provinsiIds['32'], [
            '32.73.05' => [
                'nama' => 'Andir',
                'kelurahan' => ['32.73.05.1001' => 'Malabar', '32.73.05.1002' => 'Dungus Cariang', '32.73.05.1003' => 'Ciroyom'],
            ],
            '32.73.06' => [
                'nama' => 'Cicendo',
                'kelurahan' => ['32.73.06.1001' => 'Husen Sastranegara', '32.73.06.1002' => 'Arjuna'],
            ],
        ]);

        $this->seedKota('32.75', 'Kota Bekasi', $provinsiIds['32'], [
            '32.75.01' => [
                'nama' => 'Bekasi Timur',
                'kelurahan' => ['32.75.01.1001' => 'Bekasi Jaya', '32.75.01.1002' => 'Margahayu'],
            ],
        ]);

        $this->seedKota('31.71', 'Jakarta Pusat', $provinsiIds['31'], [
            '31.71.01' => [
                'nama' => 'Gambir',
                'kelurahan' => ['31.71.01.1001' => 'Gambir', '31.71.01.1002' => 'Cideng'],
            ],
        ]);

        $this->seedKota('31.74', 'Jakarta Selatan', $provinsiIds['31'], [
            '31.74.01' => [
                'nama' => 'Tebet',
                'kelurahan' => ['31.74.01.1001' => 'Tebet Barat', '31.74.01.1002' => 'Tebet Timur'],
            ],
        ]);
    }

    private function seedKota(string $kode, string $nama, string $parentId, array $kecamatan): void
    {
        $kota = Wilayah::firstOrCreate(
            ['kode' => $kode],
            ['nama' => $nama, 'level' => Wilayah::LEVEL_KOTA, 'parent_id' => $parentId]
        );

        foreach ($kecamatan as $kodeKec => $data) {
            $kec = Wilayah::firstOrCreate(
                ['kode' => $kodeKec],
                ['nama' => $data['nama'], 'level' => Wilayah::LEVEL_KECAMATAN, 'parent_id' => $kota->id]
            );

            foreach ($data['kelurahan'] as $kodeKel => $namaKel) {
                Wilayah::firstOrCreate(
                    ['kode' => $kodeKel],
                    ['nama' => $namaKel, 'level' => Wilayah::LEVEL_KELURAHAN, 'parent_id' => $kec->id]
                );
            }
        }
    }
}
