<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workload_forms', function (Blueprint $table) {
            $table->foreignId('quantity_sub_criteria_item_id')
                ->nullable()
                ->after('quantity_sub_criteria_id')
                ->constrained('quantity_sub_criteria_items')
                ->onDelete('cascade');

            $table->index(
                'quantity_sub_criteria_item_id',
                'workload_forms_quantity_sub_criteria_item_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('workload_forms', function (Blueprint $table) {
            $table->dropIndex('workload_forms_quantity_sub_criteria_item_idx');
            $table->dropConstrainedForeignId('quantity_sub_criteria_item_id');
        });
    }
};
