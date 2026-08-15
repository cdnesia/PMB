<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wilayah', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 20)->unique();
            $table->string('nama', 150);
            $table->string('level', 20); // provinsi | kota | kecamatan | kelurahan
            $table->foreignUuid('parent_id')->nullable()->constrained('wilayah')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wilayah');
    }
};
