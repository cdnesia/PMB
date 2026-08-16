<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_tinggal',
                'jenis_tinggal_kode',
                'alat_transportasi',
                'alat_transportasi_kode',
                'pembiayaan',
                'pembiayaan_kode',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->string('jenis_tinggal', 100)->nullable();
            $table->string('jenis_tinggal_kode', 10)->nullable();
            $table->string('alat_transportasi', 100)->nullable();
            $table->string('alat_transportasi_kode', 10)->nullable();
            $table->string('pembiayaan', 100)->nullable();
            $table->string('pembiayaan_kode', 10)->nullable();
        });
    }
};