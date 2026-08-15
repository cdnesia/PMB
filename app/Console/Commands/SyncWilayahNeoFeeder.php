<?php

namespace App\Console\Commands;

use App\Models\Wilayah;
use App\Services\NeoFeederService;
use Illuminate\Console\Command;

class SyncWilayahNeoFeeder extends Command
{
    protected $signature = 'neofeeder:sync-wilayah
                            {--fresh : Hapus seluruh data wilayah lama sebelum import}';

    protected $description = 'Impor data wilayah (negara, propinsi, kab/kota, kecamatan) dari NEO Feeder';

    public function handle(NeoFeederService $neo): int
    {
        $this->info('Mengambil data wilayah dari NEO Feeder...');

        if ($this->option('fresh')) {
            $this->warn('Menghapus data wilayah lama...');
            Wilayah::query()->delete();
        }

        // NEO Feeder tidak menyediakan level kelurahan/desa (paling dalam kecamatan).
        // Level 0 (negara) tetap diimpor untuk mendukung biodata pendaftar WNA.
        $mapping = [
            0 => Wilayah::LEVEL_NEGARA,
            1 => Wilayah::LEVEL_PROVINSI,
            2 => Wilayah::LEVEL_KOTA,
            3 => Wilayah::LEVEL_KECAMATAN,
        ];

        $totalCreated = 0;
        $totalUpdated = 0;

        foreach ($mapping as $neoLevel => $level) {
            $response = $neo->getWilayah("id_level_wilayah={$neoLevel}");
            $rows = $response['data'] ?? [];

            $this->info("Memproses level '{$level}' ({$neoLevel}): ".count($rows).' data.');

            $bar = $this->output->createProgressBar(count($rows));
            $bar->start();

            foreach ($rows as $row) {
                $kode = trim((string) ($row['id_wilayah'] ?? ''));
                $induk = trim((string) ($row['id_induk_wilayah'] ?? ''));

                if ($kode === '') {
                    $bar->advance();
                    continue;
                }

                $parentId = null;
                if ($induk !== '') {
                    $parentId = Wilayah::where('kode', $induk)->value('id');
                }

                $attributes = [
                    'nama' => $row['nama_wilayah'],
                    'level' => $level,
                    'parent_id' => $parentId,
                ];

                $wilayah = Wilayah::where('kode', $kode)->first();

                if ($wilayah) {
                    $wilayah->update($attributes);
                    $totalUpdated++;
                } else {
                    Wilayah::create(array_merge(['kode' => $kode], $attributes));
                    $totalCreated++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->info("Selesai. {$totalCreated} data dibuat, {$totalUpdated} data diperbarui.");

        return self::SUCCESS;
    }
}
