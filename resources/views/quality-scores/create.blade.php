@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\quality-scores\create.blade.php --}}
@section('title', 'เพิ่มคะแนนคุณภาพ')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #cccccc;
        border-radius: 3px;
        padding: 5px 8px;
        min-height: 45px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #007bff;
        border: 1px solid #007bff;
        border-radius: 3px;
        color: white;
        padding: 2px 8px;
        margin-right: 5px;
        margin-top: 5px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffcccc;
    }
    
    .select2-container {
        width: 100% !important;
    }
</style>
@endpush

@section('content')
    <style>
        .form-container {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
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

        .criteria-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .criteria-info {
            flex: 1;
            min-width: 0;
        }

        .criteria-name {
            font-weight: 500;
            color: #333333;
            margin-bottom: 4px;
        }

        .criteria-main {
            font-size: 0.8rem;
            color: #666666;
        }

        .remove-criteria {
            flex-shrink: 0;
            background: none;
            border: none;
            color: #dc3545;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 3px;
            transition: background-color 0.15s ease;
        }

        .remove-criteria:hover {
            background-color: #f5c6cb;
        }

        .user-score-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .user-info {
            flex: 1;
            min-width: 0;
        }

        .user-name {
            font-weight: 500;
            color: #333333;
            margin-bottom: 4px;
        }

        .user-email {
            font-size: 0.8rem;
            color: #666666;
        }

        .score-input-group {
            width: 150px;
            flex-shrink: 0;
        }

        .remove-user {
            flex-shrink: 0;
            background: none;
            border: none;
            color: #dc3545;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 3px;
            transition: background-color 0.15s ease;
        }

        .remove-user:hover {
            background-color: #f5c6cb;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 0.75rem;
            border-radius: 2px;
        }

        .btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
            background-color: transparent;
        }

        .btn-outline-danger:hover {
            color: white;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-outline-warning {
            color: #ffc107;
            border-color: #ffc107;
            background-color: transparent;
        }

        .btn-outline-warning:hover {
            color: #212529;
            background-color: #ffc107;
            border-color: #ffc107;
        }

        .selected-users-container {
            min-height: 100px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            background-color: #ffffff;
        }

        .no-users-message {
            text-align: center;
            color: #666666;
            font-style: italic;
            padding: 20px;
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

        .form-check {
            margin-bottom: 8px;
        }

        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        .form-check-label {
            cursor: pointer;
            font-size: 0.9rem;
        }
    </style>

    {{-- บล็อกเนื้อหา --}}
    <div class="container-fluid">
        <!-- Header -->
        {{--  --}}
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-plus me-2"></i>เพิ่มคะแนนคุณภาพ (หลายเกณฑ์)</h4>
                <p class="mb-0 text-muted">เพิ่มคะแนนคุณภาพสำหรับผู้ใช้งานในหลายเกณฑ์การประเมินพร้อมกัน</p>
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

        <!-- Form Container -->
        {{--  --}}
        <div class="form-container">
            <div class="form-header">
                <h4><i class="fas fa-edit me-2"></i>ฟอร์มเพิ่มคะแนนคุณภาพ (หลายเกณฑ์)</h4>
            </div>

            {{-- ฟอร์ม --}}
            <form method="POST" action="{{ route('quality-scores.store') }}" id="qualityScoreForm">
                @csrf
                <div class="form-body">
                    <!-- เลือกรายงาน -->
                    <div class="form-group">
                        <label for="report_id" class="form-label">
                            เลือกรายงานการประเมิน <span class="text-danger">*</span>
                        </label>
                        <select name="report_id" id="report_id" 
                                class="form-select @error('report_id') is-invalid @enderror" required>
                            <option value="">-- เลือกรายงานการประเมิน --</option>
                            @foreach($reportDatas as $reportData)
                                <option value="{{ $reportData->id }}" 
                                        {{ (old('report_id', $selectedReport->id ?? '') == $reportData->id) ? 'selected' : '' }}>
                                    {{ $reportData->report_title }}
                                </option>
                            @endforeach
                        </select>
                        @error('report_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- เลือกเกณฑ์การประเมิน -->
                    <div class="form-group">
                        <label for="quality_sub_criteria_select" class="form-label">
                            เลือกเกณฑ์การประเมิน <span class="text-danger">*</span>
                        </label>
                        <select id="quality_sub_criteria_select" class="form-select" multiple="multiple" disabled style="width: 100%;">
                            <!-- จะถูกเติมจาก JavaScript -->
                        </select>
                        @error('criteria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- แสดงเกณฑ์ที่เลือก -->
                    <div class="form-group" id="selectedCriteriaContainer" style="display: none;">
                        <label class="form-label">เกณฑ์การประเมินที่เลือก</label>
                        <div id="selectedCriteriaList" class="selected-users-container">
                            <div id="noCriteriaMessage" class="no-users-message">
                                ยังไม่ได้เลือกเกณฑ์การประเมิน
                            </div>
                        </div>
                    </div>

                    <!-- เลือกผู้ใช้งาน -->
                    <div class="form-group">
                        <label for="user_select" class="form-label">
                            เลือกผู้ใช้งาน <span class="text-danger">*</span>
                        </label>
                        <select id="user_select" class="form-select" multiple="multiple" style="width: 100%;">
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

                    <!-- รายการผู้ใช้งานและคะแนน -->
                    <div class="form-group">
                        <label class="form-label">ผู้ใช้งานและคะแนนตามเกณฑ์</label>
                        
                        <!-- ตัวเลือกการให้คะแนน -->
                        <div class="mb-3" id="scoreTypeContainer" style="display: none;">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="score_type" id="same_score" value="same" checked>
                                <label class="form-check-label" for="same_score">
                                    คะแนนเดียวกันทุกคนทุกเกณฑ์
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="score_type" id="individual_score" value="individual">
                                <label class="form-check-label" for="individual_score">
                                    คะแนนแยกรายบุคคลและรายเกณฑ์
                                </label>
                            </div>
                        </div>

                        <!-- คะแนนสำหรับทุกคนทุกเกณฑ์ -->
                        <div class="mb-3" id="commonScoreContainer" style="display: none;">
                            <label for="common_score" class="form-label">คะแนนสำหรับทุกคนทุกเกณฑ์ <span class="text-danger">*</span></label>
                            <input type="number" id="common_score" class="form-control" min="0" max="100" step="0.1" placeholder="กรอกคะแนนสำหรับทุกคนทุกเกณฑ์">
                            <small class="text-muted">คะแนนนี้จะถูกใช้สำหรับผู้ใช้งานทุกคนในทุกเกณฑ์การประเมินที่เลือก</small>
                        </div>

                        <div id="selectedUsersContainer" class="selected-users-container">
                            <div id="noUsersMessage" class="no-users-message">
                                ยังไม่ได้เลือกผู้ใช้งาน กรุณาเลือกผู้ใช้งานจากรายการด้านบน
                            </div>
                        </div>
                    </div>

                    <!-- ปุ่มการดำเนินการ -->
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('quality-scores.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>ย้อนกลับ
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-save me-1"></i>บันทึกข้อมูล
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let selectedUsers = [];
    let selectedCriterias = [];
    let userCounter = 0;

    $(document).ready(function() {
        // Initialize Select2 for users
        $('#user_select').select2({
            placeholder: 'เลือกผู้ใช้งานหลายคน...',
            allowClear: true,
            width: '100%'
        });

        // Initialize Select2 for criteria
        $('#quality_sub_criteria_select').select2({
            placeholder: 'เลือกเกณฑ์การประเมินหลายข้อ...',
            allowClear: true,
            width: '100%'
        });

        // Handle user selection change
        $('#user_select').on('change', function() {
            const selectedValues = $(this).val() || [];
            
            // Clear existing users array
            selectedUsers = [];
            
            // Add selected users
            selectedValues.forEach(userId => {
                const option = $(this).find(`option[value="${userId}"]`);
                const userName = option.data('name');
                const userEmail = option.data('email');
                
                selectedUsers.push({
                    id: userId,
                    name: userName,
                    email: userEmail,
                    counter: userCounter++
                });
            });
            
            // Show/hide score type options
            if (selectedUsers.length > 0) {
                $('#scoreTypeContainer').show();
                $('#commonScoreContainer').show();
            } else {
                $('#scoreTypeContainer').hide();
                $('#commonScoreContainer').hide();
            }
            
            // Update UI
            updateSelectedUsersUI();
            updateSubmitButton();
        });

        // Handle criteria selection change
        $('#quality_sub_criteria_select').on('change', function() {
            const selectedValues = $(this).val() || [];
            
            // Clear existing criteria array
            selectedCriterias = [];
            
            // Add selected criteria
            selectedValues.forEach(criteriaId => {
                const option = $(this).find(`option[value="${criteriaId}"]`);
                const criteriaName = option.text();
                const optgroup = option.closest('optgroup');
                const mainCriteriaName = optgroup.length > 0 ? optgroup.attr('label') : '';
                
                selectedCriterias.push({
                    id: criteriaId,
                    name: criteriaName,
                    mainCriteriaName: mainCriteriaName
                });
            });
            
            // Show/hide criteria container
            if (selectedCriterias.length > 0) {
                $('#selectedCriteriaContainer').show();
            } else {
                $('#selectedCriteriaContainer').hide();
            }
            
            // Update UI
            updateSelectedCriteriaUI();
            updateSelectedUsersUI();
            updateSubmitButton();
        });

        // Handle score type change
        $('input[name="score_type"]').on('change', function() {
            updateSelectedUsersUI();
            updateSubmitButton();
        });

        // Handle common score change
        $('#common_score').on('input', function() {
            const scoreType = $('input[name="score_type"]:checked').val();
            if (scoreType === 'same') {
                const commonScore = $(this).val();
                $('.score-input').val(commonScore);
            }
            updateSubmitButton();
        });

        // Initialize
        const reportSelect = document.getElementById('report_id');
        if (reportSelect.value) {
            loadCriteriasByReport(reportSelect.value);
        }
        
        updateSubmitButton();
    });

    // ฟังก์ชันดึงเกณฑ์การประเมินตาม Report ที่เลือก
    function loadCriteriasByReport(reportId) {
        const criteriaSelect = $('#quality_sub_criteria_select');
        
        if (!reportId) {
            criteriaSelect.empty();
            criteriaSelect.prop('disabled', true);
            criteriaSelect.trigger('change');
            updateSubmitButton();
            return;
        }

        // แสดง loading
        criteriaSelect.empty().append('<option value="">-- กำลังโหลด... --</option>');
        criteriaSelect.prop('disabled', true);

        // เรียก API เพื่อดึงเกณฑ์
        fetch(`{{ route('quality-scores.get-criteria-by-report') }}?report_id=${reportId}`)
            .then(response => response.json())
            .then(data => {
                criteriaSelect.empty();
                
                if (data.criterias && data.criterias.length > 0) {
                    // จัดกลุ่มตาม main criteria
                    const groupedCriterias = {};
                    data.criterias.forEach(criteria => {
                        if (!groupedCriterias[criteria.main_criteria_name]) {
                            groupedCriterias[criteria.main_criteria_name] = [];
                        }
                        groupedCriterias[criteria.main_criteria_name].push(criteria);
                    });

                    // สร้าง optgroup
                    Object.keys(groupedCriterias).forEach(mainCriteriaName => {
                        const optgroup = $('<optgroup>').attr('label', mainCriteriaName);
                        
                        groupedCriterias[mainCriteriaName].forEach(criteria => {
                            const option = $('<option>').attr('value', criteria.id).text(criteria.name);
                            optgroup.append(option);
                        });
                        
                        criteriaSelect.append(optgroup);
                    });
                    
                    criteriaSelect.prop('disabled', false);
                } else {
                    criteriaSelect.append('<option value="">-- ไม่มีเกณฑ์การประเมินในรายงานนี้ --</option>');
                }
                
                // Clear previous selections
                selectedCriterias = [];
                updateSelectedCriteriaUI();
                updateSubmitButton();
            })
            .catch(error => {
                console.error('Error:', error);
                criteriaSelect.empty().append('<option value="">-- เกิดข้อผิดพลาดในการโหลด --</option>');
            });
    }

    function removeCriteria(criteriaId) {
        // Remove from Select2
        const currentValues = $('#quality_sub_criteria_select').val() || [];
        const newValues = currentValues.filter(id => id !== criteriaId);
        $('#quality_sub_criteria_select').val(newValues).trigger('change');
    }

    function removeUser(userId) {
        // Remove from Select2
        const currentValues = $('#user_select').val() || [];
        const newValues = currentValues.filter(id => id !== userId);
        $('#user_select').val(newValues).trigger('change');
    }

    function updateSelectedCriteriaUI() {
        const container = document.getElementById('selectedCriteriaList');
        const noCriteriaMessage = document.getElementById('noCriteriaMessage');

        if (selectedCriterias.length === 0) {
            noCriteriaMessage.style.display = 'block';
            container.querySelectorAll('.criteria-row').forEach(row => row.remove());
            return;
        }

        noCriteriaMessage.style.display = 'none';

        // ล้าง existing rows
        container.querySelectorAll('.criteria-row').forEach(row => row.remove());

        // สร้าง rows ใหม่
        selectedCriterias.forEach((criteria, index) => {
            const row = document.createElement('div');
            row.className = 'criteria-row';
            
            row.innerHTML = `
                <div class="criteria-info">
                    <div class="criteria-name">${criteria.name}</div>
                    <div class="criteria-main">หมวดหลัก: ${criteria.mainCriteriaName}</div>
                </div>
                <button type="button" class="remove-criteria" onclick="removeCriteria('${criteria.id}')" title="ลบเกณฑ์">
                    <i class="fas fa-times"></i>
                </button>
            `;
            container.appendChild(row);
        });
    }

    function updateSelectedUsersUI() {
        const container = document.getElementById('selectedUsersContainer');
        const noUsersMessage = document.getElementById('noUsersMessage');
        const scoreType = $('input[name="score_type"]:checked').val();
        const commonScore = $('#common_score').val();

        if (selectedUsers.length === 0 || selectedCriterias.length === 0) {
            noUsersMessage.style.display = 'block';
            noUsersMessage.textContent = selectedUsers.length === 0 ? 
                'ยังไม่ได้เลือกผู้ใช้งาน กรุณาเลือกผู้ใช้งานจากรายการด้านบน' :
                'ยังไม่ได้เลือกเกณฑ์การประเมิน กรุณาเลือกเกณฑ์การประเมินก่อน';
            container.querySelectorAll('.user-score-row').forEach(row => row.remove());
            return;
        }

        noUsersMessage.style.display = 'none';

        // ล้าง existing rows
        container.querySelectorAll('.user-score-row').forEach(row => row.remove());

        // สร้าง rows ใหม่สำหรับแต่ละผู้ใช้และแต่ละเกณฑ์
        selectedUsers.forEach((user, userIndex) => {
            selectedCriterias.forEach((criteria, criteriaIndex) => {
                const index = userIndex * selectedCriterias.length + criteriaIndex;
                const row = document.createElement('div');
                row.className = 'user-score-row';
                
                let scoreInputHtml = '';
                if (scoreType === 'same') {
                    // โหมดคะแนนเดียวกัน - แสดงคะแนนแต่ไม่ให้แก้ไข
                    scoreInputHtml = `
                        <div class="score-input-group">
                            <label class="form-label" style="margin-bottom: 4px; font-size: 0.8rem;">คะแนน</label>
                            <input type="number" name="scores[${index}]" class="form-control score-input" 
                                   min="0" max="100" step="0.1" value="${commonScore}" readonly 
                                   style="background-color: #f8f9fa;">
                            <input type="hidden" name="users[${index}]" value="${user.id}">
                            <input type="hidden" name="criterias[${index}]" value="${criteria.id}">
                        </div>
                    `;
                } else {
                    // โหมดคะแนนแยกรายบุคคล
                    scoreInputHtml = `
                        <div class="score-input-group">
                            <label class="form-label" style="margin-bottom: 4px; font-size: 0.8rem;">คะแนน</label>
                            <input type="number" name="scores[${index}]" class="form-control score-input" 
                                   min="0" max="100" step="0.1" placeholder="0.0" onchange="updateSubmitButton()">
                            <input type="hidden" name="users[${index}]" value="${user.id}">
                            <input type="hidden" name="criterias[${index}]" value="${criteria.id}">
                        </div>
                    `;
                }
                
                row.innerHTML = `
                    <div class="user-info">
                        <div class="user-name">${user.name}</div>
                        <div class="user-email">${user.email}</div>
                        <div class="criteria-name" style="font-size: 0.8rem; color: #007bff; margin-top: 4px;">
                            ${criteria.name}
                        </div>
                    </div>
                    ${scoreInputHtml}
                    <div class="d-flex flex-column gap-1">
                        <button type="button" class="remove-user btn btn-sm btn-outline-danger" 
                                onclick="removeUser('${user.id}')" title="ลบผู้ใช้งาน">
                            <i class="fas fa-user-minus"></i>
                        </button>
                        <button type="button" class="remove-criteria btn btn-sm btn-outline-warning" 
                                onclick="removeCriteria('${criteria.id}')" title="ลบเกณฑ์">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                `;
                container.appendChild(row);
            });
        });
    }

    function updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        const reportSelect = document.getElementById('report_id');
        const scoreType = $('input[name="score_type"]:checked').val();
        
        let scoresValid = false;
        
        if (scoreType === 'same') {
            // โหมดคะแนนเดียวกัน - ตรวจสอบ common score
            const commonScore = $('#common_score').val();
            scoresValid = commonScore && commonScore !== '' && parseFloat(commonScore) >= 0 && parseFloat(commonScore) <= 100;
        } else {
            // โหมดคะแนนแยกรายบุคคล - ตรวจสอบแต่ละช่อง
            const scoreInputs = document.querySelectorAll('.score-input');
            scoresValid = scoreInputs.length > 0;
            scoreInputs.forEach(input => {
                if (!input.value || input.value === '' || parseFloat(input.value) < 0 || parseFloat(input.value) > 100) {
                    scoresValid = false;
                }
            });
        }

        const canSubmit = selectedUsers.length > 0 && 
                         selectedCriterias.length > 0 &&
                         reportSelect.value &&
                         scoresValid;

        submitBtn.disabled = !canSubmit;
    }

    // Event listeners
    document.getElementById('report_id').addEventListener('change', function() {
        loadCriteriasByReport(this.value);
    });

    // ป้องกันการส่งฟอร์มเมื่อไม่พร้อม
    document.getElementById('qualityScoreForm').addEventListener('submit', function(e) {
        if (selectedUsers.length === 0) {
            e.preventDefault();
            alert('กรุณาเลือกผู้ใช้งานอย่างน้อย 1 คน');
            return false;
        }

        if (selectedCriterias.length === 0) {
            e.preventDefault();
            alert('กรุณาเลือกเกณฑ์การประเมินอย่างน้อย 1 ข้อ');
            return false;
        }

        const scoreType = $('input[name="score_type"]:checked').val();
        let scoresValid = true;
        
        if (scoreType === 'same') {
            // ตรวจสอบคะแนนรวม
            const commonScore = $('#common_score').val();
            if (!commonScore || commonScore === '' || parseFloat(commonScore) < 0 || parseFloat(commonScore) > 100) {
                scoresValid = false;
            }
        } else {
            // ตรวจสอบคะแนนรายบุคคล
            const scoreInputs = document.querySelectorAll('.score-input');
            scoreInputs.forEach(input => {
                if (!input.value || input.value === '' || parseFloat(input.value) < 0 || parseFloat(input.value) > 100) {
                    scoresValid = false;
                }
            });
        }

        if (!scoresValid) {
            e.preventDefault();
            alert('กรุณาระบุคะแนนที่ถูกต้อง (0-100)');
            return false;
        }
    });
</script>
@endpush

@endsection
