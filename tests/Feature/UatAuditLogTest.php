<?php

use App\Models\Assignments;
use App\Models\AssignmentData;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use App\Models\WorkloadFormField;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

function uatLogCreateAdmin(): User
{
    Role::findOrCreate('admin');

    $admin = User::factory()->create([
        'name' => 'UAT Admin',
        'employee_id' => 'UATADMIN',
        'status' => 'active',
    ]);
    $admin->assignRole('admin');

    return $admin;
}

function uatLogCreateWorkloadContext(): array
{
    Role::findOrCreate('ผู้รับการประเมิน');

    $evaluatee = User::factory()->create([
        'name' => 'UAT Evaluatee',
        'employee_id' => 'UATEVAL',
        'status' => 'active',
    ]);
    $evaluatee->assignRole('ผู้รับการประเมิน');

    $report = Reports::factory()->create([
        'status' => 'Draft',
        'report_data_id' => ReportData::factory(),
    ]);

    Assignments::factory()->create([
        'assignment_data_id' => AssignmentData::factory(),
        'evaluatee_id' => $evaluatee->id,
        'report_id' => $report->id,
    ]);

    $form = WorkloadForm::create([
        'formula_logic' => 'hours * rate',
        'quantity_sub_criteria_id' => QuantitySubCriteria::factory()->create([
            'require_subject' => false,
            'require_evidence' => false,
        ])->id,
    ]);

    WorkloadFormField::create([
        'label' => 'Hours',
        'variable_name' => 'hours',
        'field_type' => 'number',
        'workload_form_id' => $form->id,
    ]);

    WorkloadFormField::create([
        'label' => 'Rate',
        'variable_name' => 'rate',
        'field_type' => 'number',
        'workload_form_id' => $form->id,
    ]);

    return compact('evaluatee', 'report', 'form');
}

function uatLogActingAsEvaluatee(User $evaluatee): void
{
    Sanctum::actingAs($evaluatee);
}

function uatLogWorkloadPayload(Reports $report, WorkloadForm $form, int $hours = 2, int $rate = 3): array
{
    return [
        'report_id' => $report->id,
        'workload_form_id' => $form->id,
        'field_values' => [
            'hours' => $hours,
            'rate' => $rate,
        ],
    ];
}

function uatLogLatestWorkloadActivity(string $description): ?Activity
{
    return Activity::query()
        ->where('subject_type', WorkloadEntry::class)
        ->where('description', $description)
        ->latest()
        ->first();
}

test('UAT-LOG-001 records a log when adding workload data', function () {
    ['evaluatee' => $evaluatee, 'report' => $report, 'form' => $form] = uatLogCreateWorkloadContext();

    uatLogActingAsEvaluatee($evaluatee);

    $this
        ->post(route('evaluatee.workload-entries.store'), uatLogWorkloadPayload($report, $form))
        ->assertRedirect();

    $activity = uatLogLatestWorkloadActivity('เพิ่มข้อมูลภาระงาน');

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('ภาระงาน')
        ->and($activity->event)->toBe('created')
        ->and($activity->causer_id)->toBe($evaluatee->id);
});

test('UAT-LOG-002 records a log when editing workload data', function () {
    ['evaluatee' => $evaluatee, 'report' => $report, 'form' => $form] = uatLogCreateWorkloadContext();
    $entry = WorkloadEntry::create([
        ...uatLogWorkloadPayload($report, $form),
        'calculated_score' => 6,
    ]);

    uatLogActingAsEvaluatee($evaluatee);

    $this
        ->put(route('evaluatee.workload-entries.update', $entry->id), uatLogWorkloadPayload($report, $form, 4, 3))
        ->assertRedirect();

    $activity = uatLogLatestWorkloadActivity('แก้ไขข้อมูลภาระงาน');

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('ภาระงาน')
        ->and($activity->event)->toBe('updated')
        ->and($activity->causer_id)->toBe($evaluatee->id)
        ->and(data_get($activity->properties->toArray(), 'attributes.field_values.hours'))->toBe(4)
        ->and(data_get($activity->properties->toArray(), 'old.field_values.hours'))->toBe(2);
});

test('UAT-LOG-003 records a log when deleting workload data', function () {
    ['evaluatee' => $evaluatee, 'report' => $report, 'form' => $form] = uatLogCreateWorkloadContext();
    $entry = WorkloadEntry::create([
        ...uatLogWorkloadPayload($report, $form),
        'calculated_score' => 6,
    ]);

    uatLogActingAsEvaluatee($evaluatee);

    $this
        ->delete(route('evaluatee.workload-entries.destroy', $entry->id))
        ->assertRedirect();

    $activity = uatLogLatestWorkloadActivity('ลบข้อมูลภาระงาน');

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('ภาระงาน')
        ->and($activity->event)->toBe('deleted')
        ->and($activity->causer_id)->toBe($evaluatee->id);
});

test('UAT-LOG-004 shows the actor name on the audit log page', function () {
    $admin = uatLogCreateAdmin();
    $actor = User::factory()->create([
        'name' => 'UAT Visible Actor',
        'email' => 'visible-actor@example.test',
    ]);

    activity()
        ->useLog('ภาระงาน')
        ->causedBy($actor)
        ->log('เพิ่มข้อมูลภาระงาน');

    $this->actingAs($admin, 'web')
        ->get(route('user.management.log'))
        ->assertOk()
        ->assertSee('UAT Visible Actor')
        ->assertSee('visible-actor@example.test');
});

test('audit log page shows description as event label when event is empty', function () {
    $admin = uatLogCreateAdmin();

    activity()
        ->useLog('Manual Audit')
        ->causedBy($admin)
        ->log('Manual audit action');

    $response = $this->actingAs($admin, 'web')
        ->get(route('user.management.log', ['event' => 'Manual audit action']))
        ->assertOk();

    $activity = $response->viewData('activities')->first();
    $eventNames = $response->viewData('eventNames');

    expect($activity->event)->toBeNull()
        ->and($activity->event_label)->toBe('Manual audit action')
        ->and($activity->description)->toBe('Manual audit action')
        ->and($eventNames)->toContain('Manual audit action');
});

test('UAT-LOG-005 shows the correct audit log timestamp', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-30 09:15:16', 'Asia/Bangkok'));
    $admin = uatLogCreateAdmin();

    activity()
        ->useLog('ภาระงาน')
        ->causedBy($admin)
        ->log('เพิ่มข้อมูลภาระงาน');

    Carbon::setTestNow();

    $response = $this->actingAs($admin, 'web')
        ->get(route('user.management.log'))
        ->assertOk();

    $activity = $response->viewData('activities')->first();

    expect($activity->thai_created_at)->toBe('30 มิถุนายน 2569')
        ->and($activity->thai_time)->toBe('09:15:16');
});
