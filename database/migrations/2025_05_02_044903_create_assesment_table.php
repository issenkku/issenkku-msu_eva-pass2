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
        Schema::create('assesments', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('version');
            $table->foreignId('employee_type_id');
            $table->foreignId('main_topic_id');
            $table->timestamps();
        });

        Schema::create('main_topics', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active');
            $table->timestamps();
        });

        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->string('criteria_name')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('criterias')->onDelete('cascade');
            $table->foreignId('assesment_id')->constrained('assesments')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('criteria_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')->constrained('criterias')->onDelete('cascade');
            $table->decimal('weight',5,2)->nullable();
            $table->decimal('score',5,2)->nullable();
            $table->timestamps();
        });

        Schema::create('uploaders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->foreignId('criteria_id')->constrained('criterias')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('assesment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assesment_id');
            $table->foreignId('evaluator_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('evaluatee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criteria_scores');
        Schema::dropIfExists('uploader');
        Schema::dropIfExists('criterias');
        Schema::dropIfExists('assesments');
        Schema::dropIfExists('assesment_lines');
    }
};
