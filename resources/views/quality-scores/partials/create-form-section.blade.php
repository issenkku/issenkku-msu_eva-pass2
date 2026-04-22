{{-- ฟอร์มเพิ่มคะแนนเชิงคุณภาพแบบหลายผู้ใช้และหลายเกณฑ์ --}}
@if($errors->any())
    <div class="quality-score-alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="quality-score-form-container">
    <div class="quality-score-form-header">
        <h4><i class="fas fa-edit me-2"></i>ฟอร์มเพิ่มคะแนนเชิงคุณภาพ</h4>
    </div>

    <form method="POST" action="{{ route('quality-scores.store') }}" id="qualityScoreForm">
        @csrf
        <div class="quality-score-form-body">
            <div class="quality-score-form-group">
                <label for="report_id" class="quality-score-label">เลือกรายงานการประเมิน <span class="text-danger">*</span></label>
                <select name="report_id" id="report_id" class="quality-score-select @error('report_id') is-invalid @enderror" required>
                    <option value="">-- เลือกรายงานการประเมิน --</option>
                    @foreach($reportDatas as $reportData)
                        <option value="{{ $reportData->id }}" {{ (string) old('report_id', $selectedReport->id ?? '') === (string) $reportData->id ? 'selected' : '' }}>
                            {{ $reportData->report_title }}
                        </option>
                    @endforeach
                </select>
                @error('report_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="quality-score-form-group">
                <label for="quality_sub_criteria_select" class="quality-score-label">เลือกเกณฑ์การประเมิน <span class="text-danger">*</span></label>
                <select id="quality_sub_criteria_select" class="quality-score-select" multiple="multiple" disabled></select>
                @error('criteria')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="quality-score-form-group" id="selectedCriteriaContainer" style="display: none;">
                <label class="quality-score-label">เกณฑ์การประเมินที่เลือก</label>
                <div id="selectedCriteriaList" class="quality-score-selected-box">
                    <div id="noCriteriaMessage" class="quality-score-empty-message">ยังไม่ได้เลือกเกณฑ์การประเมิน</div>
                </div>
            </div>

            <div class="quality-score-form-group">
                <label for="user_select" class="quality-score-label">เลือกผู้ใช้งาน <span class="text-danger">*</span></label>
                <select id="user_select" class="quality-score-select" multiple="multiple">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" data-name="{{ $user->name }}" data-email="{{ $user->email }}">
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('users')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="quality-score-form-group">
                <label class="quality-score-label">ผู้ใช้งานและคะแนนตามเกณฑ์</label>

                <div class="mb-3" id="scoreTypeContainer" style="display: none;">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="score_type" id="same_score" value="same" checked>
                        <label class="form-check-label" for="same_score">ใช้คะแนนเดียวกันทุกคนทุกเกณฑ์</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="score_type" id="individual_score" value="individual">
                        <label class="form-check-label" for="individual_score">กำหนดคะแนนแยกตามคนและเกณฑ์</label>
                    </div>
                </div>

                <div class="mb-3" id="commonScoreContainer" style="display: none;">
                    <label for="common_score" class="quality-score-label">คะแนนสำหรับทุกคนทุกเกณฑ์ <span class="text-danger">*</span></label>
                    <input type="number" id="common_score" class="quality-score-control" min="0" max="100" step="0.1" placeholder="กรอกคะแนนสำหรับทุกคนทุกเกณฑ์">
                    <small class="text-muted">คะแนนนี้จะถูกใช้กับผู้ใช้งานทุกคนในทุกเกณฑ์ที่เลือก</small>
                </div>

                <div id="selectedUsersContainer" class="quality-score-selected-box">
                    <div id="noUsersMessage" class="quality-score-empty-message">ยังไม่ได้เลือกผู้ใช้งาน กรุณาเลือกผู้ใช้งานจากรายการด้านบน</div>
                </div>
            </div>

            <div class="quality-score-actions">
                <a href="{{ route('quality-scores.index') }}" class="quality-score-btn quality-score-btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i>ย้อนกลับ
                </a>
                <button type="submit" class="quality-score-btn" id="submitBtn" disabled>
                    <i class="fas fa-save me-1"></i>บันทึกข้อมูล
                </button>
            </div>
        </div>
    </form>
</div>
