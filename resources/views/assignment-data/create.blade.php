@extends('layouts.app')
@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>สำเร็จ!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
            <div class="bg-white shadow-sm rounded-lg p-6 mb-4">
                <div class="bg-white shadow-sm rounded-lg mb-6">
                    <form id="evaluation-form" action="{{ route('assignment-data.store') }}" method="POST">
                        @csrf
                        <h2 class="text-xl font-semibold text-gray-800 p-6 mb-4">กำหนดกรอบการประเมิน</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 border-b border-gray-200">
                            <div>
                                <label for="start_time"
                                    class="block text-sm font-medium text-gray-700">วันเริ่มต้นประเมิน:</label>
                                <input type="text" name="start_time" id="start_time"
                                    class="mt-1 form-input-custom flatpickr-date" required>
                            </div>
                            <div>
                                <label for="end_time"
                                    class="block text-sm font-medium text-gray-700">วันสิ้นสุดประเมิน:</label>
                                <input type="text" name="end_time" id="end_time"
                                    class="mt-1 form-input-custom flatpickr-date" required>
                            </div>
                        </div>
                </div>
                <h2 class="text-xl font-semibold text-gray-800 p-6 ">เกณฑ์การประเมิน</h2>
                <div class="mb-4 p-6 border-b border-gray-200 p-6 border-b border-gray-200">
                    <label for="report_id" class="block text-sm font-medium text-gray-700 mb-2">
                        เกณฑ์การประเมิน :
                    </label>

                    <select id="report_data_id" name="report_data_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- กรุณาเลือกเกณฑ์การประเมิน --</option>
                        @foreach ($report_data as $item)
                            <option value="{{ $item->id }}">{{ $item->report_title }}</option>
                        @endforeach
                    </select>

                    <h2 class="text-xl font-semibold text-gray-800 p-6 ">กำหนดผู้ประเมิน / ผู้รับการประเมิน</h2>
                    <!-- ฟิลเตอร์หน่วยงาน -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-2">
                            ฟิลเตอร์ตามหน่วยงาน :
                        </label>
                        <select id="department_filter" name="department_filter"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            <option value="all">แสดงทั้งหมด</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->department_name }} -
                                    {{ $department->faculty }}</option>
                            @endforeach
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
                                    <span id="evaluatees-total-count">0</span> คนทั้งหมด
                                </div>
                            </div>
                            <select id="evaluatees" name="evaluatees[]" multiple required
                                class="form-multi-select w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($evaluatees as $user)
                                    <option value="{{ $user->id }}"
                                        data-department="{{ $user->department_id ?? 'none' }}">
                                        {{ $user->name }}
                                    </option>
                                @endforeach
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
                                    <span id="evaluators-total-count">0</span> คนทั้งหมด
                                </div>
                            </div>
                            <select id="evaluators" name="evaluators[]" multiple required
                                class="form-multi-select w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach ($evaluators as $user)
                                    <option value="{{ $user->id }}" data-department="{{ $user->department_id }}">
                                        {{ $user->name }} - {{ $user->department->department_name ?? '' }}
                                    </option>
                                @endforeach
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
                    // ดึงชื่อหน่วยงานจาก blade json
                    const departmentNames = @json($departments->pluck('department_name', 'id'));

                    function formatOption(option) {
                        if (!option.id) return option.text;

                        const $option = $(option.element);
                        if (!$option.length) return option.text; // เพิ่มเช็คนี้

                        const isSelected = $option.is(':selected');
                        const department = $option.data('department') || ''; // กำหนด default
                        const isDisabled = $option.is(':disabled');

                        if (isDisabled) return null;

                        return $(`<div class="flex items-center justify-between" style="padding: 4px 0;" data-id="${option.id}">
                         <div class="flex items-center"> <input type="checkbox" class="mr-2" ${isSelected ? 'checked' : ''} disabled>
                                  <span>${option.text}</span>
                               </div>
                            <span class="${department}">${departmentNames[department] || department}</span>
                            </div>
                       `);
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

                        $select.on('select2:open', function() {
                            const selectAllClass = `select2-select-all-${selectId}`;
                            $('.select2-results__option--select-all').remove();

                            setTimeout(() => {
                                if (!$(`.${selectAllClass}`).length) {
                                    const $availableOptions = $select.find('option:not(:disabled)');
                                    if ($availableOptions.length > 1) {
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

                        $select.on('select2:close', function() {
                            $('.select2-results__option--select-all').remove();
                        });

                        $select.on('change select2:select select2:unselect', function() {
                            updateDisplayAndCounts();

                            if ($select.data('select2').isOpen()) {
                                $select.select2('close');
                                setTimeout(() => {
                                    $select.select2('open');
                                }, 0);
                            }
                        });

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

                            $(`#${selectedCountId}`).text(selected.length);

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

                        $('#total-evaluatees').text($('#evaluatees').find(':selected').length);
                        $('#total-evaluators').text($('#evaluators').find(':selected').length);
                    }

                    function filterByDepartment(department) {
                        const filterSummary = $('#filter-summary');

                        if (department === 'all' || !department) {
                            department = '';
                        }

                        ['evaluatees', 'evaluators'].forEach(selectId => {
                            const $select = $(`#${selectId}`);

                            $select.find('option').each(function() {
                                const optionDept = $(this).data('department');
                                const shouldShow = !department || optionDept == department;
                                $(this).prop('disabled', !shouldShow);
                            });

                            updateAvailableCount($select, `${selectId}-available-count`);

                            $select.select2('destroy');
                            setupSelect2WithSelectAll(
                                selectId,
                                `selected-${selectId}`,
                                `${selectId}-selected-count`,
                                `${selectId}-available-count`
                            );

                            $select.val(null).trigger('change');
                        });

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

                    setupSelect2WithSelectAll('evaluatees', 'selected-evaluatees', 'evaluatees-selected-count',
                        'evaluatees-available-count');
                    setupSelect2WithSelectAll('evaluators', 'selected-evaluators', 'evaluators-selected-count',
                        'evaluators-available-count');

                    $('#department_filter').on('change', function() {
                        const selectedDepartment = $(this).val();

                        const hasEvaluatees = ($('#evaluatees').val() || []).length > 0;
                        const hasEvaluators = ($('#evaluators').val() || []).length > 0;

                        if ((hasEvaluatees || hasEvaluators) && selectedDepartment !== "") {
                            const confirmed = confirm(
                                "คุณต้องล้างข้อมูลในฟอร์มก่อนจึงจะสามารถเปลี่ยนแผนกได้\n\nต้องการล้างฟอร์มหรือไม่?"
                            );
                            if (!confirmed) {
                                $(this).val('all').trigger('change');
                                return;
                            }

                            $('#evaluation-form')[0].reset();
                            $('#evaluatees').val(null).trigger('change');
                            $('#evaluators').val(null).trigger('change');
                        }

                        filterByDepartment(selectedDepartment);
                    });

                    $('#evaluatees-total-count').text($('#evaluatees option').length);
                    $('#evaluators-total-count').text($('#evaluators option').length);
                    updateDisplayAndCounts();
                    filterByDepartment('all');

                    $('#reset-btn').on('click', function() {
                        if (confirm('คุณต้องการล้างข้อมูลในฟอร์มทั้งหมดใช่หรือไม่?')) {
                            $('#evaluation-form')[0].reset();
                            $('#department_filter').val('all').trigger('change');
                            filterByDepartment('all');
                            alert('ล้างข้อมูลในฟอร์มเรียบร้อยแล้ว');
                        }
                    });

                    // ปรับปรุง submit form ให้สร้างข้อมูลในรูปแบบที่ Controller ต้องการ
                    $('#evaluation-form').on('submit', function(e) {
                        e.preventDefault();

                        const evaluateesSelected = $('#evaluatees').val() || [];
                        const evaluatorsSelected = $('#evaluators').val() || [];
                        const reportDataId = $('#report_data_id').val();
                        const startTime = $('#start_time').val();
                        const endTime = $('#end_time').val();

                        // Validation
                        if (!reportDataId) {
                            alert('กรุณาเลือกเกณฑ์การประเมิน');
                            return;
                        }

                        if (!startTime) {
                            alert('กรุณาเลือกวันเริ่มต้นประเมิน');
                            return;
                        }

                        if (!endTime) {
                            alert('กรุณาเลือกวันสิ้นสุดประเมิน');
                            return;
                        }

                        if (evaluateesSelected.length === 0) {
                            alert('กรุณาเลือกผู้รับการประเมินอย่างน้อย 1 คน');
                            return;
                        }

                        if (evaluatorsSelected.length === 0) {
                            alert('กรุณาเลือกผู้ประเมินอย่างน้อย 1 คน');
                            return;
                        }

                        // สร้าง assignments array ตามที่ Controller ต้องการ
                        const assignments = [];
                        evaluateesSelected.forEach(evaluatee => {
                            evaluatorsSelected.forEach(evaluator => {
                                assignments.push({
                                    report_data_id: reportDataId,
                                    evaluatee: evaluatee,
                                    evaluator: evaluator
                                });
                            });
                        });

                        // สร้าง hidden inputs สำหรับส่งข้อมูล
                        $('#evaluation-form input[name^="assignments"]').remove();

                        assignments.forEach((assignment, index) => {
                            $('<input>').attr({
                                type: 'hidden',
                                name: `assignments[${index}][report_data_id]`,
                                value: assignment.report_data_id
                            }).appendTo('#evaluation-form');

                            $('<input>').attr({
                                type: 'hidden',
                                name: `assignments[${index}][evaluatee]`,
                                value: assignment.evaluatee
                            }).appendTo('#evaluation-form');

                            $('<input>').attr({
                                type: 'hidden',
                                name: `assignments[${index}][evaluator]`,
                                value: assignment.evaluator
                            }).appendTo('#evaluation-form');
                        });

                        // Submit form
                        this.submit();
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
 
@endsection
