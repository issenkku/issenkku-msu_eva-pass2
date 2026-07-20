<?php

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportCriteria;
use App\Models\SupportScore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    foreach (['ผู้ประเมิน', 'กรรมการ', 'ผู้บริหาร'] as $role) {
        Role::create(['name' => $role]);
    }

    $this->evaluatee = User::factory()->create();
    $this->evaluator = User::factory()->create();
    $this->director = User::factory()->create();
    $this->manager = User::factory()->create();
    $this->evaluator->assignRole('ผู้ประเมิน');
    $this->director->assignRole('กรรมการ');
    $this->manager->assignRole('ผู้บริหาร');

    $this->version = CriteriaVersion::factory()->create();
    $this->reportData = ReportData::factory()->create([
        'criteria_version_id' => $this->version->id,
    ]);
    $category = Category::factory()->create(['criteria_version_id' => $this->version->id]);
    $this->evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $this->version->id,
        'categorie_id' => $category->id,
    ]);
    $this->criterion = SupportCriteria::create([
        'evaluation_list_id' => $this->evaluationList->id,
        'sequence' => 1,
        'activity_name' => 'จัดทำรายงาน',
        'indicator' => 'ส่งตรงเวลา',
        'target_value' => 100,
        'weight' => 20,
        'require_evidence' => true,
    ]);

    $this->assignmentData = AssignmentData::factory()->create([
        'evaluator_id' => $this->evaluator->id,
        'director_id' => $this->director->id,
        'manager_id' => $this->manager->id,
        'evaluation_flow' => ['evaluator', 'director', 'manager'],
    ]);
});

dataset('support reviewer roles', [
    'evaluator' => ['ผู้ประเมิน', 'Pending', 'Evaluator_draft', 'evaluator.evaluator_score.store', 'evaluator', 'evaluator_comment'],
    'director' => ['กรรมการ', 'Director_assigned', 'Director_draft', 'director_score.store', 'director', 'director_comment'],
    'manager' => ['ผู้บริหาร', 'Manager_assign', 'Manager_draft', 'manager_score.store', 'manager', 'manager_comment'],
]);

test('each reviewer role must explain a changed support score and keeps its report comment', function (
    string $role,
    string $initialStatus,
    string $draftStatus,
    string $routeName,
    string $actorProperty,
    string $commentField
) {
    $report = createSupportReviewerReport($this, $initialStatus);
    $actor = $this->{$actorProperty};
    $comment = "ความคิดเห็นจาก{$role}";
    $payload = supportReviewerPayload($this->criterion->id, $draftStatus, 90, null, $comment);

    $this->actingAs($actor, 'web')
        ->from('/review-support')
        ->post(route($routeName, ['id' => $report->id]), $payload)
        ->assertRedirect('/review-support')
        ->assertSessionHasErrors([
            "support_list.{$this->criterion->id}.modification_reason",
        ]);

    $payload['support_list'][$this->criterion->id]['modification_reason'] = 'ปรับตามหลักฐาน';
    $this->actingAs($actor, 'web')
        ->post(route($routeName, ['id' => $report->id]), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('support_scores', [
        'report_id' => $report->id,
        'support_criteria_id' => $this->criterion->id,
        'achieved_score' => '90.00',
        'weighted_score' => '18.00',
    ]);
    $this->assertDatabaseHas('support_score_histories', [
        'report_id' => $report->id,
        'support_criteria_id' => $this->criterion->id,
        'previous_achieved_score' => '80.00',
        'new_achieved_score' => '90.00',
        'previous_weighted_score' => '16.00',
        'new_weighted_score' => '18.00',
        'reason' => 'ปรับตามหลักฐาน',
        'modifier_user_id' => $actor->id,
        'modifier_role' => $role,
    ]);
    $this->assertSame($comment, $report->fresh()->{$commentField});
})->with('support reviewer roles');

test('saving an unchanged support score does not create history', function () {
    $report = createSupportReviewerReport($this, 'Pending');
    $payload = supportReviewerPayload(
        $this->criterion->id,
        'Evaluator_draft',
        '80.00',
        null,
        'ตรวจสอบแล้ว'
    );

    $this->actingAs($this->evaluator, 'web')
        ->post(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload)
        ->assertRedirect();
    $this->actingAs($this->evaluator, 'web')
        ->post(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload)
        ->assertRedirect();

    $this->assertDatabaseCount('support_score_histories', 0);
});

function createSupportReviewerReport(object $context, string $status): Reports
{
    $report = Reports::factory()->create([
        'report_data_id' => $context->reportData->id,
        'status' => $status,
        'support_score_total' => 16,
    ]);
    Assignments::factory()->create([
        'assignment_data_id' => $context->assignmentData->id,
        'evaluatee_id' => $context->evaluatee->id,
        'report_id' => $report->id,
    ]);
    SupportScore::create([
        'report_id' => $report->id,
        'support_criteria_id' => $context->criterion->id,
        'achieved_score' => 80,
        'weighted_score' => 16,
    ]);
    EvidenceAnswer::create([
        'evaluation_list_id' => $context->evaluationList->id,
        'support_criteria_id' => $context->criterion->id,
        'report_id' => $report->id,
        'link' => 'https://example.com/support-evidence',
    ]);

    return $report;
}

function supportReviewerPayload(
    int $criterionId,
    string $status,
    string|int $score,
    ?string $reason,
    string $comment
): array {
    return [
        'support_list' => [
            $criterionId => [
                'support_criteria_id' => $criterionId,
                'achieved_score' => $score,
                'modification_reason' => $reason,
                'evidence_links' => ['https://example.com/support-evidence'],
            ],
        ],
        'status' => $status,
        'comment' => $comment,
    ];
}
