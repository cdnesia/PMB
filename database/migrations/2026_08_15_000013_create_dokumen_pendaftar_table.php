<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_pendaftar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $table->foreignUuid('dokumen_persyaratan_id')->nullable()->constrained('dokumen_persyaratan')->nullOnDelete();
            $table->string('nama', 200);
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('status', 20)->default('menunggu'); // menunggu | terverifikasi | ditolak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_pendaftar');
    }
};
