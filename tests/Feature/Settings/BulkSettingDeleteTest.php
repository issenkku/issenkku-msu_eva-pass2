<?php

namespace Tests\Feature\Settings;

use App\Models\Setting\JobLevel;
use App\Models\Subject;
use App\Models\User;
use Database\Factories\DepartmentFactory;
use Database\Factories\PositionFactory;
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

        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();

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
        $subjects = collect(range(1, 12))->map(fn (int $index) => Subject::create([
            'code' => sprintf('BULK%03d', $index),
            'name_th' => "รายวิชาลบหลายรายการ {$index}",
            'name_en' => "Bulk Delete {$index}",
            'credits' => 3,
            'lecture_credits' => 2,
            'lab_credits' => 1,
            'self_study_credits' => 0,
            'is_active' => true,
        ]));

        $control = Subject::create([
            'code' => 'KEEP001',
            'name_th' => 'รายวิชาที่ไม่ถูกเลือก',
            'name_en' => 'Keep Subject',
            'credits' => 3,
            'lecture_credits' => 3,
            'lab_credits' => 0,
            'self_study_credits' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('subjects.bulk-destroy'), [
                'ids' => $subjects->pluck('id')->all(),
            ])
            ->assertRedirect(route('subjects.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, '12'));

        foreach ($subjects as $subject) {
            $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
        }
        $this->assertDatabaseHas('subjects', ['id' => $control->id]);
    }
}
