<?php

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Reports;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function reportExportUser(string $role): User
{
    Role::findOrCreate($role, 'web');

    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole($role);

    return $user;
}

/**
 * @return array{User, Reports, Assignments}
 */
function reportExportAssignment(string $status = 'Completed'): array
{
    $evaluator = reportExportUser('ผู้ประเมิน');
    $report = Reports::factory()->create(['status' => $status]);
    $assignmentData = AssignmentData::factory()->create([
        'evaluator_id' => $evaluator->id,
    ]);
    $assignment = Assignments::factory()->create([
        'assignment_data_id' => $assignmentData->id,
        'report_id' => $report->id,
    ]);

    return [$evaluator, $report, $assignment];
}

test('evaluator cannot use the admin dashboard export endpoint', function () {
    Excel::fake();
    $evaluator = reportExportUser('ผู้ประเมิน');

    $this->actingAs($evaluator, 'web')
        ->get(route('admin.export.reports'))
        ->assertForbidden();
});

test('evaluator cannot export an unassigned report', function () {
    Excel::fake();
    $evaluator = reportExportUser('ผู้ประเมิน');
    [, $report] = reportExportAssignment();

    $this->actingAs($evaluator, 'web')
        ->get(route('single.reports.export', $report->id))
        ->assertForbidden();
});

test('single report export rejects reports that are not completed', function () {
    Excel::fake();
    [$evaluator, $report] = reportExportAssignment('Draft');

    $this->actingAs($evaluator, 'web')
        ->get(route('single.reports.export', $report->id))
        ->assertStatus(409);
});

test('assigned evaluator can export a completed report', function () {
    Excel::fake();
    [$evaluator, $report] = reportExportAssignment();

    $this->actingAs($evaluator, 'web')
        ->get(route('single.reports.export', $report->id))
        ->assertOk();

    $fileName = 'รายงานผลการประเมินรายบุคคล-'.str_replace(' ', '_', $report->assignments->evaluateeUser->name).'.xlsx';
    Excel::assertDownloaded($fileName);
});

test('admin can export a completed report', function () {
    Excel::fake();
    $admin = reportExportUser('admin');
    [, $report] = reportExportAssignment();

    $this->actingAs($admin, 'web')
        ->get(route('single.reports.export', $report->id))
        ->assertOk();
});
