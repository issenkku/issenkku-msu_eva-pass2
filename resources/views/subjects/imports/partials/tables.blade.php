@php
$fieldLabels = [
    'name_th' => 'ชื่อรายวิชา (ไทย)', 'name_en' => 'ชื่อรายวิชา (อังกฤษ)',
    'credits' => 'หน่วยกิตรวม', 'lecture_credits' => 'หน่วยกิตบรรยาย',
    'lab_credits' => 'หน่วยกิตปฏิบัติ', 'self_study_credits' => 'หน่วยกิตศึกษาด้วยตนเอง',
];
@endphp

@if($preview['errors'])
<section class="card border-danger mb-4" aria-labelledby="importErrorsTitle">
    <div class="card-header text-bg-danger">
        <h2 id="importErrorsTitle" class="h5 mb-0">ข้อผิดพลาด</h2>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <caption class="visually-hidden">รายการข้อผิดพลาดในไฟล์ Excel</caption>
            <thead><tr>
                <th scope="col">แถว</th><th scope="col">รหัส</th><th scope="col">คอลัมน์</th>
                <th scope="col">ค่าที่พบ</th><th scope="col">สาเหตุ</th>
            </tr></thead>
            <tbody>
            @foreach($preview['errors'] as $error)
                <tr>
                    <td>{{ $error['excelRow'] }}</td>
                    <td>{{ $error['code'] ?? '-' }}</td>
                    <td>{{ $error['column'] }}</td>
                    <td>{{ is_scalar($error['value']) ? $error['value'] : '-' }}</td>
                    <td>{{ $error['message'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

<section class="card mb-4" aria-labelledby="changedTitle">
    <div class="card-header d-flex flex-wrap justify-content-between gap-2">
        <h2 id="changedTitle" class="h5 mb-0">ข้อมูลซ้ำที่เปลี่ยนแปลง</h2>
        <div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-subject-import-select-all>เลือกทั้งหมด</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-subject-import-select-none>ไม่เลือกทั้งหมด</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <caption class="visually-hidden">รายการรหัสซ้ำที่มีข้อมูลเปลี่ยนแปลง</caption>
            <thead><tr>
                <th scope="col">อัปเดต</th><th scope="col">แถว</th><th scope="col">รหัส</th><th scope="col">ค่าที่เปลี่ยน</th>
            </tr></thead>
            <tbody>
            @forelse($preview['changed'] as $item)
                <tr>
                    <td>
                        <input class="form-check-input" type="checkbox" name="selected_codes[]" value="{{ $item['row']['code'] }}"
                            aria-label="อัปเดตรายวิชา {{ $item['row']['code'] }}" data-subject-import-conflict>
                    </td>
                    <td>{{ $item['row']['excel_row'] }}</td>
                    <td>{{ $item['row']['code'] }}</td>
                    <td><ul class="mb-0">
                        @foreach($item['diff'] as $field => $change)
                            <li><strong>{{ $fieldLabels[$field] ?? $field }}</strong>:
                                <del>{{ $change['old'] ?? '(ว่าง)' }}</del> → <ins>{{ $change['new'] ?? '(ว่าง)' }}</ins>
                            </li>
                        @endforeach
                    </ul></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">ไม่มีรายการ</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@foreach([['เพิ่มใหม่', 'new'], ['ไม่เปลี่ยนแปลง', 'unchanged']] as [$title, $key])
@php($sectionTitleId = 'subjectImport'.ucfirst($key).'Title')
<section class="card mb-4" aria-labelledby="{{ $sectionTitleId }}">
    <div class="card-header"><h2 id="{{ $sectionTitleId }}" class="h5 mb-0">{{ $title }}</h2></div>
    <div class="table-responsive">
        <table class="table mb-0">
            <caption class="visually-hidden">{{ $title }}</caption>
            <thead><tr><th scope="col">แถว</th><th scope="col">รหัส</th><th scope="col">ชื่อรายวิชา</th></tr></thead>
            <tbody>
            @forelse($preview[$key] as $item)
                <tr>
                    <td>{{ $item['row']['excel_row'] }}</td>
                    <td>{{ $item['row']['code'] }}</td>
                    <td>{{ $item['row']['name_th'] ?: $item['row']['name_en'] }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-center">ไม่มีรายการ</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endforeach
