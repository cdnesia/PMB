<?php

namespace Database\Seeders;

use App\Models\Prodi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class ProdiSeeder extends Seeder
{
    public function run(): void
    {
        // Sumber utama program studi adalah NEO Feeder (GetProdi). Bila
        // sinkronisasi gagal (mis. offline saat development), fallback ke
        // data demo.
        try {
            if (config('neofeeder.url')) {
                $exit = Artisan::call('neofeeder:sync-prodi', ['--fresh' => true]);
                $this->command?->info(trim(Artisan::output()));

                if ($exit === 0) {
                    return;
                }
            }
        } catch (Throwable $e) {
            $this->command?->warn('Gagal sinkronisasi prodi NEO Feeder ('.$e->getMessage().'). Memakai data demo.');
        }

        $this->seedDemo();
    }

    /**
     * Data demo program studi — hanya dipakai bila NEO Feeder tidak terjangkau.
     */
    private function seedDemo(): void
    {
        $prodi = [
            ['kode' => 'TI', 'nama' => 'Teknik Informatika', 'jenjang' => 'S1', 'fakultas' => 'Teknik'],
            ['kode' => 'SI', 'nama' => 'Sistem Informasi', 'jenjang' => 'S1', 'fakultas' => 'Teknik'],
            ['kode' => 'MJ', 'nama' => 'Manajemen', 'jenjang' => 'S1', 'fakultas' => 'Ekonomi'],
            ['kode' => 'AK', 'nama' => 'Akuntansi', 'jenjang' => 'S1', 'fakultas' => 'Ekonomi'],
            ['kode' => 'HK', 'nama' => 'Ilmu Hukum', 'jenjang' => 'S1', 'fakultas' => 'Hukum'],
        ];

        foreach ($prodi as $p) {
            Prodi::firstOrCreate(['kode' => $p['kode']], $p);
        }
    }
}
