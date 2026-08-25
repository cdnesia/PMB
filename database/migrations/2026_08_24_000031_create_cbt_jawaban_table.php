<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_jawaban', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cbt_sesi_id')->constrained('cbt_sesi')->cascadeOnDelete();
            $table->foreignUuid('cbt_soal_id')->constrained('cbt_soal')->cascadeOnDelete();
            $table->char('jawaban', 1)->nullable(); // a | b | c | d | e
            $table->boolean('is_benar')->nullable();
            $table->boolean('ragu_ragu')->default(false);
            $table->dateTime('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['cbt_sesi_id', 'cbt_soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_jawaban');
    }
};
