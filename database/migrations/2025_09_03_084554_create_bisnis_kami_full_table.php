<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bisnis_kami_full', function (Blueprint $table) {
            $table->id();

            // Header
            $table->string('header_image')->nullable();
            $table->string('header_subtitle')->nullable();
            $table->string('header_title')->nullable();

            // Deskripsi umum
            $table->longText('general_description')->nullable();

            // Section: Seven Tech
            $table->string('seven_tech_title')->nullable();
            $table->longText('seven_tech_text')->nullable();
            $table->string('seven_tech_image')->nullable();

            // Section: Seven Style
            $table->string('seven_style_title')->nullable();
            $table->longText('seven_style_text')->nullable();
            $table->string('seven_style_image')->nullable();

            // Section: Seven Serve
            $table->string('seven_serve_title')->nullable();
            $table->longText('seven_serve_text')->nullable();
            $table->string('seven_serve_image')->nullable();

            // Section: Seven Edu
            $table->string('seven_edu_title')->nullable();
            $table->longText('seven_edu_text')->nullable();
            $table->string('seven_edu_image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('bisnis_kami_full');
    }
};