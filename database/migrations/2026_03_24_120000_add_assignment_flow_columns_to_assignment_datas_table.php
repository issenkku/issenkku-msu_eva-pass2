<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_datas', function (Blueprint $table) {
            if (! Schema::hasColumn('assignment_datas', 'director_id')) {
                $table->foreignId('director_id')
                    ->nullable()
                    ->after('evaluator_position_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('assignment_datas', 'director_position_id')) {
                $table->foreignId('director_position_id')
                    ->nullable()
                    ->after('director_id')
                    ->constrained('positions')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('assignment_datas', 'manager_id')) {
                $table->foreignId('manager_id')
                    ->nullable()
                    ->after('director_position_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('assignment_datas', 'manager_position_id')) {
                $table->foreignId('manager_position_id')
                    ->nullable()
                    ->after('manager_id')
                    ->constrained('positions')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('assignment_datas', 'evaluation_flow')) {
                $table->json('evaluation_flow')
                    ->nullable()
                    ->after('manager_position_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignment_datas', function (Blueprint $table) {
            if (Schema::hasColumn('assignment_datas', 'evaluation_flow')) {
                $table->dropColumn('evaluation_flow');
            }

            if (Schema::hasColumn('assignment_datas', 'manager_position_id')) {
                $table->dropForeign(['manager_position_id']);
                $table->dropColumn('manager_position_id');
            }

            if (Schema::hasColumn('assignment_datas', 'manager_id')) {
                $table->dropForeign(['manager_id']);
                $table->dropColumn('manager_id');
            }

            if (Schema::hasColumn('assignment_datas', 'director_position_id')) {
                $table->dropForeign(['director_position_id']);
                $table->dropColumn('director_position_id');
            }

            if (Schema::hasColumn('assignment_datas', 'director_id')) {
                $table->dropForeign(['director_id']);
                $table->dropColumn('director_id');
            }
        });
    }
};
