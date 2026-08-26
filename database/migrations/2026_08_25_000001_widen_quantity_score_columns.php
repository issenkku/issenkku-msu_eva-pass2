<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quantity_scores', function (Blueprint $table) {
            $table->decimal('score_C', 10, 4)->nullable()->change();
            $table->decimal('score_D', 10, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('quantity_scores', function (Blueprint $table) {
            $table->decimal('score_C', 5, 2)->nullable()->change();
            $table->decimal('score_D', 5, 2)->nullable()->change();
        });
    }
};
