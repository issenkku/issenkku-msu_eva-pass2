<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('support_criterias', 'allow_evaluatee_indicator')) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->boolean('allow_evaluatee_indicator')
                    ->default(false)
                    ->after('allow_activity_entries');
            });
        }

        if (! Schema::hasColumn('support_criterias', 'allow_evaluatee_weight')) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->boolean('allow_evaluatee_weight')
                    ->default(false)
                    ->after('allow_evaluatee_indicator');
            });
        }

        if (! Schema::hasColumn('support_activity_entries', 'indicator')) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->text('indicator')->nullable()->after('content');
            });
        }

        if (! Schema::hasColumn('support_activity_entries', 'weight')) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->decimal('weight', 5, 2)->nullable()->after('indicator');
            });
        }

        if (! Schema::hasColumn('support_activity_entries', 'achieved_score')) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->decimal('achieved_score', 5, 2)->nullable()->after('weight');
            });
        }

        if (! Schema::hasColumn('support_activity_entries', 'weighted_score')) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->decimal('weighted_score', 20, 2)->nullable()->after('achieved_score');
            });
        }

        if (! Schema::hasColumn('support_activity_entry_histories', 'previous_indicator')) {
            Schema::table('support_activity_entry_histories', function (Blueprint $table): void {
                $table->text('previous_indicator')->nullable()->after('new_content');
                $table->text('new_indicator')->nullable()->after('previous_indicator');
                $table->decimal('previous_weight', 5, 2)->nullable()->after('new_indicator');
                $table->decimal('new_weight', 5, 2)->nullable()->after('previous_weight');
                $table->decimal('previous_achieved_score', 5, 2)->nullable()->after('new_weight');
                $table->decimal('new_achieved_score', 5, 2)->nullable()->after('previous_achieved_score');
                $table->decimal('previous_weighted_score', 20, 2)->nullable()->after('new_achieved_score');
                $table->decimal('new_weighted_score', 20, 2)->nullable()->after('previous_weighted_score');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('support_activity_entry_histories', 'previous_indicator')) {
            Schema::table('support_activity_entry_histories', function (Blueprint $table): void {
                $table->dropColumn([
                    'previous_indicator',
                    'new_indicator',
                    'previous_weight',
                    'new_weight',
                    'previous_achieved_score',
                    'new_achieved_score',
                    'previous_weighted_score',
                    'new_weighted_score',
                ]);
            });
        }

        if (Schema::hasColumn('support_activity_entries', 'indicator')) {
            Schema::table('support_activity_entries', function (Blueprint $table): void {
                $table->dropColumn([
                    'indicator',
                    'weight',
                    'achieved_score',
                    'weighted_score',
                ]);
            });
        }

        if (Schema::hasColumn('support_criterias', 'allow_evaluatee_indicator')) {
            Schema::table('support_criterias', function (Blueprint $table): void {
                $table->dropColumn([
                    'allow_evaluatee_indicator',
                    'allow_evaluatee_weight',
                ]);
            });
        }
    }
};
