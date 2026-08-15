<?php

namespace App\Console\Commands;

use App\Models\Prodi;
use App\Services\NeoFeederService;
use Illuminate\Console\Command;

class SyncProdiNeoFeeder extends Command
{
    protected $signature = 'neofeeder:sync-prodi
                            {--fresh : Hapus seluruh data prodi lama sebelum import}';

    protected $description = 'Impor data program studi dari NEO Feeder (GetProdi)';

    public function handle(NeoFeederService $neo): int
    {
        $this->info('Mengambil data program studi dari NEO Feeder...');

        if ($this->option('fresh')) {
            $this->warn('Menghapus data prodi lama...');
            Prodi::query()->delete();
        }

        $response = $neo->getProdi();
        $rows = $response['data'] ?? [];

        $this->info('Memproses '.count($rows).' program studi.');

        $totalCreated = 0;
        $totalUpdated = 0;

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        foreach ($rows as $row) {
            // id_prodi adalah UUID — dipakai sebagai primary key lokal agar
            // import bersifat idempotent (update bila sudah ada, bukan duplikat).
            $id = (string) ($row['id_prodi'] ?? '');
            $kode = trim((string) ($row['kode_program_studi'] ?? ''));

            if ($id === '' || $kode === '') {
                $bar->advance();
                continue;
            }

            $attributes = [
                'id' => $id,
                'kode' => $kode,
                'nama' => (string) ($row['nama_program_studi'] ?? ''),
                'jenjang' => (string) ($row['nama_jenjang_pendidikan'] ?? ''),
                'fakultas' => null, // GetProdi tidak menyediakan data fakultas.
                'is_active' => (($row['status'] ?? 'A') === 'A'),
            ];

            $prodi = Prodi::find($id);

            if ($prodi) {
                $prodi->update($attributes);
                $totalUpdated++;
            } else {
                Prodi::create($attributes);
                $totalCreated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Selesai. {$totalCreated} data dibuat, {$totalUpdated} data diperbarui.");

        return self::SUCCESS;
    }
}
