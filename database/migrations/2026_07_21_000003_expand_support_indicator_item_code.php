<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNIQUE_INDEX = 'support_indicator_criterion_code_unique';

    public function up(): void
    {
        if (! Schema::hasTable('support_indicator_items')
            || ! Schema::hasColumn('support_indicator_items', 'code')) {
            return;
        }

        if ($this->hasUniqueCodeIndex()) {
            Schema::table('support_indicator_items', function (Blueprint $table): void {
                $table->dropUnique(self::UNIQUE_INDEX);
            });
        }

        if (! in_array(
            Schema::getColumnType('support_indicator_items', 'code'),
            ['text', 'longtext'],
            true
        )) {
            Schema::table('support_indicator_items', function (Blueprint $table): void {
                $table->longText('code')->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('support_indicator_items')
            || ! Schema::hasColumn('support_indicator_items', 'code')) {
            return;
        }

        $hasLongCode = DB::table('support_indicator_items')
            ->pluck('code')
            ->contains(fn (?string $code): bool => mb_strlen($code ?? '') > 50);

        if ($hasLongCode) {
            throw new RuntimeException(
                'Cannot restore the 50-character support indicator code limit while longer values exist.'
            );
        }

        Schema::table('support_indicator_items', function (Blueprint $table): void {
            $table->string('code', 50)->change();
        });

        if (! $this->hasUniqueCodeIndex()) {
            Schema::table('support_indicator_items', function (Blueprint $table): void {
                $table->unique(
                    ['support_criteria_id', 'code'],
                    self::UNIQUE_INDEX
                );
            });
        }
    }

    private function hasUniqueCodeIndex(): bool
    {
        return collect(Schema::getIndexes('support_indicator_items'))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === self::UNIQUE_INDEX);
    }
};
