<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuota', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_id')->constrained('tahun_penerimaan')->cascadeOnDelete();
            $table->foreignUuid('jalur_id')->constrained('jalur')->cascadeOnDelete();
            $table->foreignUuid('prodi_id')->constrained('prodi')->cascadeOnDelete();
            $table->foreignUuid('kelas_id')->nullable()->constrained('kelas_perkuliahan')->cascadeOnDelete();
            $table->integer('jumlah')->default(0);
            $table->integer('terpakai')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuota');
    }
};
