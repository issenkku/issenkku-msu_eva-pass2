<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_criterias', function (Blueprint $table): void {
            $table->boolean('allow_activity_entries')->default(false)->after('require_evidence');
        });

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

        Schema::create('support_activity_entry_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('support_activity_entry_id')
                ->constrained('support_activity_entries')
                ->cascadeOnDelete();
            $table->text('previous_content');
            $table->text('new_content');
            $table->text('reason');
            $table->foreignId('modified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('modified_by_role')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_activity_entry_histories');
        Schema::dropIfExists('support_activity_entries');

        Schema::table('support_criterias', function (Blueprint $table): void {
            $table->dropColumn('allow_activity_entries');
        });
    }
};
