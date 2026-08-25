<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_jadwal_komposisi', function (Blueprint $table) {
            // Kuota tambahan (di luar `jumlah`) khusus soal milik prodi yang dipilih peserta —
            // berlaku hanya jika prodi tsb punya bank soal sendiri di kategori ini.
            $table->unsignedInteger('jumlah_prodi')->default(0)->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('cbt_jadwal_komposisi', function (Blueprint $table) {
            $table->dropColumn('jumlah_prodi');
        });
    }
};
