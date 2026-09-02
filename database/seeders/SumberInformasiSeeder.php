<?php

namespace Database\Seeders;

use App\Models\SumberInformasi;
use Illuminate\Database\Seeder;

class SumberInformasiSeeder extends Seeder
{
    public function run(): void
    {
        $sumber = [
            'TEMAN-KELUARGA' => 'Teman / Keluarga',
            'GURU-SEKOLAH' => 'Guru / Sekolah',
            'MEDIA-SOSIAL' => 'Media Sosial (Instagram, Facebook, TikTok, dll)',
            'WEBSITE' => 'Website UM Jambi',
            'GOOGLE-INTERNET' => 'Pencarian Google / Internet',
            'BROSUR-PAMERAN' => 'Brosur / Pameran Pendidikan',
            'ALUMNI' => 'Alumni UM Jambi',
            'LAINNYA' => 'Lainnya',
        ];

        $urutan = 0;
        foreach ($sumber as $kode => $nama) {
            SumberInformasi::firstOrCreate(['kode' => $kode], ['nama' => $nama, 'urutan' => $urutan++]);
        }
    }
}
