<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->string('heading', 120);      // "Lowongan Kerja"
            $table->string('title', 255);        // "Berkarir bersama Seven INC."
            $table->string('subtitle', 500)->nullable(); // "Temukan peluang karir..."
            $table->string('hero_path')->nullable();     // gambar kanan (disimpan di storage/public)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};