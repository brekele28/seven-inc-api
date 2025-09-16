<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buat tabel 'abouts' lengkap hanya jika belum ada (aman untuk env yang sudah punya)
        if (!Schema::hasTable('abouts')) {
            Schema::create('abouts', function (Blueprint $table) {
                $table->id();

                // headline & subtitle (sesuai validator di controller)
                $table->string('subtitle', 100)->nullable();
                $table->string('headline', 255)->nullable();

                // hero images (path di storage/app/public)
                $table->string('hero_image1', 255)->nullable();
                $table->string('hero_image2', 255)->nullable();
                $table->string('hero_image3', 255)->nullable();

                // paragraphs (kiri 1..3, kanan 1..2)
                $table->text('left_p1')->nullable();
                $table->text('left_p2')->nullable();
                $table->text('left_p3')->nullable();
                $table->text('right_p1')->nullable();
                $table->text('right_p2')->nullable();

                // core value texts (gabungan dari migration lama)
                $table->string('core_title', 120)->nullable();   // sesuai file ALTER lama
                $table->string('core_headline', 255)->nullable();
                $table->text('core_paragraph')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Rollback: hapus tabel jika ada
        Schema::dropIfExists('abouts');
    }
};