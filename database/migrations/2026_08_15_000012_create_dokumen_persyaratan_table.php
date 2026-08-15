<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_persyaratan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jalur_id')->nullable()->constrained('jalur')->cascadeOnDelete();
            $table->foreignUuid('prodi_id')->nullable()->constrained('prodi')->cascadeOnDelete();
            $table->string('nama', 200);
            $table->boolean('wajib')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_persyaratan');
    }
};
