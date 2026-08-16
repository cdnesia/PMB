<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Services\NeoFeederSyncService;
use Illuminate\Database\Seeder;
use Throwable;

class AgamaSeeder extends Seeder
{
    public function run(NeoFeederSyncService $sync): void
    {
        try {
            if (config('neofeeder.url')) {
                $result = $sync->syncAgama();
                $this->command?->info(sprintf(
                    'Sinkronisasi agama NEO Feeder: %d baru, %d diperbarui.',
                    $result['created'],
                    $result['updated']
                ));

                if (Agama::exists()) {
                    return;
                }
            }
        } catch (Throwable $e) {
            $this->command?->warn('Gagal sinkronisasi agama NEO Feeder ('.$e->getMessage().'). Memakai data demo.');
        }

        $this->seedDemo();
    }

    /**
     * Data demo agama (fallback bila NEO Feeder tidak dapat dijangkau).
     */
    private function seedDemo(): void
    {
        $agama = [
            1 => 'Islam',
            2 => 'Kristen',
            3 => 'Katolik',
            4 => 'Hindu',
            5 => 'Buddha',
            6 => 'Konghucu',
            99 => 'Lainnya',
        ];

        foreach ($agama as $kode => $nama) {
            Agama::firstOrCreate(['kode' => $kode], ['nama' => $nama]);
        }
    }
}