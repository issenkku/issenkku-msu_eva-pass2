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
        //create table for report
        Schema::create('report_datas', function (Blueprint $table) {
            $table->id();
            $table->text('report_title');
            $table->text('report_description')->nullable();
            $table->string('assessment_type');
            $table->text('comment')->nullable();
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code')->unique();
            $table->string('status')->default('pending');
            $table->foreignId('report_data_id')->constrained('report_datas');
            $table->timestamps();
        });

        //create table for user score
        Schema::create('quantity_scores', function (Blueprint $table) {
            $table->decimal('score_C',5,2);
            $table->decimal('score_D',5,2);
            $table->foreignId('quantity_sub_criteria_id')->constrained('quantity_sub_criterias');
            $table->foreignId('evaluation_list_id')->constrained('evaluation_lists');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create('quality_scores', function (Blueprint $table) {
            $table->decimal('score',5,2);
            $table->foreignId('quality_sub_criteria_id')->constrained('quantity_sub_criterias');
            $table->foreignId('evaluation_list_id')->constrained('evaluation_lists');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->timestamps();
        });

        //create table for evidence storing
        Schema::create('evidence_answers', function (Blueprint $table) {
            $table->text('link');
            $table->foreignId('evaluation_list_id')->constrained('evaluation_lists');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_datas');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('quantity_scores');
        Schema::dropIfExists('quality_scores');
        Schema::dropIfExists('evidence_answers');
    }
};
