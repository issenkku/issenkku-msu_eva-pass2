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
        Schema::table('evidence_answers', function (Blueprint $table) {
            $table->foreignId('quality_main_criteria_id')
                ->nullable()
                ->after('evaluation_list_id')
                ->constrained('quality_main_criterias')
                ->nullOnDelete();

            $table->index('quality_main_criteria_id', 'evidence_answers_quality_main_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evidence_answers', function (Blueprint $table) {
            $table->dropIndex('evidence_answers_quality_main_idx');
            $table->dropConstrainedForeignId('quality_main_criteria_id');
        });
    }
};
