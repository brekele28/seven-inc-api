<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();
            $table->text('heading');
            $table->text('subheading')->nullable();
            $table->string('image_path')->nullable(); // simpan path relatif di storage/app/public
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            // Jika ingin membatasi agar selalu ada 1 record aktif, bisa tambahkan constraint unik via aplikasi.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_sections');
    }
};