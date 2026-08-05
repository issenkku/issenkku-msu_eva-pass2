<?php

use App\Models\Setting\Departments;
use App\Models\Setting\JobLevel;
use App\Models\Setting\Positions;
use App\Models\Subject;
use App\Models\User;
use Spatie\Permission\Models\Role;

function asyncMasterAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    return $admin;
}

function asyncMasterCases(): array
{
    return [
        'departments' => [
            'bulk' => 'departments.bulk-destroy',
            'create' => fn (string $suffix) => Departments::create(['department_name' => "Async Department {$suffix}"]),
            'destroy' => 'departments.destroy',
            'field' => 'department_name',
            'model' => Departments::class,
            'store' => 'departments.store',
            'updated' => ['department_name' => 'Async Department Updated'],
            'valid' => ['department_name' => 'Async Department Created'],
            'update' => 'departments.update',
        ],
        'positions' => [
            'bulk' => 'positions.bulk-destroy',
            'create' => fn (string $suffix) => Positions::create(['name' => "Async Position {$suffix}"]),
            'destroy' => 'positions.destroy',
            'field' => 'name',
            'model' => Positions::class,
            'store' => 'positions.store',
            'updated' => ['name' => 'Async Position Updated'],
            'valid' => ['name' => 'Async Position Created'],
            'update' => 'positions.update',
        ],
        'job levels' => [
            'bulk' => 'job-level.bulk-destroy',
            'create' => fn (string $suffix) => JobLevel::create(['name' => "Async Job Level {$suffix}"]),
            'destroy' => 'job-level.destroy',
            'field' => 'name',
            'model' => JobLevel::class,
            'store' => 'job-level.store',
            'updated' => ['name' => 'Async Job Level Updated'],
            'valid' => ['name' => 'Async Job Level Created'],
            'update' => 'job-level.update',
        ],
        'subjects' => [
            'bulk' => 'subjects.bulk-destroy',
            'create' => fn (string $suffix) => Subject::create([
                'code' => "ASYNC{$suffix}",
                'credits' => 3,
                'is_active' => true,
                'lab_credits' => 0,
                'lecture_credits' => 3,
                'name_th' => "Async Subject {$suffix}",
                'self_study_credits' => 6,
            ]),
            'destroy' => 'subjects.destroy',
            'field' => 'code',
            'model' => Subject::class,
            'store' => 'subjects.store',
            'updated' => [
                'code' => 'ASYNC102',
                'credits' => 3,
                'lab_credits' => 0,
                'lecture_credits' => 3,
                'name_th' => 'Async Subject Updated',
                'self_study_credits' => 6,
            ],
            'valid' => [
                'code' => 'ASYNC101',
                'credits' => 3,
                'lab_credits' => 0,
                'lecture_credits' => 3,
                'name_th' => 'Async Subject Created',
                'self_study_credits' => 6,
            ],
            'update' => 'subjects.update',
        ],
    ];
}

test('master data create and update return renderable JSON rows', function () {
    $admin = asyncMasterAdmin();

    foreach (asyncMasterCases() as $label => $case) {
        $createResponse = $this->actingAs($admin)->postJson(route($case['store']), $case['valid']);
        $createResponse
            ->assertSuccessful()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['message', 'html' => ['row'], 'state' => ['id']]);

        expect($createResponse->json('html.row'), $label)
            ->toContain('data-resource-row')
            ->toContain('data-resource-id');

        $id = $createResponse->json('state.id');
        $updateResponse = $this->actingAs($admin)->putJson(route($case['update'], $id), $case['updated']);
        $updateResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('state.id', $id)
            ->assertJsonStructure(['html' => ['row']]);

        expect($case['model']::findOrFail($id)->{$case['field']}, $label)
            ->toBe($case['updated'][$case['field']]);
    }
});

test('master data single and bulk delete return the exact deleted ids', function () {
    $admin = asyncMasterAdmin();

    foreach (asyncMasterCases() as $label => $case) {
        $single = $case['create']('SINGLE');
        $singleResponse = $this->actingAs($admin)->deleteJson(route($case['destroy'], $single->id));
        $singleResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('state.deleted_ids.0', $single->id)
            ->assertJsonStructure(['message', 'state' => ['total']]);
        expect($case['model']::find($single->id), $label)->toBeNull();

        $first = $case['create']('BULK1');
        $second = $case['create']('BULK2');
        $bulkResponse = $this->actingAs($admin)->deleteJson(route($case['bulk']), [
            'ids' => [$first->id, $second->id],
        ]);
        $bulkResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('state.deleted_ids', [$first->id, $second->id]);
        expect($case['model']::whereKey([$first->id, $second->id])->exists(), $label)->toBeFalse();
    }
});

test('master data JSON validation keeps field errors in the response', function () {
    $admin = asyncMasterAdmin();

    foreach (asyncMasterCases() as $label => $case) {
        $this->actingAs($admin)
            ->postJson(route($case['store']), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors($case['field']);
    }
});

test('master data normal form fallback still redirects', function () {
    $admin = asyncMasterAdmin();

    $this->actingAs($admin)
        ->post(route('departments.store'), ['department_name' => 'Redirect Department'])
        ->assertRedirect(route('departments.index'));
});

test('subject create and update persist empty or missing credits as zero', function () {
    $admin = asyncMasterAdmin();

    $createResponse = $this->actingAs($admin)->postJson(route('subjects.store'), [
        'code' => 'ZERO101',
        'name_th' => 'รายวิชาหน่วยกิตศูนย์',
        'credits' => '',
        'lecture_credits' => null,
        'lab_credits' => '',
        'self_study_credits' => null,
    ])->assertCreated();

    $subject = Subject::findOrFail($createResponse->json('state.id'));
    expect($subject->only(['credits', 'lecture_credits', 'lab_credits', 'self_study_credits']))
        ->toMatchArray([
            'credits' => 0,
            'lecture_credits' => 0,
            'lab_credits' => 0,
            'self_study_credits' => 0,
        ]);

    $this->actingAs($admin)->putJson(route('subjects.update', $subject), [
        'code' => 'ZERO102',
        'name_th' => 'แก้ไขรายวิชาหน่วยกิตศูนย์',
    ])->assertOk();

    expect($subject->fresh()->only(['credits', 'lecture_credits', 'lab_credits', 'self_study_credits']))
        ->toMatchArray([
            'credits' => 0,
            'lecture_credits' => 0,
            'lab_credits' => 0,
            'self_study_credits' => 0,
        ]);
});

test('subject credits still reject negative values', function () {
    $admin = asyncMasterAdmin();

    $this->actingAs($admin)->postJson(route('subjects.store'), [
        'code' => 'NEGATIVE101',
        'name_th' => 'รายวิชาหน่วยกิตติดลบ',
        'credits' => -1,
        'lecture_credits' => 0,
        'lab_credits' => 0,
        'self_study_credits' => 0,
    ])->assertUnprocessable()->assertJsonValidationErrors('credits');
});
