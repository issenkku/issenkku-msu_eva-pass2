<?php

namespace Tests\Feature\Report;

use App\Console\Commands\NotifyEndDate;
use App\Console\Commands\UpdateReportStatuses;
use App\Models\Assignments;
use App\Models\AssignmentData;
use App\Models\CriteriaVersion;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\QuantitySubCriteria;
use App\Models\QualitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\Setting\Settings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $department;
    protected $directorPosition;
    protected $managerPosition;
    protected $evaluatorPosition;
    protected $evaluateePosition;
    protected $evaluatee;
    protected $evaluator;
    protected $director;
    protected $manager;
    protected $criteriaVersion;
    protected $reportData;
    protected $assignmentData;
    protected $quantitySubCriteria;
    protected $qualitySubCriteria;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'กรรมการ']); // Director role
        Role::create(['name' => 'ผู้บริหาร']); // Manager role
        Role::create(['name' => 'ผู้ประเมิน']); // Evaluator role
        Role::create(['name' => 'ผู้รับการประเมิน']); // Evaluatee role

        // Create department and positions
        $this->department = \Database\Factories\DepartmentFactory::new()->create();
        $this->directorPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Director']);
        $this->managerPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Manager']);
        $this->evaluatorPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Evaluator']);
        $this->evaluateePosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Staff']);

        // Create users for the assessment flow: evaluatee -> evaluator -> director -> manager
        $this->evaluatee = User::factory()->create([
            'employee_id' => 'EMP001',
            'email' => 'evaluatee@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->evaluateePosition->id,
        ]);
        $this->evaluatee->assignRole('ผู้รับการประเมิน');

        $this->evaluator = User::factory()->create([
            'employee_id' => 'EVA001',
            'email' => 'evaluator@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->evaluatorPosition->id,
        ]);
        $this->evaluator->assignRole('ผู้ประเมิน');

        $this->director = User::factory()->create([
            'employee_id' => 'DIR001',
            'email' => 'director@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->directorPosition->id,
        ]);
        $this->director->assignRole('กรรมการ');

        $this->manager = User::factory()->create([
            'employee_id' => 'MGR001',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->managerPosition->id,
        ]);
        $this->manager->assignRole('ผู้บริหาร');

        // Create criteria and report data
        $this->criteriaVersion = CriteriaVersion::factory()->create();
        $this->reportData = ReportData::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
        ]);

        // Create assignment data (evaluatee -> evaluator assignment)
        $this->assignmentData = AssignmentData::factory()->create([
            'evaluatee_position_id' => $this->evaluateePosition->id,
            'evaluator_position_id' => $this->evaluatorPosition->id,
            'start_time' => now(),
            'end_time' => now()->addDays(30),
        ]);

        // Create sub-criteria for testing
        $this->quantitySubCriteria = QuantitySubCriteria::factory()->create([
            'score_a' => 10,
            'score_b' => 5,
        ]);
        $this->qualitySubCriteria = QualitySubCriteria::factory()->create();

        \Database\Factories\SettingsFactory::new()->create([
            'notification_days' => 7,
        ]);

        // Fake mail for testing
        Mail::fake();
    }

    private function createReportWithStatus(string $status, ?AssignmentData $assignmentData = null): Reports
    {
        $report = Reports::factory()->create([
            'report_data_id' => $this->reportData->id,
            'status' => $status,
        ]);

        Assignments::factory()->create([
            'assignment_data_id' => $assignmentData?->id ?? $this->assignmentData->id,
            'evaluatee_id' => $this->evaluatee->id,
            'report_id' => $report->id,
        ]);

        return $report;
    }

    public function test_notice_evaluatee_when_didnt_send_report()
    {
        // Arrange: Create assignment data ending in 3 days
        $this->assignmentData->update([
            'end_time' => Carbon::today()->addDays(3),
        ]);

        // Create a draft report (not completed)
        $report = $this->createReportWithStatus('Draft');

        // Act: Run the notification command
        $this->artisan('notify:enddate')
            ->assertExitCode(0);

        // Assert: Verify emails were sent to both evaluator and evaluatee
        Mail::assertSent(\Illuminate\Mail\Mailable::class, 2); // Both evaluator and evaluatee
        
        // Check that both users received notification emails
        Mail::assertSent(\Illuminate\Mail\Mailable::class, function ($mail) {
            return in_array($mail->to[0]['address'], [
                $this->evaluator->email,
                $this->evaluatee->email
            ]);
        });
    }

    public function test_dont_notice_when_report_is_completed()
    {
        // Arrange: Create assignment data ending in 3 days
        $this->assignmentData->update([
            'end_time' => Carbon::today()->addDays(3),
        ]);

        // Create a completed report
        $report = $this->createReportWithStatus('Completed');

        // Act: Run the notification command
        $this->artisan('notify:enddate')
            ->assertExitCode(0);

        // Assert: No emails should be sent since report is completed
        Mail::assertNotSent(\Illuminate\Mail\Mailable::class);
    }

    public function test_dont_notice_when_past_end_date()
    {
        // Arrange: Create assignment data that ended yesterday
        $this->assignmentData->update([
            'end_time' => Carbon::today()->subDays(1),
        ]);

        // Create a draft report (not completed)
        $report = $this->createReportWithStatus('Draft');

        // Act: Run the notification command
        $this->artisan('notify:enddate')
            ->assertExitCode(0);

        // Assert: No emails should be sent since end date has passed
        Mail::assertNotSent(\Illuminate\Mail\Mailable::class);
    }

    public function test_update_report_status_to_pending_when_at_end_date()
    {
        // Arrange: Create assignment data that ended yesterday
        $endDate = Carbon::yesterday();
        $assignmentData = AssignmentData::factory()->create([
            'evaluatee_position_id' => $this->evaluateePosition->id,
            'evaluator_position_id' => $this->evaluatorPosition->id,
            'end_time' => $endDate,
        ]);

        $draftReport = $this->createReportWithStatus('Draft', $assignmentData);
        $assignedReport = $this->createReportWithStatus('Assigned', $assignmentData);
        $completedReport = $this->createReportWithStatus('Completed', $assignmentData);

        // Act: Run the update statuses command
        $this->artisan('reports:update-statuses')
            ->expectsOutput('Updated 2 reports to Pending.')
            ->assertExitCode(0);

        // Assert: Check that draft report was updated to Pending
        $this->assertDatabaseHas('reports', [
            'id' => $draftReport->id,
            'status' => 'Pending',
        ]);

        $this->assertDatabaseHas('reports', [
            'id' => $assignedReport->id,
            'status' => 'Pending',
        ]);

        // Assert: Check that completed report status remained unchanged
        $this->assertDatabaseHas('reports', [
            'id' => $completedReport->id,
            'status' => 'Completed',
        ]);
    }

    public function test_dont_update_reports_that_are_already_pending()
    {
        // Arrange: Create assignment data with passed end date
        $endDate = Carbon::yesterday();
        $assignmentData = AssignmentData::factory()->create([
            'evaluatee_position_id' => $this->evaluateePosition->id,
            'evaluator_position_id' => $this->evaluatorPosition->id,
            'end_time' => $endDate,
        ]);

        // Create report that's already Pending
        $pendingReport = $this->createReportWithStatus('Pending');

        // Act: Run the update statuses command
        $this->artisan('reports:update-statuses')
            ->expectsOutput('Updated 0 reports to Pending.')
            ->assertExitCode(0);

        // Assert: Report should remain Pending
        $this->assertDatabaseHas('reports', [
            'id' => $pendingReport->id,
            'status' => 'Pending',
        ]);
    }
    
}
