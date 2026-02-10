<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workload_forms', function (Blueprint $table) {
            $table->id();
            $table->text('formula_logic');
            $table->foreignId('quantity_sub_criteria_id')->constrained('quantity_sub_criterias')->onDelete('cascade');
            $table->timestamps();

            $table->index('quantity_sub_criteria_id', 'workload_forms_quantity_sub_criteria_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workload_forms');
    }
};
