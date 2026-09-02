<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dari mana pendaftar tahu tentang UM Jambi — dipilih saat registrasi akun.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('sumber_informasi_id')->nullable()->after('referrer_id')->constrained('sumber_informasi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sumber_informasi_id');
        });
    }
};
