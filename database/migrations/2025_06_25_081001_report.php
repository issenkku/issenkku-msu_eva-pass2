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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_data_id')->constrained('report_datas')->onDelete('cascade');
            $table->string('status', 255);
            $table->timestamps();
        });
        Schema::create('quantity_scores', function (Blueprint $table) {
            $table->foreignId('quantity_sub_criteria_id')->constrained('quantity_sub_criterias')->onDelete('cascade');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->decimal('score_C', 5, 2)->nullable();
            $table->decimal('score_D', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['quantity_sub_criteria_id', 'report_id'], 'quantity_scores_all_idx');

        });
        Schema::create('quality_scores', function (Blueprint $table) {
            $table->foreignId('quality_sub_criteria_id')->constrained('quality_sub_criterias')->onDelete('cascade');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->decimal('score', 5, 2)->nullable();
            $table->timestamps();

            $table->index(['quality_sub_criteria_id', 'report_id'], 'quality_scores_all_idx');

        });
        Schema::create('evidence_answers', function (Blueprint $table) {
            $table->foreignId('evaluation_list_id')->constrained('evaluation_lists')->onDelete('cascade');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->text('link')->nullable();
            $table->timestamps();

            $table->index(['evaluation_list_id', 'report_id'], 'evidence_answers_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evidence_answers');
        Schema::dropIfExists('quality_scores');
        Schema::dropIfExists('quantity_scores');
        Schema::dropIfExists('reports');
    }
};
