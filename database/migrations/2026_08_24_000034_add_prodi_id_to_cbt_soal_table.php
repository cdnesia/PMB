<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_soal', function (Blueprint $table) {
            // Soal spesifik prodi (mis. Anestesi) — null berarti berlaku untuk semua prodi di jalur ini.
            $table->foreignUuid('prodi_id')->nullable()->after('jalur_id')->constrained('prodi')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cbt_soal', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prodi_id');
        });
    }
};
