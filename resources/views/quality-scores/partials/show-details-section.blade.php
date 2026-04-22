{{-- รายละเอียดคะแนนเชิงคุณภาพรายรายการ --}}
<div class="quality-score-card">
    <div class="quality-score-card-header">
        <h4><i class="fas fa-star me-2"></i>รายละเอียดคะแนนเชิงคุณภาพ</h4>
    </div>
    <div class="quality-score-card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>ผู้ใช้งาน:</strong>
                <p>{{ $qualityScore->user->name ?? 'N/A' }}</p>
                <strong>อีเมล:</strong>
                <p>{{ $qualityScore->user->email ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <strong>เกณฑ์การประเมิน:</strong>
                <p>{{ $qualityScore->qualitySubCriteria->name ?? 'N/A' }}</p>
                <strong>หมวดหลัก:</strong>
                <p>{{ $qualityScore->qualitySubCriteria->qualityMainCriteria->name ?? $qualityScore->qualitySubCriteria->qualityMainCriteria->criteria_name ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <strong>คะแนน:</strong>
                <h3 class="text-primary">{{ number_format($qualityScore->score, 1) }}</h3>
            </div>
        </div>

        <div class="quality-score-actions">
            <a href="{{ route('quality-scores.index') }}" class="quality-score-btn quality-score-btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>กลับ
            </a>
            <a href="{{ route('quality-scores.edit', $qualityScore->id) }}" class="quality-score-btn">
                <i class="fas fa-edit me-1"></i>แก้ไข
            </a>
        </div>
    </div>
</div>
