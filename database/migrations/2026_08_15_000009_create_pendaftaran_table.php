<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('no_urut')->unique();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('tahun_id')->constrained('tahun_penerimaan')->cascadeOnDelete();
            $table->foreignUuid('jalur_id')->constrained('jalur')->cascadeOnDelete();
            $table->foreignUuid('gelombang_id')->nullable()->constrained('gelombang')->nullOnDelete();
            $table->foreignUuid('promo_id')->nullable()->constrained('promo')->nullOnDelete();
            $table->string('nomor_pendaftaran', 40)->nullable()->unique();
            $table->string('status', 30)->default('draft'); // draft | menunggu_pembayaran | lunas | terverifikasi | lolos | cadangan | tidak_lolos | daftar_ulang | mahasiswa_baru | ditolak
            $table->string('status_pembayaran', 20)->default('belum_bayar');
            $table->decimal('nilai_seleksi', 8, 2)->nullable();
            $table->string('catatan', 500)->nullable();
            $table->timestamps();
        });

        // no_urut berfungsi sebagai sequence (non-PK) untuk nomor pendaftaran berurutan.
        // Syntax AUTO_INCREMENT non-PK hanya didukung MySQL/MariaDB; SQLite (testing) dilewati.
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE pendaftaran MODIFY no_urut BIGINT UNSIGNED AUTO_INCREMENT');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran');
    }
};
