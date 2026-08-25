<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_pelanggaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cbt_sesi_id')->constrained('cbt_sesi')->cascadeOnDelete();
            $table->string('jenis', 30); // pindah_tab | keluar_fullscreen | copy_paste | klik_kanan | devtools
            $table->string('keterangan', 255)->nullable();
            $table->dateTime('terjadi_pada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_pelanggaran');
    }
};
