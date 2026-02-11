<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quantity_sub_criteria_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sequence');
            $table->foreignId('quantity_sub_criteria_id')->constrained('quantity_sub_criterias')->onDelete('cascade');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions')->onDelete('cascade');
            $table->foreignId('evaluation_list_id')->constrained('evaluation_lists')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quantity_sub_criteria_groups');
    }
};
