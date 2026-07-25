<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluation_lists', function (Blueprint $table) {
            $table->boolean('quantity_enabled')->default(false)->after('annotation');
        });

        DB::table('evaluation_lists')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('quantity_sub_criterias')
                    ->whereColumn(
                        'quantity_sub_criterias.evaluation_list_id',
                        'evaluation_lists.id',
                    );
            })
            ->update(['quantity_enabled' => true]);
    }

    public function down(): void
    {
        Schema::table('evaluation_lists', function (Blueprint $table) {
            $table->dropColumn('quantity_enabled');
        });
    }
};
