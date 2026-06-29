<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('workload_form_fields', 'default_value')) {
            return;
        }

        Schema::table('workload_form_fields', function (Blueprint $table) {
            $column = $table->string('default_value')->nullable();

            if (Schema::hasColumn('workload_form_fields', 'note')) {
                $column->after('note');
            } else {
                $column->after('label');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('workload_form_fields', 'default_value')) {
            return;
        }

        Schema::table('workload_form_fields', function (Blueprint $table) {
            $table->dropColumn('default_value');
        });
    }
};
