<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indicator = collect(Schema::getColumns('support_criterias'))
            ->firstWhere('name', 'indicator');

        if ($indicator && ! $indicator['nullable']) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->text('indicator')->nullable()->change();
            });
        }

        if (! Schema::hasColumn('support_criterias', 'group_activity_entries_by_indicator')) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->boolean('group_activity_entries_by_indicator')
                    ->default(false)
                    ->after('allow_activity_entries');
            });
        }

        if (! Schema::hasTable('support_indicator_items')) {
            Schema::create('support_indicator_items', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('support_criteria_id');
                $table->unsignedInteger('sequence');
                $table->string('code', 50);
                $table->text('description');
                $table->timestamps();

                $table->foreign('support_criteria_id', 'support_indicator_criterion_fk')
                    ->references('id')->on('support_criterias')->cascadeOnDelete();
                $table->unique(
                    ['support_criteria_id', 'code'],
                    'support_indicator_criterion_code_unique'
                );
                $table->index(
                    ['support_criteria_id', 'sequence'],
                    'support_indicator_criterion_sequence_index'
                );
            });
        }

        if (! Schema::hasColumn('support_activity_entries', 'support_indicator_item_id')) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->unsignedBigInteger('support_indicator_item_id')
                    ->nullable()
                    ->after('support_criteria_id');
                $table->foreign('support_indicator_item_id', 'support_activity_entry_indicator_fk')
                    ->references('id')->on('support_indicator_items')->restrictOnDelete();
            });
        }

        $this->ensureForeignKeys();
    }

    public function down(): void
    {
        if (Schema::hasColumn('support_activity_entries', 'support_indicator_item_id')) {
            $foreignKey = $this->foreignKeyForColumn(
                Schema::getForeignKeys('support_activity_entries'),
                'support_indicator_item_id'
            );

            Schema::table('support_activity_entries', function (Blueprint $table) use ($foreignKey): void {
                if ($foreignKey) {
                    $table->dropForeign($foreignKey['name']);
                }
                $table->dropColumn('support_indicator_item_id');
            });
        }

        if (Schema::hasColumn('support_criterias', 'group_activity_entries_by_indicator')) {
            DB::table('support_criterias')
                ->whereNull('indicator')
                ->orderBy('id')
                ->eachById(function ($criterion): void {
                    $indicator = DB::table('support_indicator_items')
                        ->where('support_criteria_id', $criterion->id)
                        ->orderBy('sequence')
                        ->get()
                        ->map(fn ($item) => '<div><strong>'.e($item->code).'</strong> '.$item->description.'</div>')
                        ->implode('');

                    DB::table('support_criterias')
                        ->where('id', $criterion->id)
                        ->update([
                            'indicator' => $indicator !== '' ? $indicator : $criterion->activity_name,
                        ]);
                });

            Schema::dropIfExists('support_indicator_items');

            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->dropColumn('group_activity_entries_by_indicator');
                $table->text('indicator')->nullable(false)->change();
            });
        } else {
            Schema::dropIfExists('support_indicator_items');
        }
    }

    private function ensureForeignKeys(): void
    {
        if (! $this->foreignKeyForColumn(
            Schema::getForeignKeys('support_indicator_items'),
            'support_criteria_id'
        )) {
            Schema::table('support_indicator_items', function (Blueprint $table): void {
                $table->foreign('support_criteria_id', 'support_indicator_criterion_fk')
                    ->references('id')->on('support_criterias')->cascadeOnDelete();
            });
        }

        if (! $this->foreignKeyForColumn(
            Schema::getForeignKeys('support_activity_entries'),
            'support_indicator_item_id'
        )) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->foreign('support_indicator_item_id', 'support_activity_entry_indicator_fk')
                    ->references('id')->on('support_indicator_items')->restrictOnDelete();
            });
        }
    }

    /**
     * @param  array<int, array{name: string, columns: array<int, string>}>  $foreignKeys
     * @return array{name: string, columns: array<int, string>}|null
     */
    private function foreignKeyForColumn(array $foreignKeys, string $column): ?array
    {
        foreach ($foreignKeys as $foreignKey) {
            if (in_array($column, $foreignKey['columns'], true)) {
                return $foreignKey;
            }
        }

        return null;
    }
};
