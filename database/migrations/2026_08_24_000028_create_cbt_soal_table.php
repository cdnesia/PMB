<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_soal', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jalur_id')->nullable()->constrained('jalur')->cascadeOnDelete();
            $table->string('kategori', 50)->nullable(); // tag bebas mis. "Matematika", untuk filter bank soal
            $table->text('pertanyaan');
            $table->text('pilihan_a');
            $table->text('pilihan_b');
            $table->text('pilihan_c');
            $table->text('pilihan_d');
            $table->text('pilihan_e')->nullable();
            $table->char('kunci_jawaban', 1); // a | b | c | d | e
            $table->decimal('bobot', 5, 2)->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_soal');
    }
};
