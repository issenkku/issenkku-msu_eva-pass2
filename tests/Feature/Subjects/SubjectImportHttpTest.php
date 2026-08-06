<?php

use App\Data\Subjects\SubjectImportRow;
use App\Data\Subjects\SubjectWorkbookReadResult;
use App\Models\Subject;
use App\Models\User;
use App\Services\Subjects\SubjectImportPreviewService;
use App\Services\Subjects\SubjectImportSnapshotStore;
use App\Support\Subjects\SubjectWorkbookSchema;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

function importAdmin(): User
{
    Role::findOrCreate('admin');
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole('admin');

    return $user;
}

function httpSubjectPayload(array $overrides = []): array
{
    return array_replace([
        'code' => 'CS101', 'name_th' => 'ชื่อเดิม', 'name_en' => null,
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => true, 'sort_order' => 1,
    ], $overrides);
}

function httpWorkbook(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'subject-http-').'.xlsx';
    $book = new Spreadsheet;
    $sheet = $book->getActiveSheet();
    $sheet->setTitle(SubjectWorkbookSchema::DATA_SHEET);
    $sheet->fromArray(SubjectWorkbookSchema::HEADERS, null, 'A1');
    foreach ($rows as $offset => $row) {
        $sheet->fromArray($row, null, 'A'.($offset + 2), true);
    }
    (new Xlsx($book))->save($path);
    $book->disconnectWorksheets();

    return $path;
}

test('subject import routes require admin', function () {
    $this->get(route('subjects.import.template'))->assertRedirect();
    $this->actingAs(User::factory()->create(), 'web')
        ->get(route('subjects.import.template'))
        ->assertForbidden();
});

test('admin downloads template and all-current-data workbooks', function () {
    Excel::fake();
    $admin = importAdmin();
    Subject::create(httpSubjectPayload());

    $this->actingAs($admin, 'web')->get(route('subjects.import.template'))->assertOk();
    Excel::assertDownloaded('subject_import_template.xlsx');
    $this->actingAs($admin, 'web')->get(route('subjects.import.export'))->assertOk();
    Excel::assertDownloaded('subjects_current.xlsx', fn ($export) => count($export->sheets()[0]->array()) === 1);
});

test('upload rejects non xlsx and files larger than ten megabytes in the named error bag', function () {
    $admin = importAdmin();
    $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => UploadedFile::fake()->create('subjects.csv', 10, 'text/csv'),
    ])->assertSessionHasErrorsIn('subjectImport', ['import_file']);
    $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => UploadedFile::fake()->create(
            'subjects.xlsx', 10241, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ),
    ])->assertSessionHasErrorsIn('subjectImport', ['import_file']);
});

test('admin upload redirects to the subjects index with a Preview token', function () {
    $path = httpWorkbook([['CS100', 'ใหม่', '', 3, 2, 1, 0]]);
    $file = new UploadedFile($path, 'subjects.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

    $response = $this->actingAs(importAdmin(), 'web')
        ->post(route('subjects.import.preview.store'), ['import_file' => $file])
        ->assertRedirect();

    $location = $response->headers->get('Location');
    parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

    expect(parse_url($location, PHP_URL_PATH))->toBe('/subjects')
        ->and($query['import_preview'] ?? null)->toMatch('/^[A-Za-z0-9]{64}$/');
    @unlink($path);
});

test('confirm consumes a token and flashes only a result token', function () {
    $admin = importAdmin();
    $preview = (new SubjectImportPreviewService)->build(new SubjectWorkbookReadResult([
        new SubjectImportRow(2, 'CS100', 'ใหม่', null, 3, 2, 1, 0, 0, 0, 0),
    ], []));
    $token = app(SubjectImportSnapshotStore::class)->put($admin->id, 'subjects.xlsx', $preview);

    $this->actingAs($admin, 'web')
        ->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertRedirect(route('subjects.index'))
        ->assertSessionHas('subject_import_result_token');

    expect(Subject::where('code', 'CS100')->exists())->toBeTrue()
        ->and(app(SubjectImportSnapshotStore::class)->getForUser($token, $admin->id))->toBeNull();
});

test('legacy Preview route redirects to the subjects index query', function () {
    $admin = importAdmin();
    $token = app(SubjectImportSnapshotStore::class)->put($admin->id, 'subjects.xlsx', [
        'new' => [], 'changed' => [], 'unchanged' => [], 'errors' => [],
    ]);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.import.preview.show', $token))
        ->assertRedirect(route('subjects.index', ['import_preview' => $token]));
});

test('subjects index rejects a Preview token owned by another user', function () {
    $owner = importAdmin();
    $other = importAdmin();
    $token = app(SubjectImportSnapshotStore::class)->put($owner->id, 'subjects.xlsx', [
        'new' => [], 'changed' => [], 'unchanged' => [], 'errors' => [],
    ]);

    $this->actingAs($other, 'web')
        ->get(route('subjects.index', ['import_preview' => $token]))
        ->assertNotFound();
});

test('subjects index renders an owned Preview in the modal', function () {
    $admin = importAdmin();
    $token = app(SubjectImportSnapshotStore::class)->put($admin->id, 'subjects.xlsx', [
        'new' => [[
            'row' => ['excel_row' => 2, 'code' => 'CS100', 'name_th' => 'รายวิชาใหม่', 'name_en' => null],
        ]],
        'changed' => [],
        'unchanged' => [],
        'errors' => [],
    ]);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.index', ['import_preview' => $token]))
        ->assertOk()
        ->assertSee('subjectImportPreviewModal', false)
        ->assertSee('CS100');
});
