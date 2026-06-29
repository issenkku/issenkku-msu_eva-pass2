<?php

namespace Tests\Feature\Settings;

use App\Models\Setting\Departments;
use App\Models\Setting\JobLevel;
use App\Models\Setting\Positions;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BulkSettingDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);

        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $this->admin = User::factory()->create([
            'employee_id' => 'ADMIN-BULK',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_bulk_delete_job_levels(): void
    {
        $jobLevels = collect([
            JobLevel::create(['name' => 'ระดับลบหลายรายการ 1']),
            JobLevel::create(['name' => 'ระดับลบหลายรายการ 2']),
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('job-level.bulk-destroy'), [
                'ids' => $jobLevels->pluck('id')->all(),
            ])
            ->assertRedirect(route('job-level.index'))
            ->assertSessionHas('success', 'ลบระดับตำแหน่งงานที่เลือกเรียบร้อยแล้ว 2 รายการ');

        $this->assertDatabaseMissing('job_levels', ['id' => $jobLevels[0]->id]);
        $this->assertDatabaseMissing('job_levels', ['id' => $jobLevels[1]->id]);
    }

    public function test_admin_can_bulk_delete_subjects(): void
    {
        $subjects = collect([
            Subject::create([
                'code' => 'BULK101',
                'name_th' => 'รายวิชาลบหลายรายการ 1',
                'name_en' => 'Bulk Delete 1',
                'credits' => 3,
                'lecture_credits' => 2,
                'lab_credits' => 1,
                'self_study_credits' => 0,
                'is_active' => true,
            ]),
            Subject::create([
                'code' => 'BULK102',
                'name_th' => 'รายวิชาลบหลายรายการ 2',
                'name_en' => 'Bulk Delete 2',
                'credits' => 3,
                'lecture_credits' => 2,
                'lab_credits' => 1,
                'self_study_credits' => 0,
                'is_active' => true,
            ]),
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('subjects.bulk-destroy'), [
                'ids' => $subjects->pluck('id')->all(),
            ])
            ->assertRedirect(route('subjects.index'))
            ->assertSessionHas('success', 'ลบรายวิชาที่เลือกเรียบร้อยแล้ว 2 รายการ');

        $this->assertDatabaseMissing('subjects', ['id' => $subjects[0]->id]);
        $this->assertDatabaseMissing('subjects', ['id' => $subjects[1]->id]);
    }
}
