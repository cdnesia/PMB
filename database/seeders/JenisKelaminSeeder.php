<?php

namespace Database\Seeders;

use App\Models\JenisKelamin;
use Illuminate\Database\Seeder;

class JenisKelaminSeeder extends Seeder
{
    public function run(): void
    {
        $jenisKelamin = [
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
        ];

        foreach ($jenisKelamin as $kode => $nama) {
            JenisKelamin::firstOrCreate(['kode' => $kode], ['nama' => $nama]);
        }
    }
}