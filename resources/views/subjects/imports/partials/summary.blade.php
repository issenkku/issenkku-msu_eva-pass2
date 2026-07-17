<div class="d-flex flex-wrap align-items-center gap-2 mb-3"
     aria-label="สรุปสถานะการตรวจสอบข้อมูล"
     data-subject-import-status-summary>
    @foreach([
        ['เพิ่มใหม่', count($preview['new']), 'success'],
        ['ข้อมูลซ้ำที่เปลี่ยน', count($preview['changed']), 'warning'],
        ['ไม่เปลี่ยนแปลง', count($preview['unchanged']), 'secondary'],
        ['ข้อผิดพลาด', count($preview['errors']), 'danger'],
    ] as [$label, $count, $type])
        <span class="badge rounded-pill text-bg-{{ $type }}">
            {{ $label }} <span class="ms-1">{{ $count }}</span>
        </span>
    @endforeach
</div>
