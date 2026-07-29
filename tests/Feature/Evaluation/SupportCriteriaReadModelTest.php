<?php

namespace Tests\Feature\Evaluation;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportActivityEntryHistory;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\SupportScoreHistory;
use App\Models\User;
use App\Support\SupportCriteriaReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SupportCriteriaReadModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_hides_evaluatee_and_legacy_support_histories_without_deleting_audit_rows(): void
    {
        $evaluatee = User::factory()->create();
        $reviewer = User::factory()->create();
        $version = CriteriaVersion::factory()->create();
        $reportData = ReportData::factory()->create(['criteria_version_id' => $version->id]);
        $report = Reports::factory()->create(['report_data_id' => $reportData->id]);
        Assignments::factory()->create([
            'assignment_data_id' => AssignmentData::factory()->create()->id,
            'report_id' => $report->id,
            'evaluatee_id' => $evaluatee->id,
        ]);
        $category = Category::factory()->create(['criteria_version_id' => $version->id]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
        ]);
        $criterion = SupportCriteria::create([
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => 1,
            'activity_name' => 'งานบริการวิชาการ',
            'indicator' => 'จำนวนโครงการ',
            'target_value' => 10,
            'weight' => 20,
            'allow_activity_entries' => true,
        ]);
        $entry = SupportActivityEntry::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'sequence' => 1,
            'content' => '<p>โครงการบริการวิชาการ</p>',
            'created_by' => $evaluatee->id,
            'updated_by' => $evaluatee->id,
        ]);

        foreach ([
            [$evaluatee->id, null, null],
            [null, null, null],
            [$reviewer->id, 'ผู้ประเมิน', 'แก้ตามหลักฐาน'],
        ] as [$modifierUserId, $modifierRole, $reason]) {
            SupportScoreHistory::create([
                'report_id' => $report->id,
                'support_criteria_id' => $criterion->id,
                'previous_achieved_score' => 3,
                'new_achieved_score' => 4,
                'previous_weighted_score' => 0.6,
                'new_weighted_score' => 0.8,
                'reason' => $reason,
                'modifier_user_id' => $modifierUserId,
                'modifier_role' => $modifierRole,
            ]);
            SupportActivityEntryHistory::create([
                'support_activity_entry_id' => $entry->id,
                'previous_content' => '<p>ข้อมูลเดิม</p>',
                'new_content' => '<p>โครงการบริการวิชาการ</p>',
                'reason' => $reason ?? 'บันทึกการแก้ไข',
                'modified_by' => $modifierUserId,
                'modified_by_role' => $modifierRole,
            ]);
        }

        $item = app(SupportCriteriaReadModel::class)
            ->forReport($report)[$evaluationList->id][0];

        $this->assertCount(1, $item['histories']);
        $this->assertSame($reviewer->display_name, $item['histories'][0]['modified_by_name']);
        $this->assertCount(1, $item['activity_entries'][0]['histories']);
        $this->assertSame(
            $reviewer->display_name,
            $item['activity_entries'][0]['histories'][0]['modified_by_name']
        );
        $this->assertDatabaseCount('support_score_histories', 3);
        $this->assertDatabaseCount('support_activity_entry_histories', 3);
    }

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
            'allow_activity_entries' => true,
        ]);
        SupportScore::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'achieved_score' => 125.5,
            'weighted_score' => 25.1,
        ]);
        $modifier = User::factory()->create([
            'prefix' => 'นาย',
            'name' => 'ผู้ตรวจสอบ',
        ]);
        $activityEntry = SupportActivityEntry::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'sequence' => 1,
            'content' => '<p>จัดทำรายงานประจำเดือน</p>',
            'created_by' => $modifier->id,
            'updated_by' => $modifier->id,
        ]);
        EvidenceAnswer::create([
            'evaluation_list_id' => $evaluationList->id,
            'support_criteria_id' => $criterion->id,
            'support_activity_entry_id' => $activityEntry->id,
            'report_id' => $report->id,
            'link' => 'https://example.com/evidence',
        ]);
        $activityHistory = SupportActivityEntryHistory::create([
            'support_activity_entry_id' => $activityEntry->id,
            'previous_content' => '<p>ข้อความเดิม</p>',
            'new_content' => '<p>จัดทำรายงานประจำเดือน</p>',
            'reason' => 'ปรับให้ตรงผลงานจริง',
            'modified_by' => $modifier->id,
            'modified_by_role' => 'ผู้ประเมิน',
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
            'allow_activity_entries' => true,
            'allow_evaluatee_indicator' => false,
            'allow_evaluatee_weight' => false,
            'group_activity_entries_by_indicator' => false,
            'indicator_items' => [],
            'activity_entries' => [[
                'id' => $activityEntry->id,
                'sequence' => 1,
                'support_indicator_item_id' => null,
                'content' => '<p>จัดทำรายงานประจำเดือน</p>',
                'indicator' => null,
                'weight' => null,
                'achieved_score' => null,
                'weighted_score' => null,
                'evidence_links' => ['https://example.com/evidence'],
                'histories' => [[
                    'previous_content' => '<p>ข้อความเดิม</p>',
                    'new_content' => '<p>จัดทำรายงานประจำเดือน</p>',
                    'previous_indicator' => null,
                    'new_indicator' => null,
                    'previous_weight' => null,
                    'new_weight' => null,
                    'previous_achieved_score' => null,
                    'new_achieved_score' => null,
                    'previous_weighted_score' => null,
                    'new_weighted_score' => null,
                    'reason' => 'ปรับให้ตรงผลงานจริง',
                    'modified_by_name' => $modifier->display_name,
                    'modified_by_role' => 'ผู้ประเมิน',
                    'created_at' => $activityHistory->created_at->format('d/m/Y H:i'),
                ]],
            ]],
            'achieved_score' => '125.50',
            'weighted_score' => '25.10',
            'modification_reason' => null,
            'evidence_links' => [],
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

    public function test_it_hides_existing_activity_entries_when_the_admin_option_is_disabled(): void
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
            'activity_name' => 'หัวข้อจากแอดมิน',
            'indicator' => 'ตัวชี้วัด',
            'target_value' => 5,
            'weight' => 20,
            'allow_activity_entries' => false,
        ]);
        SupportActivityEntry::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'sequence' => 1,
            'content' => '<p>ข้อมูลที่ยังเก็บไว้</p>',
        ]);
        EvidenceAnswer::create([
            'evaluation_list_id' => $evaluationList->id,
            'support_criteria_id' => $criterion->id,
            'report_id' => $report->id,
            'link' => 'https://example.com/static-evidence',
        ]);

        $itemsByList = app(SupportCriteriaReadModel::class)->forReport($report);

        $this->assertFalse($itemsByList[$evaluationList->id][0]['allow_activity_entries']);
        $this->assertSame([], $itemsByList[$evaluationList->id][0]['activity_entries']);
        $this->assertSame(
            ['https://example.com/static-evidence'],
            $itemsByList[$evaluationList->id][0]['evidence_links']
        );
        $this->assertDatabaseHas('support_activity_entries', [
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
        ]);
    }

    public function test_it_exposes_grouped_indicator_items_and_project_assignments(): void
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
            'activity_name' => '<p>งานวิจัย</p>',
            'indicator' => null,
            'target_value' => 100,
            'weight' => 20,
            'allow_activity_entries' => true,
            'group_activity_entries_by_indicator' => true,
        ]);
        $firstIndicator = $criterion->indicatorItems()->create([
            'sequence' => 1,
            'code' => '2.1',
        ]);
        $secondIndicator = $criterion->indicatorItems()->create([
            'sequence' => 2,
            'code' => '2.2',
        ]);

        foreach ([$firstIndicator->id, $firstIndicator->id, $secondIndicator->id] as $index => $indicatorId) {
            SupportActivityEntry::create([
                'report_id' => $report->id,
                'support_criteria_id' => $criterion->id,
                'support_indicator_item_id' => $indicatorId,
                'sequence' => $index + 1,
                'content' => '<p>โครงการ '.($index + 1).'</p>',
            ]);
        }

        $item = app(SupportCriteriaReadModel::class)->forReport($report)[$evaluationList->id][0];

        $this->assertTrue($item['group_activity_entries_by_indicator']);
        $this->assertNull($item['indicator']);
        $this->assertSame(['2.1', '2.2'], array_column($item['indicator_items'], 'code'));
        $this->assertSame(
            [$firstIndicator->id, $firstIndicator->id, $secondIndicator->id],
            array_column($item['activity_entries'], 'support_indicator_item_id')
        );
    }

    public function test_it_exposes_evaluatee_owned_entry_fields_and_criterion_aggregate(): void
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
            'activity_name' => '<p>งานตามหน้าที่</p>',
            'indicator' => '<p>ตัวชี้วัดจาก Admin</p>',
            'target_value' => 100,
            'weight' => null,
            'allow_activity_entries' => true,
            'allow_evaluatee_indicator' => true,
            'allow_evaluatee_weight' => true,
        ]);
        foreach ([
            ['indicator' => '<p>ตัวชี้วัดหนึ่ง</p>', 'weight' => 40, 'achieved_score' => 4, 'weighted_score' => 1.6],
            ['indicator' => '<p>ตัวชี้วัดสอง</p>', 'weight' => 60, 'achieved_score' => 5, 'weighted_score' => 3],
        ] as $index => $values) {
            SupportActivityEntry::create([
                'report_id' => $report->id,
                'support_criteria_id' => $criterion->id,
                'sequence' => $index + 1,
                'content' => '<p>โครงการ '.($index + 1).'</p>',
                ...$values,
            ]);
        }

        $item = app(SupportCriteriaReadModel::class)->forReport($report)[$evaluationList->id][0];

        $this->assertTrue($item['allow_evaluatee_indicator']);
        $this->assertTrue($item['allow_evaluatee_weight']);
        $this->assertNull($item['indicator']);
        $this->assertNull($item['achieved_score']);
        $this->assertSame('4.60', $item['weighted_score']);
        $this->assertSame('<p>ตัวชี้วัดหนึ่ง</p>', $item['activity_entries'][0]['indicator']);
        $this->assertSame('40.00', $item['activity_entries'][0]['weight']);
        $this->assertSame('4.00', $item['activity_entries'][0]['achieved_score']);
        $this->assertSame('1.60', $item['activity_entries'][0]['weighted_score']);
    }

    public function test_activity_evidence_query_does_not_reference_a_nonexistent_id_column(): void
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
            'activity_name' => '<p>กิจกรรม</p>',
            'target_value' => 100,
            'weight' => 20,
            'allow_activity_entries' => true,
        ]);
        $activityEntry = SupportActivityEntry::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'sequence' => 1,
            'content' => '<p>โครงการ</p>',
        ]);
        EvidenceAnswer::create([
            'evaluation_list_id' => $evaluationList->id,
            'support_criteria_id' => $criterion->id,
            'support_activity_entry_id' => $activityEntry->id,
            'report_id' => $report->id,
            'link' => 'https://example.com/evidence',
        ]);

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        app(SupportCriteriaReadModel::class)->forReport($report);

        $evidenceQuery = collect($queries)->first(
            fn (string $sql): bool => str_contains($sql, 'from "evidence_answers"')
                && str_contains($sql, '"support_activity_entry_id" in')
        );

        $this->assertNotNull($evidenceQuery);
        $this->assertDoesNotMatchRegularExpression('/select\s+"id",/i', $evidenceQuery);
        $this->assertDoesNotMatchRegularExpression('/order by\s+"id"/i', $evidenceQuery);
    }
}
