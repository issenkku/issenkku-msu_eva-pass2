        function updateEvalSequence(container, categoryPrefix = '') {
            container.querySelectorAll('.evaluation_list_block').forEach((evalBlock, index) => {
                const evalPrefix = categoryPrefix ? `${categoryPrefix}.${index + 1}` : `${index + 1}`;
                setSequenceInputValue(evalBlock.querySelector('.eval_sequence'), evalPrefix);

                const quantityContainer = evalBlock.querySelector('.quantity_main_criterias_container');
                const qualityContainer = evalBlock.querySelector('.quality_main_criterias_container');
                if (quantityContainer) {
                    updateQuantMainSequence(quantityContainer, evalPrefix);
                }
                if (qualityContainer) {
                    updateQualMainSequence(qualityContainer, evalPrefix);
                }
            });
        }

        function updateCategorySequence(container) {
            container.querySelectorAll('.category_block').forEach((catBlock, index) => {
                const categoryPrefix = `${index + 1}`;
                setSequenceInputValue(catBlock.querySelector('.category_sequence'), categoryPrefix);

                const evalContainer = catBlock.querySelector('.evaluation_lists_container');
                if (evalContainer) {
                    updateEvalSequence(evalContainer, categoryPrefix);
                }
            });
        }

        function updateQuantMainSequence(container, evalPrefix = '') {
            container.querySelectorAll('.quant_criteria_block').forEach((block, idx) => {
                const quantPrefix = evalPrefix ? `${evalPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.quant_main_sequence'), quantPrefix);

                const subContainer = block.querySelector('.quant_sub_criteria_container');
                if (subContainer) {
                    updateQuantSubSequence(subContainer, quantPrefix);
                }
            });
        }

        function updateQuantSubSequence(container, quantPrefix = '') {
            container.querySelectorAll('.quant_sub_criteria_block').forEach((block, idx) => {
                const subPrefix = quantPrefix ? `${quantPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.quant_sub_sequence'), subPrefix);
            });
            updateQuantSubSettingLinks();
        }

        function updateQuantSubSettingLinks() {
            const baseUrl = '/workload-config';
            document.querySelectorAll('.quant_sub_criteria_block').forEach(block => {
                const idInput = block.querySelector('.quant_sub_criteria_id');
                const link = block.querySelector('.quant_sub_setting_btn');
                if (!link) {
                    return;
                }
                const idValue = idInput ? idInput.value.trim() : '';
                link.setAttribute('href', idValue ? `${baseUrl}?quant_sub_criteria_id=${encodeURIComponent(idValue)}` : baseUrl);
            });
        }

        function updateQualMainSequence(container, evalPrefix = '') {
            container.querySelectorAll('.qual_criteria_block').forEach((block, idx) => {
                const qualPrefix = evalPrefix ? `${evalPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.qual_main_sequence'), qualPrefix);

                const subContainer = block.querySelector('.qual_sub_criterias_container');
                if (subContainer) {
                    updateQualSubSequence(subContainer, qualPrefix);
                }
            });
        }

        function updateQualSubSequence(container, qualPrefix = '') {
            container.querySelectorAll('.qual_sub_criteria_block').forEach((block, idx) => {
                const subPrefix = qualPrefix ? `${qualPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.qual_sub_sequence'), subPrefix);
            });
        }

        function refreshOrderUI() {
            updateCategorySequence(document.getElementById('categories_container'));
            updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
            updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
            updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
            updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
        }
