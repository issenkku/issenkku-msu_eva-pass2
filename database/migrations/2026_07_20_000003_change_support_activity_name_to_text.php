<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_criterias', function (Blueprint $table): void {
            $table->text('activity_name')->change();
        });
    }

    public function down(): void
    {
        Schema::table('support_criterias', function (Blueprint $table): void {
            $table->string('activity_name')->change();
        });
    }
};
