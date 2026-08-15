<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gelombang_jalur', function (Blueprint $table) {
            $table->foreignUuid('gelombang_id')->constrained('gelombang')->cascadeOnDelete();
            $table->foreignUuid('jalur_id')->constrained('jalur')->cascadeOnDelete();
            $table->primary(['gelombang_id', 'jalur_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gelombang_jalur');
    }
};
