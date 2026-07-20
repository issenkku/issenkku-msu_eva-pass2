<?php

namespace Tests\Feature\Evaluation;

use App\Models\EvidenceAnswer;
use App\Models\Reports;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\SupportScoreHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupportEvaluationSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_evaluation_migration_does_not_depend_on_a_legacy_report_score_column(): void
    {
        $migration = file_get_contents(database_path(
            'migrations/2026_07_20_000002_create_support_evaluation_tables.php'
        ));

        $this->assertStringNotContainsString("->after('score')", $migration);
    }

    public function test_support_evaluation_migration_can_resume_after_a_partial_application(): void
    {
        $migration = require database_path(
            'migrations/2026_07_20_000002_create_support_evaluation_tables.php'
        );

        $migration->up();

        $this->assertTrue(Schema::hasTable('support_scores'));
        $this->assertTrue(Schema::hasTable('support_score_histories'));
        $this->assertTrue(Schema::hasColumn('reports', 'support_score_total'));
    }

    public function test_support_evaluation_schema_and_relations_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('support_criterias', 'require_evidence'));
        $this->assertTrue(Schema::hasTable('support_scores'));
        $this->assertTrue(Schema::hasTable('support_score_histories'));
        $this->assertTrue(Schema::hasColumn('evidence_answers', 'support_criteria_id'));
        $this->assertTrue(Schema::hasColumn('reports', 'support_score_total'));

        $this->assertInstanceOf(SupportScore::class, (new SupportCriteria)->scores()->getModel());
        $this->assertInstanceOf(SupportScoreHistory::class, (new SupportCriteria)->scoreHistories()->getModel());
        $this->assertInstanceOf(EvidenceAnswer::class, (new SupportCriteria)->evidenceAnswers()->getModel());
        $this->assertInstanceOf(SupportScore::class, (new Reports)->supportScores()->getModel());
    }
}
