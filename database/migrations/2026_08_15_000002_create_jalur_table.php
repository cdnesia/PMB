<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jalur', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama', 100);
            $table->string('kategori', 20)->default('mandiri'); // nasional | mandiri
            $table->integer('urutan')->default(0);
            $table->decimal('biaya_pendaftaran', 15, 2)->default(0);
            $table->boolean('requires_cbt')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jalur');
    }
};
