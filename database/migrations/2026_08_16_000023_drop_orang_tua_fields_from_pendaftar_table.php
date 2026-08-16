<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn([
                'golongan_darah',
                'status_perkawinan',
                'kebutuhan_khusus',
                'penerima_kps',
                'nomor_kps',
                'nama_ayah',
                'nik_ayah',
                'pekerjaan_ayah',
                'pekerjaan_ayah_kode',
                'penghasilan_ayah',
                'penghasilan_ayah_kode',
                'nama_ibu_kandung',
                'nik_ibu',
                'pekerjaan_ibu',
                'pekerjaan_ibu_kode',
                'penghasilan_ibu',
                'penghasilan_ibu_kode',
                'nama_wali',
                'nik_wali',
                'pekerjaan_wali',
                'pekerjaan_wali_kode',
                'penghasilan_wali',
                'penghasilan_wali_kode',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->string('golongan_darah', 3)->nullable();
            $table->string('status_perkawinan', 20)->nullable();
            $table->string('kebutuhan_khusus', 50)->nullable();
            $table->boolean('penerima_kps')->default(false);
            $table->string('nomor_kps', 50)->nullable();
            $table->string('nama_ayah', 100)->nullable();
            $table->string('nik_ayah', 16)->nullable();
            $table->string('pekerjaan_ayah', 100)->nullable();
            $table->unsignedInteger('pekerjaan_ayah_kode')->nullable();
            $table->string('penghasilan_ayah', 100)->nullable();
            $table->unsignedInteger('penghasilan_ayah_kode')->nullable();
            $table->string('nama_ibu_kandung', 100)->nullable();
            $table->string('nik_ibu', 16)->nullable();
            $table->string('pekerjaan_ibu', 100)->nullable();
            $table->unsignedInteger('pekerjaan_ibu_kode')->nullable();
            $table->string('penghasilan_ibu', 100)->nullable();
            $table->unsignedInteger('penghasilan_ibu_kode')->nullable();
            $table->string('nama_wali', 100)->nullable();
            $table->string('nik_wali', 16)->nullable();
            $table->string('pekerjaan_wali', 100)->nullable();
            $table->unsignedInteger('pekerjaan_wali_kode')->nullable();
            $table->string('penghasilan_wali', 100)->nullable();
            $table->unsignedInteger('penghasilan_wali_kode')->nullable();
        });
    }
};