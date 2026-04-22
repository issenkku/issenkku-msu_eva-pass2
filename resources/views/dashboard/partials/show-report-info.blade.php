{{-- ข้อมูลเกณฑ์ประเมินของรายงาน --}}
<div class="info-card">
    <div class="card-header">
        <h3>ข้อมูลเกณฑ์ประเมิน</h3>
    </div>
    <div class="card-body">
        <div class="info-grid">
            <div class="info-item">
                <label>ชื่อเกณฑ์:</label>
                <span>{{ $assignment['report_title'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>คำอธิบายเกณฑ์:</label>
                <span>{{ $assignment['report_description'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>ประเภท:</label>
                <span>{{ $assignment['assessment_type'] ?? '-' }}</span>
            </div>
            <div class="info-item">
                <label>หมายเหตุ:</label>
                <span>{{ $assignment['comment'] ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
