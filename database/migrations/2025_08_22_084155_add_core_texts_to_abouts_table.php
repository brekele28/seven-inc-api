<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            $table->string('core_title', 120)->nullable()->after('right_p2');     // h2: "Core Value Perusahaan"
            $table->string('core_headline', 255)->nullable()->after('core_title'); // h3: "Prinsip Utama ..."
            $table->text('core_paragraph')->nullable()->after('core_headline');    // p: deskripsi
        });
    }

    public function down(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            $table->dropColumn(['core_title','core_headline','core_paragraph']);
        });
    }
};