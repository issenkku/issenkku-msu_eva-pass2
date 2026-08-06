<?php

use Illuminate\Pagination\LengthAwarePaginator;

function modalContractPaginator(array $items, string $path): LengthAwarePaginator
{
    return new LengthAwarePaginator($items, count($items), 10, 1, ['path' => url($path)]);
}

test('department index renders create modal hooks without inline handlers', function () {
    $departments = modalContractPaginator([
        (object) [
            'id' => 1,
            'department_name' => 'IT',
            'user_count' => 0,
        ],
    ], '/departments');

    $html = view('departments.index', [
        'departments' => $departments,
    ])->render();

    expect($html)
        ->toContain('data-create-modal-open')
        ->toContain('data-modal-submit-trigger')
        ->toContain('document.querySelectorAll(\'[data-create-modal-open]\')')
        ->toContain('document.querySelectorAll(\'[data-modal-submit-trigger]\')')
        ->not->toContain('onclick="openCreateModal()"')
        ->not->toContain('onclick="submitForm()"');
});

test('position index renders create modal hooks without inline handlers', function () {
    $positions = modalContractPaginator([
        (object) [
            'id' => 2,
            'name' => 'Lecturer',
            'user_count' => 0,
        ],
    ], '/positions');

    $html = view('positions.index', [
        'positions' => $positions,
    ])->render();

    expect($html)
        ->toContain('data-create-modal-open')
        ->toContain('data-modal-submit-trigger')
        ->toContain('document.querySelectorAll(\'[data-create-modal-open]\')')
        ->toContain('document.querySelectorAll(\'[data-modal-submit-trigger]\')')
        ->not->toContain('onclick="openCreateModal()"')
        ->not->toContain('onclick="submitForm()"');
});

test('job level index renders create modal hooks without inline handlers', function () {
    $jobLevels = modalContractPaginator([
        (object) [
            'id' => 3,
            'name' => 'Senior',
        ],
    ], '/job-level');

    $html = view('Job Level.index', [
        'jobLevels' => $jobLevels,
    ])->render();

    expect($html)
        ->toContain('data-create-modal-open')
        ->toContain('data-modal-submit-trigger')
        ->toContain('document.querySelectorAll(\'[data-create-modal-open]\')')
        ->toContain('document.querySelectorAll(\'[data-modal-submit-trigger]\')')
        ->not->toContain('onclick="openCreateModal()"')
        ->not->toContain('onclick="submitForm()"');
});

test('subjects index and subject modal render create hooks without inline handlers', function () {
    $subjects = modalContractPaginator([
        (object) [
            'id' => 4,
            'code' => 'TH101',
            'name_th' => 'ภาษาไทย',
            'name_en' => 'Thai',
            'credits' => 3,
            'lecture_credits' => 2,
            'lab_credits' => 1,
            'self_study_credits' => 0,
            'lecture_hours' => 0,
            'lab_hours' => 0,
            'self_study_hours' => 0,
            'display_component_values' => [2, 1, 0],
        ],
    ], '/subjects');

    $html = view('subjects.index', [
        'subjects' => $subjects,
    ])->render();

    expect($html)
        ->toContain('data-create-modal-open')
        ->toContain('data-modal-submit-trigger')
        ->toContain('document.querySelectorAll(\'[data-create-modal-open]\')')
        ->toContain('document.querySelectorAll(\'[data-modal-submit-trigger]\')')
        ->not->toContain('onclick="openCreateModal()"')
        ->not->toContain('onclick="submitForm()"');

    $modalHtml = view('components.subject-modal')->render();

    expect($modalHtml)
        ->toContain('data-modal-submit-trigger')
        ->toContain('data-subject-credit-fields')
        ->toContain('data-subject-hour-fields')
        ->toContain('name="lecture_hours"')
        ->toContain('name="lab_hours"')
        ->toContain('name="self_study_hours"')
        ->not->toContain('onclick="submitForm()"');

    expect($modalHtml)
        ->toContain('id="subjectNameHelp"')
        ->toContain('id="subjectNameError"')
        ->toContain('กรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง')
        ->not->toMatch('/id="name_(?:th|en)"[^>]*\srequired/');

    $subjectScript = file_get_contents(resource_path('views/subjects/partials/index-script.blade.php'));
    expect($subjectScript)
        ->toContain("document.getElementById('name_en')")
        ->toContain("document.getElementById('subjectNameError')")
        ->toContain("nameThValue === '' && nameEnValue === ''")
        ->toContain("setAttribute('aria-invalid', 'true')")
        ->toContain("removeAttribute('aria-invalid')");
});

test('setting modal openers reuse bootstrap instances without cleanup races', function () {
    $scripts = [
        resource_path('views/departments/partials/index-script.blade.php'),
        resource_path('views/positions/partials/index-script.blade.php'),
        resource_path('views/Job Level/partials/index-script.blade.php'),
        resource_path('views/subjects/partials/index-script.blade.php'),
    ];

    foreach ($scripts as $scriptPath) {
        $script = file_get_contents($scriptPath);

        expect(substr_count($script, 'bootstrap.Modal.getOrCreateInstance(modalEl)'))
            ->toBe(2)
            ->and($script)
            ->not->toContain('new bootstrap.Modal(modalEl)')
            ->not->toMatch('/function openCreateModal\(\)\s*\{\s*clearModalBackdrop\(\);/')
            ->not->toMatch('/function handleEdit\([^)]*\)\s*\{\s*clearModalBackdrop\(\);/');
    }
});

test('subject forms do not calculate total credits from component fields', function () {
    $subjectScript = file_get_contents(resource_path('views/subjects/partials/index-script.blade.php'));
    $evaluateeScript = file_get_contents(resource_path('views/evaluatee/partials/workload-script-subject-form.blade.php'));

    expect($subjectScript)
        ->not->toContain('function updateTotalCredits')
        ->not->toContain('updateTotalCredits()')
        ->not->toMatch('/totalInput\.value\s*=/');

    expect($evaluateeScript)
        ->not->toContain('function updateSubjectCreditTotal')
        ->not->toContain("addEventListener('input', updateSubjectCreditTotal)")
        ->not->toMatch('/totalInput\.value\s*=/');
});
