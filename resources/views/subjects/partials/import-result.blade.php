@if($importResult)
    <section class="alert alert-success" role="status" aria-labelledby="subjectImportResultTitle">
        <h2 id="subjectImportResultTitle" class="h5">นำเข้าข้อมูลรายวิชาสำเร็จ</h2>
        @foreach([
            'created' => 'เพิ่มใหม่', 'updated' => 'อัปเดต',
            'skipped' => 'ข้าม', 'unchanged' => 'ไม่เปลี่ยนแปลง',
        ] as $key => $label)
            <details class="mt-2">
                <summary>{{ $label }} {{ count($importResult[$key] ?? []) }} รายการ</summary>
                <ul class="mb-0 mt-2">
                    @foreach($importResult[$key] ?? [] as $code)
                        <li>{{ $code }}</li>
                    @endforeach
                </ul>
            </details>
        @endforeach
    </section>
@endif
