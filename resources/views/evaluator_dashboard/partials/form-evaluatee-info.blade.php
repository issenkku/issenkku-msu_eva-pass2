{{-- ข้อมูลผู้รับการประเมิน --}}
<div class="info-card">
    <div class="card-header">
        <h3>ข้อมูลผู้รับการประเมิน</h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <label>ชื่อ-นามสกุล:</label>
                <span>{{ $assignment->evaluateeUser->name ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>ตำแหน่ง:</label>
                <span>{{ $assignment->evaluateeUser->position->name ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>หน่วยงาน:</label>
                <span>{{ $assignment->evaluateeUser->department->department_name ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
