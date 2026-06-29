<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('settings', 'use_white_background')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $column = $table->boolean('use_white_background')->default(false);

            if (Schema::hasColumn('settings', 'background_path')) {
                $column->after('background_path');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('settings', 'use_white_background')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('use_white_background');
        });
    }
};
