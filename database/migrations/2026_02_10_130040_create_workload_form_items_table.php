<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workload_form_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->decimal('score', 10, 4)->nullable();
            $table->integer('sequence');
            $table->foreignId('workload_form_id')->constrained('workload_forms')->onDelete('cascade');
            $table->timestamps();

            $table->index('workload_form_id', 'workload_form_items_form_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workload_form_items');
    }
};
