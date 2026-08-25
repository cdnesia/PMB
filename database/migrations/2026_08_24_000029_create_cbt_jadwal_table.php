<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_jadwal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jalur_id')->constrained('jalur')->cascadeOnDelete();
            $table->foreignUuid('gelombang_id')->nullable()->constrained('gelombang')->nullOnDelete();
            $table->string('nama', 150);
            $table->string('kategori_soal', 50)->nullable(); // filter pool cbt_soal.kategori; null = semua kategori
            $table->unsignedInteger('jumlah_soal');
            $table->unsignedInteger('durasi_menit');
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai');
            $table->decimal('nilai_kelulusan_minimum', 5, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_jadwal');
    }
};
