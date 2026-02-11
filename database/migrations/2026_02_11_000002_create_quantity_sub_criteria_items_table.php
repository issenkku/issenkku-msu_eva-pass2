<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quantity_sub_criteria_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sequence');
            $table->decimal('score_a', 5, 2)->default(0);
            $table->decimal('score_b', 5, 2)->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('quantity_sub_criteria_group_id');
            $table->foreignId('criteria_version_id')->constrained('criteria_versions')->onDelete('cascade');
            $table->foreignId('evaluation_list_id')->constrained('evaluation_lists')->onDelete('cascade');

            $table->foreign(
                'quantity_sub_criteria_group_id',
                'qsc_items_group_fk'
            )->references('id')->on('quantity_sub_criteria_groups')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quantity_sub_criteria_items');
    }
};
