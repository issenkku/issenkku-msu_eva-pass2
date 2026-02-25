@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\assignment-data\edit.blade.php --}}

@section('title', 'แก้ไขรอบการประเมิน')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(session('success'))
    {{--  --}}
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        {{-- บล็อกเนื้อหา --}}
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    @endif
    
    @if ($errors->any())
        {{--  --}}
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <body class="bg-gray-50 min-h-screen py-8">
        {{-- บล็อกเนื้อหา --}}
        <div class="py-12 max-w-6xl mx-auto px-4">
            <!-- Header -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-edit mr-3 text-blue-600"></i>
                            แก้ไขรอบการประเมิน #{{ $assignmentData->id }}
                        </h1>
                        <p class="text-gray-600 mt-1">แก้ไขข้อมูลรอบการประเมิน</p>
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('assignment-data.index') }}" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors flex items-center">
                            <i class="fas fa-arrow-left mr-2"></i>กลับ
                        </a>
                    </div>
                </div>
            </div>

            {{-- ฟอร์ม --}}
            <form id="evaluation-form" action="{{ route('assignment-data.update', $assignmentData->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- กำหนดกรอบการประเมิน Section -->
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-1">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-blue-600 text-white rounded-full mr-3 text-sm font-semibold">
                            1
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">กำหนดกรอบการประเมิน</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>วันเริ่มต้นประเมิน:
                            </label>
                            <input type="text" name="start_time" id="start_time"
                                value="{{ $assignmentData->start_time ? $assignmentData->start_time->format('Y-m-d') : '' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr-date" required>
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>วันสิ้นสุดประเมิน:
                            </label>
                            <input type="text" name="end_time" id="end_time"
                                value="{{ $assignmentData->end_time ? $assignmentData->end_time->format('Y-m-d') : '' }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 flatpickr-date" required>
                        </div>
                    </div>
                </div>

                <!-- เกณฑ์การประเมิน Section -->
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-2">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-green-600 text-white rounded-full mr-3 text-sm font-semibold">
                            2
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">เลือกเกณฑ์การประเมิน</h2>
                    </div>
                    <div>
                        <label for="report_data_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clipboard-list mr-2 text-green-500"></i>เกณฑ์การประเมิน:
                        </label>
                        <select id="report_data_id" name="report_data_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">-- กรุณาเลือกเกณฑ์การประเมิน --</option>
                            @php
                                $currentReportDataId = $assignmentData->assignments->first()?->report?->reportData?->id;
                            @endphp
                            @foreach ($report_data as $item)
                                <option value="{{ $item->id }}" 
                                    {{ $currentReportDataId == $item->id ? 'selected' : '' }}>
                                    {{ $item->report_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- กำหนดผู้ประเมิน / ผู้รับการประเมิน Section -->
                <div class="bg-white shadow-sm rounded-lg p-6 mb-6 form-section step-card step-3">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-purple-600 text-white rounded-full mr-3 text-sm font-semibold">
                            3
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">กำหนดผู้ประเมิน / ผู้รับการประเมิน</h2>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- ผู้รับการประเมิน Section -->
                        <div class="bg-blue-50 rounded-lg p-6 position-card">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-6 h-6 bg-blue-600 text-white rounded-full mr-2 text-xs font-semibold">
                                    A
                                </div>
                                <h3 class="text-lg font-medium text-gray-700">ผู้รับการประเมิน</h3>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    รายชื่อผู้รับการประเมิน:
                                </label>
                                <div class="text-sm text-gray-500">
                                    <span id="evaluatees-available-count">{{ $users->count() }}</span> คนที่แสดง จาก
                                    <span id="evaluatees-total-count">{{ $users->count() }}</span> คนทั้งหมด
                                </div>
                            </div>
                            <select id="evaluatees" name="evaluatees[]" multiple required
                                class="form-select w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->position->name }}"
                                        {{ in_array($user->id, $selectedEvaluatees) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->position->name }})
                                    </option>
                                @endforeach
                            </select>

                            <div class="mt-4 p-4 bg-white rounded-lg min-h-[60px] border border-blue-200">
                                <p class="text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-check-circle mr-2 text-blue-500"></i>ผู้รับการประเมินที่เลือก:
                                    <span id="evaluatees-display-count" class="text-blue-600 font-semibold">{{ count($selectedEvaluatees) }}</span> คน
                                </p>
                                <div id="selected-evaluatees" class="flex flex-wrap gap-2">
                                    @if(count($selectedEvaluatees) > 0)
                                        @foreach($assignmentData->assignments as $assignment)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                {{ $assignment->evaluateeUser->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-sm text-gray-500">ยังไม่ได้เลือกผู้รับการประเมิน</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- ผู้ประเมิน Section -->
                        <div class="bg-green-50 rounded-lg p-6 position-card">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-6 h-6 bg-green-600 text-white rounded-full mr-2 text-xs font-semibold">
                                    B
                                </div>
                                <h3 class="text-lg font-medium text-gray-700">ผู้ประเมิน</h3>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <label for="evaluator_id" class="block text-sm font-medium text-gray-700">
                                    รายชื่อผู้ประเมิน:
                                </label>
                                <div class="text-sm text-gray-500">
                                    <span id="evaluators-available-count">{{ $users->count() }}</span> คนที่แสดง จาก
                                    <span id="evaluators-total-count">{{ $users->count() }}</span> คนทั้งหมด
                                </div>
                            </div>
                            <select id="evaluator_id" name="evaluator_id" required
                                class="form-select w-full focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- เลือกผู้ประเมิน --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" 
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->position->name }}"
                                        {{ $assignmentData->evaluator_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->position->name }})
                                    </option>
                                @endforeach
                            </select>

                            <!-- Selected Display for Evaluatees -->
                            <div class="mt-4 p-4 bg-white rounded-lg min-h-[60px] border border-green-200">
                                <p class="text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-check-circle mr-2 text-green-500"></i>ผู้ประเมินที่เลือก:
                                </p>
                                <div id="selected-evaluator" class="flex flex-col gap-2">
                                    @if($assignmentData->evaluatorUser)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            {{ $assignmentData->evaluatorUser->name }} ({{ $assignmentData->evaluatorUser->email }})
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-500">ยังไม่ได้เลือกผู้ประเมิน</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- สรุปและปุ่มควบคุม Section -->
                <div class="bg-white shadow-sm rounded-lg p-6 form-section step-card step-4">
                    <div class="flex items-center mb-6">
                        <div class="flex items-center justify-center w-8 h-8 bg-orange-600 text-white rounded-full mr-3 text-sm font-semibold">
                            4
                        </div>
                        <h2 class="text-xl font-semibold text-gray-800">สรุปและยืนยันการตั้งค่า</h2>
                    </div>
                    <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white rounded-lg p-4 shadow-sm summary-card text-center">
                                <div class="text-2xl font-bold text-blue-600" id="summary-period">-</div>
                                <div class="text-sm text-gray-500">ระยะเวลาประเมิน (วัน)</div>
                            </div>
                            <div class="bg-white rounded-lg p-6 shadow-sm summary-card">
                                <div class="text-center mb-3">
                                    <div class="text-lg font-bold text-green-600">เกณฑ์การประเมินที่เลือก</div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 font-medium mb-2">ชื่อเกณฑ์:</div>
                                    <div class="text-base font-semibold text-green-700" id="summary-criteria-full">-</div>
                                    <div class="text-xs text-gray-500 mt-2" id="summary-criteria-description">กรุณาเลือกเกณฑ์การประเมิน</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex flex-column flex-md-row justify-between items-start md:items-center mb-4 gap-3">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span class="font-medium">หมายเหตุ:</span>
                            กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก
                        </div>
                        <div class="flex justify-center items-center gap-2 flex-wrap">
                            <a href="/assignment-data"
                                class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400 transition-colors">
                                <i class="fas fa-arrow-left mr-2"></i>ย้อนกลับ
                            </a>
                            <button type="button" id="reset-btn"
                                class="px-6 py-2 bg-white text-blue-800 border-2 border-blue-500 font-semibold rounded-md hover:bg-blue-50 transition-colors">
                                <i class="fas fa-undo mr-2"></i>ล้างค่า
                            </button>
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-save mr-2"></i>บันทึกการตั้งค่า
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <script>
            $(document).ready(function() {
                function formatOption(option) {
                    if (!option.id) return option.text;

                    const $option = $(option.element);
                    if (!$option.length) return option.text;

                    const userName = $option.data('user-name') || option.text || '';
                    const userEmail = $option.data('user-email') || '';

                    return $(`<div class="flex items-center justify-between" style="padding: 4px 0;">
                     <div class="flex items-center"> 
                        <span>${userName} ${userEmail ? '(' + userEmail + ')' : ''}</span>
                    </div>
                    </div>`);
                }

                function setupSelect2Multiple(selectId, displayId, selectedCountId, availableCountId) {
                    const $select = $(`#${selectId}`);
                    
                    if (!$select.length) {
                        console.warn(`Element with ID ${selectId} not found`);
                        return;
                    }

                    $select.select2({
                        placeholder: "เลือกผู้ใช้...",
                        width: '100%',
                        allowClear: true,
                        templateResult: formatOption,
                        language: {
                            noResults: function() {
                                return "ไม่พบผู้ใช้ที่ตรงกับการค้นหา";
                            },
                            searching: function() {
                                return "กำลังค้นหา...";
                            }
                        }
                    });

                    $select.on('change', function() {
                        updateDisplayAndCounts();
                    });

                    updateAvailableCount($select, availableCountId);
                }

                function setupSelect2Single(selectId, displayId, selectedCountId, availableCountId) {
                    const $select = $(`#${selectId}`);
                    
                    if (!$select.length) {
                        console.warn(`Element with ID ${selectId} not found`);
                        return;
                    }

                    $select.select2({
                        placeholder: "เลือกผู้ประเมิน...",
                        width: '100%',
                        allowClear: true,
                        templateResult: formatOption,
                        language: {
                            noResults: function() {
                                return "ไม่พบผู้ใช้ที่ตรงกับการค้นหา";
                            },
                            searching: function() {
                                return "กำลังค้นหา...";
                            }
                        }
                    });

                    $select.on('change', function() {
                        updateDisplayAndCounts();
                    });

                    updateAvailableCount($select, availableCountId);
                }

                function updateAvailableCount($select, countId) {
                    if (!$select || !$select.length) return;
                    const availableCount = $select.find('option:not(:disabled)').length - 1; // exclude placeholder
                    const $countElement = $(`#${countId}`);
                    if ($countElement.length) {
                        $countElement.text(availableCount);
                    }
                }

                function updateDisplayAndCounts() {
                    // Update evaluatees
                    const $evaluatees = $('#evaluatees');
                    const selectedEvaluatees = $evaluatees.val() || [];
                    const evaluateesCount = selectedEvaluatees.length;
                    
                    $('#evaluatees-selected-count').text(evaluateesCount);

                    if (evaluateesCount === 0) {
                        $('#selected-evaluatees').html(
                            '<span class="text-sm text-gray-500">ยังไม่ได้เลือกผู้รับการประเมิน</span>');
                    } else {
                        let html = '';
                        selectedEvaluatees.forEach(val => {
                            const option = $evaluatees.find(`option[value="${val}"]`);
                            const userName = option.data('user-name') || option.text() || 'ไม่ระบุ';
                            html += `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${userName}
                            </span>`;
                        });
                        $('#selected-evaluatees').html(html);
                    }

                    // Update evaluator
                    const $evaluator = $('#evaluator_id');
                    const selectedEvaluator = $evaluator.val();
                    const evaluatorCount = selectedEvaluator ? 1 : 0;
                    
                    $('#evaluators-selected-count').text(evaluatorCount);

                    if (!selectedEvaluator || selectedEvaluator === '') {
                        $('#selected-evaluators').html(
                            '<span class="text-sm text-gray-500">ยังไม่ได้เลือกผู้ประเมิน</span>');
                    } else {
                        const selectedOption = $evaluator.find(':selected');
                        const userName = selectedOption.data('user-name') || selectedOption.text() || 'ไม่ระบุ';
                        const tag = `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            ${userName}
                        </span>`;
                        $('#selected-evaluators').html(tag);
                    }

                    // Update summary
                    updateSummary();
                }

                function updateSummary() {
                    try {
                        // Update period
                        const startTime = $('#start_time').val();
                        const endTime = $('#end_time').val();
                        const $summaryPeriod = $('#summary-period');
                        
                        if (startTime && endTime && $summaryPeriod.length) {
                            const start = new Date(startTime);
                            const end = new Date(endTime);
                            const diffTime = Math.abs(end - start);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                            $summaryPeriod.text(diffDays);
                        } else if ($summaryPeriod.length) {
                            $summaryPeriod.text('-');
                        }

                        // Update criteria
                        const selectedCriteria = $('#report_data_id option:selected').text();
                        const $summaryCriteriaFull = $('#summary-criteria-full');
                        const $summaryCriteriaDescription = $('#summary-criteria-description');
                        
                        if ($summaryCriteriaFull.length) {
                            if (selectedCriteria && selectedCriteria !== '-- กรุณาเลือกเกณฑ์การประเมิน --') {
                                $summaryCriteriaFull.text(selectedCriteria);
                                $summaryCriteriaDescription.text('เกณฑ์ที่เลือกสำหรับการประเมินในครั้งนี้');
                            } else {
                                $summaryCriteriaFull.text('-');
                                $summaryCriteriaDescription.text('กรุณาเลือกเกณฑ์การประเมิน');
                            }
                        }
                    } catch (error) {
                        console.error('Error updating summary:', error);
                    }
                }

                // Initialize Select2
                try {
                    setupSelect2Multiple('evaluatees', 'selected-evaluatees', 'evaluatees-selected-count',
                        'evaluatees-available-count');
                    setupSelect2Single('evaluator_id', 'selected-evaluators', 'evaluators-selected-count',
                        'evaluators-available-count');
                } catch (error) {
                    console.error('Error initializing Select2:', error);
                }

                // Initialize display
                updateDisplayAndCounts();

                // Event listeners
                $('#start_time, #end_time').on('change', updateSummary);
                $('#report_data_id').on('change', updateSummary);

                // Update summary initially
                updateSummary();

                // Reset button
                $('#reset-btn').on('click', function() {
                    if (confirm('คุณต้องการล้างข้อมูลในฟอร์มทั้งหมดใช่หรือไม่?')) {
                        $('#evaluation-form')[0].reset();
                        $('#evaluatees').val(null).trigger('change');
                        $('#evaluator_id').val(null).trigger('change');
                        updateDisplayAndCounts();
                        updateSummary();
                        alert('ล้างข้อมูลในฟอร์มเรียบร้อยแล้ว');
                    }
                });

                // Form validation on submit
                $('#evaluation-form').on('submit', function(e) {
                    const submitButton = $(this).find('button[type="submit"]');
                    const loadingOverlay = $('#loading-overlay');

                    const evaluateesSelected = $('#evaluatees').val() || [];
                    const evaluatorSelected = $('#evaluator_id').val();
                    const reportDataId = $('#report_data_id').val();
                    const startTime = $('#start_time').val();
                    const endTime = $('#end_time').val();

                    // Validation
                    if (!reportDataId) {
                        e.preventDefault();
                        alert('กรุณาเลือกเกณฑ์การประเมิน');
                        return false;
                    }

                    if (!startTime) {
                        e.preventDefault();
                        alert('กรุณาเลือกวันเริ่มต้นประเมิน');
                        return false;
                    }

                    if (!endTime) {
                        e.preventDefault();
                        alert('กรุณาเลือกวันสิ้นสุดประเมิน');
                        return false;
                    }

                    if (evaluateesSelected.length === 0) {
                        e.preventDefault();
                        alert('กรุณาเลือกผู้รับการประเมินอย่างน้อย 1 คน');
                        return false;
                    }

                    if (!evaluatorSelected) {
                        e.preventDefault();
                        alert('กรุณาเลือกผู้ประเมิน');
                        return false;
                    }

                    // Check if evaluator is in evaluatees
                    if (evaluateesSelected.includes(evaluatorSelected)) {
                        e.preventDefault();
                        alert('ผู้ประเมินไม่สามารถเป็นผู้รับการประเมินได้');
                        return false;
                    }

                    submitButton.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> กำลังบันทึก...');
                    if (loadingOverlay && loadingOverlay.length) {
                        loadingOverlay.removeClass('hidden');
                    }
                });

                // Initialize Flatpickr
                flatpickr(".flatpickr-date", {
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d/m/Y",
                    locale: "th",
                    allowInput: true
                });

                // Auto hide alerts
                window.setTimeout(function() {
                    const alerts = document.querySelectorAll('.alert');
                    alerts.forEach(alert => {
                        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                            bsAlert.close();
                        }
                    });
                }, 5000);
            });
        </script>
    </body>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        /* Step indicators */
        .step-card {
            transition: all 0.3s ease;
            position: relative;
        }

        .step-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            border-radius: 8px 8px 0 0;
        }

        .step-card.step-1::before { background: linear-gradient(90deg, #2563eb, #3b82f6); }
        .step-card.step-2::before { background: linear-gradient(90deg, #059669, #10b981); }
        .step-card.step-3::before { background: linear-gradient(90deg, #7c3aed, #8b5cf6); }
        .step-card.step-4::before { background: linear-gradient(90deg, #ea580c, #f97316); }

        /* Form sections hover effects */
        .form-section {
            transition: all 0.3s ease;
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Position selection cards */
        .position-card {
            transition: all 0.3s ease;
        }

        .position-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Summary cards animation */
        .summary-card {
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        /* Gradient backgrounds */
        .bg-gradient-blue { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
        .bg-gradient-green { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); }
        .bg-gradient-purple { background: linear-gradient(135deg, #e9d5ff 0%, #ddd6fe 100%); }
        .bg-gradient-orange { background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%); }

        /* Animation for step completion */
        .step-completed {
            animation: stepComplete 0.5s ease-in-out;
        }

        @keyframes stepComplete {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>

    {{-- บล็อกเนื้อหา --}}
    <div id="loading-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden z-50">
        {{-- บล็อกเนื้อหา --}}
        <div class="flex items-center justify-center h-full">
            <div class="text-center text-white">
                <!-- Spinner -->
                <svg class="animate-spin h-10 w-10 text-white mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-lg font-semibold">กำลังบันทึกข้อมูล...</p>
                <p class="text-sm">กรุณารอสักครู่</p>
            </div>
        </div>
    </div>
@endsection
