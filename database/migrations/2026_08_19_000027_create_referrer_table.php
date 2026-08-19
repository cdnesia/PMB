<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrer', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('kode', 30)->unique();
            $table->string('jenis', 20)->default('mitra'); // karyawan | mitra
            $table->string('nama_instansi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Mahasiswa yang direferensikan (diisi saat registrasi via kode referral).
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('referrer_id')->nullable()->after('id')->constrained('referrer')->nullOnDelete();
        });

        // Sumber utama untuk laporan: referrer yang membawa pendaftar ini.
        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->foreignUuid('referrer_id')->nullable()->after('promo_id')->constrained('referrer')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referrer_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referrer_id');
        });

        Schema::dropIfExists('referrer');
    }
};
