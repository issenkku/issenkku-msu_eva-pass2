{{-- สรุปข้อมูลเดิมของคะแนนที่กำลังแก้ไข --}}
<div class="quality-score-info-card">
    <h5><i class="fas fa-info-circle me-2"></i>ข้อมูลปัจจุบัน</h5>
    <div class="quality-score-info-item">
        <div class="quality-score-info-label">ผู้ใช้งาน:</div>
        <div class="quality-score-info-value">{{ $qualityScore->user->name ?? 'N/A' }}</div>
    </div>
    <div class="quality-score-info-item">
        <div class="quality-score-info-label">อีเมล:</div>
        <div class="quality-score-info-value">{{ $qualityScore->user->email ?? 'N/A' }}</div>
    </div>
    <div class="quality-score-info-item">
        <div class="quality-score-info-label">เกณฑ์การประเมิน:</div>
        <div class="quality-score-info-value">{{ $qualityScore->qualitySubCriteria->name ?? 'N/A' }}</div>
    </div>
    <div class="quality-score-info-item">
        <div class="quality-score-info-label">เกณฑ์หลัก:</div>
        <div class="quality-score-info-value">{{ $qualityScore->qualitySubCriteria->qualityMainCriteria->criteria_name ?? $qualityScore->qualitySubCriteria->qualityMainCriteria->name ?? 'N/A' }}</div>
    </div>
    <div class="quality-score-info-item">
        <div class="quality-score-info-label">คะแนนปัจจุบัน:</div>
        <div class="quality-score-info-value">{{ number_format($qualityScore->score, 1) }}</div>
    </div>
</div>
