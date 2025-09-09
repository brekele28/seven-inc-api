<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_works', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Posisi pekerjaan
            $table->string('company'); // Nama perusahaan
            $table->string('location'); // Lokasi pekerjaan
            $table->date('close_date'); // Tanggal penutupan lowongan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_works');
    }
};