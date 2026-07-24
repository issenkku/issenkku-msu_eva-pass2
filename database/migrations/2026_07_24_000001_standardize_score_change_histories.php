<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('quantity_score_histories')
            && ! Schema::hasColumn('quantity_score_histories', 'reason')
        ) {
            Schema::table('quantity_score_histories', function (Blueprint $table) {
                $table->text('reason')->nullable()->after('new_description');
            });
        }

        if (! Schema::hasTable('quality_score_histories')) {
            Schema::create('quality_score_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
                $table->foreignId('quality_sub_criteria_id')->constrained('quality_sub_criterias')->cascadeOnDelete();
                $table->decimal('previous_score', 10, 2)->nullable();
                $table->decimal('new_score', 10, 2)->nullable();
                $table->text('reason')->nullable();
                $table->foreignId('modifier_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('modifier_role')->nullable();
                $table->timestamps();

                $table->index(
                    ['report_id', 'quality_sub_criteria_id'],
                    'qlsh_report_sub_index'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quality_score_histories');

        if (
            Schema::hasTable('quantity_score_histories')
            && Schema::hasColumn('quantity_score_histories', 'reason')
        ) {
            Schema::table('quantity_score_histories', function (Blueprint $table) {
                $table->dropColumn('reason');
            });
        }
    }
};
