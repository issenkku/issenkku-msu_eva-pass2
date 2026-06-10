{{-- ข้อมูลผู้รับการประเมิน --}}
<div class="info-card">
    <div class="card-header">
        <h3>ข้อมูลผู้รับการประเมิน</h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ชื่อ-นามสกุล:</span>
                <span>{{ $assignment['evaluatee']['name'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ตำแหน่ง:</span>
                <span>{{ $assignment['evaluatee']['position'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">หน่วยงาน:</span>
                <span>{{ $assignment['evaluatee']['department'] ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
