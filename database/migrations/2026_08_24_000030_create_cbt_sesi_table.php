<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cbt_sesi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cbt_jadwal_id')->constrained('cbt_jadwal')->cascadeOnDelete();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete();
            $table->string('status', 20)->default('berlangsung'); // berlangsung | selesai
            $table->json('soal_urutan'); // array id cbt_soal, urutan teracak tetap per peserta
            $table->decimal('skor', 8, 2)->nullable();
            $table->unsignedInteger('jumlah_benar')->nullable();
            $table->unsignedInteger('jumlah_salah')->nullable();
            $table->unsignedInteger('jumlah_kosong')->nullable();
            $table->dateTime('started_at');
            $table->dateTime('deadline_at');
            $table->dateTime('finished_at')->nullable();
            $table->string('finish_reason', 20)->nullable(); // submit | auto_timeout | admin_close
            $table->unsignedInteger('jumlah_pelanggaran')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();

            $table->unique(['cbt_jadwal_id', 'pendaftaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_sesi');
    }
};
