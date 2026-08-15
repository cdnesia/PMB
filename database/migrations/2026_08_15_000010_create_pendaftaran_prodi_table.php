<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_prodi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $table->tinyInteger('urutan'); // 1 = pilihan 1, 2 = pilihan 2
            $table->foreignUuid('prodi_id')->constrained('prodi')->cascadeOnDelete();
            $table->foreignUuid('kelas_id')->constrained('kelas_perkuliahan')->cascadeOnDelete();
            $table->string('status', 30)->nullable(); // lolos | cadangan | tidak_lolos
            $table->timestamps();

            $table->unique(['pendaftaran_id', 'urutan'], 'pp_urutan_unique');
            $table->unique(['pendaftaran_id', 'prodi_id', 'kelas_id'], 'pp_prodi_kelas_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_prodi');
    }
};
