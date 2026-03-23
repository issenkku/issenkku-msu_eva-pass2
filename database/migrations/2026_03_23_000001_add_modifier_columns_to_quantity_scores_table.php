<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quantity_scores', function (Blueprint $table) {
            $table->foreignId('modifier_user_id')
                ->nullable()
                ->after('description')
                ->constrained('users')
                ->nullOnDelete();
            $table->string('modifier_role', 255)
                ->nullable()
                ->after('modifier_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('quantity_scores', function (Blueprint $table) {
            $table->dropForeign(['modifier_user_id']);
            $table->dropColumn(['modifier_user_id', 'modifier_role']);
        });
    }
};
