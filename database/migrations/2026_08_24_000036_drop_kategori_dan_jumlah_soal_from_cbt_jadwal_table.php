<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Digantikan oleh cbt_jadwal_komposisi: satu jadwal kini bisa menyusun soal
        // dari beberapa kategori sekaligus dengan jumlah berbeda per kategori.
        Schema::table('cbt_jadwal', function (Blueprint $table) {
            $table->dropColumn(['kategori_soal', 'jumlah_soal']);
        });
    }

    public function down(): void
    {
        Schema::table('cbt_jadwal', function (Blueprint $table) {
            $table->string('kategori_soal', 50)->nullable();
            $table->unsignedInteger('jumlah_soal')->default(0);
        });
    }
};
