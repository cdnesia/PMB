<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 30)->unique();
            $table->string('nama', 100);
            $table->string('jenis', 20)->default('pendaftaran'); // pendaftaran | spp | semua
            $table->string('tipe', 20)->default('persen'); // persen | nominal
            $table->decimal('nilai', 15, 2)->default(0);
            $table->decimal('maks_potongan', 15, 2)->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_global')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo');
    }
};
