<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            // Jika tiap lowongan punya satu set syarat:
            $table->foreignId('job_work_id')
                ->nullable()
                ->constrained('job_works')
                ->cascadeOnDelete();

            $table->longText('intro_text'); // paragraf pembuka
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            // Satu requirements per job_work_id (MySQL mengizinkan banyak NULL)
            $table->unique('job_work_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};