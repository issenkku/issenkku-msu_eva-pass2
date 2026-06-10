<?php

namespace Tests\Feature\Report;

use Tests\TestCase;
use App\Models\User;
use App\Models\AssignmentData;
use App\Models\CriteriaVersion;
use App\Models\ReportData;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class AssignmentDataTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Departments $department;
    protected Positions $evaluateePosition;
    protected Positions $evaluatorPosition;
    protected ReportData $reportData;
    protected CriteriaVersion $criteriaVersion;

    protected function setUp(): void
    {
        parent::setUp();

        // roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'ผู้ประเมิน']);
        Role::create(['name' => 'ผู้รับการประเมิน']);

        // deps + positions
        $this->department = \Database\Factories\DepartmentFactory::new()->create();
        $this->evaluateePosition = \Database\Factories\PositionFactory::new()->create();
        $this->evaluatorPosition = \Database\Factories\PositionFactory::new()->create();

        // users
        $this->admin = User::factory()->create([
            'employee_id'   => 'ADMIN001',
            'password'      => Hash::make('password'),
            'status'        => 'active',
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluateePosition->id,
        ]);
        $this->admin->assignRole('admin');

        $this->criteriaVersion = CriteriaVersion::factory()->create();

        $this->reportData = ReportData::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
        ]);

        $this->evaluateePosition = \Database\Factories\PositionFactory::new()->create();
        $this->evaluatorPosition = \Database\Factories\PositionFactory::new()->create();
    }

    public function test_admin_can_view_assignment_data_index()
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('assignment-data.index'))
            ->assertStatus(200)
            ->assertViewIs('assignment-data.index');
    }

    public function test_non_admin_cannot_access_assignment_data_routes()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $staff = User::factory()->create([
            'employee_id'   => 'STAFF001',
            'department_id' => $department->id,
            'position_id'   => $position->id,
        ]);
        $staff->assignRole('staff');

        $this->actingAs($staff, 'web')
            ->get(route('assignment-data.index'))
            ->assertForbidden();
    }
    
    public function test_admin_can_create_assignment_data(): void
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('assignment-data.index'));

        $token = session()->token();

        // create users for positions
        $evaluateeUser = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluateePosition->id,
        ]);
        $evaluatorUser = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluatorPosition->id,
        ]);

        Mail::fake();

        $payload = [
            '_token'         => $token,
            'start_time'     => now()->toDateString(),
            'end_time'       => now()->addDays(5)->toDateString(),
            'report_data_id' => $this->reportData->id,
            'evaluatees'     => [$evaluateeUser->id],
            'evaluator_id'   => $evaluatorUser->id,
            'stage_order'    => ['evaluator' => 1],
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('assignment-data.store'), $payload)
            ->assertRedirect(route('assignment-data.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('assignment_datas', [
            'evaluator_position_id' => $this->evaluatorPosition->id,
            'evaluator_id' => $evaluatorUser->id,
        ]);

        $this->assertDatabaseHas('reports', [
            'report_data_id' => $this->reportData->id,
            'status' => 'Assigned',
        ]);

        $this->assertDatabaseHas('assignments', [
            'evaluatee_id' => $evaluateeUser->id,
        ]);
    }

    public function test_admin_cannot_create_assignment_data_without_required_fields(): void
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('assignment-data.index'));

        $token = session()->token();

        $payload = [
            '_token' => $token,
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('assignment-data.store'), $payload)
            ->assertSessionHasErrors([
                'start_time',
                'end_time',
                'report_data_id',
                'evaluatees',
                'evaluation_flow',
            ]);

        // ✅ make sure nothing got created
        $this->assertDatabaseCount('assignment_datas', 0);
        $this->assertDatabaseCount('reports', 0);
        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_store_fails_when_reviewer_roles_use_same_user(): void
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('assignment-data.index'));

        $token = session()->token();

        $reviewer = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluatorPosition->id,
        ]);
        $evaluateeUser = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluateePosition->id,
        ]);

        $payload = [
            '_token'         => $token,
            'start_time'     => now()->toDateString(),
            'end_time'       => now()->addDays(5)->toDateString(),
            'report_data_id' => $this->reportData->id,
            'evaluatees'     => [$evaluateeUser->id],
            'evaluator_id'   => $reviewer->id,
            'director_id'    => $reviewer->id,
            'stage_order'    => ['evaluator' => 1, 'director' => 2],
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('assignment-data.store'), $payload)
            ->assertSessionHasErrors(['evaluation_flow']);
    }

    public function test_store_fails_when_selected_reviewer_stages_share_the_same_order(): void
    {
        Role::create(['name' => 'กรรมการ']);

        $this->actingAs($this->admin, 'web')
            ->get(route('assignment-data.index'));

        $token = session()->token();

        $evaluator = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluatorPosition->id,
        ]);
        $director = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluatorPosition->id,
        ]);
        $evaluateeUser = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluateePosition->id,
        ]);

        $payload = [
            '_token'         => $token,
            'start_time'     => now()->toDateString(),
            'end_time'       => now()->addDays(5)->toDateString(),
            'report_data_id' => $this->reportData->id,
            'evaluatees'     => [$evaluateeUser->id],
            'evaluator_id'   => $evaluator->id,
            'director_id'    => $director->id,
            'stage_order'    => ['evaluator' => 1, 'director' => 1],
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('assignment-data.store'), $payload)
            ->assertSessionHasErrors(['evaluation_flow']);

        $this->assertDatabaseCount('assignment_datas', 0);
        $this->assertDatabaseCount('reports', 0);
        $this->assertDatabaseCount('assignments', 0);
    }

    public function test_admin_can_update_assignment_data(): void
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('assignment-data.index'));

        $token = session()->token();

        $assignmentData = AssignmentData::factory()->create([
            'evaluator_position_id' => $this->evaluatorPosition->id,
            'evaluatee_position_id' => $this->evaluateePosition->id,
            'start_time' => now(),
            'end_time'   => now()->addDay(),
        ]);

        $evaluateeUser = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluateePosition->id,
        ]);
        $evaluatorUser = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id'   => $this->evaluatorPosition->id,
        ]);

        $payload = [
            '_token'         => $token,
            'start_time'     => now()->toDateString(),
            'end_time'       => now()->addDays(10)->toDateString(),
            'report_data_id' => $this->reportData->id,
            'evaluatees'     => [$evaluateeUser->id],
            'evaluator_id'   => $evaluatorUser->id,
            'stage_order'    => ['evaluator' => 1],
        ];

        $this->actingAs($this->admin, 'web')
            ->put(route('assignment-data.update', $assignmentData->id), $payload)
            ->assertRedirect(route('assignment-data.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('assignment_datas', [
            'id' => $assignmentData->id,
            'end_time' => now()->addDays(10)->startOfDay()->toDateTimeString(),
        ]);
    }

    public function test_admin_can_delete_assignment_data(): void
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('assignment-data.index'));

        $token = session()->token();

        $assignmentData = AssignmentData::factory()->create([
            'evaluator_position_id' => $this->evaluatorPosition->id,
            'evaluatee_position_id' => $this->evaluateePosition->id,
            'start_time' => now(),
            'end_time'   => now()->addDay(),
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('assignment-data.destroy', $assignmentData->id),[
                '_token' => $token,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('assignment_datas', [
            'id' => $assignmentData->id,
        ]);
    }
}
