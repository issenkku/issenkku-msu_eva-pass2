<?php

use App\Models\Assignments;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\Subject;
use App\Models\User;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

function subjectWithDifferentCreditsAndHours(): Subject
{
    return Subject::create([
        'code' => 'CREDIT-HOUR-01',
        'name_th' => 'รายวิชาทดสอบหน่วยกิตและชั่วโมง',
        'name_en' => 'Credit and Hour Separation',
        'credits' => 3,
        'lecture_credits' => 1,
        'lab_credits' => 1,
        'self_study_credits' => 1,
        'lecture_hours' => 2,
        'lab_hours' => 3,
        'self_study_hours' => 4,
        'is_active' => true,
    ]);
}

test('admin workload formula uses subject credits and never subject hours', function () {
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Sanctum::actingAs($admin);

    $report = Reports::factory()->create();
    $criteriaVersion = CriteriaVersion::factory()->create();
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $subCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $form = WorkloadForm::create([
        'formula_logic' => 'lecture_credits + lab_credits + self_study_credits',
        'quantity_sub_criteria_id' => $subCriteria->id,
    ]);
    $subject = subjectWithDifferentCreditsAndHours();

    $this->postJson(route('workload-entries.store'), [
        'report_id' => $report->id,
        'workload_form_id' => $form->id,
        'subject_id' => $subject->id,
        'field_values' => [],
    ])->assertCreated()->assertJsonPath('calculated_score', 3);

    $entry = WorkloadEntry::query()->firstOrFail();

    expect((float) $entry->calculated_score)->toBe(3.0)
        ->and($entry->field_values)->toMatchArray([
            'credits' => 3,
            'lecture_credits' => 1,
            'lab_credits' => 1,
            'self_study_credits' => 1,
        ])
        ->and($entry->field_values)->not->toHaveKeys([
            'lecture_hours',
            'lab_hours',
            'self_study_hours',
        ]);
});

test('evaluatee workload formula uses subject credits and never subject hours', function () {
    Role::findOrCreate('ผู้รับการประเมิน');
    $evaluatee = User::factory()->create();
    $evaluatee->assignRole('ผู้รับการประเมิน');
    Sanctum::actingAs($evaluatee);

    $criteriaVersion = CriteriaVersion::factory()->create();
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $report = Reports::factory()->create([
        'status' => 'Draft',
        'report_data_id' => ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ])->id,
    ]);
    Assignments::factory()->create([
        'report_id' => $report->id,
        'evaluatee_id' => $evaluatee->id,
    ]);
    $subCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'require_subject' => true,
        'require_evidence' => false,
    ]);
    $form = WorkloadForm::create([
        'formula_logic' => 'lecture_credits + lab_credits + self_study_credits',
        'quantity_sub_criteria_id' => $subCriteria->id,
    ]);
    $subject = subjectWithDifferentCreditsAndHours();

    $this->postJson(route('evaluatee.workload-entries.store'), [
        'report_id' => $report->id,
        'workload_form_id' => $form->id,
        'subject_id' => $subject->id,
        'field_values' => [],
    ])->assertOk()->assertJsonPath('total_score', 3);

    $entry = WorkloadEntry::query()->firstOrFail();

    expect((float) $entry->calculated_score)->toBe(3.0)
        ->and($entry->field_values)->not->toHaveKeys([
            'lecture_hours',
            'lab_hours',
            'self_study_hours',
        ]);
});
