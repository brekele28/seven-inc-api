<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            // paragraf kiri (3 slot) & kanan (2 slot)
            $table->text('left_p1')->nullable()->after('hero_image3');
            $table->text('left_p2')->nullable()->after('left_p1');
            $table->text('left_p3')->nullable()->after('left_p2');

            $table->text('right_p1')->nullable()->after('left_p3');
            $table->text('right_p2')->nullable()->after('right_p1');
        });
    }

    public function down(): void
    {
        Schema::table('abouts', function (Blueprint $table) {
            $table->dropColumn(['left_p1','left_p2','left_p3','right_p1','right_p2']);
        });
    }
};