<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workload_entries', function (Blueprint $table) {
            $table->id();
            $table->json('field_values');
            $table->decimal('calculated_score', 10, 4)->nullable();
            $table->foreignId('report_id')->constrained('reports')->onDelete('cascade');
            $table->foreignId('workload_form_id')->constrained('workload_forms')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();

            $table->index(['report_id', 'workload_form_id'], 'workload_entries_report_form_idx');
            $table->index('subject_id', 'workload_entries_subject_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workload_entries');
    }
};
