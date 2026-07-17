<?php

use Illuminate\Pagination\LengthAwarePaginator;

function emptySubjectPaginator(): LengthAwarePaginator
{
    return new LengthAwarePaginator([], 0, 10, 1, ['path' => url('/subjects')]);
}

function subjectImportPreviewFixture(): array
{
    return [
        'new' => [
            ['row' => ['excel_row' => 2, 'code' => 'CS100', 'name_th' => 'ใหม่']],
            ['row' => ['excel_row' => 6, 'code' => 'EN100', 'name_th' => null, 'name_en' => 'English Only']],
        ],
        'changed' => [[
            'row' => ['excel_row' => 3, 'code' => 'CS101', 'name_th' => 'ใหม่'],
            'current' => ['id' => 1, 'name_th' => 'เดิม'],
            'fingerprint' => 'hash',
            'diff' => ['name_th' => ['old' => 'เดิม', 'new' => 'ใหม่']],
        ]],
        'unchanged' => [['row' => ['excel_row' => 4, 'code' => 'CS102', 'name_th' => 'เหมือนเดิม']]],
        'errors' => [['excelRow' => 5, 'code' => 'BAD', 'column' => 'หน่วยกิตรวม', 'value' => 'x', 'message' => 'ต้องเป็นจำนวนเต็ม']],
    ];
}

test('subject index renders import actions and accessible modal hooks', function () {
    $html = view('subjects.index', [
        'subjects' => emptySubjectPaginator(),
        'importResult' => null,
    ])->render();

    expect($html)
        ->toContain('data-subject-import-open')
        ->toContain('id="subjectImportModal"')
        ->toContain('aria-labelledby="subjectImportModalLabel"')
        ->toContain('data-subject-import-drop-zone')
        ->toContain('data-subject-import-file')
        ->toContain(route('subjects.import.template'))
        ->toContain(route('subjects.import.export'))
        ->not->toContain('onclick=')
        ->not->toContain('ondrop=');
});

test('subject index renders complete import result groups', function () {
    $html = view('subjects.index', [
        'subjects' => emptySubjectPaginator(),
        'importResult' => [
            'created' => ['CS100'], 'updated' => ['CS101'],
            'skipped' => ['CS102'], 'unchanged' => ['CS103'],
        ],
    ])->render();

    expect($html)->toContain('CS100', 'CS101', 'CS102', 'CS103')
        ->toContain('นำเข้าข้อมูลรายวิชาสำเร็จ');
});

test('Preview renders summaries diffs errors and safe selection hooks', function () {
    $preview = subjectImportPreviewFixture();
    $token = str_repeat('A', 64);
    $html = view('subjects.index', [
        'subjects' => emptySubjectPaginator(),
        'importResult' => null,
        'importPreview' => $preview,
        'importPreviewToken' => $token,
    ])->render();

    expect($html)->toContain('CS100', 'CS101', 'CS102', 'EN100', 'English Only', 'BAD')
        ->toContain('เดิม', 'ใหม่')
        ->toContain('id="subjectImportPreviewModal"')
        ->toContain('modal-xl modal-dialog-scrollable modal-fullscreen-sm-down')
        ->toContain('data-bs-backdrop="static"')
        ->toContain('data-bs-keyboard="false"')
        ->toContain('id="subjectImportConfirmForm"')
        ->toContain('id="subjectImportCancelForm"')
        ->toContain(route('subjects.import.confirm', $token))
        ->toContain(route('subjects.import.cancel', $token))
        ->toContain('data-subject-import-conflict')
        ->toContain('data-subject-import-select-all')
        ->toContain('data-subject-import-select-none')
        ->toContain('disabled')
        ->not->toContain('onclick=');
});

test('Preview uses compact text statuses instead of summary cards', function () {
    $html = view('subjects.imports.partials.summary', [
        'preview' => subjectImportPreviewFixture(),
    ])->render();

    expect($html)
        ->toContain('data-subject-import-status-summary')
        ->toContain('เพิ่มใหม่', 'ข้อมูลซ้ำที่เปลี่ยน', 'ไม่เปลี่ยนแปลง', 'ข้อผิดพลาด')
        ->toContain('2', '1')
        ->not->toContain('class="card')
        ->not->toContain('row g-3');
});

test('Preview modal script opens focuses and cancels with Escape', function () {
    $script = file_get_contents(resource_path('views/subjects/imports/partials/script.blade.php'));

    expect($script)
        ->toContain("document.getElementById('subjectImportPreviewModal')")
        ->toContain("document.getElementById('subjectImportCancelForm')")
        ->toContain('bootstrap.Modal.getOrCreateInstance(modalElement)')
        ->toContain("modalElement.addEventListener('shown.bs.modal'")
        ->toContain("event.key === 'Escape'")
        ->toContain('cancelForm.requestSubmit()')
        ->toContain('modal.show()')
        ->toContain('data-subject-import-select-all')
        ->toContain('data-subject-import-select-none');
});
