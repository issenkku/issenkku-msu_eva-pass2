<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workload_form_fields', function (Blueprint $table) {
            $table->string('default_value')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('workload_form_fields', function (Blueprint $table) {
            $table->dropColumn('default_value');
        });
    }
};
