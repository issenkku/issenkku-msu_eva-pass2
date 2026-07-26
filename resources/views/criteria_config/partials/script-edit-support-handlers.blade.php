        function toggleSupportIndicatorMode(block) {
            const allow = block.querySelector('.support_allow_activity_entries');
            const grouped = block.querySelector('.support_group_by_indicator');
            const allowEvaluateeIndicator = block.querySelector('.support_allow_evaluatee_indicator');
            const allowEvaluateeWeight = block.querySelector('.support_allow_evaluatee_weight');
            if (!allow || !grouped) return;

            if (!allow.checked) {
                grouped.checked = false;
                if (allowEvaluateeIndicator) allowEvaluateeIndicator.checked = false;
                if (allowEvaluateeWeight) allowEvaluateeWeight.checked = false;
            }
            grouped.disabled = !allow.checked;
            if (allowEvaluateeIndicator) allowEvaluateeIndicator.disabled = !allow.checked;
            if (allowEvaluateeWeight) allowEvaluateeWeight.disabled = !allow.checked;
            const indicatorInput = block.querySelector('.support_indicator');
            const evaluateeOwnsIndicator = Boolean(allowEvaluateeIndicator?.checked);
            if (indicatorInput) {
                if (evaluateeOwnsIndicator) indicatorInput.value = '';
                const $indicatorInput = $(indicatorInput);
                if (evaluateeOwnsIndicator
                    && $indicatorInput.next('.note-editor').length > 0
                    && typeof $indicatorInput.summernote === 'function') {
                    $indicatorInput.summernote('code', '');
                }
            }
            const weightInput = block.querySelector('.support_weight');
            const evaluateeOwnsWeight = Boolean(allowEvaluateeWeight?.checked);
            if (weightInput) {
                if (evaluateeOwnsWeight) weightInput.value = '';
                weightInput.disabled = evaluateeOwnsWeight;
            }
            block.querySelector('[data-support-weight-required]')
                ?.classList.toggle('hidden', evaluateeOwnsWeight);
            block.querySelector('[data-support-legacy-indicator]')
                ?.classList.toggle('hidden', grouped.checked || evaluateeOwnsIndicator);
            block.querySelector('.support_indicator_items')
                ?.classList.toggle('hidden', !grouped.checked);
        }

        function updateSupportIndicatorItemSequences(block) {
            block.querySelectorAll('.support_indicator_item_block').forEach((item, index) => {
                item.querySelector('.support_indicator_sequence').value = index + 1;
            });
        }

        function collectSupportIndicatorItems(block) {
            return Array.from(block.querySelectorAll('.support_indicator_item_block'))
                .map((item, index) => ({
                    ...(item.querySelector('.support_indicator_item_id').value
                        ? { support_indicator_item_id: Number(item.querySelector('.support_indicator_item_id').value) }
                        : {}),
                    sequence: index + 1,
                    code: item.querySelector('.support_indicator_code').value.trim(),
                }));
        }

        function populateSupportIndicatorItems(block, indicatorItems) {
            const list = block.querySelector('.support_indicator_item_list');
            const template = list.querySelector('.support_indicator_item_block').cloneNode(true);

            list.querySelectorAll('.support_indicator_item_block').forEach((item) => {
                resetSummernoteClone(item);
                item.remove();
            });

            const rows = indicatorItems.length > 0 ? indicatorItems : [{}];
            rows.forEach((item, index) => {
                const row = template.cloneNode(true);
                resetSummernoteClone(row);
                row.querySelector('.support_indicator_item_id').value = item.support_indicator_item_id || item.id || '';
                row.querySelector('.support_indicator_sequence').value = index + 1;
                row.querySelector('.support_indicator_code').value = item.code || '';
                list.appendChild(row);
            });
        }

        function addSupportIndicatorItem(block) {
            const list = block.querySelector('.support_indicator_item_list');
            const item = list.querySelector('.support_indicator_item_block').cloneNode(true);
            resetSummernoteClone(item);
            item.querySelectorAll('input').forEach((input) => input.value = '');
            list.appendChild(item);
            updateSupportIndicatorItemSequences(block);
            initializeSummernote(item);
            markDirty();
        }

        function deleteSupportIndicatorItem(item) {
            const block = item.closest('.support_criteria_block');
            if (block.querySelectorAll('.support_indicator_item_block').length === 1) {
                showError('ต้องมีตัวชี้วัดย่อยอย่างน้อย 1 ข้อ');
                return;
            }

            resetSummernoteClone(item);
            item.remove();
            updateSupportIndicatorItemSequences(block);
            markDirty();
        }

        function populateSupportCriteria(container, supportData) {
            const template = document.querySelector('.support_criteria_block');
            const itemsContainer = container.querySelector('.support_criteria_items');
            itemsContainer.querySelectorAll('.support_criteria_block').forEach((block) => {
                resetSummernoteClone(block);
                block.remove();
            });

            supportData.forEach((item) => {
                const block = template.cloneNode(true);
                resetSummernoteClone(block);
                block.querySelector('.support_criteria_id').value = item.support_criteria_id || item.id || '';
                block.querySelector('.support_activity_name').value = item.activity_name || '';
                block.querySelector('.support_indicator').value = item.indicator || '';
                block.querySelector('.support_target_value').value = item.target_value ?? '';
                block.querySelector('.support_weight').value = item.weight ?? '';
                block.querySelector('.support_require_evidence').checked = Boolean(item.require_evidence);
                block.querySelector('.support_allow_activity_entries').checked = Boolean(item.allow_activity_entries);
                block.querySelector('.support_allow_evaluatee_indicator').checked = Boolean(item.allow_evaluatee_indicator);
                block.querySelector('.support_allow_evaluatee_weight').checked = Boolean(item.allow_evaluatee_weight);
                block.querySelector('.support_group_by_indicator').checked = Boolean(item.group_activity_entries_by_indicator);
                populateSupportIndicatorItems(block, item.indicator_items || []);
                toggleSupportIndicatorMode(block);
                itemsContainer.appendChild(block);
                initializeSummernote(block);
            });
        }

        function addSupportCriteria(evaluationBlock) {
            const container = evaluationBlock.querySelector('.support_criteria_items');
            const template = document.querySelector('.support_criteria_block');
            const block = template.cloneNode(true);
            resetSummernoteClone(block);
            clearIdentityAttributes(block);
            block.querySelectorAll('.support_indicator_item_block:not(:first-child)').forEach((item) => item.remove());
            block.querySelectorAll('input').forEach((input) => {
                input.value = '';
                if (input.type === 'checkbox') input.checked = false;
            });
            block.querySelectorAll('textarea.richtext-editor').forEach((textarea) => {
                textarea.value = '';
            });
            toggleSupportIndicatorMode(block);
            container.appendChild(block);
            initializeSummernote(block);
            updateSequences();
            markDirty();
        }

        function deleteSupportCriteria(block) {
            const container = block.closest('.support_criteria_items');
            if (container.querySelectorAll('.support_criteria_block').length === 1) {
                showError('ต้องมีเกณฑ์สายสนับสนุนอย่างน้อย 1 รายการ');
                return;
            }

            resetSummernoteClone(block);
            block.remove();
            updateSequences();
            markDirty();
        }
