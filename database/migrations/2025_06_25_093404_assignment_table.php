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
        //ASSESSMENT_DATA
        Schema::create('assignment_datas', function(Blueprint $table){
            $table->id();
            $table->date('start_time');
            $table->date('end_time');
            $table->timestamps();
        });

        Schema::create('assignments', function(Blueprint $table){
            $table->foreignId('assignment_data_id')->constrained('assignment_datas')->onDelete('cascade');
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->foreignId('evaluatee')->constrained('users')->onDelete('cascade');
            $table->foreignId('evaluator')->constrained('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('assignment_datas');
    }
};
