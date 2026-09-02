<?php

namespace App\Console\Commands;

use App\Models\Pendaftaran;
use Illuminate\Console\Command;

class VerifikasiBerkasOtomatis extends Command
{
    protected $signature = 'pendaftaran:verifikasi-berkas-otomatis';

    protected $description = 'Tandai otomatis berkas pendaftar berstatus lunas yang seluruh dokumen persyaratannya sudah diunggah sebagai lengkap & terverifikasi';

    public function handle(): int
    {
        $total = 0;

        Pendaftaran::query()
            ->where('status', 'lunas')
            ->has('dokumen')
            ->whereDoesntHave('dokumen', fn ($q) => $q->whereNull('file_path'))
            ->each(function (Pendaftaran $pendaftaran) use (&$total) {
                if ($pendaftaran->verifikasiSemuaBerkas()) {
                    $total++;
                }
            });

        $this->info("{$total} pendaftaran otomatis ditandai berkas lengkap & terverifikasi.");

        return self::SUCCESS;
    }
}
