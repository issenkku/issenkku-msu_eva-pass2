<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_answers', function (Blueprint $table) {
            $table->foreignId('workload_entry_id')
                ->nullable()
                ->after('report_id')
                ->constrained('workload_entries')
                ->nullOnDelete();
            $table->index('workload_entry_id', 'evidence_answers_workload_entry_idx');
        });
    }

    public function down(): void
    {
        Schema::table('evidence_answers', function (Blueprint $table) {
            $table->dropForeign(['workload_entry_id']);
            $table->dropIndex('evidence_answers_workload_entry_idx');
            $table->dropColumn('workload_entry_id');
        });
    }
};
