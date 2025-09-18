<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('internship_formations', function (Blueprint $table) {
            $table->id();
            $table->string('subtitle')->nullable();
            $table->string('headline')->nullable();
            $table->text('paragraph')->nullable();
            $table->timestamps();
        });

        Schema::create('internship_formation_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formation_id')->constrained('internship_formations')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('image_path')->nullable(); // storage path
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('internship_formation_cards');
        Schema::dropIfExists('internship_formations');
    }
};