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
        if (!Schema::hasColumn('assignment_datas', 'evaluator_position_id')) {
            Schema::table('assignment_datas', function (Blueprint $table) {
                $table->foreignId('evaluator_position_id')
                    ->nullable()
                    ->after('evaluator_id')
                    ->constrained('positions');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('assignment_datas', 'evaluator_position_id')) {
            Schema::table('assignment_datas', function (Blueprint $table) {
                $table->dropForeign(['evaluator_position_id']);
                $table->dropColumn('evaluator_position_id');
            });
        }
    }
};
