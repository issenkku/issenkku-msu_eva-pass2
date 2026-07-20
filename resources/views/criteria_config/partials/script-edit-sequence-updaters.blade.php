        // Helper สำหรับอัปเดตลำดับและลิงก์ตั้งค่าของรายการย่อย
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

        function updateSequences() {
            document.querySelectorAll('.category_block:not([style*="display: none"])').forEach((catBlock, index) => {
                const categoryPrefix = `${index + 1}`;
                setSequenceInputValue(catBlock.querySelector('.category_sequence'), categoryPrefix);

                catBlock.querySelectorAll('.evaluation_lists_container').forEach(container => {
                    container.querySelectorAll('.evaluation_list_block').forEach((evalBlock, evalIndex) => {
                        const evalPrefix = `${categoryPrefix}.${evalIndex + 1}`;
                        setSequenceInputValue(evalBlock.querySelector('.eval_sequence'), evalPrefix);

                        evalBlock.querySelectorAll('.quantity_main_criterias_container').forEach(quantityContainer => {
                            quantityContainer.querySelectorAll('.quant_criteria_block').forEach((block, quantIndex) => {
                                const quantPrefix = `${evalPrefix}.${quantIndex + 1}`;
                                setSequenceInputValue(block.querySelector('.quant_main_sequence'), quantPrefix);

                                block.querySelectorAll('.quant_sub_criteria_container').forEach(subContainer => {
                                    subContainer.querySelectorAll('.quant_sub_criteria_block').forEach((subBlock, subIndex) => {
                                        setSequenceInputValue(subBlock.querySelector('.quant_sub_sequence'), `${quantPrefix}.${subIndex + 1}`);
                                    });
                                });
                            });
                        });

                        evalBlock.querySelectorAll('.quality_main_criterias_container').forEach(qualityContainer => {
                            qualityContainer.querySelectorAll('.qual_criteria_block').forEach((block, qualIndex) => {
                                const qualPrefix = `${evalPrefix}.${qualIndex + 1}`;
                                setSequenceInputValue(block.querySelector('.qual_main_sequence'), qualPrefix);

                                block.querySelectorAll('.qual_sub_criterias_container').forEach(subContainer => {
                                    subContainer.querySelectorAll('.qual_sub_criteria_block').forEach((subBlock, subIndex) => {
                                        setSequenceInputValue(subBlock.querySelector('.qual_sub_sequence'), `${qualPrefix}.${subIndex + 1}`);
                                    });
                                });
                            });
                        });

                        evalBlock.querySelectorAll('.support_criteria_block').forEach((supportBlock, supportIndex) => {
                            setSequenceInputValue(
                                supportBlock.querySelector('.support_sequence'),
                                `${evalPrefix}.${supportIndex + 1}`
                            );
                        });
                    });
                });
            });

            updateQuantSubSettingLinks();
        }
