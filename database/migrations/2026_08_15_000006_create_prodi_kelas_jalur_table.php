<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prodi_kelas_jalur', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('prodi_id')->constrained('prodi')->cascadeOnDelete();
            $table->foreignUuid('kelas_id')->constrained('kelas_perkuliahan')->cascadeOnDelete();
            $table->foreignUuid('jalur_id')->constrained('jalur')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['prodi_id', 'kelas_id', 'jalur_id'], 'pkj_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prodi_kelas_jalur');
    }
};
