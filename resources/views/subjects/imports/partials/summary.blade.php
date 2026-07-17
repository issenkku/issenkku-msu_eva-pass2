<div class="row g-3 mb-4" aria-label="สรุปผลการตรวจสอบ">
    @foreach([
        ['เพิ่มใหม่', count($preview['new']), 'text-bg-success'],
        ['ข้อมูลซ้ำที่เปลี่ยน', count($preview['changed']), 'text-bg-warning'],
        ['ไม่เปลี่ยนแปลง', count($preview['unchanged']), 'text-bg-secondary'],
        ['ข้อผิดพลาด', count($preview['errors']), 'text-bg-danger'],
    ] as [$label, $count, $class])
        <div class="col-12 col-md-6 col-xl-3">
            <div class="card {{ $class }}">
                <div class="card-body">
                    <div class="fw-semibold">{{ $label }}</div>
                    <div class="fs-3">{{ $count }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>
