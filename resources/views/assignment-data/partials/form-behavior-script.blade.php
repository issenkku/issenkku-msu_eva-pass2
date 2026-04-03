{{-- script กลางของฟอร์ม create/edit โดยรับข้อความและรูปแบบผ่าน config จากหน้าแม่ --}}
<script>
    $(document).ready(function() {
        const formBehaviorConfig = @json($formBehaviorConfig);
        // เก็บ option เดิมของผู้รับการประเมินไว้ทั้งหมด เพื่อใช้ rebuild select หลัง filter
        const evaluateeOptionTemplate = $('#evaluatees option').map(function() {
            return $(this).clone();
        }).get();
        const evaluateeOptionMap = Object.fromEntries(
            evaluateeOptionTemplate.map(option => [String($(option).val()), $(option).clone()])
        );

        // reviewerConfigs คือ bridge ระหว่าง Blade กับ JS
        // script จะอ้างอิงจาก config นี้แทนการ hard-code id/class ของแต่ละบทบาท
        const reviewerConfigs = formBehaviorConfig.reviewerConfigs;
        const reviewerOptionTemplates = Object.fromEntries(
            reviewerConfigs.map(config => [
                config.selectId,
                $(`#${config.selectId} option`).map(function() {
                    return $(this).clone();
                }).get()
            ])
        );

        function normalizePersonnelType(value) {
            // รวมคำเรียกประเภทบุคลากรหลายแบบให้เหลือค่ากลางเดียว
            // ช่วยให้การเทียบกับ assessment type ไม่พลาดเพราะสะกดต่างกันเล็กน้อย
            const text = String(value || '').trim();
            if (!text) return '';
            if (text.includes(formBehaviorConfig.personnelTypes.academic)) return formBehaviorConfig.personnelTypes.academic;
            if (text.includes(formBehaviorConfig.personnelTypes.support)) return formBehaviorConfig.personnelTypes.support;
            if (text.includes(formBehaviorConfig.personnelTypes.executive) || text.includes(formBehaviorConfig.personnelTypes.executiveAlt)) {
                return formBehaviorConfig.personnelTypes.executive;
            }
            return text;
        }

        function buildSelectionItem(className, userName) {
            return `<span class="${className}">${userName}</span>`;
        }

        function buildEmptySelection(className, text) {
            return `<span class="${className}">${text}</span>`;
        }

        function buildEvaluateeSelection(userName) {
            return buildSelectionItem(formBehaviorConfig.evaluateeSelectedItemClass, userName);
        }

        function buildReviewerSelection(config, userName) {
            return buildSelectionItem(config.selectedItemClass, userName);
        }

        let filteredEvaluateeOptions = [];

        function updateEvaluateesDropdownLabel() {
            const selectedCount = ($('#evaluatees').val() || []).length;
            const label = selectedCount > 0
                ? `${formBehaviorConfig.evaluateeDropdownLabel} (${selectedCount} ${formBehaviorConfig.countUnit})`
                : formBehaviorConfig.evaluateeDropdownLabel;
            $('#evaluatees-dropdown-label').text(label);
        }

        function renderEvaluateeCheckboxList() {
            // render จาก filteredEvaluateeOptions แทนการอ่าน DOM ตรง ๆ
            // เพื่อให้ผลลัพธ์ตรงกับ filter ล่าสุดเสมอ
            const selectedValues = new Set(($('#evaluatees').val() || []).map(String));
            const $list = $('#evaluatees-checkbox-list');

            if (!$list.length) {
                return;
            }

            if (filteredEvaluateeOptions.length === 0) {
                $list.html(`<div class="text-sm text-gray-500">${formBehaviorConfig.evaluateeNoResultsText}</div>`);
                return;
            }

            let html = '';
            filteredEvaluateeOptions.forEach(option => {
                const $option = $(option);
                const value = String($option.val());
                const userName = $option.data('user-name') || $option.text() || formBehaviorConfig.notSpecifiedText;
                const userEmail = $option.data('user-email') || '';
                const checked = selectedValues.has(value) ? 'checked' : '';

                html += `
                    <label class="grid cursor-pointer grid-cols-[18px_minmax(0,180px)_minmax(0,1fr)] items-center gap-x-3 border-b border-blue-50 px-1 py-2 text-sm text-gray-700 transition last:border-b-0 hover:bg-blue-50/50">
                        <input type="checkbox" class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-500 evaluatee-checkbox" value="${value}" ${checked}>
                        <span class="${formBehaviorConfig.evaluateeNameClass}">${userName}</span>
                        <span class="${formBehaviorConfig.evaluateeMetaClass}">${userEmail}</span>
                    </label>
                `;
            });

            $list.html(html);
        }

        function filterEvaluateesByCriteria() {
            // filter ผู้รับการประเมินตามเกณฑ์ที่เลือก + filter ย่อยใน dropdown
            // ถ้ารายการที่เคยเลือกไว้ไม่ตรง filter ปัจจุบัน จะยังเก็บค่าไว้เพื่อไม่ให้ผู้ใช้เสีย selection เดิม
            const selectedAssessmentType = normalizePersonnelType($('#report_data_id').find('option:selected').data('assessment-type'));
            const selectedDepartment = String($('#evaluatees-department-filter').val() || '').trim();
            const selectedPosition = String($('#evaluatees-position-filter').val() || '').trim();
            const searchKeyword = String($('#evaluatees-search-filter').val() || '').trim().toLowerCase();
            const $evaluatees = $('#evaluatees');
            const currentSelected = ($evaluatees.val() || []).map(String);

            filteredEvaluateeOptions = evaluateeOptionTemplate.filter(option => {
                const $option = $(option);
                const userType = normalizePersonnelType($(option).data('personnel-type'));
                const userName = String($option.data('user-name') || '').trim();
                const userDepartment = String($option.data('user-department') || '').trim();
                const userPosition = String($option.data('user-position') || '').trim();
                const matchedAssessmentType = !selectedAssessmentType || userType === selectedAssessmentType;
                const matchedDepartment = !selectedDepartment || userDepartment === selectedDepartment;
                const matchedPosition = !selectedPosition || userPosition === selectedPosition;
                const searchHaystack = `${userName} ${userPosition}`.toLowerCase();
                const matchedSearch = !searchKeyword || searchHaystack.includes(searchKeyword);

                return matchedAssessmentType && matchedDepartment && matchedPosition && matchedSearch;
            }).map(option => $(option).clone());

            const selectedButHiddenOptions = currentSelected
                .filter(value => !filteredEvaluateeOptions.some(option => String($(option).val()) === value))
                .map(value => evaluateeOptionMap[value])
                .filter(Boolean)
                .map(option => $(option).clone());

            $evaluatees.empty().append(filteredEvaluateeOptions, selectedButHiddenOptions);
            $evaluatees.val(currentSelected);
            $('#evaluatees-available-count').text(filteredEvaluateeOptions.length);
            $('#evaluatees-total-count').text(evaluateeOptionTemplate.length);
            renderEvaluateeCheckboxList();
            updateEvaluateesSelectAllState();
        }

        function filterReviewerOptions(config) {
            // ใช้ชุด option ต้นฉบับของ reviewer แต่ละบทบาทมาคัดใหม่ทุกครั้ง
            // วิธีนี้ง่ายต่อการคง selected value และลด bug จากการ filter ซ้อนหลายรอบ
            const $select = $(`#${config.selectId}`);
            const currentSelected = $select.val();
            const selectedDepartment = String($(`#${config.departmentFilterId}`).val() || '').trim();
            const selectedPosition = String($(`#${config.positionFilterId}`).val() || '').trim();
            const searchKeyword = String($(`#${config.searchFilterId}`).val() || '').trim().toLowerCase();
            const optionTemplate = reviewerOptionTemplates[config.selectId] || [];

            const matchedOptions = optionTemplate.filter(option => {
                const $option = $(option);
                const value = String($option.val() || '').trim();
                if (!value) {
                    return true;
                }

                const userName = String($option.data('user-name') || '').trim();
                const userDepartment = String($option.data('user-department') || '').trim();
                const userPosition = String($option.data('user-position') || '').trim();
                const matchedDepartment = !selectedDepartment || userDepartment === selectedDepartment;
                const matchedPosition = !selectedPosition || userPosition === selectedPosition;
                const searchHaystack = `${userName} ${userPosition}`.toLowerCase();
                const matchedSearch = !searchKeyword || searchHaystack.includes(searchKeyword);

                return matchedDepartment && matchedPosition && matchedSearch;
            }).map(option => $(option).clone());

            const hasSelected = matchedOptions.some(option => String(option.val()) === String(currentSelected || ''));
            const nextSelected = hasSelected ? currentSelected : '';

            $select.empty().append(matchedOptions);
            $select.val(nextSelected);

            const availableCount = Math.max(matchedOptions.length - 1, 0);
            $(`#${config.availableCountId}`).text(availableCount);
            $(`#${config.totalCountId}`).text(Math.max(optionTemplate.length - 1, 0));
            renderReviewerCheckboxList(config, matchedOptions);
            updateReviewerDropdownLabel(config);
        }

        function renderReviewerCheckboxList(config, matchedOptions) {
            // reviewer เป็น single-select จึงใช้ checkbox + logic บังคับให้เหลือได้ทีละคน
            // คงรูปแบบ checkbox ไว้ให้ UX ใกล้กับ block ผู้รับการประเมิน
            const $list = $(`#${config.checkboxListId}`);
            const selectedValue = String($(`#${config.selectId}`).val() || '');

            if (!$list.length) {
                return;
            }

            if (matchedOptions.length <= 1) {
                $list.html(`<div class="text-sm text-gray-500">${formBehaviorConfig.reviewerNoResultsText}</div>`);
                return;
            }

            let html = '';
            matchedOptions.forEach(option => {
                const $option = $(option);
                const value = String($option.val() || '').trim();
                if (!value) {
                    return;
                }

                const checked = selectedValue === value ? 'checked' : '';
                const userName = $option.data('user-name') || $option.text() || formBehaviorConfig.notSpecifiedText;
                const userEmail = $option.data('user-email') || '';

                html += `
                    <label class="grid cursor-pointer grid-cols-[18px_minmax(0,180px)_minmax(0,1fr)] items-center gap-x-3 border-b border-slate-100 px-1 py-2 text-sm text-gray-700 transition last:border-b-0 hover:bg-slate-50">
                        <input type="checkbox" class="h-4 w-4 rounded border-blue-300 text-blue-600 focus:ring-blue-500 reviewer-checkbox" data-select-id="${config.selectId}" value="${value}" ${checked}>
                        <span class="${formBehaviorConfig.reviewerNameClass}">${userName}</span>
                        <span class="${formBehaviorConfig.reviewerMetaClass}">${userEmail}</span>
                    </label>
                `;
            });

            $list.html(html || `<div class="text-sm text-gray-500">${formBehaviorConfig.reviewerNoResultsText}</div>`);
        }

        function updateReviewerDropdownLabel(config) {
            const $select = $(`#${config.selectId}`);
            const selectedValue = $select.val();

            if (!selectedValue) {
                $(`#${config.dropdownLabelId}`).text(config.placeholder);
                return;
            }

            const selectedOption = $select.find(`option[value="${selectedValue}"]`);
            const userName = selectedOption.data('user-name') || selectedOption.text() || config.placeholder;
            $(`#${config.dropdownLabelId}`).text(userName);
        }

        function updateDisplayAndCounts() {
            // sync พื้นที่สรุปผลด้านล่างของแต่ละ block ให้ตรงกับค่าจริงใน hidden select
            // จุดนี้เป็นศูนย์กลางของการอัปเดต UI หลังจากผู้ใช้เลือก/ลบ/filter ค่า
            const $evaluatees = $('#evaluatees');
            const selectedEvaluatees = $evaluatees.val() || [];
            const evaluateesCount = selectedEvaluatees.length;

            $('#evaluatees-selected-count').text(evaluateesCount);

            if (evaluateesCount === 0) {
                $('#selected-evaluatees').html(
                    buildEmptySelection(formBehaviorConfig.evaluateeSelectedEmptyClass, formBehaviorConfig.evaluateeEmptyText)
                );
            } else {
                let html = '';
                selectedEvaluatees.forEach(val => {
                    const option = $evaluatees.find(`option[value="${val}"]`).first().length
                        ? $evaluatees.find(`option[value="${val}"]`).first()
                        : $(evaluateeOptionMap[String(val)] || []);
                    const userName = option.data('user-name') || option.text() || formBehaviorConfig.notSpecifiedText;
                    html += buildEvaluateeSelection(userName);
                });
                $('#selected-evaluatees').html(html);
            }

            updateEvaluateesDropdownLabel();
            updateEvaluateesSelectAllState();

            reviewerConfigs.forEach(config => {
                const $select = $(`#${config.selectId}`);
                const selectedValue = $select.val();
                $(`#${config.countId}`).text(selectedValue ? 1 : 0);
                updateReviewerDropdownLabel(config);

                if (!selectedValue) {
                    $(`#${config.displayId}`).html(buildEmptySelection(config.emptyClass, config.emptyText));
                    return;
                }

                const selectedOption = $select.find(':selected');
                const userName = selectedOption.data('user-name') || selectedOption.text() || formBehaviorConfig.notSpecifiedText;
                $(`#${config.displayId}`).html(buildReviewerSelection(config, userName));
            });

            updateSummary();
        }

        function updateEvaluateesSelectAllState() {
            // select all ควรสะท้อนเฉพาะรายการที่มองเห็นหลัง filter เท่านั้น
            const $selectAll = $('#evaluatees-select-all');
            const selectedValues = new Set(($('#evaluatees').val() || []).map(String));
            const totalVisible = filteredEvaluateeOptions.length;
            const selectedVisibleCount = filteredEvaluateeOptions.filter(option => selectedValues.has(String($(option).val()))).length;

            if (!$selectAll.length) {
                return;
            }

            if (totalVisible === 0) {
                $selectAll.prop({
                    checked: false,
                    indeterminate: false,
                    disabled: true,
                });
                return;
            }

            const allSelected = selectedVisibleCount === totalVisible;
            const partiallySelected = selectedVisibleCount > 0 && selectedVisibleCount < totalVisible;

            $selectAll.prop({
                checked: allSelected,
                indeterminate: partiallySelected,
                disabled: false,
            });
        }

        function updateSummary() {
            try {
                // สรุประยะเวลาและเกณฑ์ที่เลือกไว้ในการ์ดสรุปท้ายฟอร์ม
                // แยกไว้ต่างหากเพื่อให้เรียกซ้ำได้ทุกครั้งที่ข้อมูลต้นทางเปลี่ยน
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

                const selectedCriteria = $('#report_data_id option:selected').text();
                const $summaryCriteriaFull = $('#summary-criteria-full');
                const $summaryCriteriaDescription = $('#summary-criteria-description');

                if ($summaryCriteriaFull.length) {
                    if (selectedCriteria && selectedCriteria !== formBehaviorConfig.criteriaPlaceholderText) {
                        $summaryCriteriaFull.text(selectedCriteria);
                        $summaryCriteriaDescription.text(formBehaviorConfig.criteriaSelectedDescription);
                    } else {
                        $summaryCriteriaFull.text('-');
                        $summaryCriteriaDescription.text(formBehaviorConfig.criteriaPromptDescription);
                    }
                }
            } catch (error) {
                console.error('Error updating summary:', error);
            }
        }

        filterEvaluateesByCriteria();
        updateDisplayAndCounts();

        $('#start_time, #end_time').on('change', updateSummary);
        $('#report_data_id').on('change', function() {
            filterEvaluateesByCriteria();
            updateDisplayAndCounts();
        });

        $('#evaluatees-department-filter, #evaluatees-position-filter').on('change', function() {
            filterEvaluateesByCriteria();
            updateDisplayAndCounts();
        });

        $('#evaluatees-search-filter').on('input', function() {
            filterEvaluateesByCriteria();
            updateDisplayAndCounts();
        });

        $('#toggle-evaluatees-dropdown').on('click', function() {
            $('#evaluatees-dropdown-panel').toggleClass('hidden');
            $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
        });

        reviewerConfigs.forEach(config => {
            $(`#${config.dropdownToggleId}`).on('click', function() {
                const $panel = $(`#${config.dropdownPanelId}`);
                $panel.toggleClass('hidden');
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });
        });

        $(document).on('click', function(event) {
            const $wrapper = $('#evaluatees-dropdown-wrapper');
            if ($wrapper.length && !$wrapper.is(event.target) && !$wrapper.has(event.target).length) {
                $('#evaluatees-dropdown-panel').addClass('hidden');
                $('#toggle-evaluatees-dropdown').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
            }

            reviewerConfigs.forEach(config => {
                const $reviewerWrapper = $(`#${config.dropdownWrapperId}`);
                if ($reviewerWrapper.length && !$reviewerWrapper.is(event.target) && !$reviewerWrapper.has(event.target).length) {
                    $(`#${config.dropdownPanelId}`).addClass('hidden');
                    $(`#${config.dropdownToggleId}`).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                }
            });
        });

        reviewerConfigs.forEach(config => {
            $(`#${config.departmentFilterId}, #${config.positionFilterId}`).on('change', function() {
                filterReviewerOptions(config);
                updateDisplayAndCounts();
            });

            $(`#${config.searchFilterId}`).on('input', function() {
                filterReviewerOptions(config);
                updateDisplayAndCounts();
            });
        });

        $('#evaluatees').on('change', function() {
            updateDisplayAndCounts();
        });

        $(document).on('change', '.evaluatee-checkbox', function() {
            const selectedValues = $('.evaluatee-checkbox:checked').map(function() {
                return String($(this).val());
            }).get();

            $('#evaluatees').val(selectedValues);
            updateDisplayAndCounts();
        });

        $(document).on('change', '.reviewer-checkbox', function() {
            const selectId = $(this).data('select-id');
            const config = reviewerConfigs.find(item => item.selectId === selectId);
            if (!config) {
                return;
            }

            const selectedValue = $(this).is(':checked') ? String($(this).val()) : '';
            $(`#${config.selectId}`).val(selectedValue);
            renderReviewerCheckboxList(config, $(`#${config.selectId} option`).map(function() {
                return $(this).clone();
            }).get());
            updateDisplayAndCounts();
        });

        $('#evaluatees-select-all').on('change', function() {
            const selectedValues = new Set(($('#evaluatees').val() || []).map(String));
            const visibleValues = filteredEvaluateeOptions.map(option => String($(option).val()));

            if ($(this).is(':checked')) {
                visibleValues.forEach(value => selectedValues.add(value));
            } else {
                visibleValues.forEach(value => selectedValues.delete(value));
            }

            $('#evaluatees').val(Array.from(selectedValues));
            renderEvaluateeCheckboxList();
            updateDisplayAndCounts();
        });

        reviewerConfigs.forEach(config => {
            filterReviewerOptions(config);
        });

        updateSummary();

        $('#reset-btn').on('click', function() {
            // reset ต้องล้างทั้งค่าจริงใน form และ state ของ custom dropdown/filter
            if (confirm(formBehaviorConfig.resetConfirmText)) {
                $('#evaluation-form')[0].reset();
                $('#evaluatees').val(null);
                $('#evaluatees-search-filter').val('');
                const $evaluator = $('#evaluator_id').val(null);
                if (formBehaviorConfig.resetEvaluatorWithChangeEvent) {
                    $evaluator.trigger('change');
                }
                $('#director_id').val(null);
                $('#manager_id').val(null);
                reviewerConfigs.forEach(config => {
                    $(`#${config.departmentFilterId}`).val('');
                    $(`#${config.positionFilterId}`).val('');
                    $(`#${config.searchFilterId}`).val('');
                    filterReviewerOptions(config);
                    $(`#${config.dropdownPanelId}`).addClass('hidden');
                    $(`#${config.dropdownToggleId}`).find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                });
                $('#evaluatees-dropdown-panel').addClass('hidden');
                $('#toggle-evaluatees-dropdown').find('i').removeClass('fa-chevron-up').addClass('fa-chevron-down');
                filterEvaluateesByCriteria();
                updateDisplayAndCounts();
                updateSummary();
                alert(formBehaviorConfig.resetSuccessText);
            }
        });

        $('#evaluation-form').on('submit', function(e) {
            // validation ชั้นนี้เป็น client-side guard เพื่อกันการ submit ที่ไม่ครบแบบเร็ว ๆ
            // validation หลักยังอยู่ที่ controller/form validation ฝั่ง server
            const submitButton = $(this).find('button[type="submit"]');
            const loadingOverlay = $('#loading-overlay');

            const evaluateesSelected = $('#evaluatees').val() || [];
            const selectedReviewers = ['#evaluator_id', '#director_id', '#manager_id']
                .map(id => $(id).val())
                .filter(Boolean);
            const reportDataId = $('#report_data_id').val();
            const startTime = $('#start_time').val();
            const endTime = $('#end_time').val();

            if (!reportDataId) {
                e.preventDefault();
                alert(formBehaviorConfig.validation.reportData);
                return false;
            }

            if (!startTime) {
                e.preventDefault();
                alert(formBehaviorConfig.validation.startTime);
                return false;
            }

            if (!endTime) {
                e.preventDefault();
                alert(formBehaviorConfig.validation.endTime);
                return false;
            }

            if (evaluateesSelected.length === 0) {
                e.preventDefault();
                alert(formBehaviorConfig.validation.evaluatees);
                return false;
            }

            if (selectedReviewers.length === 0) {
                e.preventDefault();
                alert(formBehaviorConfig.validation.reviewers);
                return false;
            }

            submitButton.prop('disabled', true).html(formBehaviorConfig.loadingHtml);
            if (loadingOverlay && loadingOverlay.length) {
                loadingOverlay.removeClass('hidden');
            }
        });

        flatpickr('.flatpickr-date', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            locale: 'th',
            allowInput: true
        });

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
