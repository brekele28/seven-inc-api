<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('internship_core_headers', function (Blueprint $table) {
            $table->id();
            $table->string('core_title', 100)->nullable();
            $table->string('core_headline', 255)->nullable();
            $table->text('core_paragraph')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_core_headers');
    }
};