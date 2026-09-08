<?php

namespace App\Console\Commands;

use App\Models\DaftarUlang;
use App\Models\DokumenPendaftar;
use App\Models\PembayaranPendaftaran;
use App\Models\PendaftaranSyarat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrasiDokumenKePrivat extends Command
{
    protected $signature = 'dokumen:migrasi-privat
                            {--dry-run : Hanya tampilkan berkas yang akan dipindah tanpa benar-benar memindahkannya}';

    protected $description = 'Memindahkan berkas dokumen/bukti bayar yang masih ada di disk public ke disk private (local)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $sumber = [
            [DokumenPendaftar::class, 'file_path'],
            [PendaftaranSyarat::class, 'file_path'],
            [PembayaranPendaftaran::class, 'bukti_bayar'],
            [DaftarUlang::class, 'bukti_bayar'],
        ];

        $dipindah = 0;
        $dilewati = 0;

        foreach ($sumber as [$model, $kolom]) {
            $rows = $model::query()->whereNotNull($kolom)->get();

            foreach ($rows as $row) {
                $path = $row->{$kolom};

                if (Storage::disk('local')->exists($path)) {
                    $dilewati++;

                    continue;
                }

                if (! Storage::disk('public')->exists($path)) {
                    $this->warn("Berkas tidak ditemukan di kedua disk: {$model} #{$row->id} ({$path})");

                    continue;
                }

                $this->line(($dryRun ? '[DRY RUN] ' : '')."Memindahkan: {$path}");

                if (! $dryRun) {
                    Storage::disk('local')->put($path, Storage::disk('public')->get($path));
                    Storage::disk('public')->delete($path);
                }

                $dipindah++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] ' : '')."{$dipindah} berkas dipindahkan ke disk private.");
        $this->line("{$dilewati} berkas sudah ada di disk private (dilewati).");

        return self::SUCCESS;
    }
}
