<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_criterias', function (Blueprint $table): void {
            $table->decimal('weight', 5, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('support_criterias')
            ->whereNull('weight')
            ->update(['weight' => 0]);

        Schema::table('support_criterias', function (Blueprint $table): void {
            $table->decimal('weight', 5, 2)->nullable(false)->change();
        });
    }
};
