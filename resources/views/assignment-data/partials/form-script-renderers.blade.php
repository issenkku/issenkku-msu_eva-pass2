        // ชุดฟังก์ชัน render/filter ของผู้รับการประเมินและผู้ประเมิน
        function updateEvaluateesDropdownLabel() {
            const selectedCount = ($('#evaluatees').val() || []).length;
            const label = selectedCount > 0
                ? `${formBehaviorConfig.evaluateeDropdownLabel} (${selectedCount} ${formBehaviorConfig.countUnit})`
                : formBehaviorConfig.evaluateeDropdownLabel;
            $('#evaluatees-dropdown-label').text(label);
        }

        function renderEvaluateeCheckboxList() {
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

            syncStageOrderOptions();
            updateSummary();
        }

        function updateEvaluateesSelectAllState() {
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
