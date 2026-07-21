<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('support_indicator_items', 'description')) {
            Schema::table('support_indicator_items', function (Blueprint $table): void {
                $table->dropColumn('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('support_indicator_items')
            && ! Schema::hasColumn('support_indicator_items', 'description')) {
            Schema::table('support_indicator_items', function (Blueprint $table): void {
                $table->text('description')->nullable()->after('code');
            });
        }
    }
};
