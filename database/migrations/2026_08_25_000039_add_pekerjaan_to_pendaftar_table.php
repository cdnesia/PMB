<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            // Pekerjaan pendaftar sendiri (bukan orang tua/wali) — opsional, diisi bila sudah bekerja
            // (mis. jalur RPL/Karyawan). Terpisah dari asal_sekolah agar tidak tercampur di satu kolom.
            $table->string('pekerjaan', 150)->nullable()->after('asal_sekolah');
        });
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn('pekerjaan');
        });
    }
};
