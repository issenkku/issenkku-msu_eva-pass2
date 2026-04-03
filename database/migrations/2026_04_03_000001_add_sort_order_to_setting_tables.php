<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->nullable()->after('description');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->nullable()->after('department_name');
        });

        Schema::table('job_levels', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->nullable()->after('name');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->nullable()->after('self_study_credits');
        });

        $this->backfillSortOrder('positions');
        $this->backfillSortOrder('departments');
        $this->backfillSortOrder('job_levels');
        $this->backfillSortOrder('subjects');

    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('job_levels', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('positions', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }

    private function backfillSortOrder(string $table): void
    {
        $rows = DB::table($table)->orderBy('id')->get(['id']);

        foreach ($rows as $index => $row) {
            DB::table($table)
                ->where('id', $row->id)
                ->update(['sort_order' => $index + 1]);
        }
    }
};
