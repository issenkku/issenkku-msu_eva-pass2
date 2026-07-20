<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('support_criterias', 'require_evidence')) {
            Schema::table('support_criterias', function (Blueprint $table) {
                $table->boolean('require_evidence')->default(false)->after('weight');
            });
        }

        if (! Schema::hasTable('support_scores')) {
            Schema::create('support_scores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
                $table->foreignId('support_criteria_id')->constrained('support_criterias')->cascadeOnDelete();
                $table->decimal('achieved_score', 20, 2)->nullable();
                $table->decimal('weighted_score', 20, 2)->nullable();
                $table->text('modification_reason')->nullable();
                $table->foreignId('modifier_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('modifier_role')->nullable();
                $table->timestamps();

                $table->unique(['report_id', 'support_criteria_id'], 'support_scores_report_criteria_unique');
            });
        }

        if (! Schema::hasTable('support_score_histories')) {
            Schema::create('support_score_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
                $table->foreignId('support_criteria_id')->constrained('support_criterias')->cascadeOnDelete();
                $table->decimal('previous_achieved_score', 20, 2)->nullable();
                $table->decimal('new_achieved_score', 20, 2)->nullable();
                $table->decimal('previous_weighted_score', 20, 2)->nullable();
                $table->decimal('new_weighted_score', 20, 2)->nullable();
                $table->text('reason')->nullable();
                $table->foreignId('modifier_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('modifier_role')->nullable();
                $table->timestamps();

                $table->index(['report_id', 'support_criteria_id'], 'support_history_report_criteria_index');
            });
        }

        if (! Schema::hasColumn('evidence_answers', 'support_criteria_id')) {
            Schema::table('evidence_answers', function (Blueprint $table) {
                $table->foreignId('support_criteria_id')
                    ->nullable()
                    ->after('quality_main_criteria_id')
                    ->constrained('support_criterias')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasColumn('reports', 'support_score_total')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->decimal('support_score_total', 20, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('support_score_total');
        });

        Schema::table('evidence_answers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('support_criteria_id');
        });

        Schema::dropIfExists('support_score_histories');
        Schema::dropIfExists('support_scores');

        Schema::table('support_criterias', function (Blueprint $table) {
            $table->dropColumn('require_evidence');
        });
    }
};
