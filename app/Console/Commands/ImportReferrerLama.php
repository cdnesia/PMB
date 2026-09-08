<?php

namespace App\Console\Commands;

use App\Models\Referrer;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImportReferrerLama extends Command
{
    protected $signature = 'referrer:import-lama
                            {path=storage/app/private/referrer.json : Path ke file JSON referrer lama}
                            {--dry-run : Hanya tampilkan ringkasan tanpa menyimpan data}';

    protected $description = 'Impor data referrer dari file JSON sistem lama';

    public function handle(): int
    {
        $path = base_path($this->argument('path'));

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");

            return self::FAILURE;
        }

        $raw = preg_replace('/[\x00-\x1F]/', ' ', file_get_contents($path));
        $rows = json_decode($raw, true);

        if (! is_array($rows)) {
            $this->error('File JSON tidak valid: '.json_last_error_msg());

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $dibuat = 0;
        $dilewatiKosong = 0;
        $dilewatiSudahAda = 0;
        $rusak = 0;

        DB::beginTransaction();

        foreach ($rows as $row) {
            $nama = trim((string) ($row['`nama`'] ?? ''));
            $kode = trim((string) ($row['`kode`'] ?? ''));

            if ($nama === '' || $kode === '') {
                $dilewatiKosong++;

                continue;
            }

            if (Referrer::where('kode', $kode)->exists()) {
                $dilewatiSudahAda++;

                continue;
            }

            $hp = trim((string) ($row['`hp`'] ?? ''));
            $rekening = trim((string) ($row['`rekening`'] ?? ''));
            $bank = trim((string) ($row['`bank`'] ?? ''));
            $passwordLama = (string) ($row['`password`'] ?? '');

            // Beberapa baris lama datanya bergeser kolom (nama mengandung gelar
            // dengan koma saat diekspor dari sistem lama), ditandai dari hp yang
            // bukan angka. Untuk baris ini data hp/bank/rekening tidak bisa
            // dipercaya sehingga dikosongkan dan password dibuat ulang secara acak.
            $rusakKolom = $hp !== '' && $hp !== 'NULL' && ! preg_match('/^[0-9+\s]+$/', $hp);

            if ($rusakKolom) {
                $rusak++;
                $hp = null;
                $rekening = null;
                $bank = null;
                $email = Str::lower($kode).'@referrer.local';
                $password = Str::random(32);
            } else {
                $hp = in_array($hp, ['', 'NULL'], true) ? null : $hp;
                $rekening = in_array($rekening, ['', 'NULL'], true) ? null : $rekening;
                $bank = in_array($bank, ['', 'NULL'], true) ? null : $bank;
                $email = $hp ? Str::lower($hp).'@referrer.local' : Str::lower($kode).'@referrer.local';
                $password = Hash::isHashed($passwordLama) ? $passwordLama : Str::random(32);
            }

            if (User::where('email', $email)->exists()) {
                $email = Str::lower($kode).'@referrer.local';
            }

            if ($hp && User::where('phone', $hp)->exists()) {
                $hp = null;
            }

            if ($dryRun) {
                $this->line("Akan dibuat: {$nama} ({$kode}) - {$email}".($rusakKolom ? ' [data bank/hp dikosongkan]' : ''));
                $dibuat++;

                continue;
            }

            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'phone' => $hp,
                'password' => $password,
                'email_verified_at' => now(),
            ]);

            $user->assignRole('karyawan');

            Referrer::create([
                'user_id' => $user->id,
                'kode' => $kode,
                'jenis' => 'karyawan',
                'nama_bank' => $bank,
                'nomor_rekening' => $rekening,
                'nama_pemilik_rekening' => ($bank || $rekening) ? $nama : null,
                'is_active' => true,
            ]);

            $dibuat++;
        }

        if ($dryRun) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] ' : '')."{$dibuat} referrer diproses.");
        $this->line("Dilewati (nama/kode kosong): {$dilewatiKosong}");
        $this->line("Dilewati (kode sudah ada): {$dilewatiSudahAda}");
        $this->line("Data bank/hp dikosongkan karena kolom bergeser: {$rusak}");

        return self::SUCCESS;
    }
}
