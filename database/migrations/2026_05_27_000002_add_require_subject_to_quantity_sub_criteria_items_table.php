<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quantity_sub_criteria_items', function (Blueprint $table) {
            $table->boolean('require_subject')->default(false)->after('evaluation_list_id');
        });

        $subCriteriaIds = DB::table('quantity_sub_criterias')
            ->where('require_subject', true)
            ->pluck('id');

        if ($subCriteriaIds->isNotEmpty()) {
            DB::table('quantity_sub_criteria_items')
                ->whereIn(
                    'quantity_sub_criteria_group_id',
                    DB::table('quantity_sub_criteria_groups')
                        ->whereIn('quantity_sub_criteria_id', $subCriteriaIds)
                        ->select('id')
                )
                ->update(['require_subject' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('quantity_sub_criteria_items', function (Blueprint $table) {
            $table->dropColumn('require_subject');
        });
    }
};
