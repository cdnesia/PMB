<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gelombang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tahun_id')->constrained('tahun_penerimaan')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->date('tanggal_pengumuman')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gelombang');
    }
};
