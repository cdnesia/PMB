<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jalur_kelas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jalur_id')->constrained('jalur')->cascadeOnDelete();
            $table->foreignUuid('kelas_id')->constrained('kelas_perkuliahan')->cascadeOnDelete();
            $table->decimal('biaya_pendaftaran', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['jalur_id', 'kelas_id'], 'jalur_kelas_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jalur_kelas');
    }
};
