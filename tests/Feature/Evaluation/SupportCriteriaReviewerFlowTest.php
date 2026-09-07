<?php

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\SupportActivityEntry;
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
        'target_value' => 5,
        'weight' => 20,
        'require_evidence' => false,
        'allow_activity_entries' => true,
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
    $payload = supportReviewerPayload($this->criterion->id, $draftStatus, 5, null, $comment);

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
        'achieved_score' => '5.00',
        'weighted_score' => '1.00',
    ]);
    $this->assertDatabaseHas('support_score_histories', [
        'report_id' => $report->id,
        'support_criteria_id' => $this->criterion->id,
        'previous_achieved_score' => '4.00',
        'new_achieved_score' => '5.00',
        'previous_weighted_score' => '0.80',
        'new_weighted_score' => '1.00',
        'reason' => 'ปรับตามหลักฐาน',
        'modifier_user_id' => $actor->id,
        'modifier_role' => $role,
    ]);
    $this->assertSame($comment, $report->fresh()->{$commentField});
})->with('support reviewer roles');

test('each reviewer role must explain a changed support activity and keeps its report comment', function (
    string $role,
    string $initialStatus,
    string $draftStatus,
    string $routeName,
    string $actorProperty,
    string $commentField
) {
    $this->criterion->update([
        'indicator' => null,
        'group_activity_entries_by_indicator' => true,
    ]);
    $indicatorItem = $this->criterion->indicatorItems()->create([
        'sequence' => 1,
        'code' => '2.1',
        'description' => '<p>ดำเนินการวิจัย</p>',
    ]);
    $report = createSupportReviewerReport($this, $initialStatus);
    $actor = $this->{$actorProperty};
    $entry = SupportActivityEntry::create([
        'report_id' => $report->id,
        'support_criteria_id' => $this->criterion->id,
        'support_indicator_item_id' => $indicatorItem->id,
        'sequence' => 1,
        'content' => '<p>ข้อความเดิม</p>',
        'created_by' => $this->evaluatee->id,
        'updated_by' => $this->evaluatee->id,
    ]);
    $comment = "ตรวจแก้กิจกรรมโดย{$role}";
    $payload = supportReviewerPayload($this->criterion->id, $draftStatus, 4, null, $comment);
    $payload['support_list'][$this->criterion->id]['activity_entries'] = [[
        'id' => $entry->id,
        'support_indicator_item_id' => $indicatorItem->id,
        'content' => '<p>ข้อความที่ผู้ประเมินแก้ไข</p>',
        'modification_reason' => null,
    ]];

    $this->actingAs($actor, 'web')
        ->from('/review-support')
        ->post(route($routeName, ['id' => $report->id]), $payload)
        ->assertRedirect('/review-support')
        ->assertSessionHasErrors([
            "support_list.{$this->criterion->id}.activity_entries.0.modification_reason",
        ]);

    $payload['support_list'][$this->criterion->id]['activity_entries'][0]['modification_reason'] = 'ปรับตามผลงานจริง';
    $this->actingAs($actor, 'web')
        ->post(route($routeName, ['id' => $report->id]), $payload)
        ->assertRedirect();

    $this->assertDatabaseHas('support_activity_entries', [
        'id' => $entry->id,
        'content' => '<p>ข้อความที่ผู้ประเมินแก้ไข</p>',
        'updated_by' => $actor->id,
    ]);
    $this->assertDatabaseHas('support_activity_entry_histories', [
        'support_activity_entry_id' => $entry->id,
        'previous_content' => '<p>ข้อความเดิม</p>',
        'new_content' => '<p>ข้อความที่ผู้ประเมินแก้ไข</p>',
        'reason' => 'ปรับตามผลงานจริง',
        'modified_by' => $actor->id,
        'modified_by_role' => $role,
    ]);
    $this->assertSame($comment, $report->fresh()->{$commentField});
})->with('support reviewer roles');

test('each reviewer role cannot move a grouped project to another indicator item', function (
    string $role,
    string $initialStatus,
    string $draftStatus,
    string $routeName,
    string $actorProperty,
    string $commentField
) {
    $this->criterion->update([
        'indicator' => null,
        'group_activity_entries_by_indicator' => true,
    ]);
    $first = $this->criterion->indicatorItems()->create([
        'sequence' => 1, 'code' => '2.1', 'description' => '<p>หนึ่ง</p>',
    ]);
    $second = $this->criterion->indicatorItems()->create([
        'sequence' => 2, 'code' => '2.2', 'description' => '<p>สอง</p>',
    ]);
    $report = createSupportReviewerReport($this, $initialStatus);
    $entry = SupportActivityEntry::create([
        'report_id' => $report->id,
        'support_criteria_id' => $this->criterion->id,
        'support_indicator_item_id' => $first->id,
        'sequence' => 1,
        'content' => '<p>โครงการเดิม</p>',
        'created_by' => $this->evaluatee->id,
        'updated_by' => $this->evaluatee->id,
    ]);
    $payload = supportReviewerPayload(
        $this->criterion->id,
        $draftStatus,
        4,
        null,
        'ความคิดเห็นที่ต้อง rollback'
    );
    $payload['support_list'][$this->criterion->id]['activity_entries'] = [[
        'id' => $entry->id,
        'support_indicator_item_id' => $second->id,
        'content' => '<p>โครงการเดิม</p>',
    ]];

    $this->actingAs($this->{$actorProperty}, 'web')
        ->from('/review-support')
        ->post(route($routeName, ['id' => $report->id]), $payload)
        ->assertRedirect('/review-support')
        ->assertSessionHasErrors([
            "support_list.{$this->criterion->id}.activity_entries.0.support_indicator_item_id",
        ]);

    $this->assertDatabaseHas('support_activity_entries', [
        'id' => $entry->id,
        'support_indicator_item_id' => $first->id,
        'content' => '<p>โครงการเดิม</p>',
    ]);
    $this->assertDatabaseCount('support_activity_entry_histories', 0);
    $this->assertSame($initialStatus, $report->fresh()->status);
    $this->assertNull($report->fresh()->{$commentField});
})->with('support reviewer roles');

test('saving an unchanged support score does not create history', function () {
    $report = createSupportReviewerReport($this, 'Pending');
    $payload = supportReviewerPayload(
        $this->criterion->id,
        'Evaluator_draft',
        '4.00',
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

test('evaluator can forward unchanged evaluatee owned support scores without a reason', function () {
    $this->criterion->update([
        'indicator' => null,
        'weight' => null,
        'allow_evaluatee_indicator' => true,
        'allow_evaluatee_weight' => true,
    ]);
    $report = createSupportReviewerReport($this, 'Pending');
    $entry = SupportActivityEntry::create([
        'report_id' => $report->id,
        'support_criteria_id' => $this->criterion->id,
        'sequence' => 1,
        'content' => '<p>โครงการเดิม</p>',
        'indicator' => '<p>ตัวชี้วัดเดิม</p>',
        'weight' => 40,
        'achieved_score' => 4,
        'weighted_score' => 1.6,
        'created_by' => $this->evaluatee->id,
        'updated_by' => $this->evaluatee->id,
    ]);
    $payload = supportReviewerPayload(
        $this->criterion->id,
        'Director_assigned',
        4,
        null,
        'ตรวจสอบแล้ว ส่งต่อกรรมการ'
    );
    $payload['support_list'][$this->criterion->id]['achieved_score'] = null;
    $payload['support_list'][$this->criterion->id]['activity_entries'] = [[
        'id' => $entry->id,
        'content' => '<p>โครงการเดิม</p>',
        'indicator' => '<p>ตัวชี้วัดเดิม</p>',
        'weight' => '40.00',
        'achieved_score' => '4.00',
    ]];

    $this->actingAs($this->evaluator, 'web')
        ->post(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload)
        ->assertRedirect('/evaluator-dashboard')
        ->assertSessionHasNoErrors();

    $this->assertSame('Director_assigned', $report->fresh()->status);
    $this->assertDatabaseCount('support_activity_entry_histories', 0);
});

function createSupportReviewerReport(object $context, string $status): Reports
{
    $report = Reports::factory()->create([
        'report_data_id' => $context->reportData->id,
        'status' => $status,
        'support_score_total' => 0.8,
    ]);
    Assignments::factory()->create([
        'assignment_data_id' => $context->assignmentData->id,
        'evaluatee_id' => $context->evaluatee->id,
        'report_id' => $report->id,
    ]);
    SupportScore::create([
        'report_id' => $report->id,
        'support_criteria_id' => $context->criterion->id,
        'achieved_score' => 4,
        'weighted_score' => 0.8,
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
                'evidence_links' => [],
            ],
        ],
        'status' => $status,
        'comment' => $comment,
    ];
}
