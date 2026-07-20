        function populateSupportCriteria(container, supportData) {
            const template = document.querySelector('.support_criteria_block');
            const itemsContainer = container.querySelector('.support_criteria_items');
            itemsContainer.querySelectorAll('.support_criteria_block').forEach((block) => block.remove());

            supportData.forEach((item) => {
                const block = template.cloneNode(true);
                block.querySelector('.support_criteria_id').value = item.support_criteria_id || item.id || '';
                block.querySelector('.support_activity_name').value = item.activity_name || '';
                block.querySelector('.support_indicator').value = item.indicator || '';
                block.querySelector('.support_target_value').value = item.target_value ?? '';
                block.querySelector('.support_weight').value = item.weight ?? '';
                block.querySelector('.support_require_evidence').checked = Boolean(item.require_evidence);
                itemsContainer.appendChild(block);
            });
        }

        function addSupportCriteria(evaluationBlock) {
            const container = evaluationBlock.querySelector('.support_criteria_items');
            const template = document.querySelector('.support_criteria_block');
            const block = template.cloneNode(true);
            clearIdentityAttributes(block);
            block.querySelectorAll('input').forEach((input) => {
                input.value = '';
                if (input.type === 'checkbox') input.checked = false;
            });
            container.appendChild(block);
            updateSequences();
            markDirty();
        }

        function deleteSupportCriteria(block) {
            const container = block.closest('.support_criteria_items');
            if (container.querySelectorAll('.support_criteria_block').length === 1) {
                showError('ต้องมีเกณฑ์สายสนับสนุนอย่างน้อย 1 รายการ');
                return;
            }

            block.remove();
            updateSequences();
            markDirty();
        }
