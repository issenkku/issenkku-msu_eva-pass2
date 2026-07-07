<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Departments $department;
    protected Positions $position;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);

        // Create departments and positions that will be globally available in the test DB
        $this->department = \Database\Factories\DepartmentFactory::new()->create();
        $this->position = \Database\Factories\PositionFactory::new()->create();

        // Create admin user using above department and position
        $this->admin = User::factory()->create([
            'employee_id'   => 'ADMIN001',
            'password'      => Hash::make('password'),
            'status'        => 'active',
            'department_id' => $this->department->id,
            'position_id'   => $this->position->id,
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_view_users_index()
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('users.index'))
            ->assertStatus(200)
            ->assertViewIs('user.management.index')
            ->assertSee('data-user-bulk-status-form', false)
            ->assertSee('data-user-status-badge', false)
            ->assertSee('bulkStatusUsersModal', false)
            ->assertSee('status_filter_control', false);
    }

    public function test_non_admin_cannot_access_users_routes()
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
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_user_with_csrf()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        // Step 1: Load a page with a form (like index) to get CSRF
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('users.index'));

        $token = session()->token(); // this now matches session

        $data = [
            'prefix'        => 'นาย',
            '_token'        => $token,
            'name'          => 'Test User',
            'employee_id'   => 'EMP100',
            'password'      => 'secret123',
            'email'         => 'test@example.com',
            'phone'         => '0812345678',
            'personnel_type'=> 'วิชาการ',
            'bio'           => 'ประวัติสั้น',
            'status'        => 'active',
            'position_id'   => $position->id,
            'department_id' => $department->id,
            'role'          => 'staff',
        ];

        // Step 2: Submit with CSRF
        $this->post(route('users.store'), $data)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');

        $this->assertDatabaseHas('users', [
            'employee_id' => 'EMP100',
            'name'        => 'Test User',
        ]);
    }

    public function test_admin_cannot_create_user_with_missing_data()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('users.index'));

        $token = session()->token(); // this now matches session

        $data = [
            '_token'        => $token,
            // Missing 'name'
            'employee_id'   => 'EMP300',
            'password'      => 'secret123',
            'email'         => 'invalid@example.com',
            'phone'         => '0822222222',
            'personnel_type'=> 'วิชาการ',
            'status'        => 'active',
            'position_id'   => $position->id,
            'department_id' => $department->id,
            'role'          => 'staff',
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('users.store'), $data)
            ->assertSessionHasErrors(['name']);

        $this->assertDatabaseMissing('users', [
            'employee_id' => 'EMP300',
        ]);
    }

    public function test_admin_cannot_create_user_with_duplicate_email()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();
        
        $existing = User::factory()->create([
            'email' => 'duplicate@example.com',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('users.index'));

        $token = session()->token(); // this now matches session

        $payload = [
            '_token'        => $token,
            'prefix'        => 'นาย',
            'name'          => 'สมชาย ใจดี',
            'employee_id'   => 'EMP002',
            'password'      => 'password123',
            'email'         => 'duplicate@example.com', // duplicate
            'phone'         => '0812345678',
            'personnel_type'=> 'วิชาการ',
            'status'        => 'active',
            'position_id'   => $position->id,
            'department_id' => $department->id,
        ];

        $response = $this->actingAs($this->admin, 'web')
            ->post(route('users.store'), $payload);

        $response->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('users', ['employee_id' => 'EMP002']);
    }

    public function test_admin_can_update_user()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('users.index'));

        $token = session()->token();

        $user = User::factory()->create([
            'employee_id'   => 'EMP200',
            'department_id' => $department->id,
            'position_id'   => $position->id,
        ]);

        $updateData = [
            '_token'        => $token,
            'prefix'        => 'นางสาว',
            'name'          => 'Updated User',
            'employee_id'   => 'EMP200',
            'phone'         => '0999999999',
            'personnel_type'=> 'สนับสนุน',
            'bio'           => 'อัปเดตแล้ว',
            'status'        => 'inactive',
            'position_id'   => $position->id,
            'department_id' => $department->id,
            'email'         => 'updated@example.com',
        ];

        $this->actingAs($this->admin, 'web')
            ->put(route('users.update', $user->id), $updateData)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'Updated User',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_admin_can_delete_user()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $response = $this->actingAs($this->admin, 'web')
            ->get(route('users.index'));

        $token = session()->token();
        
        $user = User::factory()->create([
            'employee_id'   => 'EMP300',
            'department_id' => $department->id,
            'position_id'   => $position->id,
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('users.destroy', $user->id), [
                '_token' => $token,
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'ลบเรียบร้อยแล้ว');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_can_bulk_delete_users_without_deleting_self()
    {
        $users = User::factory()->count(2)->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('users.bulk-destroy'), [
                'user_ids' => [
                    $users[0]->id,
                    $users[1]->id,
                    $this->admin->id,
                ],
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'ลบเจ้าหน้าที่ที่เลือกเรียบร้อยแล้ว 2 รายการ');

        $this->assertDatabaseMissing('users', ['id' => $users[0]->id]);
        $this->assertDatabaseMissing('users', ['id' => $users[1]->id]);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_bulk_delete_keeps_one_active_admin()
    {
        $secondAdmin = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'active',
        ]);
        $secondAdmin->assignRole('admin');

        $staff = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'active',
        ]);
        $staff->assignRole('staff');

        $this->actingAs($this->admin, 'web')
            ->delete(route('users.bulk-destroy'), [
                'user_ids' => [
                    $this->admin->id,
                    $secondAdmin->id,
                    $staff->id,
                ],
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, User::role('admin')->where('status', 'active')->count());
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'status' => 'active']);
        $this->assertDatabaseMissing('users', ['id' => $secondAdmin->id]);
        $this->assertDatabaseMissing('users', ['id' => $staff->id]);
    }

    public function test_admin_can_filter_users_by_status()
    {
        User::factory()->create([
            'name' => 'Active Filter User',
            'employee_id' => 'ACTIVE-FILTER',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'active',
        ]);

        User::factory()->create([
            'name' => 'Inactive Filter User',
            'employee_id' => 'INACTIVE-FILTER',
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'inactive',
        ]);

        $this->actingAs($this->admin, 'web')
            ->get(route('users.index', ['status' => ['inactive']]))
            ->assertStatus(200)
            ->assertSee('Inactive Filter User')
            ->assertDontSee('Active Filter User')
            ->assertDontSee('ADMIN001');
    }

    public function test_admin_can_bulk_update_user_status()
    {
        $users = User::factory()->count(2)->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin, 'web')
            ->patch(route('users.bulk-status'), [
                'user_ids' => [
                    $users[0]->id,
                    $users[1]->id,
                ],
                'status' => 'inactive',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $users[0]->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('users', ['id' => $users[1]->id, 'status' => 'inactive']);
    }

    public function test_admin_cannot_bulk_deactivate_self()
    {
        $user = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'active',
        ]);

        $this->actingAs($this->admin, 'web')
            ->patch(route('users.bulk-status'), [
                'user_ids' => [
                    $user->id,
                    $this->admin->id,
                ],
                'status' => 'inactive',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'status' => 'active']);
    }

    public function test_bulk_inactive_keeps_one_active_admin()
    {
        $secondAdmin = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'active',
        ]);
        $secondAdmin->assignRole('admin');

        $staff = User::factory()->create([
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'active',
        ]);
        $staff->assignRole('staff');

        $this->actingAs($this->admin, 'web')
            ->patch(route('users.bulk-status'), [
                'user_ids' => [
                    $this->admin->id,
                    $secondAdmin->id,
                    $staff->id,
                ],
                'status' => 'inactive',
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, User::role('admin')->where('status', 'active')->count());
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'status' => 'active']);
        $this->assertDatabaseHas('users', ['id' => $secondAdmin->id, 'status' => 'inactive']);
        $this->assertDatabaseHas('users', ['id' => $staff->id, 'status' => 'inactive']);
    }

    public function test_admin_cannot_delete_the_last_active_admin()
    {
        $this->actingAs($this->admin, 'web')
            ->delete(route('users.destroy', $this->admin->id))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_update_the_last_active_admin_to_inactive()
    {
        $payload = [
            'prefix' => $this->admin->prefix,
            'name' => $this->admin->name,
            'employee_id' => $this->admin->employee_id,
            'phone' => $this->admin->phone,
            'personnel_type' => $this->admin->personnel_type,
            'bio' => $this->admin->bio,
            'status' => 'inactive',
            'position_id' => $this->admin->position_id,
            'department_id' => $this->admin->department_id,
            'email' => $this->admin->email,
            'roles' => ['admin'],
        ];

        $this->actingAs($this->admin, 'web')
            ->put(route('users.update', $this->admin->id), $payload)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_cannot_remove_admin_role_from_the_last_active_admin()
    {
        $payload = [
            'prefix' => $this->admin->prefix,
            'name' => $this->admin->name,
            'employee_id' => $this->admin->employee_id,
            'phone' => $this->admin->phone,
            'personnel_type' => $this->admin->personnel_type,
            'bio' => $this->admin->bio,
            'status' => 'active',
            'position_id' => $this->admin->position_id,
            'department_id' => $this->admin->department_id,
            'email' => $this->admin->email,
            'roles' => ['staff'],
        ];

        $this->actingAs($this->admin, 'web')
            ->put(route('users.update', $this->admin->id), $payload)
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error');

        $this->assertTrue($this->admin->fresh()->hasRole('admin'));
        $this->assertDatabaseHas('users', [
            'id' => $this->admin->id,
            'status' => 'active',
        ]);
    }
}
