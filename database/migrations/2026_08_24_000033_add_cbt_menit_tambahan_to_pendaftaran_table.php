<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftaran', function (Blueprint $table) {
            // Akomodasi tambahan waktu ujian CBT bagi pendaftar berkebutuhan khusus.
            $table->unsignedInteger('cbt_menit_tambahan')->default(0)->after('nilai_seleksi');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->dropColumn('cbt_menit_tambahan');
        });
    }
};
