<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_jadwal_komposisi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cbt_jadwal_id')->constrained('cbt_jadwal')->cascadeOnDelete();
            $table->string('kategori', 50); // mis. "Akademik", "Sosial"
            $table->unsignedInteger('jumlah'); // jumlah soal diambil dari kategori ini
            $table->timestamps();

            $table->unique(['cbt_jadwal_id', 'kategori']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_jadwal_komposisi');
    }
};
