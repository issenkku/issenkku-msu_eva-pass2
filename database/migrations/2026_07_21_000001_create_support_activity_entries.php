<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('support_criterias', 'allow_activity_entries')) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->boolean('allow_activity_entries')->default(false)->after('require_evidence');
            });
        }

        if (! Schema::hasTable('support_activity_entries')) {
            Schema::create('support_activity_entries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
                $table->foreignId('support_criteria_id')->constrained('support_criterias')->cascadeOnDelete();
                $table->unsignedInteger('sequence');
                $table->text('content');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(
                    ['report_id', 'support_criteria_id', 'sequence'],
                    'support_activity_report_criterion_sequence_index'
                );
            });
        }

        if (! Schema::hasTable('support_activity_entry_histories')) {
            Schema::create('support_activity_entry_histories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('support_activity_entry_id');
                $table->text('previous_content');
                $table->text('new_content');
                $table->text('reason');
                $table->foreignId('modified_by')->nullable();
                $table->string('modified_by_role')->nullable();
                $table->timestamps();
            });
        }

        $this->ensureHistoryForeignKeys();
    }

    public function down(): void
    {
        Schema::dropIfExists('support_activity_entry_histories');
        Schema::dropIfExists('support_activity_entries');

        if (Schema::hasColumn('support_criterias', 'allow_activity_entries')) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->dropColumn('allow_activity_entries');
            });
        }
    }

    private function ensureHistoryForeignKeys(): void
    {
        $foreignKeys = Schema::getForeignKeys('support_activity_entry_histories');
        $hasEntryForeignKey = $this->hasForeignKeyForColumn($foreignKeys, 'support_activity_entry_id');
        $hasModifierForeignKey = $this->hasForeignKeyForColumn($foreignKeys, 'modified_by');

        if ($hasEntryForeignKey && $hasModifierForeignKey) {
            return;
        }

        Schema::table('support_activity_entry_histories', function (Blueprint $table) use (
            $hasEntryForeignKey,
            $hasModifierForeignKey
        ): void {
            if (! $hasEntryForeignKey) {
                $table->foreign('support_activity_entry_id', 'support_activity_history_entry_fk')
                    ->references('id')
                    ->on('support_activity_entries')
                    ->cascadeOnDelete();
            }

            if (! $hasModifierForeignKey) {
                $table->foreign('modified_by', 'support_activity_history_modifier_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * @param  array<int, array{columns: array<int, string>}>  $foreignKeys
     */
    private function hasForeignKeyForColumn(array $foreignKeys, string $column): bool
    {
        foreach ($foreignKeys as $foreignKey) {
            if (in_array($column, $foreignKey['columns'], true)) {
                return true;
            }
        }

        return false;
    }
};
