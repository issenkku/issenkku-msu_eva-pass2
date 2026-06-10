<script>
    $(document).ready(function() {
        const departmentNames = {
            total: 'ทั้งหมด',
            dev: 'ฝ่ายพัฒนาซอฟต์แวร์',
            marketing: 'ฝ่ายการตลาด',
            hr: 'ฝ่ายบุคคล',
            accounting: 'ฝ่ายบัญชี',
            support: 'ฝ่ายสนับสนุนลูกค้า',
        };

        function formatOption(option) {
            if (!option.id) return option.text;

            const $option = $(option.element);
            const isSelected = $option.is(':selected');
            const department = $option.data('department');
            const isDisabled = $option.is(':disabled');

            if (isDisabled) return null;

            return $(
                `<div class="flex items-center justify-between" style="padding: 4px 0;" data-id="${option.id}">
                    <div class="flex items-center">
                        <input id="selected-evaluator-${option.id}" type="checkbox" class="mr-2" ${isSelected ? 'checked' : ''} disabled>
                        <span>${option.text}</span>
                    </div>
                    <span class="${department}">${departmentNames[department] || department}</span>
                </div>`
            );
        }

        function updateAvailableCount($select, countId) {
            const availableCount = $select.find('option:not(:disabled)').length;
            $(`#${countId}`).text(availableCount);
        }

        function updateDisplayAndCounts() {
            ['evaluatees', 'evaluators'].forEach((type) => {
                const $select = $(`#${type}`);
                const selected = $select.find(':selected');
                const displayId = `selected-${type}`;
                const selectedCountId = `${type}-selected-count`;

                $(`#${selectedCountId}`).text(selected.length);

                if (selected.length === 0) {
                    $(`#${displayId}`).html(
                        '<span class="text-sm text-gray-500">ยังไม่ได้เลือกรายชื่อ</span>'
                    );
                    return;
                }

                const tags = selected.map(function() {
                    const department = $(this).data('department');

                    return `<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-1 mb-1">
                        ${$(this).text()}
                        <span class="${department} ml-2">${departmentNames[department]}</span>
                    </span>`;
                }).get().join('');

                $(`#${displayId}`).html(tags);
            });

            $('#total-evaluatees').text($('#evaluatees').find(':selected').length);
            $('#total-evaluators').text($('#evaluators').find(':selected').length);
        }

        function setupSelect2WithSelectAll(selectId, displayId, selectedCountId, availableCountId) {
            const $select = $(`#${selectId}`);

            $select.select2({
                placeholder: 'เลือกรายชื่อ...',
                width: '100%',
                closeOnSelect: false,
                templateResult: formatOption,
                templateSelection: function() {
                    const selected = $select.select2('data');

                    if (selected.length === 0) return 'เลือกรายชื่อ...';

                    return `เลือกแล้ว ${selected.length} คน`;
                },
                language: {
                    noResults: function() {
                        return 'ไม่พบรายชื่อที่ตรงกับการค้นหา';
                    },
                    searching: function() {
                        return 'กำลังค้นหา...';
                    },
                },
            });

            $select.on('select2:open', function() {
                const currentSelectId = $select.attr('id');
                const selectAllClass = `select2-select-all-${currentSelectId}`;

                $('.select2-results__option--select-all').remove();

                setTimeout(() => {
                    if ($(`.${selectAllClass}`).length) {
                        return;
                    }

                    const $availableOptions = $select.find('option:not(:disabled)');

                    if ($availableOptions.length <= 1) {
                        return;
                    }

                    const selectAll = $(
                        `<li class="select2-results__option select2-results__option--select-all ${selectAllClass}" role="option" style="padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #e5e7eb;">
                            <span style="font-weight: bold;"><span style="color: #3b82f6;">✓</span> เลือกทั้งหมด (${$availableOptions.length} คน)</span>
                        </li>`
                    );

                    selectAll.on('click', function(e) {
                        e.stopPropagation();

                        const allValues = $select.find('option:not(:disabled)')
                            .map(function() {
                                return $(this).val();
                            })
                            .get();

                        $select.val(allValues).trigger('change');
                        $select.select2('close');
                    });

                    $('.select2-results__options').prepend(selectAll);
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

        function filterByDepartment(department) {
            const filterSummary = $('#filter-summary');

            ['evaluatees', 'evaluators'].forEach((selectId) => {
                const $select = $(`#${selectId}`);

                $select.find('option').each(function() {
                    const optionDept = $(this).data('department');
                    const shouldShow = !department || optionDept === department;
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

                filterSummary
                    .html(
                        `<i class="fas fa-filter mr-2"></i>กำลังแสดงเฉพาะ <strong>${deptName}</strong> - ผู้รับการประเมิน ${evaluateesCount} คน, ผู้ประเมิน ${evaluatorsCount} คน`
                    )
                    .removeClass('hidden');
            } else {
                filterSummary.addClass('hidden');
            }

            updateDisplayAndCounts();
        }

        setupSelect2WithSelectAll(
            'evaluatees',
            'selected-evaluatees',
            'evaluatees-selected-count',
            'evaluatees-available-count'
        );
        setupSelect2WithSelectAll(
            'evaluators',
            'selected-evaluators',
            'evaluators-selected-count',
            'evaluators-available-count'
        );

        $('#department_filter').on('change', function() {
            const selectedDepartment = $(this).val();
            const hasEvaluatees = ($('#evaluatees').val() || []).length > 0;
            const hasEvaluators = ($('#evaluators').val() || []).length > 0;

            if ((hasEvaluatees || hasEvaluators) && selectedDepartment !== '') {
                const confirmed = confirm(
                    'คุณต้องล้างข้อมูลในฟอร์มก่อนจึงจะสามารถเปลี่ยนแผนกได้\\n\\nต้องการล้างฟอร์มหรือไม่?'
                );

                if (!confirmed) {
                    $(this).val('').trigger('change');
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
        filterByDepartment('');

        $('#reset-btn').on('click', function() {
            if (!confirm('คุณต้องการล้างข้อมูลในฟอร์มทั้งหมดใช่หรือไม่?')) {
                return;
            }

            $('#evaluation-form')[0].reset();
            $('#department_filter').val('').trigger('change');
            filterByDepartment('');
            alert('ล้างข้อมูลในฟอร์มเรียบร้อยแล้ว');
        });

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

            alert(
                `บันทึกข้อมูลเรียบร้อยแล้ว!\\nผู้รับการประเมิน: ${evaluateesSelected.length} คน\\nผู้ประเมิน: ${evaluatorsSelected.length} คน`
            );
        });
    });
</script>
