<?php

namespace Tests\Feature\Evaluation;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportCriteria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportActivityEvidenceMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_discards_only_criterion_evidence_that_now_belongs_to_activities(): void
    {
        [$report, $evaluationList] = $this->reportAndList();
        $activityCriterion = $this->criterion($evaluationList, 1, true);
        $staticCriterion = $this->criterion($evaluationList, 2, false);

        foreach ([
            [$activityCriterion, 'https://example.com/activity'],
            [$staticCriterion, 'https://example.com/static'],
        ] as [$criterion, $link]) {
            EvidenceAnswer::create([
                'evaluation_list_id' => $evaluationList->id,
                'support_criteria_id' => $criterion->id,
                'report_id' => $report->id,
                'link' => $link,
            ]);
        }

        $migration = require database_path(
            'migrations/2026_07_25_000002_add_support_activity_entry_to_evidence_answers.php'
        );
        $migration->down();
        $migration->up();

        $this->assertDatabaseMissing('evidence_answers', [
            'support_criteria_id' => $activityCriterion->id,
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'support_criteria_id' => $staticCriterion->id,
            'link' => 'https://example.com/static',
        ]);
    }

    public function test_deleting_an_activity_entry_cascades_to_its_evidence(): void
    {
        [$report, $evaluationList] = $this->reportAndList();
        $criterion = $this->criterion($evaluationList, 1, true);
        $entry = SupportActivityEntry::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'sequence' => 1,
            'content' => '<p>โครงการหนึ่ง</p>',
        ]);
        EvidenceAnswer::create([
            'evaluation_list_id' => $evaluationList->id,
            'support_criteria_id' => $criterion->id,
            'support_activity_entry_id' => $entry->id,
            'report_id' => $report->id,
            'link' => 'https://example.com/activity',
        ]);

        $entry->delete();

        $this->assertDatabaseMissing('evidence_answers', [
            'support_activity_entry_id' => $entry->id,
        ]);
    }

    /** @return array{Reports, EvaluationList} */
    private function reportAndList(): array
    {
        $version = CriteriaVersion::factory()->create();
        $reportData = ReportData::factory()->create(['criteria_version_id' => $version->id]);
        $report = Reports::factory()->create(['report_data_id' => $reportData->id]);
        $category = Category::factory()->create(['criteria_version_id' => $version->id]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
        ]);

        return [$report, $evaluationList];
    }

    private function criterion(
        EvaluationList $evaluationList,
        int $sequence,
        bool $allowsActivities
    ): SupportCriteria {
        return SupportCriteria::create([
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => $sequence,
            'activity_name' => "เกณฑ์ {$sequence}",
            'indicator' => "ตัวชี้วัด {$sequence}",
            'target_value' => 100,
            'weight' => 20,
            'allow_activity_entries' => $allowsActivities,
        ]);
    }
}
