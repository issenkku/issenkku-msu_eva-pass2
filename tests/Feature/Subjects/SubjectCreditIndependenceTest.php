<?php

use App\Models\Subject;
use App\Models\User;
use Spatie\Permission\Models\Role;

function independentCreditAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');

    return $admin;
}

function independentCreditPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'CREDIT101',
        'name_th' => 'วิชาทดสอบหน่วยกิต',
        'name_en' => '',
        'credits' => 3,
        'lecture_credits' => 3,
        'lab_credits' => 0,
        'self_study_credits' => 6,
        'lecture_hours' => 2,
        'lab_hours' => 3,
        'self_study_hours' => 1,
        'is_active' => true,
    ], $overrides);
}

test('manual create and update preserve independent credit values', function () {
    $admin = independentCreditAdmin();
    $this->actingAs($admin, 'web')
        ->post(route('subjects.store'), independentCreditPayload())
        ->assertSessionDoesntHaveErrors();

    $subject = Subject::where('code', 'CREDIT101')->firstOrFail();
    expect((int) $subject->credits)->toBe(3)
        ->and((int) $subject->lecture_credits)->toBe(3)
        ->and((int) $subject->lab_credits)->toBe(0)
        ->and((int) $subject->self_study_credits)->toBe(6)
        ->and((int) $subject->lecture_hours)->toBe(2)
        ->and((int) $subject->lab_hours)->toBe(3)
        ->and((int) $subject->self_study_hours)->toBe(1);

    $this->actingAs($admin, 'web')
        ->put(route('subjects.update', $subject->id), independentCreditPayload([
            'credits' => 4,
            'lecture_credits' => 1,
            'lab_credits' => 2,
            'self_study_credits' => 8,
            'lecture_hours' => 4,
            'lab_hours' => 5,
            'self_study_hours' => 6,
        ]))->assertSessionDoesntHaveErrors();

    $subject->refresh();
    expect((int) $subject->credits)->toBe(4)
        ->and((int) $subject->lecture_credits)->toBe(1)
        ->and((int) $subject->lab_credits)->toBe(2)
        ->and((int) $subject->self_study_credits)->toBe(8)
        ->and((int) $subject->lecture_hours)->toBe(4)
        ->and((int) $subject->lab_hours)->toBe(5)
        ->and((int) $subject->self_study_hours)->toBe(6);
});

test('manual subject writes reject invalid credit input', function (string $field, mixed $value) {
    $payload = independentCreditPayload([
        'code' => 'BAD'.strtoupper(substr(md5($field.serialize($value)), 0, 8)),
        $field => $value,
    ]);

    $this->actingAs(independentCreditAdmin(), 'web')
        ->post(route('subjects.store'), $payload)
        ->assertSessionHasErrors($field);

    expect(Subject::where('code', $payload['code'])->exists())->toBeFalse();
})->with([
    'total text' => ['credits', 'three'],
    'lab decimal' => ['lab_credits', '1.5'],
    'self study negative' => ['self_study_credits', '-1'],
    'lecture hours text' => ['lecture_hours', 'two'],
    'lab hours decimal' => ['lab_hours', '1.5'],
    'self study hours negative' => ['self_study_hours', '-1'],
]);

test('manual subject writes default empty credits and hours to zero', function () {
    $payload = independentCreditPayload([
        'code' => 'EMPTYCREDITS',
        'credits' => '',
        'lecture_credits' => null,
        'lab_credits' => '',
        'self_study_credits' => null,
        'lecture_hours' => '',
        'lab_hours' => null,
        'self_study_hours' => '',
    ]);

    $this->actingAs(independentCreditAdmin(), 'web')
        ->post(route('subjects.store'), $payload)
        ->assertSessionDoesntHaveErrors();

    $subject = Subject::where('code', 'EMPTYCREDITS')->firstOrFail();
    expect($subject->only([
        'credits', 'lecture_credits', 'lab_credits', 'self_study_credits',
        'lecture_hours', 'lab_hours', 'self_study_hours',
    ]))
        ->toMatchArray([
            'credits' => 0,
            'lecture_credits' => 0,
            'lab_credits' => 0,
            'self_study_credits' => 0,
            'lecture_hours' => 0,
            'lab_hours' => 0,
            'self_study_hours' => 0,
        ]);
});
