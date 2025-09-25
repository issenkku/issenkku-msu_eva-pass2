@extends('layouts.app')
@section('title', 'แก้ไขคะแนนคุณภาพ')
@section('content')
    <style>
        .form-container {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-r            <form method="POST" action="{{ route('quality-scores.update', $qualityScore->id) }}" id="qualityScoreEditForm">dius: 4px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .form-header {
            background-color: #f3e8ff;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
        }

        .form-header h4 {
            color: #2c2c2c;
            margin: 0;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .form-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
            color: #2c2c2c;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border: 1px solid #cccccc;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 0.9rem;
            transition: border-color 0.15s ease;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: #666666;
            box-shadow: 0 0 0 0.15rem rgba(102, 102, 102, 0.1);
            outline: none;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 400;
            font-size: 0.9rem;
            border: 1px solid;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #ffffff;
            color: #333333;
            border-color: #cccccc;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #333333;
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #666666;
            border-color: #cccccc;
        }

        .btn-secondary:hover {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #666666;
            text-decoration: none;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .container-fluid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .card {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            margin-bottom: 24px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            border: 1px solid;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .info-card {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            font-weight: 500;
            color: #333333;
            width: 150px;
            flex-shrink: 0;
        }

        .info-value {
            color: #666666;
        }
    </style>

    <div class="container-fluid">
        <!-- Header -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-edit me-2"></i>แก้ไขคะแนนคุณภาพ</h4>
                <p class="mb-0 text-muted">แก้ไขคะแนนคุณภาพสำหรับผู้ใช้งาน</p>
            </div>
        </div>

        <!-- Alert Messages -->
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ข้อมูลปัจจุบัน -->
        <div class="info-card">
            <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>ข้อมูลปัจจุบัน</h5>
            <div class="info-item">
                <div class="info-label">ผู้ใช้งาน:</div>
                <div class="info-value">{{ $qualityScore->user->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">อีเมล:</div>
                <div class="info-value">{{ $qualityScore->user->email ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">เกณฑ์การประเมิน:</div>
                <div class="info-value">{{ $qualityScore->qualitySubCriteria->name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">เกณฑ์หลัก:</div>
                <div class="info-value">{{ $qualityScore->qualitySubCriteria->qualityMainCriteria->criteria_name ?? 'N/A' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">คะแนนปัจจุบัน:</div>
                <div class="info-value">{{ number_format($qualityScore->score, 1) }}</div>
            </div>
        </div>

        <!-- Form Container -->
        <div class="form-container">
            <div class="form-header">
                <h4><i class="fas fa-edit me-2"></i>ฟอร์มแก้ไขคะแนนคุณภาพ</h4>
            </div>

            <form method="POST" action="{{ route('admin.quality-scores.update', $qualityScore->id) }}" id="qualityScoreForm">
                @csrf
                @method('PUT')
                <div class="form-body">
                    <!-- เลือกเกณฑ์การประเมิน -->
                    <div class="form-group">
                        <label for="quality_sub_criteria_id" class="form-label">
                            เกณฑ์การประเมิน <span class="text-danger">*</span>
                        </label>
                        <select name="quality_sub_criteria_id" id="quality_sub_criteria_id" 
                                class="form-select @error('quality_sub_criteria_id') is-invalid @enderror" required>
                            <option value="">-- เลือกเกณฑ์การประเมิน --</option>
                            @foreach($qualitySubCriterias->groupBy('qualityMainCriteria.criteria_name') as $mainCriteria => $subCriterias)
                                <optgroup label="{{ $mainCriteria }}">
                                    @foreach($subCriterias as $subCriteria)
                                        <option value="{{ $subCriteria->id }}" 
                                                {{ (old('quality_sub_criteria_id', $qualityScore->quality_sub_criteria_id) == $subCriteria->id) ? 'selected' : '' }}>
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

                    <!-- เลือกผู้ใช้งาน -->
                    <div class="form-group">
                        <label for="user_id" class="form-label">
                            ผู้ใช้งาน <span class="text-danger">*</span>
                        </label>
                        <select name="user_id" id="user_id" 
                                class="form-select @error('user_id') is-invalid @enderror" required>
                            <option value="">-- เลือกผู้ใช้งาน --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                        {{ (old('user_id', $qualityScore->user_id) == $user->id) ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- คะแนน -->
                    <div class="form-group">
                        <label for="score" class="form-label">
                            คะแนน <span class="text-danger">*</span>
                        </label>
                        <input type="number" name="score" id="score" 
                               class="form-control @error('score') is-invalid @enderror"
                               min="0" max="100" step="0.1" 
                               value="{{ old('score', $qualityScore->score) }}"
                               placeholder="ระบุคะแนน (0-100)" required>
                        @error('score')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">คะแนนต้องอยู่ระหว่าง 0-100</small>
                    </div>

                    <!-- ปุ่มการดำเนินการ -->
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('quality-scores.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>ย้อนกลับ
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save me-1"></i>อัพเดทข้อมูล
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function validateForm() {
            const criteriaSelect = document.getElementById('quality_sub_criteria_id');
            const userSelect = document.getElementById('user_id');
            const scoreInput = document.getElementById('score');
            const submitBtn = document.getElementById('submitBtn');

            const canSubmit = criteriaSelect.value && 
                             userSelect.value && 
                             scoreInput.value && 
                             parseFloat(scoreInput.value) >= 0 && 
                             parseFloat(scoreInput.value) <= 100;

            submitBtn.disabled = !canSubmit;
        }

        // Event listeners สำหรับการเปลี่ยนแปลง
        document.getElementById('quality_sub_criteria_id').addEventListener('change', validateForm);
        document.getElementById('user_id').addEventListener('change', validateForm);
        document.getElementById('score').addEventListener('input', validateForm);

        // ป้องกันการส่งฟอร์มเมื่อไม่พร้อม
        document.getElementById('qualityScoreForm').addEventListener('submit', function(e) {
            const score = parseFloat(document.getElementById('score').value);
            
            if (isNaN(score) || score < 0 || score > 100) {
                e.preventDefault();
                alert('กรุณาระบุคะแนนที่ถูกต้อง (0-100)');
                return false;
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            validateForm();
        });
    </script>

@endsection
