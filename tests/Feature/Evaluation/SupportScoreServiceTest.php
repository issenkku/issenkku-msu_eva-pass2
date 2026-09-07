<?php

namespace Tests\Feature\Evaluation;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\QualityMainCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\User;
use App\Services\SupportScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SupportScoreServiceTest extends TestCase
{
    use RefreshDatabase;

    private Reports $report;

    private SupportCriteria $criterion;

    private User $evaluatee;

    private User $evaluator;

    protected function setUp(): void
    {
        parent::setUp();

        $version = CriteriaVersion::factory()->create();
        $reportData = ReportData::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $this->report = Reports::factory()->create([
            'report_data_id' => $reportData->id,
            'support_score_total' => 0,
        ]);
        $category = Category::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
        ]);
        $this->criterion = SupportCriteria::create([
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => 1,
            'activity_name' => 'จัดทำรายงาน',
            'indicator' => 'ส่งตรงเวลา',
            'target_value' => 5,
            'weight' => 20,
            'require_evidence' => true,
        ]);
        $this->evaluatee = User::factory()->create();
        $this->evaluator = User::factory()->create();
    }

    public function test_it_accepts_whole_criterion_scores_within_one_through_five_and_target(): void
    {
        foreach ([1, '5.00'] as $score) {
            app(SupportScoreService::class)->persist($this->report, [[
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => $score,
                'evidence_links' => ['https://example.com/evidence'],
            ]], $this->evaluatee, null, false);

            $this->assertDatabaseHas('support_scores', [
                'report_id' => $this->report->id,
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => number_format($score, 2, '.', ''),
            ]);
        }
    }

    #[DataProvider('invalidCriterionScores')]
    public function test_it_rejects_non_integer_criterion_scores_outside_one_through_five(
        int|float|string $score
    ): void {
        try {
            app(SupportScoreService::class)->persist($this->report, [[
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => $score,
                'evidence_links' => ['https://example.com/evidence'],
            ]], $this->evaluatee, null, false);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.achieved_score', $exception->errors());
        }
    }

    public static function invalidCriterionScores(): array
    {
        return [
            'zero' => [0],
            'above five' => [6],
            'decimal' => [3.5],
        ];
    }

    public function test_it_rejects_a_criterion_score_above_its_target(): void
    {
        $this->criterion->update(['target_value' => 3.5]);

        try {
            app(SupportScoreService::class)->persist($this->report, [[
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => 4,
                'evidence_links' => ['https://example.com/evidence'],
            ]], $this->evaluatee, null, false);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.achieved_score', $exception->errors());
            $this->assertStringContainsString(
                'ต้องไม่เกินระดับค่าเป้าหมาย 3.50',
                $exception->errors()['support_list.0.achieved_score'][0]
            );
        }
    }

    public function test_it_calculates_the_weighted_total_without_deleting_other_evidence(): void
    {
        $qualityMain = QualityMainCriteria::create([
            'criteria_version_id' => $this->report->reportData->criteria_version_id,
            'name' => 'คุณภาพงาน',
            'ratio' => 10,
            'sequence' => 1,
        ]);
        EvidenceAnswer::create([
            'evaluation_list_id' => $this->criterion->evaluation_list_id,
            'quality_main_criteria_id' => $qualityMain->id,
            'report_id' => $this->report->id,
            'link' => 'https://example.com/quality',
        ]);

        $result = app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 5,
            'modification_reason' => null,
            'evidence_links' => [
                ' https://example.com/evidence ',
                'https://example.com/evidence',
                '',
            ],
        ]], $this->evaluatee, null, false);

        $this->assertDatabaseHas('support_scores', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => '5.00',
            'weighted_score' => '1.00',
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'quality_main_criteria_id' => $qualityMain->id,
            'link' => 'https://example.com/quality',
        ]);
        $this->assertDatabaseCount('evidence_answers', 2);
        $this->assertSame(1.0, $result['support_score_total']);
        $this->assertSame('1.00', $this->report->fresh()->support_score_total);
    }

    public function test_it_persists_the_support_achievement_score_using_five_target_levels(): void
    {
        $this->criterion->update(['weight' => 100]);

        $result = app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 4,
            'evidence_links' => ['https://example.com/evidence'],
            'support_achievement_score' => 99,
        ]], $this->evaluatee, null, false);

        $this->assertSame(4.0, $result['support_score_total']);
        $this->assertSame(0.8, $result['support_achievement_score']);
        $this->assertDatabaseHas('reports', [
            'id' => $this->report->id,
            'support_score_total' => '4.00',
            'support_achievement_score' => '0.80',
        ]);
    }

    public function test_it_caps_persisted_support_totals_at_five_and_achievement_at_one(): void
    {
        $this->criterion->update([
            'weight' => 100,
            'require_evidence' => false,
        ]);
        $secondCriterion = SupportCriteria::create([
            'evaluation_list_id' => $this->criterion->evaluation_list_id,
            'sequence' => 2,
            'activity_name' => 'งานเพิ่มเติม',
            'indicator' => 'สำเร็จตามเป้าหมาย',
            'target_value' => 5,
            'weight' => 100,
            'require_evidence' => false,
        ]);

        $result = app(SupportScoreService::class)->persist($this->report, [
            [
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => 5,
                'evidence_links' => [],
            ],
            [
                'support_criteria_id' => $secondCriterion->id,
                'achieved_score' => 5,
                'evidence_links' => [],
            ],
        ], $this->evaluatee, null, false);

        $this->assertSame(5.0, $result['support_score_total']);
        $this->assertSame(1.0, $result['support_achievement_score']);
        $this->assertDatabaseHas('reports', [
            'id' => $this->report->id,
            'support_score_total' => '5.00',
            'support_achievement_score' => '1.00',
        ]);
    }

    public function test_incomplete_criterion_can_be_saved_without_score_or_required_evidence(): void
    {
        $result = app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => null,
            'evidence_links' => [],
        ]], $this->evaluatee, null, false);

        $this->assertSame(0.0, $result['support_score_total']);
        $this->assertDatabaseHas('support_scores', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => null,
            'weighted_score' => null,
        ]);
        $this->assertDatabaseCount('evidence_answers', 0);
    }

    public function test_activity_criterion_accepts_required_evidence_on_any_activity(): void
    {
        $this->criterion->update(['allow_activity_entries' => true]);

        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => null,
            'evidence_links' => [],
            'activity_entries' => [
                [
                    'content' => '<p>กิจกรรมไม่มีหลักฐาน</p>',
                    'evidence_links' => [],
                ],
                [
                    'content' => '<p>กิจกรรมมีหลักฐาน</p>',
                    'evidence_links' => ['https://example.com/proof'],
                ],
            ],
        ]], $this->evaluatee, null, false);

        $this->assertDatabaseHas('evidence_answers', [
            'support_criteria_id' => $this->criterion->id,
            'link' => 'https://example.com/proof',
        ]);
    }

    public function test_incomplete_activity_can_be_saved_without_required_evidence(): void
    {
        $this->criterion->update(['allow_activity_entries' => true]);

        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => null,
            'evidence_links' => [],
            'activity_entries' => [[
                'content' => '<p>กิจกรรมไม่มีหลักฐาน</p>',
                'evidence_links' => [],
            ]],
        ]], $this->evaluatee, null, false);

        $this->assertDatabaseHas('support_activity_entries', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'content' => '<p>กิจกรรมไม่มีหลักฐาน</p>',
        ]);
        $this->assertDatabaseCount('evidence_answers', 0);
    }

    public function test_evaluatee_weighted_criterion_uses_entry_scores_without_a_parent_score(): void
    {
        $this->criterion->update([
            'weight' => null,
            'allow_activity_entries' => true,
            'allow_evaluatee_indicator' => true,
            'allow_evaluatee_weight' => true,
        ]);

        $result = app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'activity_entries' => [
                [
                    'content' => '<p>โครงการหนึ่ง</p>',
                    'indicator' => '<p>ผ่านความเห็นชอบ</p>',
                    'weight' => 40,
                    'achieved_score' => 4,
                    'evidence_links' => ['https://example.com/project-one'],
                ],
                [
                    'content' => '<p>โครงการสอง</p>',
                    'indicator' => '<p>เผยแพร่แล้ว</p>',
                    'weight' => 60,
                    'achieved_score' => 5,
                    'evidence_links' => [],
                ],
            ],
        ]], $this->evaluatee, null, false);

        $this->assertDatabaseMissing('support_scores', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
        ]);
        $this->assertSame(4.6, $result['support_score_total']);
        $this->assertSame('4.60', $this->report->fresh()->support_score_total);
    }

    public function test_omitting_required_criterion_preserves_existing_scores_and_evidence(): void
    {
        $service = app(SupportScoreService::class);
        $service->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 5,
            'evidence_links' => ['https://example.com/preserved'],
        ]], $this->evaluatee, null, false);

        $result = $service->persist($this->report, [], $this->evaluatee, null, false);

        $this->assertSame(1.0, $result['support_score_total']);
        $this->assertDatabaseHas('support_scores', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 5,
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'link' => 'https://example.com/preserved',
        ]);
    }

    public function test_reviewer_change_requires_reason_and_records_history(): void
    {
        SupportScore::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 4,
            'weighted_score' => 0.8,
        ]);

        try {
            app(SupportScoreService::class)->persist($this->report, [[
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => 5,
                'evidence_links' => ['https://example.com/evidence'],
            ]], $this->evaluator, 'ผู้ประเมิน', true);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.modification_reason', $exception->errors());
        }

        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 5,
            'modification_reason' => 'ปรับตามหลักฐาน',
            'evidence_links' => ['https://example.com/evidence'],
        ]], $this->evaluator, 'ผู้ประเมิน', true);

        $this->assertDatabaseHas('support_score_histories', [
            'previous_achieved_score' => '4.00',
            'new_achieved_score' => '5.00',
            'previous_weighted_score' => '0.80',
            'new_weighted_score' => '1.00',
            'reason' => 'ปรับตามหลักฐาน',
            'modifier_user_id' => $this->evaluator->id,
            'modifier_role' => 'ผู้ประเมิน',
        ]);
    }

    public function test_reviewer_add_from_blank_requires_reason_and_records_history(): void
    {
        try {
            app(SupportScoreService::class)->persist($this->report, [[
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => 4,
                'evidence_links' => ['https://example.com/evidence'],
            ]], $this->evaluator, 'ผู้ประเมิน', true);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.modification_reason', $exception->errors());
        }

        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 4,
            'modification_reason' => 'เพิ่มคะแนนหลังตรวจหลักฐาน',
            'evidence_links' => ['https://example.com/evidence'],
        ]], $this->evaluator, 'ผู้ประเมิน', true);

        $this->assertDatabaseHas('support_score_histories', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'previous_achieved_score' => null,
            'new_achieved_score' => '4.00',
            'reason' => 'เพิ่มคะแนนหลังตรวจหลักฐาน',
        ]);
    }

    public function test_it_rejects_a_criterion_from_another_version(): void
    {
        $otherVersion = CriteriaVersion::factory()->create();
        $otherCategory = Category::factory()->create(['criteria_version_id' => $otherVersion->id]);
        $otherList = EvaluationList::factory()->create([
            'criteria_version_id' => $otherVersion->id,
            'categorie_id' => $otherCategory->id,
        ]);
        $otherCriterion = SupportCriteria::create([
            'evaluation_list_id' => $otherList->id,
            'sequence' => 1,
            'activity_name' => 'นอกแบบประเมิน',
            'indicator' => 'ไม่เกี่ยวข้อง',
            'target_value' => 1,
            'weight' => 1,
        ]);

        $this->expectException(ValidationException::class);

        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $otherCriterion->id,
            'achieved_score' => 1,
            'evidence_links' => [],
        ]], $this->evaluatee, null, false);
    }

    public function test_activity_validation_failure_rolls_back_score_and_evidence_changes(): void
    {
        $this->criterion->update(['allow_activity_entries' => false]);
        SupportScore::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 4,
            'weighted_score' => 0.8,
        ]);
        EvidenceAnswer::create([
            'evaluation_list_id' => $this->criterion->evaluation_list_id,
            'support_criteria_id' => $this->criterion->id,
            'report_id' => $this->report->id,
            'link' => 'https://example.com/original',
        ]);

        try {
            app(SupportScoreService::class)->persist($this->report, [[
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => 5,
                'evidence_links' => ['https://example.com/replacement'],
                'activity_entries' => [['content' => '<p>รายการที่ไม่อนุญาต</p>']],
            ]], $this->evaluatee, null, false);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.activity_entries', $exception->errors());
        }

        $this->assertDatabaseHas('support_scores', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => '4.00',
            'weighted_score' => '0.80',
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'link' => 'https://example.com/original',
        ]);
        $this->assertDatabaseMissing('evidence_answers', [
            'link' => 'https://example.com/replacement',
        ]);
    }
}
