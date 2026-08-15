<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran_syarat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $table->foreignUuid('syarat_jalur_id')->constrained('syarat_jalur')->cascadeOnDelete();
            $table->string('nilai')->nullable(); // untuk tipe 'field'
            $table->string('file_path')->nullable(); // untuk tipe 'file'
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_syarat');
    }
};
