@extends('layouts.app')
@section('content')
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
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
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .card-checkbox.selected .card-avatar {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .check-icon {
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s ease;
        }

        .card-checkbox.selected .check-icon {
            opacity: 1;
            transform: scale(1);
        }

        .selected-counter {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .select2-results__option--select-all {
            font-weight: bold;
            background-color: #eef2ff !important;
            color: #1e40af !important;
            border-bottom: 1px solid #e5e7eb;
        }

        .select2-results__option--select-all:hover {
            background-color: #dbeafe !important;
        }

        .select2-selection__choice:not(:first-child) {
            display: none !important;
        }

        /* Checkbox ใน dropdown ไม่ควรถูกคลิก */
        .select2-results__option input[type="checkbox"] {
            pointer-events: none;
        }
    </style>
    <div class="py-12">

        <body class="bg-gray-50 min-h-screen py-8">
            <div class="max-w-6xl mx-auto px-4">
                <div class="bg-white shadow-sm rounded-lg p-6 mb-4">
                    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">กำหนดกรอบการประเมิน</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">วันเริ่มต้นประเมิน
                                    :</label>
                                <input type="date" name="start_date" id="start_date" class="mt-1 form-input-custom">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">วันสิ้นสุดประเมิน
                                    :</label>
                                <input type="date" name="end_date" id="end_date" class="mt-1 form-input-custom">
                            </div>
                        </div>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">กำหนดผู้ประเมิน / ผู้รับการประเมิน</h2>

                    <form id="evaluation-form" action="#" method="POST">
                        <!-- CSRF Token -->
                        <input type="hidden" name="_token" value="csrf-token-here">

                        <!-- ฟิลเตอร์หน่วยงาน -->
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-2">
                                ฟิลเตอร์ตามหน่วยงาน :
                            </label>
                            <select id="department_filter" name="department_filter"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="total">แสดงทั้งหมด</option>
                                <option value="dev">ฝ่ายพัฒนาซอฟต์แวร์</option>
                                <option value="marketing">ฝ่ายการตลาด</option>
                                <option value="hr">ฝ่ายบุคคล</option>
                                <option value="accounting">ฝ่ายบัญชี</option>
                                <option value="support">ฝ่ายสนับสนุนลูกค้า</option>
                            </select>
                            <p class="mt-2 text-xs text-gray-500">
                                เลือกหน่วยงานเพื่อกรองรายชื่อผู้ประเมินและผู้รับการประเมินด้านล่าง
                            </p>
                            <div id="filter-summary" class="mt-2 p-2 bg-blue-50 rounded text-sm text-blue-700 hidden">
                                <!-- แสดงสรุปการฟิลเตอร์ -->
                            </div>
                        </div>

                        <div class="space-y-8">
                            <!-- ผู้รับการประเมิน Section -->
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <label class="block text-sm font-medium text-gray-700">
                                        รายชื่อผู้รับการประเมิน :
                                    </label>
                                    <div class="text-sm text-gray-500">
                                        <span id="evaluatees-available-count">0</span> คนที่แสดง จาก
                                        <span id="evaluatees-total-count">5</span> คนทั้งหมด
                                    </div>
                                </div>
                                <select id="evaluatees" name="evaluatees[]" multiple data-coreui-search="true"
                                    class="form-multi-select w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="1" data-department="dev">นายสมชาย ใจดี - ฝ่ายพัฒนาซอฟต์แวร์</option>
                                    <option value="2" data-department="accounting">นางสาวสมศรี มีสุข - ฝ่ายบัญชี
                                    </option>
                                    <option value="3" data-department="hr">นายพัฒนา รักงาน - ฝ่ายทรัพยากรมนุษย์
                                    </option>
                                    <option value="4" data-department="hr">นางนิภาพร ใฝ่รู้ - ฝ่ายบุคคล</option>
                                    <option value="5" data-department="marketing">นายอนันต์ ใจเย็น - ฝ่ายการตลาด
                                    </option>
                                </select>

                                <!-- Selected Display for Evaluatees -->
                                <div class="mt-4 p-4 bg-gray-50 rounded-lg min-h-[60px]">
                                    <p class="text-sm font-medium text-gray-700 mb-2">
                                        รายชื่อผู้รับการประเมินที่เลือก:
                                        <span id="evaluatees-selected-count" class="text-blue-600 font-semibold">0</span> คน
                                    </p>
                                    <div id="selected-evaluatees" class="flex flex-col gap-2">
                                        <span class="text-sm text-gray-500">ยังไม่ได้เลือกรายชื่อ</span>
                                    </div>
                                </div>
                            </div>

                            <!-- ส่วนที่เกี่ยวกับ Evaluators -->
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <label for="evaluators" class="block text-sm font-medium text-gray-700">
                                        รายชื่อผู้ประเมิน :
                                    </label>
                                    <div class="text-sm text-gray-500">
                                        <span id="evaluators-available-count">0</span> คนที่แสดง จาก
                                        <span id="evaluators-total-count">5</span> คนทั้งหมด
                                    </div>
                                </div>
                                <select id="evaluators" name="evaluators[]" multiple
                                    class="form-multi-select w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="boss1" data-department="dev">หัวหน้าแผนกพัฒนา - ฝ่ายพัฒนาซอฟต์แวร์
                                    </option>
                                    <option value="boss2" data-department="marketing">หัวหน้าฝ่ายการตลาด - ฝ่ายการตลาด
                                    </option>
                                    <option value="boss3" data-department="hr">หัวหน้า HR - ฝ่ายบุคคล</option>
                                    <option value="boss4" data-department="support">หัวหน้าฝ่ายสนับสนุน -
                                        ฝ่ายสนับสนุนลูกค้า</option>
                                    <option value="boss5" data-department="dev">ผู้นำทีม DEV - ฝ่ายพัฒนาซอฟต์แวร์</option>
                                </select>

                                <!-- Selected Display for Evaluators -->
                                <div class="mt-4 p-4 bg-gray-50 rounded-lg min-h-[60px]">
                                    <p class="text-sm font-medium text-gray-700 mb-2">
                                        รายชื่อผู้ประเมินที่เลือก:
                                        <span id="evaluators-selected-count" class="text-blue-600 font-semibold">0</span> คน
                                    </p>
                                    <div id="selected-evaluators" class="flex flex-col gap-2">
                                        <span class="text-sm text-gray-500">ยังไม่ได้เลือกรายชื่อ</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-between items-center mt-8">
                            <div class="text-sm text-gray-500">
                                <span class="font-medium">สรุป:</span>
                                ผู้รับการประเมิน <span id="total-evaluatees" class="text-blue-600 font-semibold">0</span>
                                คน,
                                ผู้ประเมิน <span id="total-evaluators" class="text-blue-600 font-semibold">0</span> คน
                            </div>
                            <div class="flex space-x-3">
                                <button type="button" id="reset-btn"
                                    class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400 transition-colors">
                                    ล้างค่า
                                </button>
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    บันทึก
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    const departmentNames = {
                        'total': 'ทั้งหมด',
                        'dev': 'ฝ่ายพัฒนาซอฟต์แวร์',
                        'marketing': 'ฝ่ายการตลาด',
                        'hr': 'ฝ่ายบุคคล',
                        'accounting': 'ฝ่ายบัญชี',
                        'support': 'ฝ่ายสนับสนุนลูกค้า'
                    };

                    function formatOption(option) {
                        if (!option.id) return option.text;

                        const $option = $(option.element);
                        const isSelected = $option.is(':selected');
                        const department = $option.data('department');
                        const isDisabled = $option.is(':disabled');

                        if (isDisabled) return null; // ไม่แสดงตัวเลือกที่ถูก disable

                        return $(
                            `<div class="flex items-center justify-between" style="padding: 4px 0;" data-id="${option.id}">
                                <div class="flex items-center">
                              <input type="checkbox" class="mr-2" ${isSelected ? 'checked' : ''} disabled>
                                <span>${option.text}</span>
                                </div>
                             <span class=" ${department}">${departmentNames[department] || department}</span>
                         </div>`
                        );

                    }

                    function setupSelect2WithSelectAll(selectId, displayId, selectedCountId, availableCountId) {
                        const $select = $(`#${selectId}`);

                        $select.select2({
                            placeholder: "เลือกรายชื่อ...",
                            width: '100%',
                            closeOnSelect: false,
                            templateResult: formatOption,
                            templateSelection: function(data) {
                                const selected = $select.select2('data');
                                if (selected.length === 0) return 'เลือกรายชื่อ...';
                                return `เลือกแล้ว ${selected.length} คน`;
                            },
                            language: {
                                noResults: function() {
                                    return "ไม่พบรายชื่อที่ตรงกับการค้นหา";
                                },
                                searching: function() {
                                    return "กำลังค้นหา...";
                                }
                            }
                        });

                        // เพิ่ม Select All option
                        $select.on('select2:open', function() {
                            const selectId = $select.attr('id');
                            const selectAllClass = `select2-select-all-${selectId}`;

                            // ลบ Select All เก่าออกก่อน
                            $('.select2-results__option--select-all').remove();

                            setTimeout(() => {
                                if (!$(`.${selectAllClass}`).length) {
                                    const $availableOptions = $select.find('option:not(:disabled)');
                                    if ($availableOptions.length > 1) { // มีตัวเลือกมากกว่า 1 ตัว
                                        const selectAll = $(
                                            `<li class="select2-results__option select2-results__option--select-all ${selectAllClass}" role="option" style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #e5e7eb;">` +
                                            '<span style="font-weight: bold;"><span style="color: #3b82f6;">✓</span> เลือกทั้งหมด (' +
                                            $availableOptions.length + ' คน)</span>' +
                                            '</li>'
                                        );

                                        selectAll.on('click', function(e) {
                                            e.stopPropagation();
                                            const allValues = $select.find('option:not(:disabled)')
                                                .map(function() {
                                                    return $(this).val();
                                                }).get();
                                            $select.val(allValues).trigger('change');
                                            $select.select2('close');
                                        });

                                        $(".select2-results__options").prepend(selectAll);
                                    }
                                }
                            }, 50);
                        });

                        // ล้าง Select All เมื่อปิด dropdown
                        $select.on('select2:close', function() {
                            $('.select2-results__option--select-all').remove();
                        });

                        // Handle selection changes
                        $select.on('change select2:select select2:unselect', function() {
                            updateDisplayAndCounts();

                            // 🧠 Trick: Force refresh dropdown so checkbox updates
                            if ($select.data('select2').isOpen()) {
                                $select.select2('close');
                                setTimeout(() => {
                                    $select.select2('open');
                                }, 0);
                            }
                        });


                        // Update available count initially
                        updateAvailableCount($select, availableCountId);
                    }

                    function updateAvailableCount($select, countId) {
                        const availableCount = $select.find('option:not(:disabled)').length;
                        $(`#${countId}`).text(availableCount);
                    }

                    function updateDisplayAndCounts() {
                        ['evaluatees', 'evaluators'].forEach(type => {
                            const $select = $(`#${type}`);
                            const selected = $select.find(':selected');
                            const displayId = `selected-${type}`;
                            const selectedCountId = `${type}-selected-count`;

                            // Update selected count
                            $(`#${selectedCountId}`).text(selected.length);

                            // Update display tags
                            if (selected.length === 0) {
                                $(`#${displayId}`).html(
                                    '<span class="text-sm text-gray-500">ยังไม่ได้เลือกรายชื่อ</span>');
                            } else {
                                const tags = selected.map(function() {
                                    const department = $(this).data('department');
                                    return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                                        ${$(this).text()}
                                        <span class=" ${department} ml-2">${departmentNames[department]}</span>
                                    </span>`;
                                }).get().join('');
                                $(`#${displayId}`).html(tags);
                            }
                        });

                        // Update summary counts
                        $('#total-evaluatees').text($('#evaluatees').find(':selected').length);
                        $('#total-evaluators').text($('#evaluators').find(':selected').length);
                    }

                    function filterByDepartment(department) {
                        const filterSummary = $('#filter-summary');

                        ['evaluatees', 'evaluators'].forEach(selectId => {
                            const $select = $(`#${selectId}`);
                            let availableCount = 0;

                            $select.find('option').each(function() {
                                const optionDept = $(this).data('department');
                                const shouldShow = !department || optionDept === department;
                                $(this).prop('disabled', !shouldShow);
                                if (shouldShow) availableCount++;
                            });

                            // Update available count
                            updateAvailableCount($select, `${selectId}-available-count`);

                            // Destroy and recreate Select2 to refresh options
                            $select.select2('destroy');
                            setupSelect2WithSelectAll(
                                selectId,
                                `selected-${selectId}`,
                                `${selectId}-selected-count`,
                                `${selectId}-available-count`
                            );

                            // Clear selections when filtering
                            $select.val(null).trigger('change');
                        });

                        // Show/hide filter summary
                        if (department) {
                            const deptName = departmentNames[department];
                            const evaluateesCount = $('#evaluatees').find('option:not(:disabled)').length;
                            const evaluatorsCount = $('#evaluators').find('option:not(:disabled)').length;

                            filterSummary.html(
                                `<i class="fas fa-filter mr-2"></i>กำลังแสดงเฉพาะ <strong>${deptName}</strong> - ` +
                                `ผู้รับการประเมิน ${evaluateesCount} คน, ผู้ประเมิน ${evaluatorsCount} คน`
                            ).removeClass('hidden');
                        } else {
                            filterSummary.addClass('hidden');
                        }

                        updateDisplayAndCounts();
                    }

                    // Initialize Select2 for both dropdowns
                    setupSelect2WithSelectAll('evaluatees', 'selected-evaluatees', 'evaluatees-selected-count',
                        'evaluatees-available-count');
                    setupSelect2WithSelectAll('evaluators', 'selected-evaluators', 'evaluators-selected-count',
                        'evaluators-available-count');

                    // Handle department filter change
                    $('#department_filter').on('change', function() {
                        const selectedDepartment = $(this).val();

                        const hasEvaluatees = ($('#evaluatees').val() || []).length > 0;
                        const hasEvaluators = ($('#evaluators').val() || []).length > 0;

                        if ((hasEvaluatees || hasEvaluators) && selectedDepartment !== "") {
                            const confirmed = confirm(
                                "คุณต้องล้างข้อมูลในฟอร์มก่อนจึงจะสามารถเปลี่ยนแผนกได้\n\nต้องการล้างฟอร์มหรือไม่?"
                                );
                            if (!confirmed) {
                                // ยกเลิกการเลือกใหม่
                                $(this).val('').trigger('change');
                                return;
                            }

                            // ล้างฟอร์มก่อน
                            $('#evaluation-form')[0].reset();
                            $('#evaluatees').val(null).trigger('change');
                            $('#evaluators').val(null).trigger('change');
                        }

                        // กรองตามแผนก
                        filterByDepartment(selectedDepartment);
                    });


                    // Initialize counts
                    $('#evaluatees-total-count').text($('#evaluatees option').length);
                    $('#evaluators-total-count').text($('#evaluators option').length);
                    updateDisplayAndCounts();
                    filterByDepartment(''); // Initialize with no filter

                    // Reset form
                    $('#reset-btn').on('click', function() {
                        if (confirm('คุณต้องการล้างข้อมูลในฟอร์มทั้งหมดใช่หรือไม่?')) {
                            $('#evaluation-form')[0].reset();
                            $('#department_filter').val('').trigger('change');
                            filterByDepartment('');
                            alert('ล้างข้อมูลในฟอร์มเรียบร้อยแล้ว');
                        }
                    });

                    // Form submission
                    $('#evaluation-form').on('submit', function(e) {
                        e.preventDefault();

                        const evaluateesSelected = $('#evaluatees').val() || [];
                        const evaluatorsSelected = $('#evaluators').val() || [];

                        if (evaluateesSelected.length === 0) {
                            alert('กรุณาเลือกผู้รับการประเมินอย่างน้อย 1 คน');
                            return;
                        }

                        if (evaluatorsSelected.length === 0) {
                            alert('กรุณาเลือกผู้ประเมินอย่างน้อย 1 คน');
                            return;
                        }

                        const formData = {
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val(),
                            evaluatees: evaluateesSelected,
                            evaluators: evaluatorsSelected,
                            department_filter: $('#department_filter').val()
                        };

                        console.log('Form Data:', formData);
                        alert(
                            `บันทึกข้อมูลเรียบร้อยแล้ว!\nผู้รับการประเมิน: ${evaluateesSelected.length} คน\nผู้ประเมิน: ${evaluatorsSelected.length} คน`
                        );
                    });
                });
            </script>
        </body>
    </div>
@endsection
