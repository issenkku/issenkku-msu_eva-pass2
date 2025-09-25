@extends('layouts.app')

@section('title', 'แก้ไขรอบการประเมิน')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(session('success'))
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
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
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <body class="bg-gray-50 min-h-screen py-8">
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
                                <h3 class="text-lg font-medium text-gray-700">ตำแหน่งผู้รับการประเมิน</h3>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <label class="block text-sm font-medium text-gray-700">
                                    รายชื่อตำแหน่งผู้รับการประเมิน:
                                </label>
                                <div class="text-sm text-gray-500">
                                    <span id="evaluatees-available-count">0</span> ตำแหน่งที่แสดง จาก
                                    <span id="evaluatees-total-count">0</span> ตำแหน่งทั้งหมด
                                </div>
                            </div>
                            <select id="evaluatees" name="evaluatees" required
                                class="form-select w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- เลือกตำแหน่งผู้รับการประเมิน --</option>
                                @foreach ($evaluatees as $position)
                                    <option value="{{ $position->id }}"
                                        data-position-name="{{ $position->name }}"
                                        data-user-count="{{ $position->user->count() }}"
                                        {{ $assignmentData->evaluatee_position_id == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }} ({{ $position->user->count() }} คน)
                                    </option>
                                @endforeach
                            </select>

                            <!-- Selected Display for Evaluatees -->
                            <div class="mt-4 p-4 bg-white rounded-lg min-h-[60px] border border-blue-200">
                                <p class="text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-check-circle mr-2 text-blue-500"></i>ตำแหน่งผู้รับการประเมินที่เลือก:
                                    <span id="evaluatees-selected-count" class="text-blue-600 font-semibold">0</span> ตำแหน่ง
                                </p>
                                <div id="selected-evaluatees" class="flex flex-col gap-2">
                                    <span class="text-sm text-gray-500">ยังไม่ได้เลือกตำแหน่ง</span>
                                </div>
                            </div>
                        </div>

                        <!-- ผู้ประเมิน Section -->
                        <div class="bg-green-50 rounded-lg p-6 position-card">
                            <div class="flex items-center mb-4">
                                <div class="flex items-center justify-center w-6 h-6 bg-green-600 text-white rounded-full mr-2 text-xs font-semibold">
                                    B
                                </div>
                                <h3 class="text-lg font-medium text-gray-700">ตำแหน่งผู้ประเมิน</h3>
                            </div>
                            <div class="flex items-center justify-between mb-4">
                                <label for="evaluators" class="block text-sm font-medium text-gray-700">
                                    รายชื่อตำแหน่งผู้ประเมิน:
                                </label>
                                <div class="text-sm text-gray-500">
                                    <span id="evaluators-available-count">0</span> ตำแหน่งที่แสดง จาก
                                    <span id="evaluators-total-count">0</span> ตำแหน่งทั้งหมด
                                </div>
                            </div>
                            <select id="evaluators" name="evaluators" required
                                class="form-select w-full focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- เลือกตำแหน่งผู้ประเมิน --</option>
                                @foreach ($evaluators as $position)
                                    <option value="{{ $position->id }}" 
                                        data-position-name="{{ $position->name }}"
                                        data-user-count="{{ $position->user->count() }}"
                                        {{ $assignmentData->evaluator_position_id == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }} ({{ $position->user->count() }} คน)
                                    </option>
                                @endforeach
                            </select>

                            <!-- Selected Display for Evaluators -->
                            <div class="mt-4 p-4 bg-white rounded-lg min-h-[60px] border border-green-200">
                                <p class="text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-check-circle mr-2 text-green-500"></i>ตำแหน่งผู้ประเมินที่เลือก:
                                    <span id="evaluators-selected-count" class="text-green-600 font-semibold">0</span> ตำแหน่ง
                                </p>
                                <div id="selected-evaluators" class="flex flex-col gap-2">
                                    <span class="text-sm text-gray-500">ยังไม่ได้เลือกตำแหน่ง</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- สรุปและปุ่มควบคุม Section -->
                <div class="bg-white shadow-sm rounded-lg p-6 form-section step-card step-4">
                    <!-- Form Actions -->
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-2"></i>
                            <span class="font-medium">หมายเหตุ:</span>
                            กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึกการแก้ไข
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('assignment-data.index') }}" 
                                class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400 transition-colors">
                                <i class="fas fa-times mr-2"></i>ยกเลิก
                            </a>
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-save mr-2"></i>บันทึกการแก้ไข
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

                    const positionName = $option.data('position-name') || option.text || '';
                    const userCount = $option.data('user-count') || 0;

                    return $(`<div class="flex items-center justify-between" style="padding: 4px 0;">
                     <div class="flex items-center"> 
                        <span>${positionName} (${userCount} คน)</span>
                    </div>
                    </div>`);
                }

                function setupSelect2Single(selectId, displayId, selectedCountId, availableCountId) {
                    const $select = $(`#${selectId}`);
                    
                    if (!$select.length) {
                        console.warn(`Element with ID ${selectId} not found`);
                        return;
                    }

                    $select.select2({
                        placeholder: "เลือกตำแหน่ง...",
                        width: '100%',
                        allowClear: true,
                        templateResult: formatOption,
                        language: {
                            noResults: function() {
                                return "ไม่พบตำแหน่งที่ตรงกับการค้นหา";
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
                    const availableCount = $select.find('option:not(:disabled)').length;
                    const $countElement = $(`#${countId}`);
                    if ($countElement.length) {
                        $countElement.text(availableCount);
                    }
                }

                function updateDisplayAndCounts() {
                    ['evaluatees', 'evaluators'].forEach(type => {
                        const $select = $(`#${type}`);
                        const selectedValue = $select.val();
                        const selectedOption = $select.find(':selected');
                        const displayId = `selected-${type}`;
                        const selectedCountId = `${type}-selected-count`;

                        const selectedCount = selectedValue ? 1 : 0;
                        $(`#${selectedCountId}`).text(selectedCount);

                        if (!selectedValue || selectedValue === '') {
                            $(`#${displayId}`).html(
                                '<span class="text-sm text-gray-500">ยังไม่ได้เลือกตำแหน่ง</span>');
                        } else {
                            const positionName = selectedOption.data('position-name') || selectedOption.text() || 'ไม่ระบุ';
                            const userCount = selectedOption.data('user-count') || 0;
                            const tag = `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${positionName} (${userCount} คน)
                            </span>`;
                            $(`#${displayId}`).html(tag);
                        }
                    });

                    // อัปเดตสรุปข้อมูล
                    updateSummary();
                }

                function updateSummary() {
                    try {
                        // อัปเดตระยะเวลาประเมิน
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

                        // อัปเดตเกณฑ์การประเมิน (แสดงแบบเต็ม)
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

                // Initialize Select2 with error handling
                try {
                    setupSelect2Single('evaluatees', 'selected-evaluatees', 'evaluatees-selected-count',
                        'evaluatees-available-count');
                    setupSelect2Single('evaluators', 'selected-evaluators', 'evaluators-selected-count',
                        'evaluators-available-count');
                } catch (error) {
                    console.error('Error initializing Select2:', error);
                }

                // Initialize counts safely
                const $evaluatees = $('#evaluatees');
                const $evaluators = $('#evaluators');
                
                if ($evaluatees.length) {
                    $('#evaluatees-total-count').text($evaluatees.find('option').length);
                    $('#evaluatees-available-count').text($evaluatees.find('option').length);
                }
                
                if ($evaluators.length) {
                    $('#evaluators-total-count').text($evaluators.find('option').length);
                    $('#evaluators-available-count').text($evaluators.find('option').length);
                }
                
                updateDisplayAndCounts();

                // เพิ่ม event listeners สำหรับอัปเดตสรุปข้อมูล
                $('#start_time, #end_time').on('change', updateSummary);
                $('#report_data_id').on('change', updateSummary);

                // อัปเดตสรุปข้อมูลครั้งแรก
                updateSummary();

                // ปรับปรุง submit form ให้สร้างข้อมูลในรูปแบบที่ Controller ต้องการ
                $('#evaluation-form').on('submit', function(e) {
                    const submitButton = $(this).find('button[type="submit"]');

                    const evaluateesSelected = $('#evaluatees').val();
                    const evaluatorsSelected = $('#evaluators').val();
                    const reportDataId = $('#report_data_id').val();
                    const startTime = $('#start_time').val();
                    const endTime = $('#end_time').val();

                    // Validation
                    if (!reportDataId) {
                        alert('กรุณาเลือกเกณฑ์การประเมิน');
                        e.preventDefault();
                        return;
                    }

                    if (!startTime) {
                        alert('กรุณาเลือกวันเริ่มต้นประเมิน');
                        e.preventDefault();
                        return;
                    }

                    if (!endTime) {
                        alert('กรุณาเลือกวันสิ้นสุดประเมิน');
                        e.preventDefault();
                        return;
                    }

                    if (!evaluateesSelected) {
                        alert('กรุณาเลือกตำแหน่งผู้รับการประเมิน');
                        e.preventDefault();
                        return;
                    }

                    if (!evaluatorsSelected) {
                        alert('กรุณาเลือกตำแหน่งผู้ประเมิน');
                        e.preventDefault();
                        return;
                    }

                    submitButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>กำลังบันทึก...');
                });
            });

            flatpickr(".flatpickr-date", {
                dateFormat: "Y-m-d", // ฟอร์แมตที่ส่งเข้า backend
                altInput: true, // แสดงวันที่แบบอ่านง่าย
                altFormat: "d/m/Y", // ฟอร์แมตที่ผู้ใช้เห็น
                locale: "th", // ภาษาไทย
                allowInput: true
            });
            
            window.setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    // Bootstrap 5 วิธีปิด alert programmatically
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                });
            }, 5000);
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

        .card-checkbox {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card-checkbox:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card-checkbox.selected {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border-color: #1d4ed8;
        }

        /* Summary cards */
        .summary-card {
            transition: all 0.3s ease;
        }

        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        /* Button animations */
        .transition-colors {
            transition: all 0.3s ease;
        }

        .transition-colors:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
    </style>
@endsection
