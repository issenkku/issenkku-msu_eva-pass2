<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quantity_sub_criterias', function (Blueprint $table) {
            $table->boolean('require_subject')->default(false)->after('require_evidence');
        });
    }

    public function down(): void
    {
        Schema::table('quantity_sub_criterias', function (Blueprint $table) {
            $table->dropColumn('require_subject');
        });
    }
};
