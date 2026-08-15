<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_ketentuan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('promo_id')->constrained('promo')->cascadeOnDelete();
            $table->foreignUuid('jalur_id')->constrained('jalur')->cascadeOnDelete();
            $table->foreignUuid('prodi_id')->constrained('prodi')->cascadeOnDelete();
            $table->foreignUuid('kelas_id')->constrained('kelas_perkuliahan')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['promo_id', 'jalur_id', 'prodi_id', 'kelas_id'], 'pk_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_ketentuan');
    }
};
