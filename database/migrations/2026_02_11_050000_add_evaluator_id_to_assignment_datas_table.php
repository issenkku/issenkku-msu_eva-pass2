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
        Schema::table('assignment_datas', function (Blueprint $table) {
            if (!Schema::hasColumn('assignment_datas', 'evaluator_id')) {
                $table->foreignId('evaluator_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_datas', function (Blueprint $table) {
            if (Schema::hasColumn('assignment_datas', 'evaluator_id')) {
                $table->dropConstrainedForeignId('evaluator_id');
            }
        });
    }
};
