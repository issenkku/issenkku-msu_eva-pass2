<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quantity_score_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->foreignId('quantity_sub_criteria_id')->constrained('quantity_sub_criterias')->cascadeOnDelete();
            $table->decimal('previous_score_c', 10, 2)->nullable();
            $table->decimal('new_score_c', 10, 2)->nullable();
            $table->text('previous_description')->nullable();
            $table->text('new_description')->nullable();
            $table->foreignId('modifier_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modifier_role')->nullable();
            $table->timestamps();

            $table->index(['report_id', 'quantity_sub_criteria_id'], 'qsh_report_sub_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quantity_score_histories');
    }
};
