<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('settings', 'background_path')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $column = $table->string('background_path')->nullable();

            if (Schema::hasColumn('settings', 'logo_path')) {
                $column->after('logo_path');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('settings', 'background_path')) {
            return;
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('background_path');
        });
    }
};
