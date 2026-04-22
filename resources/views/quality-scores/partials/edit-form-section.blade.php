{{-- ฟอร์มแก้ไขคะแนนเชิงคุณภาพรายรายการ --}}
@if($errors->any())
    <div class="quality-score-alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@include('quality-scores.partials.edit-info-card')

<div class="quality-score-form-container">
    <div class="quality-score-form-header">
        <h4><i class="fas fa-edit me-2"></i>ฟอร์มแก้ไขคะแนนเชิงคุณภาพ</h4>
    </div>

    <form method="POST" action="{{ route('quality-scores.update', $qualityScore->id) }}" id="qualityScoreForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="report_id" value="{{ old('report_id', $qualityScore->report_id) }}">

        <div class="quality-score-form-body">
            <div class="quality-score-form-group">
                <label for="quality_sub_criteria_id" class="quality-score-label">เกณฑ์การประเมิน <span class="text-danger">*</span></label>
                <select name="quality_sub_criteria_id" id="quality_sub_criteria_id" class="quality-score-select @error('quality_sub_criteria_id') is-invalid @enderror" required>
                    <option value="">-- เลือกเกณฑ์การประเมิน --</option>
                    @foreach($qualitySubCriterias->groupBy(function ($item) { return $item->qualityMainCriteria->criteria_name ?? $item->qualityMainCriteria->name ?? 'ไม่ระบุหมวด'; }) as $mainCriteria => $subCriterias)
                        <optgroup label="{{ $mainCriteria }}">
                            @foreach($subCriterias as $subCriteria)
                                <option value="{{ $subCriteria->id }}" {{ (string) old('quality_sub_criteria_id', $qualityScore->quality_sub_criteria_id) === (string) $subCriteria->id ? 'selected' : '' }}>
                                    {{ $subCriteria->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('quality_sub_criteria_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="quality-score-form-group">
                <label for="user_id" class="quality-score-label">ผู้ใช้งาน <span class="text-danger">*</span></label>
                <select name="user_id" id="user_id" class="quality-score-select @error('user_id') is-invalid @enderror" required>
                    <option value="">-- เลือกผู้ใช้งาน --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (string) old('user_id', $qualityScore->user_id) === (string) $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="quality-score-form-group">
                <label for="score" class="quality-score-label">คะแนน <span class="text-danger">*</span></label>
                <input type="number" name="score" id="score" class="quality-score-control @error('score') is-invalid @enderror" min="0" max="100" step="0.1" value="{{ old('score', $qualityScore->score) }}" placeholder="ระบุคะแนน (0-100)" required>
                @error('score')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">คะแนนต้องอยู่ระหว่าง 0-100</small>
            </div>

            <div class="quality-score-actions">
                <a href="{{ route('quality-scores.index') }}" class="quality-score-btn quality-score-btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>ย้อนกลับ
                </a>
                <button type="submit" class="quality-score-btn" id="submitBtn">
                    <i class="fas fa-save me-1"></i>อัปเดตข้อมูล
                </button>
            </div>
        </div>
    </form>
</div>
