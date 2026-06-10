        // reset, submit validation, และ init component
        $('#reset-btn').on('click', function() {
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

            if (hasDuplicateStageOrders()) {
                e.preventDefault();
                alert(formBehaviorConfig.validation.stageOrderDuplicate);
                syncStageOrderOptions();
                return false;
            }

            submitButton.prop('disabled', true).html(formBehaviorConfig.loadingHtml);
            if (loadingOverlay && loadingOverlay.length) {
                loadingOverlay.removeClass('hidden');
            }
        });

        document.querySelectorAll('.flatpickr-date').forEach(function(input) {
            flatpickr(input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                locale: 'th',
                allowInput: true,
                onReady: function(selectedDates, dateStr, instance) {
                    const originalId = instance.input.id;

                    if (!originalId || !instance.altInput) {
                        return;
                    }

                    const displayId = `${originalId}_display`;
                    instance.altInput.id = displayId;
                    instance.altInput.name = displayId;
                    instance.altInput.setAttribute('autocomplete', 'off');

                    const label = document.querySelector(`label[for="${originalId}"]`);
                    if (label) {
                        label.setAttribute('for', displayId);
                    }

                    ensureAssignmentRuntimeFieldIds(instance.altInput);
                    ensureAssignmentRuntimeFieldIds(instance.calendarContainer);
                }
            });
        });

        observeAssignmentRuntimeFields();

        window.setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                    bsAlert.close();
                }
            });
        }, 5000);
