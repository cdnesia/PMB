<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrer', function (Blueprint $table) {
            $table->string('nama_bank')->nullable()->after('nama_instansi');
            $table->string('nomor_rekening', 50)->nullable()->after('nama_bank');
            $table->string('nama_pemilik_rekening')->nullable()->after('nomor_rekening');
        });
    }

    public function down(): void
    {
        Schema::table('referrer', function (Blueprint $table) {
            $table->dropColumn(['nama_bank', 'nomor_rekening', 'nama_pemilik_rekening']);
        });
    }
};
