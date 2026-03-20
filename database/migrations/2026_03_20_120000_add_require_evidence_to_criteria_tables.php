<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quality_main_criterias', function (Blueprint $table) {
            $table->boolean('require_evidence')->default(false)->after('sequence');
        });

        Schema::table('quantity_sub_criterias', function (Blueprint $table) {
            $table->boolean('require_evidence')->default(false)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('quality_main_criterias', function (Blueprint $table) {
            $table->dropColumn('require_evidence');
        });

        Schema::table('quantity_sub_criterias', function (Blueprint $table) {
            $table->dropColumn('require_evidence');
        });
    }
};
