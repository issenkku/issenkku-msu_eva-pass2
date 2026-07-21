<?php

namespace Tests\Feature\Evaluation;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportActivityEntryHistory;
use App\Models\SupportCriteria;
use App\Models\SupportIndicatorItem;
use App\Models\SupportScore;
use App\Models\SupportScoreHistory;
use App\Models\User;
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

    public function test_support_activity_migration_uses_mysql_safe_foreign_key_names(): void
    {
        $migration = file_get_contents(database_path(
            'migrations/2026_07_21_000001_create_support_activity_entries.php'
        ));

        $this->assertStringContainsString("'support_activity_history_entry_fk'", $migration);
        $this->assertLessThanOrEqual(64, strlen('support_activity_history_entry_fk'));
    }

    public function test_support_activity_migration_can_resume_after_a_partial_application(): void
    {
        $migration = require database_path(
            'migrations/2026_07_21_000001_create_support_activity_entries.php'
        );

        $migration->up();

        $this->assertTrue(Schema::hasColumn('support_criterias', 'allow_activity_entries'));
        $this->assertTrue(Schema::hasTable('support_activity_entries'));
        $this->assertTrue(Schema::hasTable('support_activity_entry_histories'));
    }

    public function test_support_evaluation_schema_and_relations_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('support_criterias', 'require_evidence'));
        $this->assertTrue(Schema::hasColumn('support_criterias', 'allow_activity_entries'));
        $this->assertTrue(Schema::hasTable('support_scores'));
        $this->assertTrue(Schema::hasTable('support_score_histories'));
        $this->assertTrue(Schema::hasTable('support_activity_entries'));
        $this->assertTrue(Schema::hasTable('support_activity_entry_histories'));
        $this->assertTrue(Schema::hasColumn('evidence_answers', 'support_criteria_id'));
        $this->assertTrue(Schema::hasColumn('reports', 'support_score_total'));

        $this->assertInstanceOf(SupportScore::class, (new SupportCriteria)->scores()->getModel());
        $this->assertInstanceOf(SupportScoreHistory::class, (new SupportCriteria)->scoreHistories()->getModel());
        $this->assertInstanceOf(EvidenceAnswer::class, (new SupportCriteria)->evidenceAnswers()->getModel());
        $this->assertInstanceOf(SupportActivityEntry::class, (new SupportCriteria)->activityEntries()->getModel());
        $this->assertInstanceOf(SupportScore::class, (new Reports)->supportScores()->getModel());
        $this->assertInstanceOf(SupportActivityEntry::class, (new Reports)->supportActivityEntries()->getModel());
        $this->assertInstanceOf(SupportActivityEntryHistory::class, (new SupportActivityEntry)->histories()->getModel());
    }

    public function test_support_indicator_items_schema_and_relations_exist(): void
    {
        $this->assertTrue(Schema::hasColumn(
            'support_criterias',
            'group_activity_entries_by_indicator'
        ));
        $this->assertTrue(Schema::hasTable('support_indicator_items'));
        $this->assertTrue(Schema::hasColumns('support_indicator_items', [
            'id',
            'support_criteria_id',
            'sequence',
            'code',
        ]));
        $this->assertFalse(Schema::hasColumn('support_indicator_items', 'description'));
        $this->assertContains(
            Schema::getColumnType('support_indicator_items', 'code'),
            ['text', 'longtext']
        );

        $hasUniqueCodeIndex = collect(Schema::getIndexes('support_indicator_items'))
            ->contains(fn (array $index): bool => ($index['unique'] ?? false)
                && in_array('code', $index['columns'] ?? [], true));

        $this->assertFalse($hasUniqueCodeIndex);
        $this->assertTrue(Schema::hasColumn(
            'support_activity_entries',
            'support_indicator_item_id'
        ));

        $indicatorColumn = collect(Schema::getColumns('support_criterias'))
            ->firstWhere('name', 'indicator');

        $this->assertTrue((bool) $indicatorColumn['nullable']);
        $this->assertInstanceOf(
            SupportIndicatorItem::class,
            (new SupportCriteria)->indicatorItems()->getModel()
        );
        $this->assertInstanceOf(
            SupportIndicatorItem::class,
            (new SupportActivityEntry)->indicatorItem()->getModel()
        );
    }

    public function test_support_activity_entries_and_histories_cascade_with_their_report(): void
    {
        $version = CriteriaVersion::factory()->create();
        $reportData = ReportData::factory()->create(['criteria_version_id' => $version->id]);
        $report = Reports::factory()->create(['report_data_id' => $reportData->id]);
        $category = Category::factory()->create(['criteria_version_id' => $version->id]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
        ]);
        $criterion = SupportCriteria::create([
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => 1,
            'activity_name' => 'งานตามหน้าที่',
            'indicator' => 'ส่งตรงเวลา',
            'target_value' => 100,
            'weight' => 20,
            'allow_activity_entries' => true,
        ]);
        $actor = User::factory()->create();
        $entry = SupportActivityEntry::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'sequence' => 1,
            'content' => '<p>จัดทำรายงาน</p>',
            'created_by' => $actor->id,
            'updated_by' => $actor->id,
        ]);
        SupportActivityEntryHistory::create([
            'support_activity_entry_id' => $entry->id,
            'previous_content' => '<p>รายงานเดิม</p>',
            'new_content' => '<p>จัดทำรายงาน</p>',
            'reason' => 'ปรับรายละเอียด',
            'modified_by' => $actor->id,
            'modified_by_role' => 'ผู้ประเมิน',
        ]);

        $report->delete();

        $this->assertDatabaseCount('support_activity_entries', 0);
        $this->assertDatabaseCount('support_activity_entry_histories', 0);
    }
}
