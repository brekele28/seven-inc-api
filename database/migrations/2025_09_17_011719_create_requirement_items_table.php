<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('requirement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')
                ->constrained('requirements')
                ->cascadeOnDelete();

            $table->enum('type', ['umum', 'khusus', 'tanggung_jawab', 'benefit']);
            $table->text('text');                 // isi butir (bukan judul)
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['requirement_id', 'type', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requirement_items');
    }
};