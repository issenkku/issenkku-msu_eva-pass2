        // event handlers ของ dropdown, filter, และ checkbox
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

        $('[data-stage-order-select]').on('change', function() {
            const stageKey = String($(this).data('stage-key') || '');

            normalizeStageOrdersAfterChange(stageKey);
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

        normalizeStageOrdersAfterChange();
        updateSummary();
