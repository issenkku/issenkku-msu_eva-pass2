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
            'target_value' => 100,
            'weight' => 20,
            'require_evidence' => true,
        ]);
        $this->evaluatee = User::factory()->create();
        $this->evaluator = User::factory()->create();
    }

    public function test_it_calculates_and_keeps_the_uncapped_total_without_deleting_other_evidence(): void
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
            'achieved_score' => '125.50',
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
            'achieved_score' => '125.50',
            'weighted_score' => '25.10',
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'quality_main_criteria_id' => $qualityMain->id,
            'link' => 'https://example.com/quality',
        ]);
        $this->assertDatabaseCount('evidence_answers', 2);
        $this->assertSame(25.10, $result['support_score_total']);
        $this->assertSame('25.10', $this->report->fresh()->support_score_total);
    }

    public function test_required_evidence_is_required_even_when_score_is_blank(): void
    {
        try {
            app(SupportScoreService::class)->persist($this->report, [[
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => null,
                'evidence_links' => [],
            ]], $this->evaluatee, null, false);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.evidence_links', $exception->errors());
        }
    }

    public function test_the_persisted_total_is_not_capped_at_one_hundred(): void
    {
        $result = app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 600,
            'evidence_links' => ['https://example.com/evidence'],
        ]], $this->evaluatee, null, false);

        $this->assertSame(120.0, $result['support_score_total']);
        $this->assertSame('120.00', $this->report->fresh()->support_score_total);
    }

    public function test_required_criterion_cannot_be_omitted_from_the_payload(): void
    {
        try {
            app(SupportScoreService::class)->persist(
                $this->report,
                [],
                $this->evaluatee,
                null,
                false
            );
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list', $exception->errors());
        }
    }

    public function test_reviewer_change_requires_reason_and_records_history(): void
    {
        SupportScore::create([
            'report_id' => $this->report->id,
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 80,
            'weighted_score' => 16,
        ]);

        try {
            app(SupportScoreService::class)->persist($this->report, [[
                'support_criteria_id' => $this->criterion->id,
                'achieved_score' => 90,
                'evidence_links' => ['https://example.com/evidence'],
            ]], $this->evaluator, 'ผู้ประเมิน', true);
            $this->fail('Expected validation failure');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('support_list.0.modification_reason', $exception->errors());
        }

        app(SupportScoreService::class)->persist($this->report, [[
            'support_criteria_id' => $this->criterion->id,
            'achieved_score' => 90,
            'modification_reason' => 'ปรับตามหลักฐาน',
            'evidence_links' => ['https://example.com/evidence'],
        ]], $this->evaluator, 'ผู้ประเมิน', true);

        $this->assertDatabaseHas('support_score_histories', [
            'previous_achieved_score' => '80.00',
            'new_achieved_score' => '90.00',
            'previous_weighted_score' => '16.00',
            'new_weighted_score' => '18.00',
            'reason' => 'ปรับตามหลักฐาน',
            'modifier_user_id' => $this->evaluator->id,
            'modifier_role' => 'ผู้ประเมิน',
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
            'achieved_score' => 10,
            'evidence_links' => [],
        ]], $this->evaluatee, null, false);
    }
}
