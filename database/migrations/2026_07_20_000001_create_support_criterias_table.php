<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_criterias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_list_id')
                ->constrained('evaluation_lists')
                ->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('activity_name');
            $table->text('indicator');
            $table->decimal('target_value', 10, 2);
            $table->decimal('weight', 5, 2);
            $table->timestamps();

            $table->index(['evaluation_list_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_criterias');
    }
};
