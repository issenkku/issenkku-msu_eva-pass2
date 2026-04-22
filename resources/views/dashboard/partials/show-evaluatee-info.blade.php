{{-- ข้อมูลผู้รับการประเมิน --}}
<div class="info-card">
    <div class="card-header">
        <h3>ข้อมูลผู้รับการประเมิน</h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <label>ชื่อ-นามสกุล:</label>
                <span>{{ $assignment['evaluatee']['name'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>ตำแหน่ง:</label>
                <span>{{ $assignment['evaluatee']['position'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>หน่วยงาน:</label>
                <span>{{ $assignment['evaluatee']['department'] ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
