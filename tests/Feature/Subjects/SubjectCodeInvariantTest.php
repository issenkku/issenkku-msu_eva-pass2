<?php

use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Role;

function subjectPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'CS101',
        'name_th' => 'วิทยาการคอมพิวเตอร์',
        'name_en' => 'Computer Science',
        'credits' => 3,
        'lecture_credits' => 2,
        'lab_credits' => 1,
        'self_study_credits' => 0,
        'is_active' => true,
    ], $overrides);
}

function subjectAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');

    return $admin;
}

test('subject model stores a trimmed uppercase code', function () {
    $subject = Subject::create(subjectPayload(['code' => ' cs101 ']));

    expect($subject->fresh()->code)->toBe('CS101');
});

test('manual create rejects a normalized duplicate code', function () {
    Subject::create(subjectPayload());

    $this->actingAs(subjectAdmin(), 'web')
        ->post(route('subjects.store'), subjectPayload(['code' => ' cs101 ']))
        ->assertSessionHasErrors('code');

    expect(Subject::count())->toBe(1);
});

test('manual update ignores itself but rejects another normalized code', function () {
    $first = Subject::create(subjectPayload());
    $second = Subject::create(subjectPayload(['code' => 'CS102']));
    $admin = subjectAdmin();

    $this->actingAs($admin, 'web')
        ->put(route('subjects.update', $first->id), subjectPayload(['code' => ' cs101 ']))
        ->assertSessionDoesntHaveErrors('code');

    $this->actingAs($admin, 'web')
        ->put(route('subjects.update', $second->id), subjectPayload(['code' => 'cs101']))
        ->assertSessionHasErrors('code');
});

test('database unique index rejects direct duplicate writes', function () {
    Subject::create(subjectPayload());

    expect(fn () => Subject::query()->insert(subjectPayload(['code' => 'CS101'])))
        ->toThrow(QueryException::class);
});
