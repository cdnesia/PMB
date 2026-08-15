<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftar', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pendaftaran_id')->constrained('pendaftaran')->cascadeOnDelete()->unique();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            // Identitas
            $table->string('nik', 16)->unique();
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->string('jenis_kelamin', 1); // L | P
            $table->string('kewarganegaraan', 3)->default('WNI'); // WNI | WNA
            $table->string('negara', 60)->nullable();
            $table->string('negara_kode', 10)->nullable();
            $table->string('agama', 50)->nullable();
            $table->unsignedInteger('agama_kode')->nullable();
            $table->string('golongan_darah', 3)->nullable(); // A | B | AB | O | -
            $table->string('status_perkawinan', 20)->nullable();
            $table->string('kebutuhan_khusus', 50)->nullable();
            $table->boolean('penerima_kps')->default(false);
            $table->string('nomor_kps', 50)->nullable();

            // Alamat
            $table->text('alamat');
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('dusun', 100)->nullable();
            $table->string('jenis_tinggal', 100)->nullable();
            $table->string('jenis_tinggal_kode', 10)->nullable();
            $table->string('alat_transportasi', 100)->nullable();
            $table->string('alat_transportasi_kode', 10)->nullable();
            $table->string('pembiayaan', 100)->nullable();
            $table->string('pembiayaan_kode', 10)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('provinsi_kode', 10)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('kota_kode', 10)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kecamatan_kode', 10)->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kelurahan_kode', 15)->nullable();
            $table->string('kode_pos', 10)->nullable();

            // Pendidikan
            $table->string('asal_sekolah', 150);
            $table->string('tahun_lulus', 4)->nullable();

            // Data orang tua / wali
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

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftar');
    }
};
