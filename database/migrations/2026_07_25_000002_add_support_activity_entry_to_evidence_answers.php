<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('evidence_answers', 'support_activity_entry_id')) {
            return;
        }

        DB::table('evidence_answers')
            ->whereNotNull('support_criteria_id')
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('support_criterias')
                    ->whereColumn(
                        'support_criterias.id',
                        'evidence_answers.support_criteria_id'
                    )
                    ->where('support_criterias.allow_activity_entries', true);
            })
            ->delete();

        Schema::table('evidence_answers', function (Blueprint $table): void {
            $table->foreignId('support_activity_entry_id')
                ->nullable()
                ->after('support_criteria_id')
                ->constrained('support_activity_entries')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('evidence_answers', 'support_activity_entry_id')) {
            return;
        }

        Schema::table('evidence_answers', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('support_activity_entry_id');
        });
    }
};
