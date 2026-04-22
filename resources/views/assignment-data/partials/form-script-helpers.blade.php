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

        let filteredEvaluateeOptions = [];
