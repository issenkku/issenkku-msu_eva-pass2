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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->decimal('workload',5,2)->nullable();
            $table->decimal('total_score',5,2)->nullable();
            $table->foreignId('assesment_line_id')->constrained('assesment_lines')->onDelete('cascade');
            $table->foreignId('criteria_id')->constrained('criterias')->onDelete('cascade');
            $table->text('comment') ->nullable();
            $table->timestamps();
        });

        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->text('url');
            $table->foreignId('evaluation_id')->constrained('evaluations')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->decimal('final_score',5,2)->nullable();
            $table->enum('status', ['ผ่าน','ไม่ผ่าน','รอผล'])->default('รอผล');
            $table->foreignId('assesment_line_id')->constrained('assesment_lines')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('evidences');
        Schema::dropIfExists('status');
    }
};
