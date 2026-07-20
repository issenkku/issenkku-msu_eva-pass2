<?php

namespace Tests\Feature\Evaluation;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\SupportScoreHistory;
use App\Models\User;
use App\Support\SupportCriteriaReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportCriteriaReadModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_support_items_grouped_by_evaluation_list(): void
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
            'activity_name' => 'จัดทำรายงาน',
            'indicator' => 'ส่งตรงเวลา',
            'target_value' => 12,
            'weight' => 20,
            'require_evidence' => true,
        ]);
        SupportScore::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'achieved_score' => 125.5,
            'weighted_score' => 25.1,
        ]);
        EvidenceAnswer::create([
            'evaluation_list_id' => $evaluationList->id,
            'support_criteria_id' => $criterion->id,
            'report_id' => $report->id,
            'link' => 'https://example.com/evidence',
        ]);

        $modifier = User::factory()->create([
            'prefix' => 'นาย',
            'name' => 'ผู้ตรวจสอบ',
        ]);
        $history = SupportScoreHistory::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'previous_achieved_score' => 100,
            'new_achieved_score' => 125.5,
            'previous_weighted_score' => 20,
            'new_weighted_score' => 25.1,
            'reason' => 'ปรับตามหลักฐาน',
            'modifier_user_id' => $modifier->id,
            'modifier_role' => 'ผู้ประเมิน',
        ]);

        $itemsByList = app(SupportCriteriaReadModel::class)->forReport($report);

        $this->assertArrayHasKey($evaluationList->id, $itemsByList);
        $this->assertSame([
            'id' => $criterion->id,
            'sequence' => 1,
            'activity_name' => 'จัดทำรายงาน',
            'indicator' => 'ส่งตรงเวลา',
            'target_value' => '12.00',
            'weight' => '20.00',
            'require_evidence' => true,
            'achieved_score' => '125.50',
            'weighted_score' => '25.10',
            'modification_reason' => null,
            'evidence_links' => ['https://example.com/evidence'],
            'histories' => [[
                'previous_achieved_score' => '100.00',
                'new_achieved_score' => '125.50',
                'previous_weighted_score' => '20.00',
                'new_weighted_score' => '25.10',
                'reason' => 'ปรับตามหลักฐาน',
                'modified_by_name' => $modifier->display_name,
                'modified_by_role' => 'ผู้ประเมิน',
                'created_at' => $history->created_at->format('d/m/Y H:i'),
            ]],
        ], $itemsByList[$evaluationList->id][0]);
    }
}
