<?php

use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

function flexibleSubjectPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'FLEX101',
        'name_th' => 'ชื่อไทย',
        'name_en' => 'English name',
        'credits' => 3,
        'lecture_credits' => 2,
        'lab_credits' => 1,
        'self_study_credits' => 0,
        'is_active' => true,
    ], $overrides);
}

function flexibleSubjectAdmin(): User
{
    Role::findOrCreate('admin');
    $admin = User::factory()->create(['status' => 'active']);
    $admin->assignRole('admin');

    return $admin;
}

test('subject names use nullable text storage and an English display fallback', function () {
    $longEnglishName = trim(str_repeat('Environmental Health ', 20));
    $subject = Subject::create(flexibleSubjectPayload([
        'name_th' => '   ',
        'name_en' => "  {$longEnglishName}  ",
    ]));

    expect(Schema::getColumnType('subjects', 'name_th'))->toBe('text')
        ->and(Schema::getColumnType('subjects', 'name_en'))->toBe('text')
        ->and($subject->fresh()->name_th)->toBeNull()
        ->and($subject->fresh()->name_en)->toBe($longEnglishName)
        ->and($subject->fresh()->display_name)->toBe($longEnglishName);
});
