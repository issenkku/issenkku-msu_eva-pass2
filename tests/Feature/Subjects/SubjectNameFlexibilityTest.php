<?php

use App\Models\Subject;
use App\Models\User;
use App\Support\EvaluateeWorkloadModalData;
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

test('manual create accepts an English-only name longer than 255 characters', function () {
    $longEnglishName = trim(str_repeat('Public Health Administration ', 15));

    $this->actingAs(flexibleSubjectAdmin(), 'web')
        ->post(route('subjects.store'), flexibleSubjectPayload([
            'name_th' => ' ',
            'name_en' => " {$longEnglishName} ",
        ]))
        ->assertSessionDoesntHaveErrors();

    $subject = Subject::where('code', 'FLEX101')->firstOrFail();
    expect($subject->name_th)->toBeNull()
        ->and($subject->name_en)->toBe($longEnglishName);
});

test('manual create rejects a subject with both names blank', function () {
    $this->actingAs(flexibleSubjectAdmin(), 'web')
        ->post(route('subjects.store'), flexibleSubjectPayload([
            'name_th' => ' ',
            'name_en' => '',
        ]))
        ->assertSessionHasErrors([
            'name_th' => 'กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง',
        ]);

    expect(Subject::where('code', 'FLEX101')->exists())->toBeFalse();
});

test('manual update can replace a Thai name with an English-only long name', function () {
    $subject = Subject::create(flexibleSubjectPayload());
    $longEnglishName = trim(str_repeat('Environmental and Occupational Health ', 10));

    $this->actingAs(flexibleSubjectAdmin(), 'web')
        ->put(route('subjects.update', $subject->id), [
            'name_th' => '',
            'name_en' => $longEnglishName,
        ])
        ->assertSessionDoesntHaveErrors();

    expect($subject->fresh()->name_th)->toBeNull()
        ->and($subject->fresh()->name_en)->toBe($longEnglishName);
});

test('subject index displays searches and sorts English-only names', function () {
    Subject::create(flexibleSubjectPayload([
        'code' => 'FLEX-Z', 'name_th' => null, 'name_en' => 'Zulu Health',
    ]));
    Subject::create(flexibleSubjectPayload([
        'code' => 'FLEX-A', 'name_th' => null, 'name_en' => 'Alpha Health',
    ]));
    $admin = flexibleSubjectAdmin();

    $this->actingAs($admin, 'web')
        ->get(route('subjects.index', ['sort' => 'name_asc']))
        ->assertOk()
        ->assertSeeInOrder(['Alpha Health', 'Zulu Health']);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.index', ['search' => 'Zulu']))
        ->assertOk()
        ->assertSee('Zulu Health')
        ->assertDontSee('Alpha Health');
});

test('workload picker exposes one primary English fallback without duplication', function () {
    $subject = new Subject(flexibleSubjectPayload([
        'name_th' => null,
        'name_en' => 'English Only',
    ]));
    $subject->id = 99;

    $modal = EvaluateeWorkloadModalData::build(
        (object) ['groups' => []],
        collect(),
        collect([$subject]),
    );

    expect($modal['subjects'][0]['display_name'])->toBe('English Only')
        ->and($modal['subjects'][0]['secondary_name'])->toBeNull()
        ->and($modal['subjects'][0]['search'])->toContain('english only');
});
