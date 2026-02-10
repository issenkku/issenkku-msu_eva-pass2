<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workload_form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('variable_name');
            $table->string('field_type');
            $table->foreignId('workload_form_id')->constrained('workload_forms')->onDelete('cascade');
            $table->timestamps();

            $table->index('workload_form_id', 'workload_form_fields_form_idx');
            $table->unique(['workload_form_id', 'variable_name'], 'workload_form_fields_variable_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workload_form_fields');
    }
};
