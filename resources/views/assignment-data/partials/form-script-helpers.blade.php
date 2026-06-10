        // helper กลางสำหรับ normalize ค่าและสร้าง HTML ของรายการที่เลือก
        const formBehaviorConfig = @json($formBehaviorConfig);
        const evaluateeOptionTemplate = $('#evaluatees option').map(function() {
            return $(this).clone();
        }).get();
        const evaluateeOptionMap = Object.fromEntries(
            evaluateeOptionTemplate.map(option => [String($(option).val()), $(option).clone()])
        );

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

        function getSelectedStageOrderEntries() {
            return reviewerConfigs
                .map(config => ({
                    config,
                    reviewerId: String($(`#${config.selectId}`).val() || ''),
                    order: String($(`#${config.stageOrderId}`).val() || ''),
                }))
                .filter(entry => entry.reviewerId && entry.order);
        }

        function hasDuplicateStageOrders() {
            const orders = getSelectedStageOrderEntries().map(entry => entry.order);

            return orders.length !== new Set(orders).size;
        }

        function syncStageOrderOptions() {
            const selectedEntries = getSelectedStageOrderEntries();

            reviewerConfigs.forEach(config => {
                const $select = $(`#${config.stageOrderId}`);
                const currentValue = String($select.val() || '');
                const usedByOtherActiveReviewers = new Set(
                    selectedEntries
                        .filter(entry => entry.config.stageKey !== config.stageKey)
                        .map(entry => entry.order)
                );

                $select.find('option').each(function() {
                    const optionValue = String($(this).val() || '');
                    $(this).prop('disabled', optionValue !== currentValue && usedByOtherActiveReviewers.has(optionValue));
                });
            });
        }

        let filteredEvaluateeOptions = [];
