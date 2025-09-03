<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('excerpt', 500)->nullable();
            $table->longText('body');
            $table->string('cover_path')->nullable();  // storage path (public)
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->string('author')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_published', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};