<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->integer('lecture_credits')->default(0)->after('credits');
            $table->integer('lab_credits')->default(0)->after('lecture_credits');
            $table->integer('self_study_credits')->default(0)->after('lab_credits');
        });

        DB::table('subjects')->update([
            'lecture_credits' => DB::raw('credits'),
            'lab_credits' => 0,
            'self_study_credits' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['lecture_credits', 'lab_credits', 'self_study_credits']);
        });
    }
};
