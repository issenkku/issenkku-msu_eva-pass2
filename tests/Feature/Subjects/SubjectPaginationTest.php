<?php

namespace Tests\Feature\Subjects;

use App\Models\Subject;
use App\Models\User;
use Database\Factories\DepartmentFactory;
use Database\Factories\PositionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubjectPaginationTest extends TestCase
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
            'employee_id' => 'ADMIN-SUBJECT-PAGE',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);
        $this->admin->assignRole('admin');

        foreach (range(1, 120) as $index) {
            Subject::create([
                'code' => sprintf('PAGE%03d', $index),
                'name_th' => "รายวิชาทดสอบ {$index}",
                'name_en' => "Pagination Subject {$index}",
                'credits' => 3,
                'lecture_credits' => 3,
                'lab_credits' => 0,
                'self_study_credits' => 0,
                'is_active' => true,
            ]);
        }
    }

    #[DataProvider('allowedPageSizes')]
    public function test_admin_can_choose_subject_page_size(int $perPage): void
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('subjects.index', ['per_page' => $perPage]))
            ->assertOk()
            ->assertViewHas('subjects', fn (LengthAwarePaginator $subjects) => $subjects->perPage() === $perPage
                && $subjects->count() === $perPage
                && $subjects->total() === 120)
            ->assertSee('name="per_page"', false)
            ->assertSee("value=\"{$perPage}\" selected", false);
    }

    #[DataProvider('invalidPageSizes')]
    public function test_invalid_subject_page_size_falls_back_to_ten(mixed $perPage): void
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('subjects.index', ['per_page' => $perPage]))
            ->assertOk()
            ->assertViewHas('subjects', fn (LengthAwarePaginator $subjects) => $subjects->perPage() === 10
                && $subjects->count() === 10);
    }

    public static function allowedPageSizes(): array
    {
        return [[10], [25], [50], [100]];
    }

    public static function invalidPageSizes(): array
    {
        return [[0], [11], [101], ['all']];
    }
}
