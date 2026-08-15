<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_penerimaan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->string('status', 20)->default('aktif'); // draft | aktif | ditutup | arsip
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_penerimaan');
    }
};
