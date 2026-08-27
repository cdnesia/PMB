<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_pendaftaran', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete()->unique();
            $table->decimal('nominal', 15, 2)->nullable();
            $table->string('status', 30)->default('menunggu_verifikasi'); // menunggu_verifikasi | lunas | ditolak
            $table->string('bukti_bayar')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->date('tanggal_bayar')->nullable();
            $table->string('catatan', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_pendaftaran');
    }
};
