<?php

namespace Database\Seeders;

use App\Models\Pekerjaan;
use Illuminate\Database\Seeder;

class PekerjaanSeeder extends Seeder
{
    public function run(): void
    {
        $pekerjaan = [
            1 => 'PNS / ASN',
            2 => 'TNI',
            3 => 'POLRI',
            4 => 'Karyawan BUMN / BUMD',
            5 => 'Karyawan Swasta',
            6 => 'Wiraswasta / Pengusaha',
            7 => 'Pedagang',
            8 => 'Petani / Peternak / Nelayan',
            9 => 'Buruh / Pekerja Lepas',
            10 => 'Guru / Dosen (Non-PNS)',
            11 => 'Tenaga Kesehatan',
            12 => 'Freelancer / Pekerja Digital',
            13 => 'Ibu Rumah Tangga',
            99 => 'Lainnya',
        ];

        foreach ($pekerjaan as $kode => $nama) {
            Pekerjaan::firstOrCreate(['kode' => $kode], ['nama' => $nama]);
        }
    }
}
