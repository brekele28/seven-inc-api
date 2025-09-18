<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('internship_terms', function (Blueprint $table) {
            $table->id();
            $table->string('subtitle')->nullable(); 
            $table->string('headline')->nullable(); 
            $table->json('items')->nullable();        
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_terms');
    }
};