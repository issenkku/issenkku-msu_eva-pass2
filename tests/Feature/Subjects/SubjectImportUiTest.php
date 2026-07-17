<?php

use Illuminate\Pagination\LengthAwarePaginator;

function emptySubjectPaginator(): LengthAwarePaginator
{
    return new LengthAwarePaginator([], 0, 10, 1, ['path' => url('/subjects')]);
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
