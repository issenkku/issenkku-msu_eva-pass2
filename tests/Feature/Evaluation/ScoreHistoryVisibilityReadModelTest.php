<?php

namespace Tests\Feature\Evaluation;

use App\Http\Controllers\Evaluatee\DashboardEvaluateeController;
use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualityMainCriteria;
use App\Models\QualityScore;
use App\Models\QualityScoreHistory;
use App\Models\QualitySubCriteria;
use App\Models\QuantityMainCriteria;
use App\Models\QuantityScore;
use App\Models\QuantityScoreHistory;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use App\Services\ReportDataService;
use App\Support\SupportCriteriaReadModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ScoreHistoryVisibilityReadModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewer_read_model_excludes_evaluatee_and_legacy_histories_without_deleting_audit_rows(): void
    {
        $fixture = $this->createScoreHistoryFixture();

        $data = app(ReportDataService::class)->getReportData($fixture['report']->id);
        $list = $data['categoryItems'][0]['evaluation_lists'][0];
        $quantityHistories = $list['quantity_items'][0]['sub_criterias'][0]['score_histories'];
        $qualityHistories = $list['quality_items'][0]['sub_criterias'][0]['score_histories'];

        $this->assertCount(1, $quantityHistories);
        $this->assertSame($fixture['reviewer']->display_name, $quantityHistories[0]['modified_by_name']);
        $this->assertCount(1, $qualityHistories);
        $this->assertSame($fixture['reviewer']->display_name, $qualityHistories[0]['modified_by_name']);
        $this->assertDatabaseCount('quantity_score_histories', 3);
        $this->assertDatabaseCount('quality_score_histories', 3);
    }

    public function test_evaluatee_read_model_excludes_evaluatee_and_legacy_histories(): void
    {
        $fixture = $this->createScoreHistoryFixture();
        $request = Request::create(
            route('evaluation.show', ['id' => $fixture['report']->id]),
            'GET'
        );
        $request->setUserResolver(fn () => $fixture['evaluatee']);

        $view = app(DashboardEvaluateeController::class)->evaluation(
            $request,
            $fixture['report']->id,
            app(SupportCriteriaReadModel::class)
        );
        $list = $view->getData()['categoryItems'][0]['evaluation_lists'][0];

        $this->assertCount(
            1,
            $list['quantity_items'][0]['sub_criterias'][0]['score_histories']
        );
        $this->assertCount(
            1,
            $list['quality_items'][0]['sub_criterias'][0]['score_histories']
        );
    }

    /**
     * @return array{report: Reports, evaluatee: User, reviewer: User}
     */
    private function createScoreHistoryFixture(): array
    {
        $evaluatee = User::factory()->create();
        $reviewer = User::factory()->create();
        $version = CriteriaVersion::factory()->create();
        $reportData = ReportData::factory()->create(['criteria_version_id' => $version->id]);
        $report = Reports::factory()->create([
            'report_data_id' => $reportData->id,
            'status' => 'Draft',
        ]);
        $assignmentData = AssignmentData::factory()->create();
        Assignments::factory()->create([
            'assignment_data_id' => $assignmentData->id,
            'report_id' => $report->id,
            'evaluatee_id' => $evaluatee->id,
        ]);
        $category = Category::factory()->create(['criteria_version_id' => $version->id]);
        $list = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
            'quantity_enabled' => true,
            'sum_score' => 10,
        ]);
        $quantityMain = QuantityMainCriteria::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $quantitySub = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $list->id,
            'quantity_main_criteria_id' => $quantityMain->id,
        ]);
        $qualityMain = QualityMainCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'ratio' => 100,
        ]);
        $qualitySub = QualitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $list->id,
            'quality_main_criteria_id' => $qualityMain->id,
            'num_score' => 5,
        ]);

        QuantityScore::factory()->create([
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $quantitySub->id,
            'score_C' => 4,
        ]);
        QualityScore::factory()->create([
            'report_id' => $report->id,
            'quality_sub_criteria_id' => $qualitySub->id,
            'score' => 4,
        ]);

        foreach ([
            [$evaluatee->id, null, null],
            [null, null, null],
            [$reviewer->id, 'ผู้ประเมิน', 'แก้ตามหลักฐาน'],
        ] as [$modifierUserId, $modifierRole, $reason]) {
            QuantityScoreHistory::create([
                'report_id' => $report->id,
                'quantity_sub_criteria_id' => $quantitySub->id,
                'previous_score_c' => 3,
                'new_score_c' => 4,
                'reason' => $reason,
                'modifier_user_id' => $modifierUserId,
                'modifier_role' => $modifierRole,
            ]);
            QualityScoreHistory::create([
                'report_id' => $report->id,
                'quality_sub_criteria_id' => $qualitySub->id,
                'previous_score' => 3,
                'new_score' => 4,
                'reason' => $reason,
                'modifier_user_id' => $modifierUserId,
                'modifier_role' => $modifierRole,
            ]);
        }

        return compact('report', 'evaluatee', 'reviewer');
    }
}
