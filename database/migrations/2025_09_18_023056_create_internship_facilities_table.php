<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('internship_facilities', function (Blueprint $table) {
            $table->id();
            $table->string('subtitle')->nullable();
            $table->string('headline')->nullable();
            $table->timestamps();
        });

        Schema::create('internship_facility_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('internship_facilities')->cascadeOnDelete();
            $table->text('text')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('internship_facility_items');
        Schema::dropIfExists('internship_facilities');
    }
};