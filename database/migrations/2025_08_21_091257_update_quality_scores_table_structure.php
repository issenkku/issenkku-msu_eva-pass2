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
        if (! Schema::hasColumn('quality_scores', 'id')) {
            Schema::table('quality_scores', function (Blueprint $table) {
                $table->id()->first();
            });
        }

        if (! Schema::hasColumn('quality_scores', 'user_id')) {
            Schema::table('quality_scores', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('quality_scores', 'user_id')) {
            Schema::table('quality_scores', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }

        if (Schema::hasColumn('quality_scores', 'id')) {
            Schema::table('quality_scores', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }
    }
};
