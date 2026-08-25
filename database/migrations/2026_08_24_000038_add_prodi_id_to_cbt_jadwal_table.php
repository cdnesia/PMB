<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_jadwal', function (Blueprint $table) {
            // Target prodi jadwal ini — null berarti umum, berlaku untuk semua prodi di jalur ini.
            // Menggantikan pendekatan lama yang menebak prodi dari pilihan pendaftar saat ujian dimulai.
            $table->foreignUuid('prodi_id')->nullable()->after('gelombang_id')->constrained('prodi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cbt_jadwal', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prodi_id');
        });
    }
};
