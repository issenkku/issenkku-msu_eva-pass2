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
        Schema::create('criteria_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version_name');
            $table->foreignId('created_by')->constrained('users','id');
            $table->timestamps();
        });

        //create table for quantity criteria
        Schema::create('quantity_main_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });

        Schema::create('quantity_sub_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sequence');
            $table->decimal('score_A',5,2);
            $table->decimal('score_B',5,2);
            $table->foreignId('quantity_main_criteria_id')->constrained('quantity_main_criterias')->onDelete('cascade');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });

        //create table for quality criteria
        Schema::create('quality_main_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('ratio');
            $table->integer('sequence');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });

        Schema::create('quality_sub_criterias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sequence');
            $table->decimal('num_score',5,2)->nullable();
            $table->foreignId('quality_main_criteria_id')->constrained('quality_main_criterias')->onDelete('cascade');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });

        //create categories table
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('main_categories');
            $table->string('sub_categories');
            $table->integer('sequence');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });

        Schema::create('evaluation_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('sum_score',5,2)->nullable();
            $table->integer('sequence');
            $table->string('annotation');
            $table->foreignId('categorie_id')->constrained('categories')->onDelete('cascade');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criteria_versions');
        Schema::dropIfExists('quantity_main_criterias');
        Schema::dropIfExists('quantity_sub_criterias');
        Schema::dropIfExists('quality_main_criterias');
        Schema::dropIfExists('quality_sub_criterias');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('evaluation_lists');
    }
};
