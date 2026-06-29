<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('workload_form_fields', 'note')) {
            return;
        }

        Schema::table('workload_form_fields', function (Blueprint $table) {
            $table->string('note')->nullable()->after('label');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('workload_form_fields', 'note')) {
            return;
        }

        Schema::table('workload_form_fields', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
