<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workload_form_items', function (Blueprint $table) {
            $table->integer('score')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('workload_form_items', function (Blueprint $table) {
            $table->decimal('score', 10, 4)->nullable()->change();
        });
    }
};
