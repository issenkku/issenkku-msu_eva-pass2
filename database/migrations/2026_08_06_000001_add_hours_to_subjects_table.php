<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('lecture_hours')->default(0)->after('self_study_credits');
            $table->integer('lab_hours')->default(0)->after('lecture_hours');
            $table->integer('self_study_hours')->default(0)->after('lab_hours');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['lecture_hours', 'lab_hours', 'self_study_hours']);
        });
    }
};
