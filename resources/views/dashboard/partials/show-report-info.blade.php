{{-- ข้อมูลเกณฑ์ประเมินของรายงาน --}}
<div class="info-card">
    <div class="card-header">
        <h3>ข้อมูลเกณฑ์ประเมิน</h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">ชื่อเกณฑ์:</span>
                <span>{{ $assignment['report_title'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">คำอธิบายเกณฑ์:</span>
                <span>{{ $assignment['report_description'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ประเภท:</span>
                <span>{{ $assignment['assessment_type'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">หมายเหตุ:</span>
                <span>{{ $assignment['comment'] ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
