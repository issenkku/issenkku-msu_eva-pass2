<?php

use App\Models\Subject;
use App\Models\User;
use App\Services\Subjects\SubjectImportSnapshotStore;
use App\Support\Subjects\SubjectWorkbookSchema;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Role;

function e2eAdmin(): User
{
    Role::findOrCreate('admin');
    $user = User::factory()->create(['status' => 'active']);
    $user->assignRole('admin');

    return $user;
}

function e2eUpload(array $rows): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'subject-e2e-').'.xlsx';
    $book = new Spreadsheet;
    $sheet = $book->getActiveSheet();
    $sheet->setTitle(SubjectWorkbookSchema::DATA_SHEET);
    $sheet->fromArray(SubjectWorkbookSchema::HEADERS, null, 'A1');
    foreach ($rows as $offset => $row) {
        $sheet->fromArray($row, null, 'A'.($offset + 2), true);
    }
    (new Xlsx($book))->save($path);
    $book->disconnectWorksheets();

    return new UploadedFile(
        $path,
        'subjects.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

function tokenFromRedirect($response): string
{
    parse_str((string) parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);

    return $query['import_preview'];
}

test('invalid row shows every error and cannot be confirmed', function () {
    $admin = e2eAdmin();
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([['BAD', '', '', 'x', 2, 1, 0]]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);

    $this->actingAs($admin, 'web')->get(route('subjects.import.preview.show', $token))
        ->assertRedirect(route('subjects.index', ['import_preview' => $token]));
    $this->actingAs($admin, 'web')->get(route('subjects.index', ['import_preview' => $token]))
        ->assertOk()->assertSee('ข้อผิดพลาด')->assertSee('ชื่อรายวิชา (ไทย/อังกฤษ)')->assertSee('หน่วยกิตรวม');
    $this->actingAs($admin, 'web')->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertSessionHasErrors('selected_codes');
    expect(Subject::count())->toBe(0);
});

test('another admin cannot view or cancel an owned Preview', function () {
    $owner = e2eAdmin();
    $other = e2eAdmin();
    $token = app(SubjectImportSnapshotStore::class)->put($owner->id, 'subjects.xlsx', [
        'new' => [], 'changed' => [], 'unchanged' => [], 'errors' => [],
    ]);

    $this->actingAs($other, 'web')->get(route('subjects.import.preview.show', $token))->assertNotFound();
    $this->actingAs($other, 'web')->delete(route('subjects.import.cancel', $token))->assertRedirect(route('subjects.index'));
    expect(app(SubjectImportSnapshotStore::class)->getForUser($token, $owner->id))->not->toBeNull();
});

test('owner can cancel a Preview and an expired Preview returns not found', function () {
    Carbon::setTestNow('2026-07-17 10:00:00');
    $owner = e2eAdmin();
    $store = app(SubjectImportSnapshotStore::class);
    $cancelToken = $store->put($owner->id, 'cancel.xlsx', ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []]);
    $this->actingAs($owner, 'web')->delete(route('subjects.import.cancel', $cancelToken))
        ->assertRedirect(route('subjects.index'));
    expect($store->getForUser($cancelToken, $owner->id))->toBeNull();

    $expiredToken = $store->put($owner->id, 'expired.xlsx', ['new' => [], 'changed' => [], 'unchanged' => [], 'errors' => []]);
    Carbon::setTestNow('2026-07-17 10:31:00');
    $this->actingAs($owner, 'web')->get(route('subjects.import.preview.show', $expiredToken))->assertNotFound();
    Carbon::setTestNow();
});

test('stale Preview prevents every write and consumes the token', function () {
    $admin = e2eAdmin();
    $existing = Subject::create([
        'code' => 'CS101', 'name_th' => 'เดิม', 'name_en' => null,
        'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1,
        'self_study_credits' => 0, 'is_active' => true, 'sort_order' => 1,
    ]);
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([
            ['CS100', 'ใหม่', '', 3, 2, 1, 0],
            ['CS101', 'จากไฟล์', '', 3, 2, 1, 0],
        ]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);
    $existing->update(['name_th' => 'แก้ล่าสุด']);

    $this->actingAs($admin, 'web')->post(route('subjects.import.confirm', $token), [
        'selected_codes' => ['CS101'],
    ])->assertRedirect(route('subjects.index'))->assertSessionHas('error');

    expect(Subject::where('code', 'CS100')->exists())->toBeFalse()
        ->and(app(SubjectImportSnapshotStore::class)->getForUser($token, $admin->id))->toBeNull();
});

test('successful import result appears once after redirect', function () {
    $admin = e2eAdmin();
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([['CS100', 'ใหม่', '', 3, 2, 1, 0]]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);

    $this->actingAs($admin, 'web')->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertRedirect(route('subjects.index'));
    $this->actingAs($admin, 'web')->get(route('subjects.index'))
        ->assertOk()->assertSee('นำเข้าข้อมูลรายวิชาสำเร็จ')->assertSee('CS100');
    $this->actingAs($admin, 'web')->get(route('subjects.index'))
        ->assertOk()->assertDontSee('นำเข้าข้อมูลรายวิชาสำเร็จ');
});

test('English-only long names survive preview and confirm without copying languages', function () {
    $admin = e2eAdmin();
    $longEnglishName = trim(str_repeat('Environmental Health and Safety ', 12));
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([['ENV101', '', $longEnglishName, 3, 2, 1, 0]]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.import.preview.show', $token))
        ->assertRedirect(route('subjects.index', ['import_preview' => $token]));
    $this->actingAs($admin, 'web')
        ->get(route('subjects.index', ['import_preview' => $token]))
        ->assertOk()
        ->assertSee($longEnglishName);

    $this->actingAs($admin, 'web')
        ->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertRedirect(route('subjects.index'));

    $subject = Subject::where('code', 'ENV101')->firstOrFail();
    expect($subject->name_th)->toBeNull()
        ->and($subject->name_en)->toBe($longEnglishName);
});

test('independent credit and hour values survive preview and confirm', function () {
    $admin = e2eAdmin();
    $response = $this->actingAs($admin, 'web')->post(route('subjects.import.preview.store'), [
        'import_file' => e2eUpload([['ENV301', 'อนามัยสิ่งแวดล้อม', '', 3, 3, 0, 6, 2, 3, 1]]),
    ])->assertRedirect();
    $token = tokenFromRedirect($response);

    $this->actingAs($admin, 'web')
        ->get(route('subjects.import.preview.show', $token))
        ->assertRedirect(route('subjects.index', ['import_preview' => $token]));
    $this->actingAs($admin, 'web')
        ->get(route('subjects.index', ['import_preview' => $token]))
        ->assertOk()->assertSee('ENV301')
        ->assertDontSee('หน่วยกิตรวมต้องเท่ากับผลรวมของหน่วยกิตย่อย');

    $this->actingAs($admin, 'web')
        ->post(route('subjects.import.confirm', $token), ['selected_codes' => []])
        ->assertRedirect(route('subjects.index'));

    $subject = Subject::where('code', 'ENV301')->firstOrFail();
    expect((int) $subject->credits)->toBe(3)
        ->and((int) $subject->lecture_credits)->toBe(3)
        ->and((int) $subject->lab_credits)->toBe(0)
        ->and((int) $subject->self_study_credits)->toBe(6)
        ->and((int) $subject->lecture_hours)->toBe(2)
        ->and((int) $subject->lab_hours)->toBe(3)
        ->and((int) $subject->self_study_hours)->toBe(1);
});
