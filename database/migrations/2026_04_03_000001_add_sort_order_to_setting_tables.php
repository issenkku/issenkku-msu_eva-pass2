<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addSortOrderColumn('positions', 'description');
        $this->addSortOrderColumn('departments', 'department_name');
        $this->addSortOrderColumn('job_levels', 'name');
        $this->addSortOrderColumn('subjects', 'self_study_credits');

        $this->backfillSortOrder('positions');
        $this->backfillSortOrder('departments');
        $this->backfillSortOrder('job_levels');
        $this->backfillSortOrder('subjects');

    }

    public function down(): void
    {
        $this->dropSortOrderColumn('subjects');
        $this->dropSortOrderColumn('job_levels');
        $this->dropSortOrderColumn('departments');
        $this->dropSortOrderColumn('positions');
    }

    private function addSortOrderColumn(string $table, string $after): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'sort_order')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($after) {
            $blueprint->unsignedInteger('sort_order')->nullable()->after($after);
        });
    }

    private function dropSortOrderColumn(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'sort_order')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('sort_order');
        });
    }

    private function backfillSortOrder(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'sort_order')) {
            return;
        }

        $rows = DB::table($table)->orderBy('id')->get(['id']);

        foreach ($rows as $index => $row) {
            DB::table($table)
                ->where('id', $row->id)
                ->update(['sort_order' => $index + 1]);
        }
    }
};
