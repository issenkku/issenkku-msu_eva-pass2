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
        // Report Version
        Schema::create('criteria_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_name')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Report Structure - Quantity table
        Schema::create('quantity_main_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('tooltips');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions')->onDelete('cascade');
        });
        Schema::create('quantity_sub_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sequence');
            $table->decimal('score_a', 5, 2);
            $table->decimal('score_b', 5, 2);
            $table->foreignId('quantity_main_criteria_id')->constrained('quantity_main_criterias')->onDelete('cascade');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });

        // Report Structure - Quality table
        Schema::create('quality_main_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('ratio');
            $table->text('tooltips');
            $table->integer('sequence');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions')->onDelete('cascade');
        });
        Schema::create('quality_sub_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sequence');
            $table->decimal('num_score', 5, 2);
            $table->foreignId('quality_main_criteria_id')->constrained('quality_main_criterias')->onDelete('cascade');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions')->onDelete('cascade');
        });

        // Report Structure - Main Report
        Schema::create('report_datas', function (Blueprint $table) {
            $table->id();
            $table->text('report_desc_bottom');
            $table->string('assessment_type');
            $table->text('comment');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_categories');
            $table->string('sub_categories');
            $table->integer('sequence');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions')->onDelete('cascade');
        });
        Schema::create('evaluation_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('sum_score', 5, 2);
            $table->integer('sequence');
            $table->string('annotation');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_lists');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('report_datas');
        Schema::dropIfExists('quality_sub_criterias');
        Schema::dropIfExists('quality_main_criterias');
        Schema::dropIfExists('quantity_sub_criterias');
        Schema::dropIfExists('quantity_main_criterias');
        Schema::dropIfExists('criteria_versions');
    }
};
