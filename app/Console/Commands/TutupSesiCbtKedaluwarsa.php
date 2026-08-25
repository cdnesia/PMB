<?php

namespace App\Console\Commands;

use App\Services\CbtService;
use Illuminate\Console\Command;

class TutupSesiCbtKedaluwarsa extends Command
{
    protected $signature = 'cbt:tutup-sesi-kedaluwarsa';

    protected $description = 'Tutup & nilai otomatis sesi CBT yang sudah lewat deadline tapi belum di-submit peserta';

    public function handle(CbtService $cbt): int
    {
        $total = $cbt->tutupSesiKedaluwarsa();

        $this->info("{$total} sesi CBT kedaluwarsa ditutup & dinilai otomatis.");

        return self::SUCCESS;
    }
}
